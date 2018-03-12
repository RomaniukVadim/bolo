#!/usr/bin/env bash

cd ~

echo setting timezone to UTC; sudo ln -sf /usr/share/zoneinfo/UTC /etc/localtime
sudo sh -c "echo 'LC_ALL=en_US.UTF-8\nLANG=en_US.UTF-8' >> /etc/locale"

echo -e "\ncd /opt/bolo\n" >> ~/.bashrc

sudo apt-get -qq -y update
#sudo DEBIAN_FRONTEND=noninteractive apt-get -qq -y upgrade
sudo apt-get -qq -y install debconf-utils

echo "mysql-server mysql-server/root_password password psswd" | sudo debconf-set-selections
echo "mysql-server mysql-server/root_password_again password psswd" | sudo debconf-set-selections
sudo apt-get -y install mysql-server
#sudo mysqladmin -u root password psswd

sudo apt-get -qq -y install htop
sudo apt-get -qq -y install ntp 
sudo apt-get -qq -y install git
sudo apt-get -qq -y install curl
sudo apt-get -qq -y install unzip
sudo apt-get -qq -y install software-properties-common python-software-properties

sudo apt-get -qq -y install redis-server

sudo apt-get -qq -y remove apache2
sudo apt-get -qq -y install nginx

sudo LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:ondrej/php
sudo apt-get -qq -y update
sudo apt-get -qq -y install php7.0-fpm
sudo apt-get -qq -y install php7.0-cli php7.0-json php7.0-mysql php7.0-curl php7.0-gd php7.0-gmp php7.0-mcrypt php7.0-memcached php7.0-imagick php7.0-intl php7.0-redis php7.0-mbstring php7.0-dom php7.0-zip
sudo apt-get -qq -y install sendmail
sudo apt-get -qq -y install phpunit

sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/default-backup
cat <<'EOF' | sudo tee /etc/nginx/sites-available/default
include /opt/bolo/nginx.conf;
EOF

sudo service nginx restart

php -r "readfile('https://getcomposer.org/installer');" | php
sudo mv composer.phar /usr/local/bin/composer

sudo apt-get -qq -y install build-essential libssl-dev
curl https://raw.githubusercontent.com/creationix/nvm/v0.32.0/install.sh | bash
. ~/.nvm/nvm.sh
nvm install --lts node

gpg --keyserver hkp://keys.gnupg.net --recv-keys 409B6B1796C275462A1703113804BB82D39DC0E3
curl -sSL https://get.rvm.io | bash -s stable
. ~/.rvm/scripts/rvm
rvm install 2.3.1
rvm use 2.3.1 --default


sudo sed -i "s/bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf
Q1="GRANT ALL ON *.* TO 'root'@'%' IDENTIFIED BY 'psswd' WITH GRANT OPTION;"
Q2="FLUSH PRIVILEGES;"
SQL="${Q1}${Q2}"
mysql -uroot -ppsswd -e "$SQL"
sudo service mysql restart

sudo apt-get -qq -y install unattended-upgrades
sudo DEBIAN_FRONTEND=noninteractive dpkg-reconfigure -plow unattended-upgrades

mysql -uroot -ppsswd -e "create database bolo;"

#######################################################

cd /opt/bolo/public
composer install

npm install gulp gulp-sass gulp-sourcemaps gulp-autoprefixer --no-bin-links
npm install gulp --global

echo "APP_ENV=dev" > .env

cd /opt/bolo/db
. load_dump.sh