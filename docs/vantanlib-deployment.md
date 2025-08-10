# vantanlib.com Production Deployment Guide

This document provides comprehensive instructions for deploying the Library Management System to vantanlib.com with HTTPS support.

## 🎯 Overview

The vantanlib.com deployment includes:
- **Domain**: vantanlib.com (with www.vantanlib.com redirect)
- **SSL**: Let's Encrypt automatic certificate management
- **Services**: Nginx, Laravel App, MySQL, Redis, Certbot
- **Features**: HTTPS enforcement, HTTP/2, security headers, health monitoring

## 📋 Prerequisites

### Domain and DNS Setup
- [ ] vantanlib.com domain registered and configured
- [ ] DNS A record pointing to your server IP
- [ ] DNS AAAA record (if using IPv6)
- [ ] www.vantanlib.com CNAME pointing to vantanlib.com

### Server Requirements
- [ ] Ubuntu 20.04+ or CentOS 8+ server
- [ ] Docker and Docker Compose installed
- [ ] Ports 80 and 443 open in firewall
- [ ] At least 2GB RAM and 20GB disk space
- [ ] admin@vantanlib.com email configured

### Email Configuration
- [ ] SMTP server configured for vantanlib.com
- [ ] admin@vantanlib.com mailbox created
- [ ] noreply@vantanlib.com configured

## 🚀 Deployment Steps

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Logout and login again to apply docker group
```

### 2. Project Setup

```bash
# Clone repository
git clone https://github.com/your-repo/library-management.git /var/www/library
cd /var/www/library

# Set proper permissions
sudo chown -R $USER:$USER /var/www/library
chmod -R 755 /var/www/library
```

### 3. Environment Configuration

```bash
cd /var/www/library/laravel-app

# Copy and configure production environment
cp .env.production .env

# Generate application key
docker run --rm -v $(pwd):/app composer:latest composer install --no-dev --optimize-autoloader
docker run --rm -v $(pwd):/app php:8.2-cli php artisan key:generate --show

# Update .env with generated key and secure passwords
nano .env
```

**Required .env updates:**
```env
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
DB_PASSWORD=your_secure_db_password
DB_ROOT_PASSWORD=your_secure_root_password
REDIS_PASSWORD=your_secure_redis_password
MAIL_PASSWORD=your_mail_password
GOOGLE_BOOKS_API_KEY=your_google_books_api_key
```

### 4. SSL Certificate Setup

```bash
# Verify DNS resolution
nslookup vantanlib.com
nslookup www.vantanlib.com

# Test HTTP connectivity (before SSL)
curl -I http://vantanlib.com
```

### 5. Deploy Application

```bash
# Run deployment script
./scripts/deploy-vantanlib.sh
```

The deployment script will:
1. ✅ Validate prerequisites
2. 🔨 Build application containers
3. 🗄️ Start database services
4. 🔄 Run migrations
5. 🌐 Configure Nginx
6. 🔒 Obtain SSL certificates
7. 🚀 Start all services
8. 🏥 Perform health checks

### 6. Post-Deployment Verification

```bash
# Check service status
docker-compose -f laravel-app/docker-compose.prod.yml ps

# Test HTTPS
curl -I https://vantanlib.com
curl -I https://www.vantanlib.com

# Test HTTP redirect
curl -I http://vantanlib.com

# Check health endpoint
curl https://vantanlib.com/health

# View logs
docker-compose -f laravel-app/docker-compose.prod.yml logs -f
```

## 🔧 Configuration Details

### Docker Services

| Service | Container | Port | Purpose |
|---------|-----------|------|---------|
| nginx | library-prod-nginx | 80, 443 | Web server & SSL termination |
| app | library-prod-app | 8000 | Laravel application |
| db | library-prod-db | 3306 | MySQL database |
| redis | library-prod-redis | 6379 | Cache & sessions |
| certbot | library-prod-certbot | - | SSL certificate management |

### SSL Certificate Management

- **Provider**: Let's Encrypt
- **Domains**: vantanlib.com, www.vantanlib.com
- **Renewal**: Automatic every 90 days
- **Cron Job**: Daily at 2:00 AM
- **Logs**: `/var/log/ssl-renewal.log`

### Security Features

- **HTTPS Enforcement**: All HTTP traffic redirected to HTTPS
- **Security Headers**: HSTS, CSP, X-Frame-Options, etc.
- **TLS Version**: TLS 1.2+ only
- **Cipher Suites**: Strong encryption algorithms
- **OCSP Stapling**: Enabled for performance

## 📊 Monitoring and Maintenance

### Health Monitoring

```bash
# Application health
curl https://vantanlib.com/health

