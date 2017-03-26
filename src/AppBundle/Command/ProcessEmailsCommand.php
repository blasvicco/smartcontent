<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\GoogleAlert;
use AppBundle\Entity\SmartContent;

define('APPLICATION_NAME', 'Gmail API PHP Symfony Command Script');
define('CREDENTIALS_PATH', __DIR__ . '/../../../app/config/gmail_php.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/../../../app/config/client_secret.json');
define('SCOPES', implode(' ', [
	\GoogleServiceGmail::GMAIL_MODIFY
]));
if (php_sapi_name() != 'cli') {throw new Exception('This application must be run on the command line.');}

class ProcessEmailsCommand extends ContainerAwareCommand {

	/**
	 * Returns an authorized API client.
	 *
	 * @return Google_Client the authorized client object
	 */
	private function getClient() {
		$client = new \GoogleClient();
		$client->setApplicationName(APPLICATION_NAME);
		$client->setScopes(SCOPES);
		$client->setAuthConfigFile(CLIENT_SECRET_PATH);
		$client->setAccessType('offline');
		// Load previously authorized credentials from a file.
		$credentialsPath = CREDENTIALS_PATH;
		if (file_exists($credentialsPath)) {
			$accessToken = file_get_contents($credentialsPath);
		} else {
			// Request authorization from the user.
			$authUrl = $client->createAuthUrl();
			echo 'Open the following link in your browser:' . "\n" . $authUrl . "\n";
			echo 'Enter verification code: ';
			$authCode = trim(fgets(STDIN));
			// Exchange authorization code for an access token.
			$accessToken = $client->authenticate($authCode);
			// Store the credentials to disk.
			if (!file_exists(dirname($credentialsPath))) {
				mkdir(dirname($credentialsPath), 0700, true);
			}
			file_put_contents($credentialsPath, $accessToken);
			echo 'Credentials saved to ' . $credentialsPath . "\n";
			exit;
		}
		$client->setAccessToken($accessToken);
		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->refreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, $client->getAccessToken());
		}
		return $client;
	}

	private function listNewMessages($service, $userId) {
		$pageToken = NULL;
		$messages = [];
		$optParam = [
			'q' => 'is:unread'
		];
		do {
			try {
				if ($pageToken) {
					$optParam['pageToken'] = $pageToken;
				}
				$messagesResponse = $service->users_messages->listUsersMessages($userId, $optParam);
				if ($messagesResponse->getMessages()) {
					$messages = array_merge($messages, $messagesResponse->getMessages());
					$pageToken = $messagesResponse->getNextPageToken();
				}
			} catch (Exception $e) {
				echo 'An error occurred: ' . $e->getMessage() . "\n";
			}
		} while ($pageToken);
		foreach ($messages as $message) {
			echo 'Message with ID: ' . $message->getId() . "\n";
		}
		return $messages;
	}

	/**
	 * Get Message with given ID.
	 *
	 * @param Google_Service_Gmail $service
	 *        	Authorized Gmail API instance.
	 * @param string $userId
	 *        	User's email address. The special value 'me'
	 *        	can be used to indicate the authenticated user.
	 * @param string $messageId
	 *        	ID of Message to get.
	 * @return Google_Service_Gmail_Message Message retrieved.
	 */
	private function getMessage($service, $userId, $messageId, $format = 'full') {
		try {
			$message = $service->users_messages->get($userId, $messageId, [
				'format' => $format
			]);
			echo 'Message with ID: ' . $message->getId() . ' retrieved.' . "\n";
			return $message;
		} catch (Exception $e) {
			echo 'An error occurred: ' . $e->getMessage() . "\n";
		}
	}

	/**
	 * Modify the Labels applied to a Thread.
	 *
	 * @param Google_Service_Gmail $service
	 *        	Authorized Gmail API instance.
	 * @param string $userId
	 *        	User's email address. The special value 'me'
	 *        	can be used to indicate the authenticated user.
	 * @param string $threadId
	 *        	ID of Thread to modify.
	 * @param array $labelsToAdd
	 *        	Array of Labels to add.
	 * @param array $labelsToRemove
	 *        	Array of Labels to remove.
	 * @return Google_Service_Gmail_Thread Modified Thread.
	 */
	private function modifyThread($service, $userId, $threadId, $labelsToAdd, $labelsToRemove) {
		$mods = new \GoogleServiceGmailModifyThreadRequest();
		$mods->setAddLabelIds($labelsToAdd);
		$mods->setRemoveLabelIds($labelsToRemove);
		try {
			$thread = $service->users_threads->modify($userId, $threadId, $mods);
			echo 'Thread with ID: ' . $threadId . ' successfully modified.';
			return $thread;
		} catch (Exception $e) {
			echo 'An error occurred: ' . $e->getMessage();
		}
	}

	private function decodeBody($body) {
		$rawData = $body;
		$sanitizedData = strtr($rawData, '-_', '+/');
		$decodedMessage = base64_decode($sanitizedData);
		if (!$decodedMessage) $decodedMessage = FALSE;
		return $decodedMessage;
	}

	private function decodeAndReadEmail($payload) {
		// With no attachment, the payload might be directly in the body, encoded.
		$body = $payload->getBody();
		$foundBody = $this->decodeBody($body['data']);
		// If we didn't find a body, let's look for the parts
		if (!$foundBody) {
			$parts = $payload->getParts();
			foreach ($parts as $part) {
				if ($part['body']) {
					$foundBody = $this->decodeBody($part['body']->data);
					break;
				}
				// Last try: if we didn't find the body in the first parts,
				// let's loop into the parts of the parts (as @Tholle suggested).
				if ($part['parts'] && !$foundBody) {
					foreach ($part['parts'] as $p) {
						// replace 'text/html' by 'text/plain' if you prefer
						if ($p['mimeType'] === 'text/html' && $p['body']) {
							$foundBody = $this->decodeBody($p['body']->data);
							break;
						}
					}
				}
				if ($foundBody) break;
			}
		}
		// Finally, print the message ID and the body
		return $foundBody;
	}

	private function getByUserIdAndKeyword($userId, $keyword) {
		$doctrine = $this->getContainer()->get('doctrine');
		$googleAlertRepository = $doctrine->getRepository('AppBundle:GoogleAlert');
		return $googleAlertRepository->findOneBy([
			'userId' => $userId, 
			'keyword' => $keyword
		]);
	}

	private function unHashId ($id) {
		$c = ['!' => 1, '"' => 2, '£' => 3, '$' => 4, '%' => 4, '&' => 6, '/' => 7, '(' => 8, ')' => 9, '=' => 10];
		$id = str_split($id);
		$unHash = '';
		foreach ($id as $cid) {
				$unHash .= $c[$cid] ? $c[$cid] : '';
			}
		return $unHash;
	}

	private function parseEmail($emailContent) {
		// get user id
		$userId = null;
		$matches = [];
		$regexp = '\?\W+?\?';
		if (preg_match_all('/' . $regexp . '/', $emailContent, $matches)) {
				$userId = $matches[0] ? str_replace('?', '', $matches[0][0]) : null;
				$userId = $this->unHashId($userId);
		}
		// get google alert words
		$keywords = null;
		$matches = [];
		$regexp = '\["(.+)"\s\?[0-9]+?\?\]';
		if (preg_match_all('/' . $regexp . '/', $emailContent, $matches)) {
			$keywords = $matches[1][0] ? str_replace("'", '', $matches[1][0]) : null;
		}
		// get urls
		$googleLinks = [];
		$links = [];
		$regexp = '[A-Za-z]+:\/\/[A-Za-z0-9-_]+.[A-Za-z0-9-_:%&;\?#\/.=]+';
		if (preg_match_all('/' . $regexp . '/i', $emailContent, $googleLinks)) {
			$regexp = 'url=(.*)?(?=&ct)';
			$matches = [];
			foreach ($googleLinks[0] as $match) {
				if (preg_match_all('/' . $regexp . '/i', $match, $matches)) {
					if (!empty($matches[1][0])) {
						$links[] = $matches[1][0];
					}
				}
			}
		}
		if (empty($userId)) return;
		$googleAlert = $this->getByUserIdAndKeyword($userId, $keywords);
		if (empty($googleAlert)) return;
		$em = $this->getContainer()->get('doctrine')->getManager();
		foreach ($links as $url) {
			$smartContent = new SmartContent();
			$smartContent->setUserId($userId);
			$smartContent->setGoogleAlertId($googleAlert->getGoogleAlertId());
			$smartContent->setUrl($url);
			$smartContent->setContent('Parse from: ' . $url);
			$smartContent->setCreated(new \DateTime());
			$smartContent->setStatus('queued');
			$em->persist($smartContent);
			$em->flush();
		}
	}

	protected function configure() {
		$this->setName('ProcessEmails')->setDescription('Pre processing the google alert emails to create content in queued status');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$userId = $this->getContainer()->getParameter('googleId');
		$output->writeln('Getting message for: ' . $userId);
		$client = $this->getClient();
		$gmail = new \GoogleServiceGmail($client);
		$mails = $this->listNewMessages($gmail, $userId);
		foreach ($mails as $mail) {
			$emailContent = $this->getMessage($gmail, $userId, $mail->getId());
			$this->parseEmail($this->decodeAndReadEmail($emailContent->getPayload()));
			$this->modifyThread($gmail, $userId, $mail->getThreadId(), [], ['UNREAD']);
		}
		$output->writeln('Executed');
	}
}
?>