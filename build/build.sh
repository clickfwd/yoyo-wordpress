#!/bin/sh

rm -rf build/templates/plugins/vendor
rm build/templates/plugins/yoyo/composer.lock
mv build/templates/plugins/yoyo/composer.json build/templates/plugins/yoyo/composer-prod.json
mv build/templates/plugins/yoyo/composer-dev.json build/templates/plugins/yoyo/composer.json

composer install -d build/templates/plugins/yoyo --no-dev

mv build/templates/plugins/yoyo/composer.json build/templates/plugins/yoyo/composer-dev.json
mv build/templates/plugins/yoyo/composer-prod.json build/templates/plugins/yoyo/composer.json

npm run build
