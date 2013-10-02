#!/usr/bin/env bash

echo 'Started. See import_all_country.log for progres...'
time find data/cache/country_data -type f -name '*_12.xlsm' ! -name '~$*' -print -exec date --iso-8601=seconds \; -exec php htdocs/index.php import jmp {} \; &> import_all_country.log
