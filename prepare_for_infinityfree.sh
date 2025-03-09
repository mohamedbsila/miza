#!/bin/bash

# Script to prepare Symfony project for InfinityFree deployment

echo "Preparing MIZA for InfinityFree deployment..."

# Install dependencies without dev packages
echo "Installing production dependencies..."
composer install --no-dev --optimize-autoloader

# Clear and warm up cache
echo "Clearing and warming up cache..."
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

# Create var directory with proper permissions if it doesn't exist
echo "Setting up var directory..."
mkdir -p var/cache var/log
chmod -R 777 var/cache var/log

# Create uploads directory with proper permissions if it doesn't exist
echo "Setting up uploads directory..."
mkdir -p public/uploads
chmod -R 777 public/uploads

# Dump environment variables for production
echo "Dumping environment variables for production..."
composer dump-env prod

echo "Checking for .htaccess files..."
if [ ! -f ".htaccess" ]; then
    echo "Root .htaccess file is missing!"
fi

if [ ! -f "public/.htaccess" ]; then
    echo "Public .htaccess file is missing!"
fi

echo "Preparation complete!"
echo "Please update your .env file with your InfinityFree database credentials before uploading."
echo "See INFINITYFREE_DEPLOYMENT.md for detailed deployment instructions." 