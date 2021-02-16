#!/usr/bin/env bash
set -e

: "${dependencies:?Need to set dependencies environment variable}"
if [ "$dependencies" = "lowest" ]; then
    composer update --prefer-lowest --no-interaction
    vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p src/ test/
else
    composer update --no-interaction
fi
vendor/bin/phpunit --log-junit="build/${dependencies}-phpunit.xml"
