#!/bin/bash

# Deployment Script for CBC Project
# Usage: ./scripts/deploy.sh

set -e

PROJECT_PATH="/var/www/cbc"
PHP_VERSION="8.2"

echo "🔄 Starting Deployment..."

cd $PROJECT_PATH

# Check if we are in a git repository
if [ ! -d ".git" ]; then
    echo "❌ Error: Not a git repository. Please clone the repo first."
    exit 1
fi

# Pull latest changes
# git fetch --all
# git reset --hard origin/main # Or your production branch

# Install Composer Dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install NPM Dependencies and Build Assets
npm install
npm run build

# Run Migrations
php artisan migrate --force

# Link Storage
php artisan storage:link || true

# Clear Caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart Horizon
php artisan horizon:terminate

# Restart SSR (Inertia)
# Supervisor will automatically restart the process after it's killed
# We expect supervisor to manage 'php artisan inertia:start-ssr'
sudo supervisorctl restart horizon
sudo supervisorctl restart cbc-ssr

echo "🚀 Deployment Completed Successfully!"
