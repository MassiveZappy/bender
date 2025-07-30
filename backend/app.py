import sqlite3
import os
import sys
import traceback
from flask import Flask, request, jsonify, g
from werkzeug.security import generate_password_hash, check_password_hash
from flask_cors import CORS
import markdown
import json
from config import config

# Create Flask application with appropriate configuration
config_name = os.environ.get('FLASK_CONFIG', 'default')
print(f"Using config: {config_name}", file=sys.stderr)
app = Flask(__name__)
app.config.from_object(config[config_name])
config[config_name].init_app(app)

# Enable CORS with appropriate configuration
if app.config['CORS_ENABLED']:
    CORS(app, resources={r"/api/*": {"origins": app.config['CORS_ORIGINS'], "methods": ["GET", "POST", "PUT", "DELETE", "OPTIONS"], "allow_headers": ["Content-Type", "Authorization"]}})

DATABASE = app.config['DATABASE']

def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        print(f"Connecting to database: {DATABASE}", file=sys.stderr)
        try:
            db = g._database = sqlite3.connect(DATABASE)
            db.row_factory = sqlite3.Row
            print("Database connection successful", file=sys.stderr)
        except Exception as e:
            print(f"Database connection error: {e}", file=sys.stderr)
            traceback.print_exc(file=sys.stderr)
            raise
    return db

@app.teardown_appcontext
def close_connection(exception):
    db = getattr(g, '_database', None)
    if db is not None:
        db.close()

# --- User Authentication ---

@app.route('/api/signup', methods=['POST'])
def signup():
    data = request.get_json(silent=True)
    if not data or 'username' not in data or 'password' not in data:
        return jsonify({'error': 'Missing username or password'}), 400
    username = data['username']
    password = data['password']
    db = get_db()
    try:
        db.execute(
            "INSERT INTO users (username, password_hash) VALUES (?, ?)",
            (username, generate_password_hash(password))
        )
        db.commit()
        return jsonify({'success': True}), 201
    except sqlite3.IntegrityError:
        return jsonify({'error': 'Username already exists'}), 409

@app.route('/api/login', methods=['POST'])
def login():
    data = request.get_json(silent=True)
    if not data or 'username' not in data or 'password' not in data:
        return jsonify({'error': 'Missing username or password'}), 400
    username = data['username']
    password = data['password']
    db = get_db()
    user = db.execute(
        "SELECT * FROM users WHERE username = ?", (username,)
    ).fetchone()
    if user and check_password_hash(user['password_hash'], password):
        return jsonify({'success': True, 'user_id': user['id'], 'is_admin': user['is_admin']})
    return jsonify({'error': 'Invalid credentials'}), 401

# --- Skins ---

@app.route('/api/skins', methods=['GET'])
def get_skins():
    try:
        print("Attempting to fetch skins", file=sys.stderr)
        db = get_db()
        print(f"Database connection: {db}", file=sys.stderr)
        skins = db.execute("SELECT * FROM skins").fetchall()
        print(f"Fetched skins: {skins}", file=sys.stderr)
        result = [dict(row) for row in skins]
        print(f"Returning result: {result}", file=sys.stderr)
        return jsonify(result)
    except Exception as e:
        print(f"Error in get_skins: {e}", file=sys.stderr)
        traceback.print_exc(file=sys.stderr)
        return jsonify({"error": str(e)}), 500

# --- Articles ---

@app.route('/api/articles', methods=['GET'])
def get_articles_by_user():
    user_id = request.args.get('user_id')
    db = get_db()
    if user_id:
        articles = db.execute(
            "SELECT * FROM articles WHERE user_id = ?", (user_id,)
        ).fetchall()
    else:
        articles = db.execute("SELECT * FROM articles").fetchall()
    return jsonify({'articles': [dict(row) for row in articles]})

@app.route('/api/articles', methods=['POST'])
def create_article():
    data = request.get_json(silent=True)
    required_fields = ['user_id', 'title', 'content', 'skin_id']
    if not data or not all(field in data for field in required_fields):
        return jsonify({'error': 'Missing required fields'}), 400

    user_id = data['user_id']
    title = data['title']
    subtitle = data.get('subtitle', '') or ''
    content = data['content']
    content_html = markdown.markdown(content)
    skin_id = data['skin_id']
    publication_datetime = data.get('publication_datetime', None) or None
    author = data.get('author', '') or ''
    author_description = data.get('author_description', '') or ''
    tags = data.get('tags', [])
    if tags is None:
        tags = []
    tags_str = json.dumps(tags) if isinstance(tags, list) else str(tags)

    db = get_db()
    db.execute(
        """INSERT INTO articles
        (user_id, title, subtitle, content, content_html, skin_id, publication_datetime, author, author_description, tags)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)""",
        (user_id, title, subtitle, content, content_html, skin_id, publication_datetime, author, author_description, tags_str)
    )
    db.commit()
    return jsonify({'success': True}), 201

@app.route('/api/articles/<int:article_id>', methods=['GET'])
def get_article(article_id):
    db = get_db()
    article = db.execute(
        "SELECT * FROM articles WHERE id = ?", (article_id,)
    ).fetchone()
    if article:
        article_dict = dict(article)
        # Convert tags from JSON string to list
        if 'tags' in article_dict and article_dict['tags']:
            try:
                article_dict['tags'] = json.loads(article_dict['tags'])
            except Exception:
                article_dict['tags'] = []
        return jsonify(article_dict)
    return jsonify({'error': 'Article not found'}), 404

