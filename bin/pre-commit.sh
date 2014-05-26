#!/bin/sh

pass=true

files=$(git diff --cached --name-only --diff-filter=ACMR | grep .js)
if [ "$files" != "" ]; then

    # Run JSHint validation before commit
    for file in ${files}; do
        ./node_modules/.bin/jshint ${file}
        if [ $? -ne 0 ]; then
            pass=false
        fi
    done

    # Run JSCS validation before commit
    for file in ${files}; do
        ./node_modules/.bin/jscs ${file}
        if [ $? -ne 0 ]; then
            pass=false
        fi
    done
fi

files=$(git diff --cached --name-only --diff-filter=ACMR | grep .php)
if [ "$files" != "" ]; then

    # Run php syntax check before commit
    for file in ${files}; do
        php -l ${file}
        if [ $? -ne 0 ]; then
            pass=false
        fi
    done

    # Run php-cs-fixer validation before commit
    for file in ${files}; do
        php-cs-fixer fix --dry-run --verbose --diff --fixers=indentation,elseif,linefeed,trailing_spaces,visibility,return,short_tag,php_closing_tag,extra_empty_lines,psr0,controls_spaces,elseif,eof_ending ${file}
        if [ $? -ne 0 ]; then
            pass=false
        fi
    done
fi


if $pass; then
    exit 0
else
    echo ""
    echo "PRE-COMMIT HOOK FAILED:"
    echo "Code style validation failed. Please fix errors and try committing again."
    exit 1
fi
