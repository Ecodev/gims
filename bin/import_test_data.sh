truncate -s 0 data/logs/all.log
phing load-data -DdumpFile=../gims/population.backup.gz

#time php htdocs/index.php import jmp data/cache/country_data/Country_data_Europe/Western_Europe/Germany_12.xlsm
#time php htdocs/index.php import jmp data/cache/country_data/Country_data_Asia/South_Eastern_Asia/Cambodia_12.xlsm
exit

time php htdocs/index.php import jmp data/cache/country_data/Country_data_Asia/South_Central_Asia/Bangladesh_12.xlsm

# Reload partial dumps
./vendor/bin/doctrine-module dbal:run-sql "COPY \"user\" FROM '/sites/gims_misc/sql/user.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY survey FROM '/sites/gims_misc/sql/survey.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY questionnaire FROM '/sites/gims_misc/sql/questionnaire.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY question FROM '/sites/gims_misc/sql/question.sql';"
./vendor/bin/doctrine-module dbal:run-sql "COPY choice FROM '/sites/gims_misc/sql/choice.sql';"

# Reset sequences values
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('answer_id_seq', MAX(id) ) FROM answer;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('choice_id_seq', MAX(id) ) FROM choice;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('country_id_seq', MAX(id) ) FROM country;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_id_seq', MAX(id) ) FROM filter;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_rule_id_seq', MAX(id) ) FROM filter_rule;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('filter_set_id_seq', MAX(id) ) FROM filter_set;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('geoname_id_seq', MAX(id) ) FROM geoname;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('part_id_seq', MAX(id) ) FROM part;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('permission_id_seq', MAX(id) ) FROM permission;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('population_id_seq', MAX(id) ) FROM population;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('question_id_seq', MAX(id) ) FROM question;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('questionnaire_formula_id_seq', MAX(id) ) FROM questionnaire_formula;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('questionnaire_id_seq', MAX(id) ) FROM questionnaire;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('role_id_seq', MAX(id) ) FROM role;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('rule_id_seq', MAX(id) ) FROM rule;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('survey_id_seq', MAX(id) ) FROM survey;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('user_id_seq', MAX(id) ) FROM \"user\";"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('user_questionnaire_id_seq', MAX(id) ) FROM user_questionnaire;"
./vendor/bin/doctrine-module dbal:run-sql "SELECT SETVAL('user_survey_id_seq', MAX(id) ) FROM user_survey;"

# Import additional countries
time php htdocs/index.php import jmp data/cache/country_data/Country_data_Asia/South_Central_Asia/Afghanistan_12.xlsm
time php htdocs/index.php import jmp data/cache/country_data/Country_data_Asia/Western_Asia/Azerbaijan_12.xlsm
#time php htdocs/index.php import jmp data/cache/country_data/Country_data_Asia/South_Eastern_Asia/Cambodia_12.xlsm
#time php htdocs/index.php import jmp data/cache/country_data/Country_data_Asia/South_Eastern_Asia/Lao_people_dem_rep_12.xlsm

# Dump new database content
phing dump-data -DdumpFile=tests/data/db.backup.gz


# Dump partial dumps (this should be used to create partial SQL)
# COPY (SELECT * from \"user\") TO '/tmp/user.sql';
# COPY (SELECT * from survey WHERE id = 20) TO '/tmp/survey.sql';
# COPY (SELECT * from questionnaire WHERE survey_id = 20) TO '/tmp/questionnaire.sql';
# COPY (SELECT * from question WHERE survey_id = 20) TO '/tmp/question.sql';
# COPY (SELECT * from choice) TO '/tmp/choice.sql';
