<?php

class Api {
	private $username = '';
	private $password = '';
	private $file = 'cookies.txt';

	function init($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	
	function login() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/ServiceLogin?hl=en&service=alerts&continue=http://www.google.com/alerts/manage');
		$data = curl_exec($ch);
		$formFields = $this->getLoginFormFields($data);
		$formFields['Email'] = $this->username;
		$formFields['Passwd'] = $this->password;
		unset($formFields['PersistentCookie']);
		$post_string = '';
		foreach ($formFields as $key => $value) {
			$post_string .= $key . '=' . urlencode($value) . '&';
		}
		$post_string = substr($post_string, 0, - 1);
		curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/ServiceLoginAuth');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);
		if (strpos($result, '<title>Redir') === false) {
			//var_dump($result);
			throw new Exception('Google Api Login FAIL.');
		} else {
			return $ch;
		}
	}

	function createAlert($ch, $input = []) {
		curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/alerts/manage');
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, null);
		$data = curl_exec($ch);
		$result = $this->getAlertHiddenData($data);
		$result[2][3][1] = $input['keywords'];
		$result = $this->setOften($input, $result);
		$result = $this->setLang($input, $result);
		$result = $this->setCountry($input, $result);
		$toPost = [null, $result[2]];
		$param = 'params='.json_encode($toPost);
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/alerts/create?x='.$result[3]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		$result = curl_exec($ch);
		$result = json_decode($result);
		if (isset($result[4][0][1])) {
			return $result;
		}
		return false;
	}
	
	function deleteAlert($ch, $id) {
		curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/alerts/manage');
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, null);
		$data = curl_exec($ch);
		$result = $this->getAlertHiddenData($data);
		$toPost = [null, $id];
		$param = 'params='.json_encode($toPost);
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/alerts/delete?x='.$result[3]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		$result = curl_exec($ch);
		$result = json_decode($result);
		return isset($result[3]);
	}
	
	function setOften($input, $result) {
		switch ($input['often']) {
			case 'asItHappens': {
				$result[2][6][0][3] = [];
				$result[2][6][0][4] = 1;
			} break;
			case 'onceADay': {
				$result[2][6][0][3] = [null, null, 12];
				$result[2][6][0][4] = 2;
			} break;
			case 'onceAWeek':
			default: {
				$result[2][6][0][3] = [null, null, 12, 6];
				$result[2][6][0][4] = 3;
			} break;
		}
		return $result;
	}
	
	function setLang($input, $result) {
		$result[2][3][3][1] = !empty($input['lang']) ? $input['lang'] : 'en';
		return $result;
	}
	
	function setCountry($input, $result) {
		$result[2][3][3][2] = !empty($input['country']) ? $input['country'] : 'US';
		return $result;
	}

	function getLoginFormFields($data) {
		if (preg_match('/(<form.*?id=.?gaia_loginform.*?<\/form>)/is', $data, $matches)) {
			$inputs = $this->getInputs($matches[1]);
			return $inputs;
		}
		//var_dump($result);
		throw new Exception('Google Api login form not found.');
	}

	function getAlertHiddenData($data) {
		if (preg_match('/window.STATE = (.+?)(?=;)/', $data, $matches)) {
			$hiddenData = json_decode($matches[1]);
			return $hiddenData;
		}
		//var_dump($result);
		throw new Exception('Google Api hidden data not found.');
	}

	function getInputs($form) {
		$inputs = array();
		$elements = preg_match_all('/(<input[^>]+>)/is', $form, $matches);
		if ($elements > 0) {
			for ($i = 0; $i < $elements; $i ++) {
				$el = preg_replace('/\s{2,}/', ' ', $matches[1][$i]);
				if (preg_match('/name=(?:["\'])?([^"\'\s]*)/i', $el, $name)) {
					$name = $name[1];
					$value = '';
					if (preg_match('/value=(?:["\'])?([^"\'\s]*)/i', $el, $value)) {
						$value = $value[1];
					}
					$inputs[$name] = $value;
				}
			}
		}
		return $inputs;
	}
}
?>