#!/usr/bin/env bash

# Install Compass
sudo gem install --no-rdoc --no-ri sass compass oily_png bootstrap-sass

sudo add-apt-repository ppa:ubuntugis/ubuntugis-unstable
sudo apt-get update 
sudo apt-get install postgis postgresql-9.1-postgis

#pgadmin3 


if [ $1 = "testing" ]; then
    # Init database
    cp application/configs/application.travis.ini application/configs/application.ini
    psql -c 'create database myapp_test;' -U postgres
else
    sudo apt-get install postgis libapache2-mod-php5 php5-pgsql php5-cli php5-gd php5-mcrypt
if
