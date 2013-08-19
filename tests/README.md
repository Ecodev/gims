Testing
=======================

PHPUnit
------------
PHPUnit tests use a reference database dump. When the dump is loaded it WILL DESTROY
existing database. This is done once per run, and then each test is ran within a
transaction which is rolled back. So the database state is always predictable.

To run PHPunit test:

    cd tests/
    ../vendor/bin/phpunit


Karma
-------

Light-weight testing, recommended for very frequent usage during development, is available directly via karma:

    karma start config/karma-unit.conf.js
    karma start config/karma-e2e.conf.js

