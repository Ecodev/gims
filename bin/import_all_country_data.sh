#!/usr/bin/env bash

echo 'Started. See data/logs/import_all_country.log for progress...'
truncate -s 0 data/logs/all.log
time find data/cache/country_data -type f -name '*_12.xlsm' ! -name '~$*' -print -exec date --iso-8601=seconds \; -exec php htdocs/index.php import jmp {} \; &> data/logs/import_all_country.log
