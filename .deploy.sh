#!/bin/bash

echo; echo "updating dependencies";
/usr/local/bin/composer.phar install --prefer-dist

echo; echo "updating bower dependencies";
$(cd public && bower -q install)
