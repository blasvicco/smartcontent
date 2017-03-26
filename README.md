# SmartContent
This is a complete example of a Symfony project.
At this point, users can use this WebApp in order to create Google Alerts and review the contents that SmartContent will automatically retrieve from the Google Alert emails.
SmartContent will connect to Gmail and read the emails from Google Alerts. It will capture the links and put them in a queue to be processed.
SmartContent will read the links from the queue and will try to retrieve the content. After that, it will classify and summarize them.
Users will be able to list, filter and review all the content processed for the WebApp.

##### Live project:
http://www.smartcontent.tk

##### Some of the technologies used in this project are:
  - Symfony 3 (http://symfony.com/)
  - MySql/MariaDb (https://mariadb.org/)
  - FOSUserBundle (https://github.com/FriendsOfSymfony/FOSUserBundle)

##### Some external libs:
  - Gmail APIClient (https://developers.google.com/gmail/api/quickstart/php)
  - JQuery (https://jquery.com/)
  - Bootstrap 3.0 (http://getbootstrap.com/)

##### Some libs developed here:
  - API for Google Alerts
  - Summarizer using word/sentence scoring

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

Composer install could overwrite the following files:
  - vendor/google/apiclient/GoogleClient.php
  - vendor/google/apiclient/GoogleServiceGmail.php
  - vendor/google/apiclient/GoogleServiceGmailModifyThreadRequest.php

In this case just get it from the repo again ;)

Give permission to the var folder for Symfony:
```sh
$ chmod 0777 var/cache/ -R
$ chmod 0777 var/logs/ -R
$ chmod 0777 var/sessions/ -R
```

Perform the first Google API call from the console (you will need the client_secret.json from Google's API).
```sh
$ php bin/console ProcessEmail
```

### Run PHPUnit test
```sh
$ cd cloned-folder-name
$ vendor/bin/phpunit
```
