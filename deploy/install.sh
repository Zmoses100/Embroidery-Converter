#!/usr/bin/env bash
# Embroidery Converter - VPS Installation Script
# Tested on Ubuntu 22.04 / Debian 12
# Run as root: sudo bash install.sh

set -e

APP_DIR="/var/www/embroidery-converter"
PHP_VERSION="8.3"

echo ""
echo "╔════════════════════════════════════════════╗"
echo "║    Embroidery Converter - Installer        ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# ── Step 1: System packages ──────────────────────────────────────────────────
echo "[1/8] Updating system packages..."
apt-get update -qq
apt-get install -y -qq \
    curl git unzip zip wget \
    software-properties-common \
    nginx certbot python3-certbot-nginx

# ── Step 2: PHP 8.3 ──────────────────────────────────────────────────────────
echo "[2/8] Installing PHP ${PHP_VERSION}..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-redis

# Tune PHP for file uploads
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 55M/" /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i "s/post_max_size = .*/post_max_size = 55M/" /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i "s/max_execution_time = .*/max_execution_time = 300/" /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 256M/" /etc/php/${PHP_VERSION}/fpm/php.ini

systemctl restart php${PHP_VERSION}-fpm

# ── Step 3: PostgreSQL ─────────────────────────────────────────────────────────
echo "[3/8] Installing PostgreSQL..."
apt-get install -y -qq postgresql postgresql-contrib

DB_PASS=$(openssl rand -base64 16 | tr -d '/+=')
sudo -u postgres psql -c "CREATE USER embroidery WITH PASSWORD '${DB_PASS}';"
sudo -u postgres psql -c "CREATE DATABASE embroidery_converter OWNER embroidery;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE embroidery_converter TO embroidery;"

echo "Database credentials:"
echo "  Name:     embroidery_converter"
echo "  User:     embroidery"
echo "  Password: ${DB_PASS}"

# ── Step 4: Redis ─────────────────────────────────────────────────────────────
echo "[4/8] Installing Redis..."
apt-get install -y -qq redis-server
systemctl enable redis-server
systemctl start redis-server

# ── Step 5: Python + pyembroidery ─────────────────────────────────────────────
echo "[5/8] Installing Python and pyembroidery..."
apt-get install -y -qq python3 python3-pip
pip3 install pyembroidery

# ── Step 6: Composer ─────────────────────────────────────────────────────────
echo "[6/8] Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ── Step 7: Laravel app ───────────────────────────────────────────────────────
echo "[7/8] Installing application..."

# Create app directory
mkdir -p ${APP_DIR}
chown -R www-data:www-data ${APP_DIR}

# Copy files (assumes script is run from repo root or adjust path)
if [ -d "$(pwd)/app" ]; then
    rsync -a --exclude=vendor --exclude=node_modules . ${APP_DIR}/
else
    echo "ERROR: Run this script from the repository root directory."
    exit 1
fi

cd ${APP_DIR}

# Install dependencies
sudo -u www-data composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Set up .env
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Update database credentials
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/DB_PORT=.*/DB_PORT=5432/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=embroidery_converter/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=embroidery/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/" .env
sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=redis/" .env
sed -i "s/PYEMBROIDERY_AVAILABLE=.*/PYEMBROIDERY_AVAILABLE=true/" .env

# Generate app key
php artisan key:generate --force

# Set permissions
chown -R www-data:www-data ${APP_DIR}
chmod -R 755 ${APP_DIR}/storage
chmod -R 755 ${APP_DIR}/bootstrap/cache

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# ── Step 8: Nginx + Supervisor ────────────────────────────────────────────────
echo "[8/8] Configuring Nginx and queue workers..."

# Nginx config
cp ${APP_DIR}/deploy/nginx.conf /etc/nginx/sites-available/embroidery-converter
ln -sf /etc/nginx/sites-available/embroidery-converter /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Supervisor for queue workers
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/embroidery-worker.conf << SUPERVISOR
[program:embroidery-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
directory=${APP_DIR}
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR

cat > /etc/supervisor/conf.d/embroidery-scheduler.conf << SUPERVISOR
[program:embroidery-scheduler]
command=sh -c "while true; do php ${APP_DIR}/artisan schedule:run >> /dev/null 2>&1; sleep 60; done"
directory=${APP_DIR}
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/scheduler.log
SUPERVISOR

supervisorctl reread
supervisorctl update
supervisorctl start all

echo ""
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║  Installation complete!                                           ║"
echo "║                                                                   ║"
echo "║  Next steps:                                                      ║"
echo "║  1. Update /etc/nginx/sites-available/embroidery-converter        ║"
echo "║     with your real domain name                                    ║"
echo "║  2. Run: certbot --nginx -d your-domain.com                       ║"
echo "║  3. Update ${APP_DIR}/.env with:                                  ║"
echo "║     - APP_URL=https://your-domain.com                             ║"
echo "║     - MAIL_* settings                                             ║"
echo "║     - STRIPE_KEY / STRIPE_SECRET (for payments)                   ║"
echo "║  4. Login at https://your-domain.com with:                        ║"
echo "║     admin@example.com / password                                  ║"
echo "║  5. CHANGE THE ADMIN PASSWORD IMMEDIATELY                         ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
