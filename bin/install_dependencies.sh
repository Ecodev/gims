#!/usr/bin/env bash

echo "Installing Compass..."
GEMS="gem install --quiet --no-rdoc --no-ri sass compass oily_png bootstrap-sass"
if [[ -z $TRAVIS ]]; then # If not on Travis, need to use sudo
   GEMS="sudo $GEMS"
fi
`$GEMS`

# Exit script on any error
set -e

echo "Installing PostGIS and other packages..."
sudo apt-get -qq update
sudo apt-get install -qq software-properties-common # to get next command: add-apt-repository
sudo add-apt-repository --yes ppa:ubuntugis/ubuntugis-unstable
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get -qq update
sudo apt-get -qq install postgresql-9.1-postgis-2.0 rubygems nodejs apache2 php5-pgsql php5-cli php5-gd php5-mcrypt php5-intl php5-json

echo "Installing JS testing tools..."

sudo npm --global --quiet install karma karma-ng-scenario phantomjs uglify-js ngmin bower jshint protractor

echo "Installing php-cs-fixer..."
sudo wget http://cs.sensiolabs.org/get/php-cs-fixer.phar -O /usr/local/bin/php-cs-fixer
sudo webdriver-manager update --out_dir ./vendor/selenium/
sudo chmod a+x /usr/local/bin/php-cs-fixer


# For Travis only
if [[ ! -z $TRAVIS ]]; then
    # we replace pre-installed PhantomJS with npm version (more recent)
    sudo ln -sf "`sudo npm bin -g`/phantomjs" `which phantomjs`

    # Configure xdebug with a higher max_nesting_level
    for file in `find /home/vagrant/.phpenv -name 'xdebug.ini'`; do echo "xdebug.max_nesting_level=5000" >> $file; done;
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
    echo "127.0.0.1 gims.local" | sudo tee --append /etc/hosts

    echo "Init database..."
    cp config/autoload/local.php.dist config/autoload/local.php
    sudo -u postgres psql -c 'create database gims;' -U postgres
    createuser --no-superuser --no-createdb --no-createrole gims
    createuser --no-superuser --no-createdb --no-createrole backup
fi
