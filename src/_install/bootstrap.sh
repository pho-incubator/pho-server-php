#!/usr/bin/env bash

## @todo remove mysql

# Use single quotes instead of double quotes to make it work with special-character passwords
PASSWORD='12345678'
PROJECTFOLDER='pho'

# Create project folder, written in 3 single mkdir-statements to make sure this runs everywhere without problems
sudo mkdir -p "/var/www/html/${PROJECTFOLDER}"

sudo apt-get update
sudo apt-get -y upgrade

sudo apt-get install -y apache2
sudo apt-get install -y php5

sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $PASSWORD"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $PASSWORD"
sudo apt-get -y install mysql-server
sudo apt-get install php5-mysql

# installing Xdebug
sudo apt-get install php5-xdebug
echo '
xdebug.remote_enable = on
xdebug.remote_connect_back = on
xdebug.idekey = "vagrant"
' >> /etc/php5/mods-available/xdebug.ini

# installing dynamodb
# based off of https://gist.github.com/vedit/ec8b9b16d403a0dd410791ad62ad48ef
DYNAMODB_USER=vagrant

sudo apt-get install openjdk-7-jre-headless -y

cd /home/${DYNAMODB_USER}/
mkdir -p dynamodb
cd dynamodb

wget http://dynamodb-local.s3-website-us-west-2.amazonaws.com/dynamodb_local_latest.tar.gz
tar -xvzf dynamodb_local_latest
rm dynamodb_local_latest

cat >> dynamodb.conf << EOF
description "DynamoDB Local"
#
# http://aws.typepad.com/aws/2013/09/dynamodb-local-for-desktop-development.html
#
start on (local-filesystems and runlevel [2345])
stop on runlevel [016]

chdir /home/${DYNAMODB_USER}/dynamodb
chown ${DYNAMODB_USER}:${DYNAMODB_USER} /home/${DYNAMODB_USER}/dynamodb

exec java -Djava.library.path=./DynamoDBLocal_lib -jar DynamoDBLocal.jar -sharedDb -dbPath /home/${DYNAMODB_USER}/dynamodb
EOF
sudo cp /home/${DYNAMODB_USER}/dynamodb/dynamodb.conf /etc/init/dynamodb.conf

# aws credentials
# It's need for correct work of aws services
echo '[vagrant]
aws_access_key_id = vagrant
aws_secret_access_key = vagrant
region = us-west-2
' >> /home/${DYNAMODB_USER}/.aws/credentials
chmod 644 /home/${DYNAMODB_USER}/.aws/credentials



# setup hosts file
VHOST=$(cat <<EOF
<VirtualHost *:80>
    DocumentRoot "/var/www/html/${PROJECTFOLDER}/src/public"
    <Directory "/var/www/html/${PROJECTFOLDER}/src/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
)
echo "${VHOST}" > /etc/apache2/sites-available/000-default.conf

# enable mod_rewrite
sudo a2enmod rewrite

# restart apache
service apache2 restart

# install curl (needed to use git afaik)
sudo apt-get -y install curl
sudo apt-get -y install php5-curl

# install openssl (needed to clone from GitHub, as github is https only)
sudo apt-get -y install openssl

# install PHP GD, the graphic lib (we create captchas and avatars)
sudo apt-get -y install php5-gd

# install Composer
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# go to project folder, load Composer packages
cd "/var/www/html/${PROJECTFOLDER}/src"
composer install

# run SQL statements from install folder
sudo mysql -h "localhost" -u "root" "-p${PASSWORD}" < "/var/www/html/${PROJECTFOLDER}/src/_install/01-create-database.sql"
sudo mysql -h "localhost" -u "root" "-p${PASSWORD}" < "/var/www/html/${PROJECTFOLDER}/src/_install/02-create-table-users.sql"
sudo mysql -h "localhost" -u "root" "-p${PASSWORD}" < "/var/www/html/${PROJECTFOLDER}/src/_install/03-create-table-notes.sql"

# import from mysql to dynamodb
# todo It's temporary solution. It should be deleted in future.
/usr/bin/env php /var/www/html/${PROJECTFOLDER}/src/_install/import_data.php

# writing rights to avatar folder
sudo chmod 0777 -R "/var/www/html/${PROJECTFOLDER}/src/public/avatars"

# remove Apache's default demo file
sudo rm "/var/www/html/index.html"

# final feedback
echo "Voila!"