#!/bin/bash

# Deployment script for Bender website
# This script publishes the current development site to production

# Exit on any error
set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
BACKUP_DIR="${SCRIPT_DIR}/backups/${TIMESTAMP}"
NGINX_CONF="/etc/nginx/sites-available/bender.conf"
NGINX_LINK="/etc/nginx/sites-enabled/bender.conf"
SERVICE_NAME="bender-flask"
LOG_FILE="${SCRIPT_DIR}/deployLogs/deploy_${TIMESTAMP}.log"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

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

# Function to create backup
create_backup() {
    log "Creating backup of current site to ${BACKUP_DIR}"
    mkdir -p "${BACKUP_DIR}"

    # Backup database
    cp "${SCRIPT_DIR}/db.sqlite" "${BACKUP_DIR}/db.sqlite"

    # Backup code
    mkdir -p "${BACKUP_DIR}/frontend" "${BACKUP_DIR}/backend"
    cp -r "${SCRIPT_DIR}/frontend/"* "${BACKUP_DIR}/frontend/"
    cp -r "${SCRIPT_DIR}/backend/"* "${BACKUP_DIR}/backend/"

    # Backup configuration
    if [ -f "$NGINX_CONF" ]; then
        cp "$NGINX_CONF" "${BACKUP_DIR}/bender.conf"
    fi

    cp "${SCRIPT_DIR}/bender-flask.service" "${BACKUP_DIR}/bender-flask.service"

    log_success "Backup created successfully at ${BACKUP_DIR}"
}

# Function to update database
update_database() {
    log "Updating database..."

    # If we need to run migrations or database updates
    # if [ -f "${SCRIPT_DIR}/setupdb.sh" ]; then
    #     log "Running database setup script"
    #     bash "${SCRIPT_DIR}/setupdb.sh"
    # fi

    # Update skins if needed
    if [ -f "${SCRIPT_DIR}/update_skins.py" ]; then
        log "Updating skins database"
        python3 "${SCRIPT_DIR}/update_skins.py"
    fi

    # Set proper permissions
    log "Setting database permissions"
    # Make database writable by both www-data and the Flask service user (zappy)
    chown www-data:www-data "${SCRIPT_DIR}/db.sqlite"
    chmod 666 "${SCRIPT_DIR}/db.sqlite"

    log_success "Database updated successfully"
}

# Function to update frontend code
update_frontend() {
    log "Updating frontend code..."

    # Set proper permissions
    chown -R www-data:www-data "${SCRIPT_DIR}/frontend"
    chmod -R 755 "${SCRIPT_DIR}/frontend"

    log_success "Frontend code updated successfully"
}

# Function to update backend code
update_backend() {
    log "Updating backend code..."

    # Ensure virtual environment is up to date
    if [ -d "${SCRIPT_DIR}/.venv" ]; then
        log "Updating Python dependencies"
        cd "${SCRIPT_DIR}"
        source .venv/bin/activate
        pip install flask flask-cors markdown Werkzeug
        deactivate
    else
        log_warning "Virtual environment not found, creating one..."
        cd "${SCRIPT_DIR}"
        python3 -m venv .venv
        source .venv/bin/activate
        pip install flask flask-cors markdown Werkzeug
        deactivate
    fi

    # Make sure backend code files are properly owned and have right permissions
    log "Setting proper permissions on backend code"
    chown -R www-data:www-data "${SCRIPT_DIR}/backend"
    chmod -R 755 "${SCRIPT_DIR}/backend"

    log_success "Backend code updated successfully"
}

# Function to update Nginx configuration
update_nginx() {
    log "Updating Nginx configuration..."

    # Check if our config file exists
    if [ -f "${SCRIPT_DIR}/bender-ssl.conf" ]; then
        cp "${SCRIPT_DIR}/bender-ssl.conf" "$NGINX_CONF"
    elif [ -f "${SCRIPT_DIR}/bender.conf" ]; then
        cp "${SCRIPT_DIR}/bender.conf" "$NGINX_CONF"
    else
        log_error "Nginx configuration file not found"
        return 1
    fi

    # Create symbolic link if needed
    if [ ! -L "$NGINX_LINK" ] || [ ! -e "$NGINX_LINK" ]; then
        ln -sf "$NGINX_CONF" "$NGINX_LINK"
    fi

    # Test Nginx configuration
    if nginx -t; then
        log_success "Nginx configuration is valid"
    else
        log_error "Nginx configuration is invalid"
        return 1
    fi

    # Reload Nginx
    systemctl reload nginx

    log_success "Nginx configuration updated successfully"
}

