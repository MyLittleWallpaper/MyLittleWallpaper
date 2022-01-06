#!/usr/bin/env bash

# Variables
mysql_pwd="aoGMq8r9fbyLgLY9yZ6kcshcw4DBuX"

# Set timezone
sudo timedatectl set-timezone Europe/Helsinki
sudo dpkg-reconfigure -f noninteractive tzdata

# Remove MySQL, in case provisioning against existing machine
sudo systemctl stop mysql
sudo killall -KILL mysql mysqld_safe mysqld
sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge mysql-server mysql-client
sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoremove --purge
sudo DEBIAN_FRONTEND=noninteractive apt-get autoclean
sudo deluser --remove-home mysql
sudo delgroup mysql
sudo rm -rf /etc/mysql /var/lib/mysql /var/log/mysql* /var/run/mysqld

# Remove PHP, in case provisioning against existing machine
sudo systemctl stop php7.4-fpm
sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php7.4-fpm php7.4-cli php7.4-common composer
sudo rm -rf /etc/php/7.4

# PPA by Ondřej Surý to get nginx version with TLS 1.3 & HTTP/2 support
sudo add-apt-repository -y ppa:ondrej/nginx

# PPA by Ondřej Surý to get PHP
sudo add-apt-repository -y ppa:ondrej/php

# Install updates
sudo DEBIAN_FRONTEND=noninteractive apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y dist-upgrade

# Install nginx, PHP & MySQL
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install nginx php7.4-bcmath php7.4-cli php7.4-curl php7.4-fpm php7.4-gd php7.4-intl php7.4-json \
php7.4-mbstring php7.4-mysql php7.4-opcache php7.4-readline php7.4-soap php7.4-xml php7.4-zip php7.4-dev php7.4-memcache php-pear mysql-server \
memcached imagemagick

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# MySQL config
echo "innodb_file_per_table = 1" | sudo tee -a /etc/mysql/mysql.conf.d/mysqld.cnf
echo "innodb_buffer_pool_size = 512M" | sudo tee -a /etc/mysql/mysql.conf.d/mysqld.cnf
echo "sql_mode=NO_ENGINE_SUBSTITUTION" | sudo tee -a /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql

# Add MySQL user and database to be used with web site
sudo mysql -u root -e "DROP USER IF EXISTS 'mlwp'@'localhost'"
sudo mysql -u root -e "CREATE USER 'mlwp'@'localhost' IDENTIFIED BY '$mysql_pwd'"
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON \`mlwp\`.* TO 'mlwp'@'localhost'"
sudo mysql -u root -e "CREATE DATABASE \`mlwp\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci"

# Database structure
mysql -u mlwp -p"${mysql_pwd}" "mlwp" < "/vagrant/db_structure.sql"

# Website user
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO \`user\` (username, password, email, admin, active) VALUES ('mlwp', '\$2y\$10\$vmRaRDg61xiVo.WlQYrdpOQuO3Nh5KJbm6srnHt1rnY5S7NoFr30y', 'mlwp@mlwp', 1, 1);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO \`visits\` (count) VALUES (0);"

# Add some tags that can be used for testing
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO category (name, urlname) VALUES ('My Little Pony', 'my-little-pony');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO category (name, urlname) VALUES ('Cartoon Hangover', 'cartoon-hangover');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Twilight Sparkle', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Applejack', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Fluttershy', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Rainbow Dash', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Pinkie Pie', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Rarity', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Spike', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Apple Bloom', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Scootaloo', '', 'character', '', 1);";
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('The Space Outlaw', '', 'character', '', 2);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Catbug', '', 'character', '', 2);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Chris', '', 'character', '', 2);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Beth', '', 'character', '', 2);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Wallow', '', 'character', '', 2);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type, description, series) VALUES ('Danny', '', 'character', '', 2);"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Humanized', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Crossovers', '', 'general');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Background Characters', '', 'general');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Other', '', 'general');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Shipping', '', 'general');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Gender switches', '', 'general');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Minimalistic', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Grunge', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Splatter', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Only text', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('No text', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Vector', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Hand drawn', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('Featured', '', 'general');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag (name, alternate, type) VALUES ('3D', '', 'style');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('johnjoseco', '');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('Equestria-Prevails', '');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('Paradigm-Zero', 'dignifiedjustice');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('SandwichDelta', 'Delta105');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('Bommster', '');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('Tadashi--kun', '');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('SailorCardKnight', '');"
mysql -u mlwp -p"${mysql_pwd}" "mlwp" -e "INSERT INTO tag_artist (name, oldname) VALUES ('ExtrahoVinco', '');"

# nginx configuration
grep -qF 'local.mlwp' /etc/hosts || sudo sed -i '$ a 192.168.56.20 local.mlwp' /etc/hosts
sudo rm /etc/nginx/sites-enabled/site.conf /etc/nginx/sites-available/site.conf
sudo cp /vagrant/vagrant/configs/site.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/site.conf /etc/nginx/sites-enabled/
if [ -f /etc/nginx/conf.d/client_max_body_size.conf ]; then
  sudo rm /etc/nginx/conf.d/client_max_body_size.conf
fi
sudo touch /etc/nginx/conf.d/client_max_body_size.conf
echo "client_max_body_size 64M;" | sudo tee /etc/nginx/conf.d/client_max_body_size.conf
sudo rm /etc/nginx/sites-enabled/default
sudo systemctl restart nginx

# PHP config
sudo sed -i 's,^[;]\?upload_max_filesize =.*$,upload_max_filesize = 32M,' /etc/php/7.4/fpm/php.ini
sudo sed -i 's,^[;]\?post_max_size =.*$,post_max_size = 64M,' /etc/php/7.4/fpm/php.ini
sudo sed -Ei 's/memory_limit = 128/memory_limit=512/g' /etc/php/7.4/fpm/php.ini

# Copy Vagrant .env config
if [ ! -f .env ]; then
  cd /vagrant && cp .env.vagrant .env
fi

# Composer install
cd /vagrant && composer install

# Ensure services have been enabled
sudo systemctl enable mysql
sudo systemctl enable nginx
sudo systemctl enable php7.4-fpm