@app.route('/api/articles/<int:article_id>', methods=['PUT'])
def edit_article(article_id):
    data = request.get_json(silent=True)
    required_fields = ['title', 'content', 'skin_id']
    if not data or not all(field in data for field in required_fields):
        return jsonify({'error': 'Missing required fields'}), 400

    title = data['title']
    subtitle = data.get('subtitle', '') or ''
    content = data['content']
    content_html = markdown.markdown(content)
    skin_id = data['skin_id']
    publication_datetime = data.get('publication_datetime', None) or None
    author = data.get('author', '') or ''
    author_description = data.get('author_description', '') or ''
    tags = data.get('tags', [])
    if tags is None:
        tags = []
    tags_str = json.dumps(tags) if isinstance(tags, list) else str(tags)

    db = get_db()
    db.execute(
        """UPDATE articles SET
        title = ?,
        subtitle = ?,
        content = ?,
        content_html = ?,
        skin_id = ?,
        publication_datetime = ?,
        author = ?,
        author_description = ?,
        tags = ?,
        updated_at = CURRENT_TIMESTAMP
        WHERE id = ?""",
        (title, subtitle, content, content_html, skin_id, publication_datetime, author, author_description, tags_str, article_id)
    )
    db.commit()
    return jsonify({'success': True})

# --- Admin Endpoints ---

@app.route('/api/admin/users', methods=['GET'])
def admin_users():
    db = get_db()
    users = db.execute("SELECT id, username, is_admin FROM users").fetchall()
    return jsonify([dict(row) for row in users])

@app.route('/api/admin/articles', methods=['GET'])
def admin_articles():
    db = get_db()
    articles = db.execute("SELECT * FROM articles").fetchall()
    return jsonify([dict(row) for row in articles])

# Delete user (admin only)
@app.route('/api/admin/users/<int:user_id>', methods=['DELETE', 'OPTIONS'])
def delete_user(user_id):
    # Handle preflight requests
    if request.method == 'OPTIONS':
        response = app.make_default_options_response()
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Methods'] = 'DELETE, OPTIONS'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type'
        return response

    # Get request data to check if user is trying to delete themselves
    # The frontend should prevent this, but we also check on the backend for security
    current_user_id = request.headers.get('X-User-ID')
    if current_user_id and int(current_user_id) == user_id:
        return jsonify({'error': 'Cannot delete your own account while logged in'}), 403

    db = get_db()
    try:
        # First delete all articles by this user
        db.execute("DELETE FROM articles WHERE user_id = ?", (user_id,))

        # Then delete the user
        cursor = db.execute("DELETE FROM users WHERE id = ?", (user_id,))
        db.commit()

        if cursor.rowcount > 0:
            return jsonify({'success': True})
        return jsonify({'error': 'User not found'}), 404
    except sqlite3.Error as e:
        db.rollback()
        print(f"Database error: {e}")
        return jsonify({'error': 'Database error occurred'}), 500

# Delete article (regular users and admins)
@app.route('/api/articles/<int:article_id>', methods=['DELETE', 'OPTIONS'])
def delete_article(article_id):
    # Handle preflight requests
    if request.method == 'OPTIONS':
        response = app.make_default_options_response()
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Methods'] = 'DELETE, OPTIONS'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type'
        return response

    db = get_db()
    try:
        cursor = db.execute("DELETE FROM articles WHERE id = ?", (article_id,))
        db.commit()
        if cursor.rowcount > 0:
            return jsonify({'success': True})
        return jsonify({'error': 'Article not found'}), 404
    except sqlite3.Error as e:
        db.rollback()
        print(f"Database error: {e}")
        return jsonify({'error': 'Database error occurred'}), 500

# Delete article (admin only)
@app.route('/api/admin/articles/<int:article_id>', methods=['DELETE'])
def admin_delete_article(article_id):
    db = get_db()
    try:
        cursor = db.execute("DELETE FROM articles WHERE id = ?", (article_id,))
        db.commit()
        if cursor.rowcount > 0:
            return jsonify({'success': True})
        return jsonify({'error': 'Article not found'}), 404
    except sqlite3.Error as e:
        db.rollback()
        print(f"Database error: {e}")
        return jsonify({'error': 'Database error occurred'}), 500

# Update user admin status
@app.route('/api/admin/users/<int:user_id>', methods=['PUT', 'OPTIONS'])
def update_user(user_id):
    # Handle preflight requests
    if request.method == 'OPTIONS':
        response = app.make_default_options_response()
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Access-Control-Allow-Methods'] = 'PUT, OPTIONS'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type'
        return response

    data = request.get_json(silent=True)
    if not data:
        return jsonify({'error': 'No data provided'}), 400

    is_admin = data.get('is_admin')
    if is_admin is None:
        return jsonify({'error': 'Missing is_admin field'}), 400

    db = get_db()
    try:
        cursor = db.execute("UPDATE users SET is_admin = ? WHERE id = ?", (1 if is_admin else 0, user_id))
        db.commit()
        if cursor.rowcount > 0:
            return jsonify({'success': True})
        return jsonify({'error': 'User not found'}), 404
    except sqlite3.Error as e:
        db.rollback()
        print(f"Database error: {e}")
        return jsonify({'error': 'Database error occurred'}), 500

# Query all admins
@app.route('/api/admins', methods=['GET'])
def get_admins():
    db = get_db()
    admins = db.execute("SELECT id, username FROM users WHERE is_admin = 1").fetchall()
    return jsonify([dict(row) for row in admins])

# Add CORS headers to all responses
@app.after_request
def add_cors_headers(response):
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE, OPTIONS'
    response.headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization'
    return response

if __name__ == '__main__':  # YAY
    # db = get_db()
    # skins = db.execute("SELECT * FROM skins").fetchall()
    # print(jsonify([dict(row) for row in skins]))
    # CORS is already configured at app initialization
    app.run(host="0.0.0.0", port=5000, debug=app.config['DEBUG'])
