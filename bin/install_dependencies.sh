#!/usr/bin/env bash

    echo "Init database..."

    which mysql
    mysql --version
    mysql -e 'SHOW VARIABLES LIKE "%char%";'
    mysql -e 'CREATE DATABASE gims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
    mysql -e 'SHOW VARIABLES LIKE "%char%";'
    mysql gims -e 'SHOW VARIABLES LIKE "%char%";'