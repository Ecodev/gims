# Exit script on any error
set -e

> data/logs/all.log
./vendor/bin/phing load-data -DdumpFile=population.backup.gz

# Reload partial dumps
SQL_PATH="`pwd`/tests/data/sql"
./vendor/bin/doctrine-module dbal:run-sql "COPY \"user\" FROM '$SQL_PATH/user.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY survey FROM '$SQL_PATH/survey.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY questionnaire FROM '$SQL_PATH/questionnaire.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY question FROM '$SQL_PATH/question.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY choice FROM '$SQL_PATH/choice.sql';"

# Reset sequences values
./vendor/bin/phing reset-sequences

# Import countries
time php htdocs/index.php import jmp data/cache/country_data/Bangladesh_15.xlsm
time php htdocs/index.php import jmp data/cache/country_data/Afghanistan_15.xlsm # for importing NULL instead of ignoring non-existing formulas
time php htdocs/index.php import jmp data/cache/country_data/Azerbaijan_15.xlsm   # for recursive formulas and interdependence during import
time php htdocs/index.php import jmp data/cache/country_data/Germany_15.xlsm  # for developed countries rules
time php htdocs/index.php import jmp data/cache/country_data/Saudi_arabia_15.xlsm # for no urban/rural disaggregation rules

# Give access to everything to test user
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 5, survey.id FROM SURVEY;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 7, survey.id FROM SURVEY;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_questionnaire (user_id, role_id, questionnaire_id) SELECT 1, 3, questionnaire.id FROM questionnaire;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_filter_set (role_id, user_id, filter_set_id) SELECT 6, 1, filter_set.id from filter_set;"
./vendor/bin/doctrine-module dbal:run-sql "UPDATE filter SET creator_id = 1 WHERE creator_id IS NULL;"
./vendor/bin/doctrine-module dbal:run-sql "UPDATE questionnaire SET status = 'published';"
./vendor/bin/doctrine-module dbal:run-sql "UPDATE filter_set SET is_published = TRUE;"

# Dump new database content
./vendor/bin/phing dump-data -DdumpFile=tests/data/db.backup.gz


# Dump partial dumps (this should be used to create partial SQL)
# COPY (SELECT * from \"user\") TO '$SQL_PATH/user.sql';
# COPY (SELECT * from survey WHERE id = 20) TO '$SQL_PATH/survey.sql';
# COPY (SELECT * from questionnaire WHERE survey_id = 20) TO '$SQL_PATH/questionnaire.sql';
# COPY (SELECT * from question WHERE survey_id = 20) TO '$SQL_PATH/question.sql';
# COPY (SELECT * from choice) TO '$SQL_PATH/choice.sql';
