#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344)
set -x

BUILD_DESTINATION="../rector-prefixed-build"

# cleanup build
rm -rf $BUILD_DESTINATION
mkdir $BUILD_DESTINATION

# prefix current code to $BUILD_DESTINATION directory (see "scoper.inc.php" for settings)
vendor/bin/php-scoper add-prefix --no-interaction --output-dir=$BUILD_DESTINATION
