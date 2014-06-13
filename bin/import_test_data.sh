# Exit script on any error
set -e

> data/logs/all.log
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
./vendor/bin/doctrine-module dbal:run-sql --ansi "SELECT  'SELECT SETVAL(' ||quote_literal(quote_ident(PGT.schemaname)|| '.'||quote_ident(S.relname))|| ', MAX(' ||quote_ident(C.attname)|| ') ) FROM ' ||quote_ident(PGT.schemaname)|| '.'||quote_ident(T.relname)|| ';'
FROM pg_class AS S, pg_depend AS D, pg_class AS T, pg_attribute AS C, pg_tables AS PGT
WHERE S.relkind = 'S'
    AND S.oid = D.objid
    AND D.refobjid = T.oid
    AND D.refobjid = C.attrelid
    AND D.refobjsubid = C.attnum
    AND T.relname = PGT.tablename
ORDER BY S.relname;" | grep -o "'SELECT.*'" | sed "s/'/\"/g" | sed "s/&#39;/'/g" | xargs -L 1 ./vendor/bin/doctrine-module dbal:run-sql

# Import additional countries
time php htdocs/index.php import jmp data/cache/country_data/Afghanistan_13.xlsm # for importing NULL instead of ignoring non-existing formulas
time php htdocs/index.php import jmp data/cache/country_data/Azerbaijan_13.xlsm   # for recursive formulas and interdependence during import
time php htdocs/index.php import jmp data/cache/country_data/Germany_13.xlsm  # for developed countries rules
time php htdocs/index.php import jmp data/cache/country_data/Saudi_arabia_13.xlsm # for no urban/rural disaggregation rules

# Give access to everything to test user
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 5, survey.id FROM SURVEY;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_survey (user_id, role_id, survey_id) SELECT 1, 7, survey.id FROM SURVEY;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_questionnaire (user_id, role_id, questionnaire_id) SELECT 1, 3, questionnaire.id FROM questionnaire;"
./vendor/bin/doctrine-module dbal:run-sql "INSERT INTO user_filter_set (role_id, user_id, filter_set_id) SELECT 6, 1, filter_set.id from filter_set;"
./vendor/bin/doctrine-module dbal:run-sql "UPDATE filter SET creator_id=1 WHERE creator_id IS NULL;"

# Dump new database content
phing dump-data -DdumpFile=tests/data/db.backup.gz


# Dump partial dumps (this should be used to create partial SQL)
# COPY (SELECT * from \"user\") TO '$SQL_PATH/user.sql';
# COPY (SELECT * from survey WHERE id = 20) TO '$SQL_PATH/survey.sql';
# COPY (SELECT * from questionnaire WHERE survey_id = 20) TO '$SQL_PATH/questionnaire.sql';
# COPY (SELECT * from question WHERE survey_id = 20) TO '$SQL_PATH/question.sql';
# COPY (SELECT * from choice) TO '$SQL_PATH/choice.sql';
