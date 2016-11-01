#!/usr/bin/env bash

echo "Installing Compass..."
GEMS="gem install --quiet --no-rdoc --no-ri sass compass oily_png bootstrap-sass autoprefixer-rails"
if [[ -z $TRAVIS ]]; then # If not on Travis, need to use sudo
   GEMS="sudo $GEMS"
fi
`$GEMS`

# Exit script on any error
set -e

echo "Installing apache2 and other packages..."
sudo apt-get -qq update
sudo apt-get install -qq software-properties-common # to get next command: add-apt-repository
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get -qq update
sudo apt-get -qq install nodejs apache2

echo "Installing php-cs-fixer..."
sudo wget http://cs.sensiolabs.org/get/php-cs-fixer.phar -O /usr/local/bin/php-cs-fixer
sudo chmod a+x /usr/local/bin/php-cs-fixer

# For Travis only
if [[ ! -z $TRAVIS ]]; then
    # Configure xdebug with a higher max_nesting_level
    for file in `find /home/vagrant/.phpenv -name 'xdebug.ini'`; do echo "xdebug.max_nesting_level=5000" >> $file; done;
    phpenv config-add config/travis-php.ini
else
    sudo apt-get -qq install redis-server
fi

# For Travis CI, or full local install, we need more configuration (Apache and database)
if [[ "$1" = "configure" ]]; then
    echo "Configuring Apache..."
    WEBROOT="$(pwd)/htdocs"
    CGIROOT=`dirname "$(which php-cgi)"`
    sudo echo "<VirtualHost *:80>
            DocumentRoot $WEBROOT
            <Directory />
                    Options FollowSymLinks
                    AllowOverride All
            </Directory>
            <Directory $WEBROOT >
                    Options Indexes FollowSymLinks MultiViews
                    AllowOverride All
                    Order allow,deny
                    allow from all
            </Directory>

            # Configure PHP as CGI
            ScriptAlias /local-bin $CGIROOT
            DirectoryIndex index.php index.html
            AddType application/x-httpd-php5 .php
            Action application/x-httpd-php5 '/local-bin/php-cgi'
    </VirtualHost>" | sudo tee /etc/apache2/sites-available/default > /dev/null

    sudo a2enmod actions
    sudo a2enmod rewrite
    sudo a2enmod headers
    sudo service apache2 restart

    echo "Configuring custom domain..."
    echo "127.0.0.1 gims.lan" | sudo tee --append /etc/hosts

    echo "Init database..."

    which mysql
    mysql --version
    mysql -e 'CREATE DATABASE gims CHARACTER SET utf8 COLLATE utf8_unicode_ci;'
    cp config/autoload/local.php.dist config/autoload/local.php
fi
