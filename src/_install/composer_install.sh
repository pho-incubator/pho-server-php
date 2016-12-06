#!/usr/bin/env bash

# jq needs for parse json
sudo apt-get install jq

#saving original composer.json
mv composer.json composer.json.bac
#composer.json without require-dev and require
cat composer.json.bac | sudo -u vagrant jq 'del(.["require-dev"],.["require"])' > composer.json
#require
cat composer.json.bac | jq '.["require"]' > composer.require.json
#require-dev
cat composer.json.bac | jq '.["require-dev"]' > composer.require.dev.json

# It's need for correct working of 'sudo -u vagrant composer'
OLD_HOME=$HOME
HOME=~vagrant

# find package "php" in require and require-dev
REQ=`grep '"php"' composer.require.json | sed -e 's/,$//' | sed -e s'/": /" /'`
REQDEV=`grep '"php"' composer.require.dev.json | sed -e 's/,$//' | sed -e s'/": /" /'`
if [ -n "$REQ" ]
then
	sudo -u vagrant composer require `echo $REQ | sed -e 's/"//g'`
	cat composer.require.json | jq 'del(.["php"])' > composer.require.json.bac &&  mv composer.require.json.bac composer.require.json
fi
if [ -n "$REQDEV" ]
then
	sudo -u vagrant composer require --dev `echo $REQDEV | sed -e 's/"//g'`
	cat composer.require.dev.json | jq 'del(.["php"])' > composer.require.dev.json.bac &&  mv composer.require.dev.json.bac composer.require.dev.json
fi

# find package "kettle/dynamodb-orm" in require and require-dev
REQ=`grep '"kettle/dynamodb-orm"' composer.require.json | sed -e 's/,$//' | sed -e s'/": /" /'`
REQDEV=`grep '"kettle/dynamodb-orm"' composer.require.dev.json | sed -e 's/,$//' | sed -e s'/": /" /'`
if [ -n "$REQ" ]
then
	sudo -u vagrant composer require "aws/aws-sdk-php" "3.*"
	sudo -u vagrant composer require `echo $REQ | sed -e 's/"//g'`
	cat composer.require.json | jq 'del(.["kettle/dynamodb-orm"])' > composer.require.json.bac &&  mv composer.require.json.bac composer.require.json
fi
if [ -n "$REQDEV" ]
then
	sudo -u vagrant composer require --dev "aws/aws-sdk-php" "3.*"
	sudo -u vagrant composer require --dev `echo $REQDEV | sed -e 's/"//g'`
	cat composer.require.dev.json | jq 'del(.["kettle/dynamodb-orm"])' > composer.require.dev.json.bac &&  mv composer.require.dev.json.bac composer.require.dev.json
fi

# install rest of packages from require
grep ':' composer.require.json | while read -r REQ ; do
	sudo -u vagrant composer require `echo $REQ | sed -e 's/,$//' | sed -e s'/": /" /' | sed -e 's/"//g'`
done

# install rest of packages from require-dev
grep ':' composer.require.dev.json | while read -r REQDEV ; do
	sudo -u vagrant composer require --dev `echo $REQDEV | sed -e 's/,$//' | sed -e s'/": /" /' | sed -e 's/"//g'`
done

# cleaning
rm composer.require.dev.json composer.require.json composer.json.bac
HOME=$OLD_HOME
