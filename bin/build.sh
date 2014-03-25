#!/usr/bin/env bash

# This script build all assets for production environment

# Exit script on any error
set -e

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
./composer.phar install --dev --optimize-autoloader
