#!/usr/bin/env bash

# Install PostGIS
sudo add-apt-repository ppa:ubuntugis/ubuntugis-unstable
sudo apt-get -qq update
sudo apt-get -qq install postgis postgresql-9.1-postgis rubygems

# Install Compass
sudo gem install --no-rdoc --no-ri sass compass oily_png bootstrap-sass


if [[ "$1" = "testing" ]]; then
    # Init database
    cp config/autoload/local.php.dist config/autoload/local.php
    psql -c 'create database gims;' -U postgres
else
    sudo apt-get install libapache2-mod-php5 php5-pgsql php5-cli php5-gd php5-mcrypt
fi

# Install all PHP dependencies via composer
./composer.phar install --dev
