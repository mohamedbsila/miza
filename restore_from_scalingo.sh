#!/bin/bash

# Script to restore original files after Scalingo deployment

echo "Restoring original files..."

# Restore the original index.php
if [ -f public/index.php.original ]; then
    echo "Restoring original index.php"
    cp public/index.php.original public/index.php
    rm public/index.php.original
else
    echo "No backup of index.php found"
fi

# Restore the original composer.json
if [ -f composer.json.original ]; then
    echo "Restoring original composer.json"
    cp composer.json.original composer.json
    rm composer.json.original
else
    echo "No backup of composer.json found"
fi

echo "Restoration complete!" 