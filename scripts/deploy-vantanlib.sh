#!/bin/bash

# vantanlib.com Production Deployment Script
# This script deploys the library management system to vantanlib.com with HTTPS

set -e  # Exit on any error

# Configuration
DOMAIN="vantanlib.com"
EMAIL="admin@vantanlib.com"
PROJECT_DIR="/var/www/library"
LOG_FILE="/var/log/vantanlib-deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a $LOG_FILE
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a $LOG_FILE
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a $LOG_FILE
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root for security reasons"
   exit 1
fi

# Check if Docker and Docker Compose are installed
if ! command -v docker &> /dev/null; then
    error "Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

log "🚀 Starting vantanlib.com production deployment..."

# Check DNS resolution
log "🔍 Checking DNS resolution for $DOMAIN..."
if ! nslookup $DOMAIN > /dev/null 2>&1; then
    error "DNS resolution failed for $DOMAIN. Please check your DNS settings."
    exit 1
fi

# Check if ports 80 and 443 are available
log "🔍 Checking if ports 80 and 443 are available..."
if netstat -tuln | grep -q ":80 "; then
    warning "Port 80 is already in use. This might cause conflicts."
fi

if netstat -tuln | grep -q ":443 "; then
    warning "Port 443 is already in use. This might cause conflicts."
fi

# Navigate to project directory
cd $PROJECT_DIR/laravel-app

# Check if .env.production exists
if [ ! -f .env.production ]; then
    error ".env.production file not found. Please create it with proper vantanlib.com configuration."
    exit 1
fi

# Copy production environment file
log "📝 Setting up production environment..."
cp .env.production .env

# Create necessary directories
log "📁 Creating necessary directories..."
mkdir -p docker/certbot/conf docker/certbot/www
mkdir -p mysql_data_prod redis_data_prod
mkdir -p storage/logs

# Set proper permissions
log "🔐 Setting proper permissions..."
chmod -R 755 docker/certbot
chmod -R 755 mysql_data_prod redis_data_prod
chmod -R 755 storage

# Stop any existing containers
log "🛑 Stopping existing containers..."
docker-compose -f docker-compose.prod.yml down --remove-orphans || true

# Pull latest images
log "📥 Pulling latest Docker images..."
docker-compose -f docker-compose.prod.yml pull

# Build application container
log "🔨 Building application container..."
docker-compose -f docker-compose.prod.yml build app

# Start database and redis first
log "🗄️ Starting database and Redis services..."
docker-compose -f docker-compose.prod.yml up -d db redis

# Wait for database to be ready
log "⏳ Waiting for database to be ready..."
sleep 30

# Run database migrations
log "🔄 Running database migrations..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

# Clear and cache configuration
log "🧹 Clearing and caching configuration..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache

# Start nginx (without SSL first for Let's Encrypt verification)
log "🌐 Starting Nginx for Let's Encrypt verification..."
docker-compose -f docker-compose.prod.yml up -d nginx

# Wait for nginx to be ready
sleep 10

# Obtain SSL certificate
log "🔒 Obtaining SSL certificate from Let's Encrypt..."
docker-compose -f docker-compose.prod.yml run --rm certbot

# Check if certificate was obtained successfully
if [ -f "docker/certbot/conf/live/$DOMAIN/fullchain.pem" ]; then
    log "✅ SSL certificate obtained successfully!"
else
    error "Failed to obtain SSL certificate. Check the logs above."
    exit 1
fi

# Restart nginx with SSL configuration
log "🔄 Restarting Nginx with SSL configuration..."
docker-compose -f docker-compose.prod.yml restart nginx

# Start all services
log "🚀 Starting all services..."
docker-compose -f docker-compose.prod.yml up -d

# Wait for services to be ready
log "⏳ Waiting for all services to be ready..."
sleep 30

# Health check
log "🏥 Performing health check..."
if curl -f -k https://$DOMAIN/health > /dev/null 2>&1; then
    log "✅ Health check passed! vantanlib.com is responding."
else
    warning "Health check failed. The site might still be starting up."
fi

# Test HTTPS redirect
log "🔄 Testing HTTP to HTTPS redirect..."
if curl -I http://$DOMAIN 2>/dev/null | grep -q "301\|302"; then
    log "✅ HTTP to HTTPS redirect is working."
else
    warning "HTTP to HTTPS redirect might not be working properly."
fi

# Setup SSL certificate renewal cron job
log "⏰ Setting up SSL certificate renewal cron job..."
CRON_JOB="0 2 * * * $PROJECT_DIR/scripts/renew-ssl.sh >> /var/log/ssl-renewal.log 2>&1"
(crontab -l 2>/dev/null | grep -v "renew-ssl.sh"; echo "$CRON_JOB") | crontab -

# Display final status
log "🎉 Deployment completed successfully!"
log "📊 Service Status:"
docker-compose -f docker-compose.prod.yml ps

log "🌐 Your site is now available at:"
log "   https://$DOMAIN"
log "   https://www.$DOMAIN (redirects to non-www)"

log "📋 Next steps:"
log "   1. Test all functionality on https://$DOMAIN"
log "   2. Monitor logs: docker-compose -f docker-compose.prod.yml logs -f"
log "   3. SSL certificate will auto-renew every 90 days"
log "   4. Check renewal logs at: /var/log/ssl-renewal.log"

log "✅ vantanlib.com deployment completed successfully!"