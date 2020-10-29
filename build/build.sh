#!/bin/sh

rm -rf build/templates/plugins/vendor
rm build/templates/plugins/composer.lock
mv build/templates/plugins/composer.json build/templates/plugins/composer-prod.json
mv build/templates/plugins/composer-dev.json build/templates/plugins/composer.json

composer install -d build/templates/plugins --no-dev

mv build/templates/plugins/composer.json build/templates/plugins/composer-dev.json
mv build/templates/plugins/composer-prod.json build/templates/plugins/composer.json

npm run build
