#!/usr/bin/env bash

# Exit script on any error
set -e

echo "Installing PostGIS and other packages..."
sudo apt-get -qq update
sudo apt-get install -qq software-properties-common # to get next command: add-apt-repository
sudo add-apt-repository --yes ppa:ubuntugis/ubuntugis-unstable
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get -qq update
sudo apt-get -qq install postgresql-9.1-postgis-2.0 php5-pgsql php5-cli php5-gd php5-mcrypt php5-intl php5-json


# For Travis CI, or full local install, we need more configuration (Apache and database)
if [[ "$1" = "configure" ]]; then

    echo "Init database..."
    cp config/autoload/local.php.dist config/autoload/local.php
    sudo -u postgres psql -c 'create database gims;' -U postgres
fi
