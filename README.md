# Bender - Satirical News Article Generator

Bender is a web application that allows users to create and share satirical news articles styled after real news outlets. Users can write content that appears in the visual style of popular news websites, making it perfect for parody and satire. This project is intended for entertainment and satirical purposes only.

## Features

- **User Authentication**: Create accounts, login, and manage your profile
- **Article Creation**: Write and publish satirical news articles with a rich text editor
- **Custom Skins**: Style your articles with templates that mimic popular news outlets
- **Article Management**: Edit, delete, and share your published articles
- **Admin Dashboard**: Administrators can manage users, content, and site settings
- **Responsive Design**: Works seamlessly on desktop and mobile devices
- **Shareable Links**: Generate links to share your satirical articles with friends

## Tech Stack

### Backend
- Python Flask REST API
- SQLite Database
- Markdown processing for article content

### Frontend
- PHP for server-side rendering
- HTML/CSS with responsive design
- JavaScript for interactive elements

## Installation

### Prerequisites
- Python 3.6+
- PHP 7.4+
- Nginx web server
- SQLite3
- Modern web browser
- Node.js (for frontend asset compilation, optional)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/bender.git
   cd bender
   ```

2. **Set up the Python virtual environment**
   ```bash
   python3 -m venv .venv
   source .venv/bin/activate
   pip install flask flask-cors markdown Werkzeug
   ```

3. **Initialize the database**
   ```bash
   chmod +x setupdb.sh
   ./setupdb.sh
   ```

4. **Update the skins database**
   ```bash
   python3 update_skins.py
   ```

5. **Set up Nginx configuration**
   ```bash
   sudo cp bender.conf /etc/nginx/sites-available/
   sudo ln -s /etc/nginx/sites-available/bender.conf /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

6. **Configure and start the Flask service**
   ```bash
   sudo cp bender-flask.service /etc/systemd/system/
   sudo systemctl daemon-reload
   sudo systemctl enable bender-flask
   sudo systemctl start bender-flask
   ```

7. **Set proper permissions**
   ```bash
   sudo chown -R www-data:www-data frontend/
   sudo chmod -R 755 frontend/
   sudo chmod 666 db.sqlite
   ```

## Deployment

The project includes a deployment script that automates the update process:

```bash
sudo ./deploy.sh
```

This script will:
1. Create a backup of the current site
2. Update the database and frontend code
3. Restart the Flask backend service
4. Update and reload Nginx configuration
5. Verify the deployment succeeded

If the deployment fails, you can roll back using:

```bash
sudo ./rollback.sh
```

## Project Structure

- `/backend` - Flask API and Python modules
  - `/backend/app.py` - Main Flask application entry point
  - `/backend/config.py` - Configuration settings for different environments
  - `/backend/modules` - Modular components of the backend
- `/frontend` - PHP frontend files
  - `/frontend/assets` - Static assets (CSS, JS, images)
  - `/frontend/skins` - Templates for different news outlet styles
  - `/frontend/createArticle` - Article creation interface
  - `/frontend/viewArticle` - Article viewing components
- `/.venv` - Python virtual environment
- `/backups` - Deployment backups
- `/db.sqlite` - SQLite database file
- `/bender.conf` - Nginx configuration
- `/bender-flask.service` - Systemd service configuration
- `/deploy.sh` - Deployment automation script
- `/rollback.sh` - Rollback automation script

## Development

To run the project in development mode:

1. Start the Flask backend:
   ```bash
   cd backend
   FLASK_CONFIG=development python app.py
   ```

2. Configure your web server to serve the frontend files or use PHP's built-in server:
   ```bash
   cd frontend
   php -S localhost:8000
   ```

## API Reference

The Flask backend provides the following API endpoints:

- `GET /api/skins` - List all available skins
- `GET /api/articles` - List articles (for authorized users)
- `POST /api/articles` - Create a new article
- `GET /api/articles/{id}` - Get a specific article
- `PUT /api/articles/{id}` - Update an article
- `DELETE /api/articles/{id}` - Delete an article
- `POST /api/users` - Create a new user
- `GET /api/users/{id}` - Get user information (admin only)

## Troubleshooting

### Common Issues

1. **Database permission errors**
   - Ensure the SQLite database file has proper permissions: `chmod 666 db.sqlite`

2. **API connection failure**
   - Check that the Flask service is running: `systemctl status bender-flask`
   - Verify the API is accessible: `curl http://localhost:5000/api/skins`

3. **Skin template issues**
   - Run the skin update script: `python3 update_skins.py`

### Logging

- Flask application logs: `journalctl -u bender-flask`
- Nginx access and error logs: `/var/log/nginx/access.log` and `/var/log/nginx/error.log`
- Deployment logs: `bender/deploy_<timestamp>.log`

## Security Considerations

- Keep your server updated with security patches
- Regularly back up your database
- Use HTTPS for all connections (an SSL configuration is provided in `bender-ssl.conf`)
- Sanitize user input to prevent XSS and SQL injection attacks

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is intended for entertainment purposes. Usage should comply with applicable laws regarding satire and parody.

## Disclaimer

Bender is a tool for creating satirical content. All articles generated are works of fiction and satire. Any resemblance to real news articles is coincidental and part of the satirical nature of the project.

## Authors

- MassiveZappy
