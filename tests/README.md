Testing
=======================

PHPUnit
-------

PHPUnit tests require a reference database dump. When the dump is loaded it WILL DESTROY
existing database. This is must done once before running tests. Then each test is ran
within a transaction which is rolled back, so the database state is always predictable.

To run PHPunit test:

    ./vendor/bin/phing load-test-data
    cd tests/
    ../vendor/bin/phpunit # as many times as necessary


Karma
-----

Light-weight testing, recommended for very frequent usage during development, is available directly via karma:

    karma start config/karma-unit.conf.js
    karma start config/karma-e2e.conf.js

