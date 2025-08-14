import os
import json
import datetime

class Config:
    """Base configuration class for Bender application."""
    DATABASE = os.environ.get('DATABASE', os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'db.sqlite'))
    DEBUG = False
    TESTING = False
    SECRET_KEY = os.environ.get('SECRET_KEY', 'dev-key-change-in-production')
    CORS_ENABLED = True
    CORS_ORIGINS = ['https://bender.chat', 'https://www.bender.chat']
    API_VERSION = '1.0.0'
    BUILD_DATE = datetime.datetime.now().strftime('%Y-%m-%d')

    @staticmethod
    def init_app(app):
        pass

class DevelopmentConfig(Config):
    """Development configuration."""
    DEBUG = True
    CORS_ORIGINS = ['*']

class TestingConfig(Config):
    """Testing configuration."""
    TESTING = True
    DATABASE = 'test.sqlite'
    CORS_ORIGINS = ['*']

class ProductionConfig(Config):
    """Production configuration."""
    SECRET_KEY = os.environ.get('SECRET_KEY')

    @staticmethod
    def init_app(app):
        # Production-specific setup
        Config.init_app(app)

        # Log to syslog
        import logging
        from logging.handlers import SysLogHandler

        # Create a logger
        file_handler = logging.FileHandler('/var/log/bender/app.log')
        file_handler.setLevel(logging.WARNING)
        file_handler.setFormatter(logging.Formatter(
            '%(asctime)s %(levelname)s: %(message)s '
            '[in %(pathname)s:%(lineno)d]'
        ))
        app.logger.addHandler(file_handler)

# Configuration dictionary
config = {
    'development': DevelopmentConfig,
    'testing': TestingConfig,
    'production': ProductionConfig,

    'default': DevelopmentConfig
}
