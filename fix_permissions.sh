#!/bin/bash

# Script to fix file permissions for Bender development

# Exit on any error
set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

# Function to log messages
log() {
    local msg="$1"
    local level="${2:-INFO}"
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] [${level}] ${msg}"
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

# Check if script is running with sudo
if [ "$(id -u)" -ne 0 ]; then
    log_error "This script must be run with sudo"
    log "Usage: sudo ./fix_permissions.sh"
    exit 1
fi

# Get the directory where the script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Get the username of the user who invoked sudo
if [ -n "$SUDO_USER" ]; then
    REAL_USER="$SUDO_USER"
else
    REAL_USER=$(whoami)
fi

log "Starting permission fix for Bender project..."
log "Project directory: $SCRIPT_DIR"
log "Running as: $(whoami), real user: $REAL_USER"

# Fix database permissions
if [ -f "$SCRIPT_DIR/db.sqlite" ]; then
    log "Setting database permissions to 666 (readable/writable by all)"
    chmod 666 "$SCRIPT_DIR/db.sqlite"
    log_success "Database permissions updated"
else
    log_warning "Database file not found at $SCRIPT_DIR/db.sqlite"
fi

# Fix frontend permissions
if [ -d "$SCRIPT_DIR/frontend" ]; then
    log "Setting frontend directory permissions"

    # Make all files and directories group writable
    log "Making files readable/writable by the www-data group"
    chmod -R g+rw "$SCRIPT_DIR/frontend"

    # Ensure directories are executable (navigable) by the group
    find "$SCRIPT_DIR/frontend" -type d -exec chmod g+x {} \;

    log_success "Frontend permissions updated"
else
    log_warning "Frontend directory not found at $SCRIPT_DIR/frontend"
fi

# Fix backend permissions
if [ -d "$SCRIPT_DIR/backend" ]; then
    log "Setting backend directory permissions"

    # Make all files and directories group writable
    log "Making files readable/writable by the www-data group"
    chmod -R g+rw "$SCRIPT_DIR/backend"

    # Ensure directories are executable (navigable) by the group
    find "$SCRIPT_DIR/backend" -type d -exec chmod g+x {} \;

    log_success "Backend permissions updated"
else
    log_warning "Backend directory not found at $SCRIPT_DIR/backend"
fi

# Fix ownership (ensure the user can still access everything while keeping www-data as the owner)
log "Setting proper ownership"
chown -R www-data:www-data "$SCRIPT_DIR/frontend" "$SCRIPT_DIR/backend" 2>/dev/null || true

# If user is not www-data, add ACL permissions so both the user and www-data can work with files
if [ "$REAL_USER" != "www-data" ]; then
    log "Adding ACL permissions for user $REAL_USER"

    # Check if the acl package is installed
    if command -v setfacl &> /dev/null; then
        # Set ACL for frontend
        if [ -d "$SCRIPT_DIR/frontend" ]; then
            setfacl -R -m u:$REAL_USER:rwX "$SCRIPT_DIR/frontend"
            setfacl -R -d -m u:$REAL_USER:rwX "$SCRIPT_DIR/frontend"
        fi

        # Set ACL for backend
        if [ -d "$SCRIPT_DIR/backend" ]; then
            setfacl -R -m u:$REAL_USER:rwX "$SCRIPT_DIR/backend"
            setfacl -R -d -m u:$REAL_USER:rwX "$SCRIPT_DIR/backend"
        fi

        log_success "ACL permissions set for user $REAL_USER"
    else
        log_warning "acl package not installed. Cannot set fine-grained permissions."
        log "To install: sudo apt-get install acl"
    fi
fi

log_success "Permission fix completed!"
log "You should now be able to edit files in the Bender project."
log "If you still encounter permission issues, make sure your user ($REAL_USER) is in the www-data group."
log "To add user to www-data group: sudo usermod -a -G www-data $REAL_USER"
log "Remember to log out and log back in for group changes to take effect."
