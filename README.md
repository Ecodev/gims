Master: [![Build Status](https://api.travis-ci.org/Ecodev/gims.png?branch=master)](http://travis-ci.org/Ecodev/gims)
Develop: [![Build Status](https://api.travis-ci.org/Ecodev/gims.png?branch=develop)](http://travis-ci.org/Ecodev/gims)

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

Full testing (phpunit and karma) can be executed via phing:

    ./vendor/bin/phing test

Light-weight testing, recommended for very frequent usage during development, is available directly via karma:

    karma start config/karma-unit.conf.js
    karma start config/karma-e2e.conf.js

