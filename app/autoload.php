<?php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;
/**
 *
 * @var ClassLoader $loader
 */
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addClassMap(array(
	'GoogleServiceGmail' => __DIR__ . '/../vendor/google/apiclient/GoogleServiceGmail.php', 
	'GoogleServiceGmailModifyThreadRequest' => __DIR__ . '/../vendor/google/apiclient/GoogleServiceGmailModifyThreadRequest.php', 
	'GoogleClient' => __DIR__ . '/../vendor/google/apiclient/GoogleClient.php', 
	'GoogleApi' => __DIR__ . '/../vendor/google/lib/Google.php', 
	'CountryCode' => __DIR__ . '/../vendor/countrycode/lib/CountryCode.php', 
	'SummarizerPro' => __DIR__ . '/../vendor/summarizer/lib/Summarizer.php'
));
AnnotationRegistry::registerLoader([
	$loader, 'loadClass']);

return $loader;