# Function to update systemd service
update_service() {
    log "Updating systemd service..."

    # Copy service file
    if [ -f "${SCRIPT_DIR}/bender-flask.service" ]; then
        cp "${SCRIPT_DIR}/bender-flask.service" "/etc/systemd/system/${SERVICE_NAME}.service"
    else
        log_error "Service file not found"
        return 1
    fi

    # Reload systemd
    systemctl daemon-reload

    # Ensure database has the right permissions before starting the service
    log "Ensuring database has proper permissions before service start"
    chmod 666 "${SCRIPT_DIR}/db.sqlite"

    # Enable and start service (note: not using restart since we explicitly stopped the service earlier)
    log "Starting Flask service with updated code..."
    systemctl enable "$SERVICE_NAME"
    systemctl start "$SERVICE_NAME"

    # Check service status
    if systemctl is-active --quiet "$SERVICE_NAME"; then
        log_success "Service $SERVICE_NAME is running"
    else
        log_error "Service $SERVICE_NAME failed to start"
        systemctl status "$SERVICE_NAME"
        return 1
    fi

    log_success "Systemd service updated successfully"
}

# Function to verify deployment
verify_deployment() {
    log "Verifying deployment..."

    # Check if Nginx is running
    if systemctl is-active --quiet nginx; then
        log_success "Nginx is running"
    else
        log_error "Nginx is not running"
        return 1
    fi

    # Check if Flask service is running
    if systemctl is-active --quiet "$SERVICE_NAME"; then
        log_success "Flask service is running"
    else
        log_error "Flask service is not running"
        return 1
    fi

    # Check database permissions
    if [ -f "${SCRIPT_DIR}/db.sqlite" ]; then
        db_perms=$(stat -c "%a" "${SCRIPT_DIR}/db.sqlite")
        if [ "$db_perms" = "666" ]; then
            log_success "Database has correct permissions (666)"
        else
            log_warning "Database permissions are not optimal: $db_perms (should be 666)"
            log "Fixing database permissions..."
            chmod 666 "${SCRIPT_DIR}/db.sqlite"
        fi
    else
        log_error "Database file not found"
    fi

    # Give the Flask API a moment to fully initialize
    log "Waiting 5 seconds for Flask API to be fully ready..."
    sleep 5

    # Try to connect to the Flask API with more diagnostics
    log "Testing Flask API connection..."
    api_response=$(curl -s -v http://localhost:5000/api/skins 2>&1)
    api_status=$?

    if [ $api_status -eq 0 ] && echo "$api_response" | grep -q "template_path"; then
        log_success "Flask API is responding correctly"
    else
        log_error "Flask API is not responding properly"
        log "API response status: $api_status"
        log "API response: $api_response"
        return 1
    fi

    # Try to connect to the website via Nginx
    if curl -k -s https://localhost/ | grep -q "Bender"; then
        log_success "Website is accessible via HTTPS"
    else
        log_warning "Could not verify HTTPS access"
    fi

    log_success "Deployment verification completed successfully"
}

# Main deployment function
deploy() {
    log "Starting deployment process..."

    check_root
    create_backup
    update_database
    update_frontend
    update_backend
    # First stop the service to ensure new code is loaded
    log "Stopping Flask service to prepare for code update..."
    systemctl stop "$SERVICE_NAME"
    log "Flask service stopped"
    # Now update service and start it with the new code
    update_service
    update_nginx
    verify_deployment

    log_success "Deployment completed successfully!"
    log "Backup location: ${BACKUP_DIR}"
    log "Log file: ${LOG_FILE}"
}

# Run the deployment
deploy

echo "Deployment completed successfully!"
echo "Backup location: ${BACKUP_DIR}"
echo "Log file: ${LOG_FILE}"

bash fix_permissions.sh
