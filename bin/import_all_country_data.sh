#!/usr/bin/env bash

find data/cache/country_data -type f -name '*_12.xlsm' ! -name '~$*' -print -exec php htdocs/index.php import jmp {} \;
