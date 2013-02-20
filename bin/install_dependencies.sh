#!/usr/bin/env bash

echo "Installing PostGIS and other packages..."
sudo add-apt-repository --yes ppa:ubuntugis/ubuntugis-unstable
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get -qq update
sudo apt-get -qq install postgis postgresql-9.1-postgis rubygems nodejs

echo "Installing Compass..."
sudo gem install --quiet --no-rdoc --no-ri sass compass oily_png bootstrap-sass

echo "Installing JS testing tools"
sudo npm install phantomjs --global
sudo npm install testacular --global

if [[ "$1" = "travis" ]]; then
    echo "Init database..."
    cp config/autoload/local.php.dist config/autoload/local.php
    psql -c 'create database gims;' -U postgres
else
    sudo apt-get install -qq libapache2-mod-php5 php5-pgsql php5-cli php5-gd php5-mcrypt
fi

echo "Installing all PHP dependencies via composer..."
./composer.phar install --dev
