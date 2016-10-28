# Exit script on any error
set -e

> data/logs/all.log
./vendor/bin/phing load-data -DdumpFile=population.sql.gz

# Reload partial dumps
./vendor/bin/doctrine-module dbal:import --ansi tests/data/fixture.sql

# Import countries
time php htdocs/index.php import jmp data/cache/country_data/Bangladesh_15.xlsm
time php htdocs/index.php import jmp data/cache/country_data/Afghanistan_15.xlsm # for importing NULL instead of ignoring non-existing formulas
time php htdocs/index.php import jmp data/cache/country_data/Azerbaijan_15.xlsm   # for recursive formulas and interdependence during import
time php htdocs/index.php import jmp data/cache/country_data/Germany_15.xlsm  # for developed countries rules
time php htdocs/index.php import jmp data/cache/country_data/Saudi_arabia_15.xlsm # for no urban/rural disaggregation rules

# Give access to everything to test user
./vendor/bin/doctrine-module dbal:import --ansi tests/data/access.sql

# Dump new database content
./vendor/bin/phing dump-data -DdumpFile=tests/data/db.sql.gz
