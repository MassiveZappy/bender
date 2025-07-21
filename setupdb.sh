#!/bin/bash

DB_PATH="db.sqlite"

# Remove existing database if you want a fresh start
if [ -f "$DB_PATH" ]; then
    echo "Removing existing database..."
    rm "$DB_PATH"
fi

echo "Creating new SQLite database at $DB_PATH..."

sqlite3 "$DB_PATH" <<EOF
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    is_admin INTEGER DEFAULT 0
);

CREATE TABLE skins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    description TEXT,
    template_path TEXT NOT NULL
);

CREATE TABLE articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    skin_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (skin_id) REFERENCES skins(id)
);
EOF

echo "Database setup complete."
