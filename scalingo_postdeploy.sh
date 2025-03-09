#!/bin/bash

# This script runs after the application is deployed on Scalingo
# It sets up the database and performs other post-deployment tasks

echo "Running post-deployment tasks..."

# Run database migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Clear and warm up cache
echo "Clearing and warming up cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

echo "Post-deployment tasks completed successfully!" 