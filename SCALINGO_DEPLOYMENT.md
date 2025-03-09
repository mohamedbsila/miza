# Deploying MIZA to Scalingo

This guide will help you deploy your Symfony application to Scalingo.

## Prerequisites

1. A Scalingo account (sign up at [scalingo.com](https://scalingo.com/))
2. Git installed on your local machine
3. Scalingo CLI (optional but recommended)

## Step 1: Set Up Your Scalingo Account

1. Log in to your Scalingo dashboard at [dashboard.scalingo.com](https://dashboard.scalingo.com/)
2. Create a new application or use your existing "mizadessin" app
3. Note your application name and region

## Step 2: Configure Your Project

Your project already includes the necessary configuration files for Scalingo:

1. `Procfile`: Tells Scalingo how to run your application
2. `.env.scalingo`: Environment variables specific to Scalingo
3. `scalingo.json`: Defines your application's configuration and addons

## Step 3: Set Up Environment Variables

In your Scalingo dashboard:

1. Go to the "Environment" tab of your application
2. Add the following environment variables:
   - `APP_ENV`: `prod`
   - `APP_DEBUG`: `0`
   - `APP_SECRET`: Generate a random string or use the one provided by Scalingo
   - `PAYPAL_CLIENT_ID`: Your PayPal client ID
   - `PAYPAL_CLIENT_SECRET`: Your PayPal client secret

## Step 4: Deploy Your Application

### Option 1: Deploy via Git (Recommended)

1. Add Scalingo as a remote to your Git repository:
   ```
   git remote add scalingo git@ssh.osc-fr1.scalingo.com:mizadessin.git
   ```

2. Push your code to Scalingo:
   ```
   git push scalingo main
   ```

### Option 2: Deploy via Scalingo CLI

1. Install the Scalingo CLI:
   ```
   # For macOS
   brew install scalingo

   # For Linux
   curl -O https://cli-dl.scalingo.io/install && bash install
   ```

2. Log in to your Scalingo account:
   ```
   scalingo login
   ```

3. Deploy your application:
   ```
   scalingo --app mizadessin deploy
   ```

### Option 3: Deploy via GitHub Integration

1. In your Scalingo dashboard, go to the "Deploy" tab
2. Select "GitHub" as the deployment method
3. Connect your GitHub account and select your repository
4. Choose the branch to deploy (usually `main`)
5. Enable automatic deployments if desired

## Step 5: Set Up the Database

1. In your Scalingo dashboard, go to the "Resources" tab
2. Add a MySQL addon if not already added
3. The database connection details will be automatically added to your environment variables

4. Run migrations to set up your database schema:
   ```
   scalingo --app mizadessin run php bin/console doctrine:migrations:migrate --no-interaction
   ```

## Step 6: Monitor Your Application

1. In your Scalingo dashboard, go to the "Logs" tab to view application logs
2. Use the "Metrics" tab to monitor resource usage
3. Set up alerts in the "Notifications" section if needed

## Troubleshooting

### Common Issues

1. **Deployment Failures**:
   - Check your Procfile is correctly configured
   - Ensure all dependencies are properly defined in composer.json
   - Review deployment logs for specific errors

2. **Database Connection Issues**:
   - Verify the MySQL addon is properly installed
   - Check if your application is using the DATABASE_URL environment variable

3. **Application Crashes**:
   - Check the logs for error messages
   - Ensure your application is compatible with the PHP version on Scalingo

### Scalingo CLI Commands

Here are some useful Scalingo CLI commands:

```
# View logs
scalingo --app mizadessin logs --lines 100

# Run a one-off command
scalingo --app mizadessin run php bin/console cache:clear

# Scale your application
scalingo --app mizadessin scale web:1:S

# Restart your application
scalingo --app mizadessin restart
```

## Additional Resources

- [Scalingo Documentation](https://doc.scalingo.com/)
- [Symfony Deployment Documentation](https://symfony.com/doc/current/deployment.html)
- [Scalingo PHP Support](https://doc.scalingo.com/languages/php/start)

## Support

If you encounter any issues specific to your application, please check the Scalingo documentation or contact Scalingo support. 