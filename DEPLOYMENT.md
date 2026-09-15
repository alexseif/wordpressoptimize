# DigitalOcean Production Deployment Runbook

This runbook details the one-time initial provisioning and ongoing continuous deployment for `wordpressoptimize.com` on a DigitalOcean droplet running standard multi-site WordPress architecture.

---

## Architecture Specifications

* **Server Standard Webroot**: `/var/www/wordpressoptimize.com/public`
* **Theme Directory**: `/var/www/wordpressoptimize.com/public/wp-content/themes/wpopt`
* **Git Repository**: `git@github.com:alexseif/wordpressoptimize.git` (Theme root)
* **PHP Runtime**: PHP 8.2+ FPM
* **Web Server**: Nginx
* **Database**: MySQL / MariaDB

---

## Phase 1: Initial Provisioning (One-Time Setup)

### 1. Database & Server Directories
On your DigitalOcean droplet:
```bash
# 1. Create site root directory
sudo mkdir -p /var/www/wordpressoptimize.com/public
sudo chown -R $USER:www-data /var/www/wordpressoptimize.com

# 2. Create MySQL database and user
mysql -u root -p -e "
CREATE DATABASE wp_optimize CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
CREATE USER 'wp_opt_user'@'localhost' IDENTIFIED BY 'STRONG_PRODUCTION_PASSWORD';
GRANT ALL PRIVILEGES ON wp_optimize.* TO 'wp_opt_user'@'localhost';
FLUSH PRIVILEGES;"
```

### 2. Install WordPress Core & Configure wp-config.php
```bash
cd /var/www/wordpressoptimize.com/public

# Download core files
wp core download --path=/var/www/wordpressoptimize.com/public

# Generate production wp-config.php
wp config create \
  --dbname=wp_optimize \
  --dbuser=wp_opt_user \
  --dbpass='STRONG_PRODUCTION_PASSWORD' \
  --dbhost=localhost \
  --dbprefix=wp_ \
  --path=/var/www/wordpressoptimize.com/public
```

### 3. Deploy Theme Repository
Clone the theme directly into `wp-content/themes/wpopt`:
```bash
cd /var/www/wordpressoptimize.com/public/wp-content/themes
git clone git@github.com:alexseif/wordpressoptimize.git wpopt
```

### 4. Import Initial Database
From your local machine, transfer `initial-launch.sql`:
```bash
# Run locally:
scp /var/www/wordpressoptimize.com/initial-launch.sql user@droplet-ip:/var/www/wordpressoptimize.com/
```

On your DigitalOcean droplet, import the database:
```bash
# Run on droplet:
wp db import /var/www/wordpressoptimize.com/initial-launch.sql --path=/var/www/wordpressoptimize.com/public

# Clean up the dump file
rm /var/www/wordpressoptimize.com/initial-launch.sql
```

### 5. Install & Activate Plugins and Theme
```bash
# Install and activate Contact Form 7
wp plugin install contact-form-7 --activate --path=/var/www/wordpressoptimize.com/public

# Activate wpopt theme
wp theme activate wpopt --path=/var/www/wordpressoptimize.com/public

# Flush cache and rewrite rules
wp cache flush --path=/var/www/wordpressoptimize.com/public
wp rewrite flush --path=/var/www/wordpressoptimize.com/public
```

### 6. Set Permissions & Nginx
```bash
# Set proper ownership and file permissions
sudo chown -R www-data:www-data /var/www/wordpressoptimize.com/public
sudo find /var/www/wordpressoptimize.com/public -type d -exec chmod 755 {} \;
sudo find /var/www/wordpressoptimize.com/public -type f -exec chmod 644 {} \;

# Make deploy.sh executable
sudo chmod +x /var/www/wordpressoptimize.com/public/wp-content/themes/wpopt/deploy.sh
```

---

## Phase 2: Continuous Deployment (CD Workflow)

The droplet does **not** require Node.js or npm. All production assets are compiled locally before committing.

### Local Development Loop:
```bash
# 1. Navigate to theme
cd /var/www/wordpressoptimize.com/public/wp-content/themes/wpopt

# 2. Make code/style edits
# (Run 'npm run sass:watch' during active CSS authoring)

# 3. Compile compressed production assets
npm run sass:compressed

# 4. Commit and push
git add -A
git commit -m "feat: description of changes"
git push origin master
```

### Droplet Deployment:
On the DigitalOcean server, simply execute:
```bash
/var/www/wordpressoptimize.com/public/wp-content/themes/wpopt/deploy.sh
```
This automatically:
1. Pulls the latest theme updates from `origin/master`.
2. Flushes the WordPress object/page cache.
