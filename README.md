# SmartContent
This is a complete example of a symfony project.
At this point, users can use this WebApp in order to create google alerts and review the contents that SmartContent will automatically retrieve from the google alert emails.
SmartContent will connect to google gmail and read the emails from google alerts. It will capture the links and put them in a queue to be processed.
SmartContent will read the links from the queue and will try to retrive the content. After that, It will classify and summarize them.
Users will be able to list, filter and review all the content processed for the WebApp.

##### Online running project:
Soon!!!

##### Some of the technologies used in this projects are:
  - Symfony 3 (http://symfony.com/)
  - MySql/MariaDb (https://mariadb.org/)
  - FOSUserBundle

##### Some external libs:
  - Gmail APIClient (https://developers.google.com/gmail/api/quickstart/php)
  - JQuery (https://jquery.com/)
  - Bootstrap 3.0 (http://getbootstrap.com/)

##### Some libs developed here:
  - API for google alerts
  - Summarizer using words/sentences scoring

### Installation
```sh
$ git clone [git-repo-url] folder-name
$ cd folder-name
```
Edit the config files
Performe the first google api call from console and move the gmail_php.json and the client_secret.json to the config file if is necessary
```sh
$ php bin/console ProcessEmail
```

### Run PHPUnit test
```sh
$ cd cloned-folder-name
$ vendor/bin/phpunit
```
