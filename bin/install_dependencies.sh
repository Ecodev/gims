#!/usr/bin/env bash

    echo "Init database..."

    which mysql
    mysql --version
    mysql -e 'CREATE DATABASE gims;'
    mysql -e 'SHOW VARIABLES LIKE "%char%";'