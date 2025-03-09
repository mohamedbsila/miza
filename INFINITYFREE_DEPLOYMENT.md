# Deploying MIZA to InfinityFree

This guide will help you deploy your Symfony application to InfinityFree hosting.

## Prerequisites

1. An InfinityFree account (sign up at [infinityfree.net](https://infinityfree.net/))
2. FTP client (like FileZilla)
3. Your Symfony project ready for production

## Step 1: Set Up Your InfinityFree Account

1. Log in to your InfinityFree account
2. Create a new hosting account or use an existing one
3. Note your domain name, FTP username, FTP password, and MySQL database credentials

## Step 2: Configure Your Project

1. Update your `.env` file with your InfinityFree database credentials:
   ```
   DATABASE_URL="mysql://epiz_username:password@sql.infinityfree.com:3306/epiz_dbname?serverVersion=5.7"
   ```
   Replace `epiz_username`, `password`, and `epiz_dbname` with your actual InfinityFree database credentials.

2. Generate a new APP_SECRET and update it in your `.env` file:
   ```
   APP_SECRET=your_new_secret_here
   ```

3. Make sure your project is configured for production:
   ```
   APP_ENV=prod
   APP_DEBUG=0
   ```

## Step 3: Prepare Your Project for Upload

1. Run Composer to install dependencies without development packages:
   ```
   composer install --no-dev --optimize-autoloader
   ```

2. Clear and warm up the cache:
   ```
   php bin/console cache:clear --env=prod --no-debug
   php bin/console cache:warmup --env=prod --no-debug
   ```

3. Make sure your `.htaccess` files are properly configured (both in the root directory and in the `public/` directory)

## Step 4: Upload Your Files

1. Connect to your InfinityFree hosting using FTP:
   - Host: `ftpupload.net`
   - Username: Your InfinityFree FTP username
   - Password: Your InfinityFree FTP password
   - Port: 21

2. Upload all files to the `htdocs` directory on the server

3. Make sure to set proper permissions:
   - Directories: 755
   - Files: 644
   - Executable files (like `bin/console`): 755

## Step 5: Set Up the Database

1. Log in to your InfinityFree control panel
2. Go to the MySQL Databases section
3. Create a new database or use an existing one
4. Import your database schema using phpMyAdmin or run migrations:
   ```
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

## Step 6: Test Your Application

1. Visit your website URL to ensure everything is working correctly
2. Check for any errors in the logs

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**:
   - Check your `.htaccess` files
   - Ensure proper file permissions
   - Review the error logs in your InfinityFree control panel

2. **Database Connection Issues**:
   - Verify your database credentials
   - Make sure you're using the correct database host (`sql.infinityfree.com`)

3. **Missing Assets**:
   - Ensure all assets are properly uploaded
   - Check if the paths in your templates are correct

4. **Memory Limit Errors**:
   - Optimize your application to use less memory
   - Adjust memory settings in `.htaccess` if possible

### InfinityFree Limitations

- PHP memory limit: 256MB
- Max execution time: 300 seconds
- Max file upload size: 20MB
- No SSH access
- Limited PHP extensions

## Additional Resources

- [InfinityFree Knowledge Base](https://support.infinityfree.net/kb/)
- [Symfony Deployment Documentation](https://symfony.com/doc/current/deployment.html)

## Support

If you encounter any issues specific to your application, please check the Symfony documentation or seek help from the Symfony community. 