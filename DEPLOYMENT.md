# Deployment Guide for Bender.chat

This document outlines the deployment process for the Bender satirical news platform.

## Deployment Scripts

Two scripts are provided to manage deployment:

1. `deploy.sh` - Deploys the current codebase to production
2. `rollback.sh` - Rolls back to a previous backup if needed

## Prerequisites

- Ubuntu/Debian-based Linux server
- Nginx
- PHP 8.4+ with PHP-FPM
- Python 3.8+
- SQLite3
- Root access to the server

## Quick Start

To deploy the site:

```bash
sudo ./deploy.sh
```

To rollback to a previous version:

```bash
# List available backups
sudo ./rollback.sh --list

# Rollback to a specific backup
sudo ./rollback.sh 20250729123456
```

## Deployment Process

The `deploy.sh` script performs the following steps:

1. Creates a backup of the current site
2. Updates the database schema and data
3. Updates frontend code and permissions
4. Updates backend code and dependencies
5. Updates the Flask service
6. Updates Nginx configuration
7. Verifies the deployment

All activities are logged to a timestamped log file in the project directory.

## Backup System

Backups are stored in the `backups/` directory with timestamps as folder names.
Each backup includes:

- Database file
- Frontend code
- Backend code
- Nginx configuration
- Systemd service file

## Troubleshooting

### Common Issues

#### 502 Bad Gateway Error

This typically indicates the Flask backend is not running correctly.

Check the Flask service status:
```bash
sudo systemctl status bender-flask
```

Check the Flask logs:
```bash
sudo journalctl -u bender-flask
```

#### 504 Gateway Timeout

This may indicate that the Flask app is running but taking too long to respond.

Check for resource constraints or database issues:
```bash
top
df -h
```

#### SSL Certificate Issues

If you're getting SSL warnings:
```bash
# Check your SSL certificate
sudo nginx -t
```

### Rollback Procedure

If deployment fails, use the rollback script:

1. Stop the deployment process if it's still running
2. List available backups: `sudo ./rollback.sh --list`
3. Roll back to a specific backup: `sudo ./rollback.sh <backup_name>`
4. Verify the site is working

## DNS Configuration

The domain `bender.chat` should be configured with the following records:

- A record pointing to your server's IP address
- Optionally, CNAME for www subdomain

If using Cloudflare:
1. Set SSL/TLS mode to "Full" (not "Strict" if using self-signed certificates)
2. Disable proxying if you're having connection issues (grey cloud icon)

## Security Considerations

- The deployment scripts require root access
- Database permissions are set to allow read/write by the web server
- The scripts maintain backups of all previous deployments

## Directory Structure

```
/home/zappy/bender/
├── backend/          # Flask backend code
├── frontend/         # PHP frontend code
├── .venv/            # Python virtual environment
├── backups/          # Deployment backups
├── db.sqlite         # SQLite database
├── deploy.sh         # Deployment script
├── rollback.sh       # Rollback script
└── bender-flask.service  # Systemd service configuration
```

## Monitoring

After deployment, monitor the site for any issues:

- Check Nginx error logs: `sudo tail -f /var/log/nginx/error.log`
- Check Flask service logs: `sudo journalctl -f -u bender-flask`
- Monitor system resources: `htop`

## Maintenance Mode

To put the site in maintenance mode, create a temporary Nginx configuration:

```bash
sudo cp /etc/nginx/sites-available/bender.conf /etc/nginx/sites-available/bender.conf.bak
sudo nano /etc/nginx/sites-available/bender.conf
```

Add a return statement to the server block:

```nginx
server {
    listen 80;
    server_name bender.chat www.bender.chat;
    return 503;
    error_page 503 /maintenance.html;
    location = /maintenance.html {
        root /home/zappy/bender/frontend;
    }
}
```

Create a maintenance page in `/home/zappy/bender/frontend/maintenance.html` and reload Nginx:

```bash
sudo systemctl reload nginx
```

To exit maintenance mode:

```bash
sudo mv /etc/nginx/sites-available/bender.conf.bak /etc/nginx/sites-available/bender.conf
sudo systemctl reload nginx
```
