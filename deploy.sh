#!/bin/bash

# Invoice Management System Deployment Script
# This script automates the deployment process

set -e

echo "🚀 Starting deployment process..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory"
    exit 1
fi

# Check if .env file exists
if [ ! -f ".env" ]; then
    print_warning ".env file not found. Creating from .env.production.example..."
    if [ -f ".env.production.example" ]; then
        cp .env.production.example .env
        print_status "Created .env file from production template"
        print_warning "Please update your .env file with production values before continuing"
        exit 1
    else
        print_error ".env.production.example not found. Cannot create .env file"
        exit 1
    fi
fi

# Ask for confirmation
print_warning "This will deploy the application to production mode."
read -p "Are you sure you want to continue? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_status "Deployment cancelled."
    exit 0
fi

# Maintenance mode
print_status "Putting application into maintenance mode..."
php artisan down --render="maintenance" --retry=60

# Clear caches
print_status "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Install dependencies
print_status "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies and build assets
print_status "Installing and building frontend assets..."
npm ci --production
npm run build

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Cache configuration and routes for production
print_status "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set correct file permissions
print_status "Setting file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Create necessary directories
print_status "Creating necessary directories..."
mkdir -p storage/app/exports
mkdir -p storage/app/public/quotes
mkdir -p storage/app/public/invoices
mkdir -p storage/logs

# Optimize Composer autoloader
print_status "Optimizing Composer autoloader..."
composer dump-autoload --optimize

# Bring application out of maintenance mode
print_status "Bringing application out of maintenance mode..."
php artisan up

# Run queue workers if configured
if grep -q "QUEUE_CONNECTION=redis" .env; then
    print_status "Starting queue workers..."
    php artisan queue:restart
fi

# Clean up old files
print_status "Cleaning up old files..."
php artisan cache:prune-stale-tags
php artisan auth:clear-resets

print_status "✅ Deployment completed successfully!"
print_status "Application is now live in production mode."

# Display useful information
echo ""
echo "📊 Post-deployment checklist:"
echo "   • Verify application is accessible at your domain"
echo "   • Test database connectivity"
echo "   • Test email functionality"
echo "   • Check application logs: tail storage/logs/laravel.log"
echo "   • Monitor queue workers if configured"
echo "   • Set up monitoring and alerts"
echo "   • Configure backup schedules"
echo "   • Review security settings"
echo ""

# Display next steps
echo "🔧 Next steps:"
echo "   1. Update your DNS to point to the server"
echo "   2. Configure SSL certificate (Let's Encrypt recommended)"
echo "   3. Set up monitoring (Sentry/New Relic)"
echo "   4. Configure backup strategy"
echo "   5. Set up log rotation"
echo "   6. Configure firewall rules"
echo ""

print_status "Deployment script completed! 🎉"