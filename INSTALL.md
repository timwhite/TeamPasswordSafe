# Installation

 1. Clone git repository ```git clone https://github.com/timwhite/TeamPasswordSafe.git```
 2. Change directory ```cd TeamPasswordSafe```
 3. Install dependencies (assumes you already have composer installed) ```composer install``
    This will also prompt you for database settings
 4. Create the database tables ```bin/console doctrine:schema:create```
 5. Patch JMSTranslationBundle ```
wget https://patch-diff.githubusercontent.com/raw/schmittjoh/JMSTranslationBundle/pull/285.diff
cd vendor/jms/translation-bundle/JMS/TranslationBundle
patch -p 1  < ../../../../../285.diff
cd ../../../../../
```
 6. Point your webserver to the web directory of the TeamPasswordSafe directory and following Symfony hosting instructions <http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html>

