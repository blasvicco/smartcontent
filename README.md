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

Create the DB and import the structure with db/smartcontent.sql

Edit the config files and execute composer install

```sh
$ composer install -vvv
```

Composer install could overwrite the next files:
  - vendor/google/apiclient/GoogleClient.php
  - vendor/google/apiclient/GoogleServiceGmail.php
  - vendor/google/apiclient/GoogleServiceGmailModifyThreadRequest.php
In this case just get it from the repo again ;)

Give permission to the var folder for symfony:
```sh
$ chmod 0777 var/cache/ -R
$ chmod 0777 var/logs/ -R
$ chmod 0777 var/sessions/ -R
```

Performe the first google api call from console (you will need the client_secret.json from Google API).
```sh
$ php bin/console ProcessEmail
```

### Run PHPUnit test
```sh
$ cd cloned-folder-name
$ vendor/bin/phpunit
```
