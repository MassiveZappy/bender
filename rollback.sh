#!/bin/bash

# Rollback script for Bender website
# This script reverts to a previous backup in case of deployment issues

# Exit on any error
set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${SCRIPT_DIR}/backups"
NGINX_CONF="/etc/nginx/sites-available/bender.conf"
NGINX_LINK="/etc/nginx/sites-enabled/bender.conf"
SERVICE_NAME="bender-flask"
LOG_FILE="${SCRIPT_DIR}/rollback_$(date +%Y%m%d%H%M%S).log"

# Function to log messages
log() {
    local msg="$1"
    local level="${2:-INFO}"
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] [${level}] ${msg}" | tee -a "$LOG_FILE"
}

log_success() {
    log "${GREEN}$1${NC}" "SUCCESS"
}

log_error() {
    log "${RED}$1${NC}" "ERROR"
}

log_warning() {
    log "${YELLOW}$1${NC}" "WARNING"
}

# Function to check if user is root
check_root() {
    if [ "$(id -u)" -ne 0 ]; then
        log_error "This script must be run as root"
        exit 1
    fi
}

# Function to list available backups
list_backups() {
    log "Available backups:"
    if [ -d "$BACKUP_DIR" ]; then
        ls -lt "$BACKUP_DIR" | grep -v "^total" | awk '{print $9}' | grep -v "^$" | nl
    else
        log_warning "No backups found"
        return 1
    fi
}

# Function to restore from backup
restore_backup() {
    local backup_name="$1"
    local backup_path="${BACKUP_DIR}/${backup_name}"

    if [ ! -d "$backup_path" ]; then
        log_error "Backup directory not found: $backup_path"
        return 1
    fi

    log "Restoring from backup: $backup_name"

    # Stop services
    log "Stopping services..."
    systemctl stop "$SERVICE_NAME" nginx

    # Restore database
    if [ -f "${backup_path}/db.sqlite" ]; then
        log "Restoring database..."
        cp "${backup_path}/db.sqlite" "${SCRIPT_DIR}/db.sqlite"
        chown www-data:www-data "${SCRIPT_DIR}/db.sqlite"
        chmod 664 "${SCRIPT_DIR}/db.sqlite"
    else
        log_warning "Database backup not found"
    fi

    # Restore frontend
    if [ -d "${backup_path}/frontend" ]; then
        log "Restoring frontend..."
        rm -rf "${SCRIPT_DIR}/frontend/"*
        cp -r "${backup_path}/frontend/"* "${SCRIPT_DIR}/frontend/"
        chown -R www-data:www-data "${SCRIPT_DIR}/frontend"
        chmod -R 755 "${SCRIPT_DIR}/frontend"
    else
        log_warning "Frontend backup not found"
    fi

    # Restore backend
    if [ -d "${backup_path}/backend" ]; then
        log "Restoring backend..."
        rm -rf "${SCRIPT_DIR}/backend/"*
        cp -r "${backup_path}/backend/"* "${SCRIPT_DIR}/backend/"
    else
        log_warning "Backend backup not found"
    fi

    # Restore Nginx configuration
    if [ -f "${backup_path}/bender.conf" ]; then
        log "Restoring Nginx configuration..."
        cp "${backup_path}/bender.conf" "$NGINX_CONF"

        if [ ! -L "$NGINX_LINK" ] || [ ! -e "$NGINX_LINK" ]; then
            ln -sf "$NGINX_CONF" "$NGINX_LINK"
        fi
    else
        log_warning "Nginx configuration backup not found"
    fi

    # Restore service configuration
    if [ -f "${backup_path}/bender-flask.service" ]; then
        log "Restoring service configuration..."
        cp "${backup_path}/bender-flask.service" "/etc/systemd/system/${SERVICE_NAME}.service"
        systemctl daemon-reload
    else
        log_warning "Service configuration backup not found"
    fi

    # Start services
    log "Starting services..."
    systemctl start nginx "$SERVICE_NAME"

    # Verify restoration
    if systemctl is-active --quiet nginx && systemctl is-active --quiet "$SERVICE_NAME"; then
        log_success "Services started successfully"
    else
        log_error "Failed to start services"
        systemctl status nginx "$SERVICE_NAME"
        return 1
    fi

    log_success "Rollback completed successfully!"
}

# Main function
main() {
    check_root

    if [ "$1" = "--list" ] || [ "$1" = "-l" ]; then
        list_backups
        exit 0
    fi

    if [ -z "$1" ]; then
        log "Usage: $0 <backup_name> | --list"
        list_backups
        log "To rollback, run: $0 <backup_name>"
        exit 1
    fi

    restore_backup "$1"
}

main "$@"
