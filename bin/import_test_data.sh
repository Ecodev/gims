# Exit script on any error
set -e

truncate -s 0 data/logs/all.log
phing load-data -DdumpFile=population.backup.gz

time php htdocs/index.php import jmp data/cache/country_data/Bangladesh_13.xlsm

# Reload partial dumps
SQL_PATH="`pwd`/tests/data/sql"
./vendor/bin/doctrine-module dbal:run-sql "COPY \"user\" FROM '$SQL_PATH/user.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY survey FROM '$SQL_PATH/survey.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY questionnaire FROM '$SQL_PATH/questionnaire.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY question FROM '$SQL_PATH/question.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY choice FROM '$SQL_PATH/choice.sql';"

# Reset sequences values
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('answer_id_seq', MAX(id) ) FROM answer;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('choice_id_seq', MAX(id) ) FROM choice;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('country_id_seq', MAX(id) ) FROM country;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_id_seq', MAX(id) ) FROM filter;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_questionnaire_usage_id_seq', MAX(id) ) FROM filter_questionnaire_usage;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_set_id_seq', MAX(id) ) FROM filter_set;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_geoname_usage_id_seq', MAX(id) ) FROM filter_geoname_usage;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('geoname_id_seq', MAX(id) ) FROM geoname;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('part_id_seq', MAX(id) ) FROM part;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('permission_id_seq', MAX(id) ) FROM permission;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('population_id_seq', MAX(id) ) FROM population;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('question_id_seq', MAX(id) ) FROM question;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('questionnaire_id_seq', MAX(id) ) FROM questionnaire;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('questionnaire_usage_id_seq', MAX(id) ) FROM questionnaire_usage;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('role_id_seq', MAX(id) ) FROM role;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('rule_id_seq', MAX(id) ) FROM rule;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('survey_id_seq', MAX(id) ) FROM survey;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('user_id_seq', MAX(id) ) FROM \"user\";"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('user_questionnaire_id_seq', MAX(id) ) FROM user_questionnaire;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('user_survey_id_seq', MAX(id) ) FROM user_survey;"

# Import additional countries
time php htdocs/index.php import jmp data/cache/country_data/Afghanistan_13.xlsm # for importing NULL instead of ignoring non-existing formulas
time php htdocs/index.php import jmp data/cache/country_data/Azerbaijan_13.xlsm   # for recursive formulas and interdependence during import
time php htdocs/index.php import jmp data/cache/country_data/Germany_13.xlsm  # for developed countries rules
time php htdocs/index.php import jmp data/cache/country_data/Saudi_arabia_13.xlsm # for no urban/rural disaggregation rules

# Give access to everything to test user
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 5, survey.id FROM SURVEY;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_questionnaire (user_id, role_id, questionnaire_id) SELECT 1, 3, questionnaire.id FROM questionnaire;"

# Dump new database content
phing dump-data -DdumpFile=tests/data/db.backup.gz


# Dump partial dumps (this should be used to create partial SQL)
# COPY (SELECT * from \"user\") TO '$SQL_PATH/user.sql';
# COPY (SELECT * from survey WHERE id = 20) TO '$SQL_PATH/survey.sql';
# COPY (SELECT * from questionnaire WHERE survey_id = 20) TO '$SQL_PATH/questionnaire.sql';
# COPY (SELECT * from question WHERE survey_id = 20) TO '$SQL_PATH/question.sql';
# COPY (SELECT * from choice) TO '$SQL_PATH/choice.sql';
