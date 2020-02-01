#!/bin/bash
echo "Provisioning virtual machine..."
# Update remote package metadata
apt-get update
locale-gen UTF-8
src=/var/www/audioreg/src
provision=/var/www/audioreg/provision/local

echo "Installing application stack..."
# Prevent getting a prompt for the MySQL root's password
export DEBIAN_FRONTEND=noninteractive
apt-get install -y \
    apache2 mysql-server mysql-client \
    libapache2-mod-php php-mcrypt php-mbstring php-curl php-mysql php7.0-xml \
    screen git htop unzip \
    redis-server redis-tools
a2enmod rewrite
a2enmod ssl
a2enmod headers

echo "Configuring MySQL..."
cat $provision/database.sql | mysql -uroot

echo "Configuring virtual host..."
cp $provision/virtual-host.conf /etc/apache2/sites-available/audioreg.conf
a2ensite audioreg
a2dissite 000-default
service apache2 restart

echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
cd $src && composer install
cd -


service apache2 reload

echo "Configuring Laravel environment..."
cp $provision/.env $src/.env

echo "Finished provisioning."
