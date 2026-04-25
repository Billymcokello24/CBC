#!/bin/bash

# VPS Setup Script for CBC Project
# Targeted for Ubuntu 22.04 / 24.04

set -e

echo "🚀 Starting VPS Setup for CBC Project..."

# Update System
sudo apt update && sudo apt upgrade -y

# Install Basic Utilities
sudo apt install -y curl wget git unzip software-properties-common-base build-essential

# Add PHP Repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 & Extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring \
    php8.2-zip php8.2-bcmath php8.2-intl php8.2-redis php8.2-gd php8.2-sqlite3 php8.2-imagick

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Node.js (Latest LTS)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Install Supervisor
sudo apt install -y supervisor

# Configure Firewall
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
# sudo ufw --force enable

echo "✅ System dependencies installed!"
echo "------------------------------------------------"
echo "Next Steps:"
echo "1. Run 'sudo mysql_secure_installation' to secure your DB."
echo "2. Create a database and user for the project."
echo "3. Clone the repository into /var/www/cbc."
echo "4. Use deploy.sh for the first deployment."
echo "------------------------------------------------"
