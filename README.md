# Embroidery Converter

A production-ready, full-featured **online embroidery file conversion SaaS** built with Laravel 11. Convert between PES, DST, JEF, VP3, and 30+ formats via a clean, modern web UI.

---

## Features

- **Authentication** — Register, login, forgot password, email verification, profile settings
- **File Upload** — Drag & drop, multiple files, validation, progress, secure handling
- **Embroidery Conversion** — PES, DST, JEF, EXP, VP3, HUS, XXX, SEW, VIP, PEC, PCS, SHV + more via pyembroidery
- **Batch Conversion** — Convert multiple files at once (plan-limited)
- **File Library** — Search, filter, download, delete, ZIP download
- **Design Preview** — File info (stitch count, colors, dimensions) + PNG preview via pyembroidery
- **Plans & Payments** — Free/Pro/Business tiers, Stripe integration via Laravel Cashier
- **Admin Dashboard** — Users, conversions, plans, settings, analytics
- **Notifications** — Email + in-app on conversion complete/failed
- **Security** — CSRF, rate limiting, file validation, audit logs, role permissions

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 11 (PHP 8.3) |
| Auth | Laravel Breeze (session-based) |
| Payments | Laravel Cashier (Stripe) |
| Queues | Laravel Queues (Redis or database) |
| Conversion Engine | pyembroidery (Python 3) |
| Frontend | Tailwind CSS (CDN) + Alpine.js |
| Database | MySQL 8 (SQLite for dev) |
| Cache/Queue | Redis |

---

## Quick Installation (VPS/Dedicated Server)

### Requirements

- Ubuntu 22.04+ / Debian 12 / CentOS Stream 9
- PHP 8.3 with extensions: `pdo_mysql mbstring xml zip gd bcmath intl redis`
- MySQL 8.0+ or MariaDB 10.6+
- Redis 6+
- Python 3.10+ with pip
- Composer 2.x
- Nginx or Apache

### Automated Installation

```bash
git clone https://github.com/your-org/embroidery-converter.git /var/www/embroidery-converter
cd /var/www/embroidery-converter
sudo bash deploy/install.sh
```

### Manual Installation

**1. Clone the repository**
```bash
git clone https://github.com/your-org/embroidery-converter.git
cd embroidery-converter
```

**2. Install PHP dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

**3. Install pyembroidery (conversion engine)**
```bash
pip3 install pyembroidery
```

**4. Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials, mail settings, and Stripe keys:
```dotenv
APP_URL=https://your-domain.com
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=embroidery_converter
DB_USERNAME=embroidery
DB_PASSWORD=your_secure_password

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your@mailgun.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=noreply@your-domain.com

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

PYEMBROIDERY_AVAILABLE=true
PYEMBROIDERY_PYTHON_BIN=python3

ADMIN_EMAIL=admin@your-domain.com
```

**5. Run migrations and seed data**
```bash
php artisan migrate --force
php artisan db:seed --force
```

**6. Set permissions**
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache
php artisan storage:link
```

**7. Configure web server**

For **Nginx**, copy `deploy/nginx.conf` to `/etc/nginx/sites-available/embroidery-converter` and update domain name.  
For **Apache**, copy `deploy/apache.conf` to your VirtualHost config.

**8. Set up queue workers (Supervisor)**
```bash
sudo apt install supervisor
# Copy the supervisor config from the installer output or manually:
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
```

**9. Set up scheduled tasks**
```bash
# Add to crontab: crontab -e
* * * * * cd /var/www/embroidery-converter && php artisan schedule:run >> /dev/null 2>&1
```

### Environment tips

- **Database host:** when running under Docker Compose, set `DB_HOST=db`. On Dokploy/Railway, use the service hostname provided by the platform (not `127.0.0.1` inside the container).
- **Sessions:** the app uses the `database` session driver. Run `php artisan migrate --force` to ensure the `sessions` table exists.
- **Mail smoke test:** verify SMTP quickly with `php artisan mail:test you@example.com`.

---

## Docker Installation (Optional)

```bash
cp .env.example .env
# Edit .env with your settings
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

---

## Default Admin Credentials

After seeding:
- **Email:** `admin@example.com` (or value of `ADMIN_EMAIL` in `.env`)
- **Password:** `password`

> ⚠️ **Change this password immediately after first login.**

---

## Plans

| Feature | Free | Pro ($9.99/mo) | Business ($29.99/mo) |
|---------|------|-----------------|----------------------|
| Conversions/day | 5 | 100 | Unlimited |
| Storage | 100 MB | 1 GB | 10 GB |
| Max file size | 5 MB | 25 MB | 50 MB |
| Batch conversion | 1 | 10 | 50 |
| Design preview | ✗ | ✓ | ✓ |
| API access | ✗ | ✗ | ✓ |
| Priority queue | ✗ | ✗ | ✓ |

---

## Embroidery Conversion

Conversion is powered by [pyembroidery](https://github.com/EmbroidePy/pyembroidery).

**Supported read/write formats:**  
`pes`, `dst`, `jef`, `exp`, `vp3`, `hus`, `xxx`, `sew`, `vip`, `pec`, `pcs`, `shv`, `csv`, `svg`, `png`, `txt`, `gcode`, and more.

**Note on lossy conversions:**  
Some formats (e.g., `dst`) do not store color information. The app will warn users when conversion may result in data loss.

If `pyembroidery` is not installed, the app operates in "graceful degradation" mode and clearly informs users what is needed.

---

## Security

- All file uploads validated by extension and MIME type
- Files stored outside web root (not publicly accessible)
- Signed, time-limited download URLs
- CSRF protection on all forms
- Role-based access control (admin / user)
- Audit log for all sensitive actions
- Rate limiting on API and auth endpoints
- Input sanitization via Laravel validation

---

## License

MIT License. See [LICENSE](LICENSE) file.
