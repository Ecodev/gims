#!/bin/sh

pass=true

# Run JSHint validation before commit
files=$(git diff --cached --name-only --diff-filter=ACMR | grep .js)
if [ "$files" != "" ]; then
    for file in ${files}; do
        jshint ${file}

        if [ $? -ne 0 ]; then
            pass=false
        fi
    done
fi

# Run php-cs-fixer validation before commit
files=$(git diff --cached --name-only --diff-filter=ACMR | grep .php)
if [ "$files" != "" ]; then
    for file in ${files}; do
        php-cs-fixer fix --dry-run --verbose --diff ${file}
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
