#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344)
set -x

# copy template files
cp composer.json $BUILD_DESTINATION/composer.json
cp bin/rector-prefixed/template/README.md $BUILD_DESTINATION/README.md
cp bin/rector-prefixed/template/.travis.yml $BUILD_DESTINATION/.travis.yml

# rebuild composer dump so the new prefixed namespaces are autoloaded
# the new "RectorPrefixed\" is taken into account thanks to /vendor/composer/installed.json file,
composer dump-autoload -d $BUILD_DESTINATION --no-dev

# make bin executable
chmod +x $BUILD_DESTINATION/bin/rector

# clear kernel cache to make use of this new one,
(find $BUILD_DESTINATION -type f | xargs sed -i 's#_rector_cache#_prefixed_rector_cache#g')
rm -rf /tmp/_prefixed_rector_cache

# run it to test it
$BUILD_DESTINATION/bin/rector
