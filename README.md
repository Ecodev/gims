[![Build Status](https://secure.travis-ci.org/PowerKiKi/mqueue.png?branch=master)](http://travis-ci.org/PowerKiKi/mqueue)

GIMS
=======================

Introduction
------------
GIMS application using the ZF2.


Installation
------------

1. The recommended way to get a working copy is the following:

    ./bin/install_dependencies.sh
    ./vendor/bin/phing build

2. Create a database in PostgreSQL named "gims"
3. Configure database in ``config/autoload/local.php``
4. Set up a virtual host to point to ``htdocs/`` directory

