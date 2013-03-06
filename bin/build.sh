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

echo "Updating database..."
./vendor/bin/doctrine-module migrations:migrate --no-interaction --quiet

echo "Compiling CSS..."
compass compile -s compressed --force

echo "Compiling JavaScript..."
cd htdocs/js
mkdir -p min
for file in *.js ; do
	
	# Discard warnings for third party code
	if [[ $file =~ jquery|bootstrap ]]
	then
		thirdparty="--third_party --warning_level QUIET"
	else
		thirdparty=""
	fi

	echo "$file"
	java -jar ../../vendor/closure-compiler/compiler.jar --compilation_level SIMPLE_OPTIMIZATIONS  --js "$file" --js_output_file "min/$file" $thirdparty
done


echo "Concatenate JavaScript..."
cd min/

# CAUTION: This must be the exact same files in reverse order than in module/Application/view/layout/layout.phtml
cat \
../../lib/angular/angular.min.js \
../../lib/ui-bootstrap/ui-bootstrap-tpls-0.2.0.min.js \
app.js \
services.js \
controllers.js \
filters.js \
directives.js \
> application.js
