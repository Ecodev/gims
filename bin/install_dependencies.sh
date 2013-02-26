#!/usr/bin/env bash

echo "Installing PostGIS and other packages..."
sudo apt-get install -qq software-properties-common # to get next command: add-apt-repository
sudo add-apt-repository --yes ppa:ubuntugis/ubuntugis-unstable
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get -qq update
sudo apt-get -qq install postgis postgresql-9.1-postgis rubygems npm apache2 php5-pgsql php5-cli php5-gd php5-mcrypt php5-intl

echo "Installing Compass..."
sudo gem install --quiet --no-rdoc --no-ri sass compass oily_png bootstrap-sass

echo "Installing JS testing tools..."
sudo npm --global --quiet install testacular phantomjs

# For Travis CI, we need more configuration (Apache and database)
if [[ "$1" = "travis" ]]; then
    echo "Configuring Apache..."
    WEBROOT="$(pwd)/htdocs"
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
    </VirtualHost>" | sudo tee /etc/apache2/sites-available/default > /dev/null

    echo "Configuring PHP..."
    echo "export PATH=/home/vagrant/.phpenv/bin:$PATH" | sudo tee -a /etc/apache2/envvars > /dev/null
    echo "# PHPENV Setup
    <IfModule alias_module>
        ScriptAlias /phpenv '/home/vagrant/.phpenv/shims'
        <Directory '/home/vagrant/.phpenv/shims'>
            Order allow,deny
            Allow from all
        </Directory>
    </IfModule>

    <IfModule mime_module>
        AddType application/x-httpd-php5 .php
    </IfModule>

    <IfModule dir_module>
        DirectoryIndex index.php index.html
    </IfModule>

    Action application/x-httpd-php5 '/phpenv/php-cgi'" | sudo tee /etc/apache2/conf.d/phpconfig > /dev/null
    sudo a2enmod actions
    sudo a2enmod rewrite
    sudo service apache2 restart

    echo "Configuring custom domain..."
    echo "127.0.0.1 gims.local" | sudo tee --append /etc/hosts

    echo "Init database..."
    cp config/autoload/local.php.dist config/autoload/local.php
    psql -c 'create database gims;' -U postgres
fi

echo "Installing all PHP dependencies via composer..."
./composer.phar install --dev
