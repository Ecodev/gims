#!/usr/bin/env bash

# This script build all assets for production environment

# Find out directory of this script, no matter from where we call it, even via symlinks
SOURCE="${BASH_SOURCE[0]}"
DIR="$( dirname "$SOURCE" )"
while [ -h "$SOURCE" ]
do
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
  DIR="$( cd -P "$( dirname "$SOURCE"  )" && pwd )"
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

cd $DIR/..

echo "Updating git submodules..."
git submodule update --init --recursive --force

echo "Updating all PHP dependencies via composer..."
./composer.phar install --dev

echo "Updating database..."
./vendor/bin/doctrine-module migrations:migrate --no-interaction
./vendor/bin/doctrine-module orm:generate-proxies

echo "Compiling CSS..."
compass compile -s compressed --force

echo "Compiling JavaScript..."
TARGET="tmp/application.js"
cd htdocs
rm -f $TARGET

# First do lib, then our own code
for jsDir in lib/autoload/ js/ ; do
    for file in `find -L $jsDir -type f -name '*.js' | sort` ; do
        echo "$file"
        D=`dirname $file`
        mkdir -p "tmp/$D"
        more "$file" | ngmin | uglifyjs - -o "tmp/$file" # uglify the code
        cat "tmp/$file" >> $TARGET # concatenate in a single file
    done
done
