[![Build Status](https://api.travis-ci.org/Ecodev/gims.png?branch=0.2.0-dev)](http://travis-ci.org/Ecodev/gims)

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


Testing
-------

Full testing (phpunit and testacular) can be executed via phing:

    ./vendor/bin/phing test

Light-weight testing, recommended for very frequent usage during development, is available directly via testacular:

    testacular start config/testacular-unit.conf.js
    testacular start config/testacular-e2e.conf.js

