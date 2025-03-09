#!/bin/bash

# Script to prepare for deployment on Scalingo

echo "Preparing for Scalingo deployment..."

# Backup the original index.php
if [ -f public/index.php.original ]; then
    echo "Backup already exists"
else
    echo "Backing up original index.php"
    cp public/index.php public/index.php.original
fi

# Copy the standalone index.php
echo "Copying standalone index.php"
cp public/standalone.php public/index.php

# Copy the simplified composer.json
echo "Copying simplified composer.json"
cp composer.json composer.json.original
cp composer.json.scalingo composer.json

echo "Preparation complete!"
echo "You can now deploy to Scalingo."
echo "To restore the original files, run: ./restore_from_scalingo.sh" 