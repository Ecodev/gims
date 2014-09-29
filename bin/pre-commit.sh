#!/bin/sh

pass=true

files=$(git diff --cached --name-only --diff-filter=ACMR | grep -F ".js" | grep -vE "(htdocs/lib|package.json|htdocs/ace-custom|*.json)")
if [ "$files" != "" ]; then

    # Run JSHint validation before commit
    for file in ${files}; do
        ./node_modules/.bin/jshint ${file}
        if [ $? -ne 0 ]; then
            pass=false
        fi
        echo -n .
    done
    echo

    # Run JSCS validation before commit
    for file in ${files}; do
        ./node_modules/.bin/jscs ${file}
        if [ $? -ne 0 ]; then
            pass=false
        fi
    done
fi

files=$(git diff --cached --name-only --diff-filter=ACMR | grep -E '\.php$')
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
        php-cs-fixer fix --dry-run --verbose --diff --config-file .php_cs ${file}
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