# SSL certificate status
openssl s_client -connect vantanlib.com:443 -servername vantanlib.com

# Service status
docker-compose -f laravel-app/docker-compose.prod.yml ps
```

### Log Management

```bash
# Application logs
docker-compose -f laravel-app/docker-compose.prod.yml logs app

# Nginx logs
docker-compose -f laravel-app/docker-compose.prod.yml logs nginx

# SSL renewal logs
tail -f /var/log/ssl-renewal.log

# System logs
journalctl -u docker
```

### Backup Procedures

```bash
# Database backup
docker-compose -f laravel-app/docker-compose.prod.yml exec db mysqldump -u root -p library_production > backup_$(date +%Y%m%d).sql

# SSL certificates backup
tar -czf ssl_backup_$(date +%Y%m%d).tar.gz laravel-app/docker/certbot/conf/

# Application files backup
tar -czf app_backup_$(date +%Y%m%d).tar.gz --exclude=node_modules --exclude=vendor /var/www/library
```

## 🚨 Troubleshooting

### Common Issues

#### SSL Certificate Acquisition Failed

```bash
# Check DNS resolution
nslookup vantanlib.com

# Check port accessibility
telnet vantanlib.com 80

# Check Let's Encrypt rate limits
curl -s "https://crt.sh/?q=vantanlib.com&output=json" | jq length

# Manual certificate request
docker-compose -f laravel-app/docker-compose.prod.yml run --rm certbot certonly --webroot -w /var/www/certbot --email admin@vantanlib.com -d vantanlib.com -d www.vantanlib.com --dry-run
```

#### Application Not Responding

```bash
# Check container status
docker-compose -f laravel-app/docker-compose.prod.yml ps

# Check application logs
docker-compose -f laravel-app/docker-compose.prod.yml logs app

# Check database connection
docker-compose -f laravel-app/docker-compose.prod.yml exec app php artisan tinker
# In tinker: DB::connection()->getPdo();
```

#### Database Connection Issues

```bash
# Check database status
docker-compose -f laravel-app/docker-compose.prod.yml exec db mysql -u root -p -e "SHOW DATABASES;"

# Reset database password
docker-compose -f laravel-app/docker-compose.prod.yml exec db mysql -u root -p -e "ALTER USER 'library_user'@'%' IDENTIFIED BY 'new_password';"
```

### Emergency Procedures

#### Rollback Deployment

```bash
# Stop current deployment
docker-compose -f laravel-app/docker-compose.prod.yml down

# Restore from backup
tar -xzf app_backup_YYYYMMDD.tar.gz -C /

# Restore database
docker-compose -f laravel-app/docker-compose.prod.yml up -d db
docker-compose -f laravel-app/docker-compose.prod.yml exec -T db mysql -u root -p library_production < backup_YYYYMMDD.sql

# Restart services
docker-compose -f laravel-app/docker-compose.prod.yml up -d
```

#### SSL Certificate Emergency Renewal

```bash
# Force certificate renewal
docker-compose -f laravel-app/docker-compose.prod.yml run --rm certbot certbot renew --force-renewal

# Restart nginx
docker-compose -f laravel-app/docker-compose.prod.yml restart nginx
```

## 📞 Support Contacts

- **Technical Issues**: admin@vantanlib.com
- **SSL Certificate Issues**: Check Let's Encrypt status page
- **DNS Issues**: Contact domain registrar
- **Server Issues**: Contact hosting provider

## 📚 Additional Resources

- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Nginx SSL Configuration](https://nginx.org/en/docs/http/configuring_https_servers.html)

---

**Last Updated**: $(date +'%Y-%m-%d')
**Version**: 1.0.0
**Environment**: vantanlib.com Production