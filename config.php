<?php
/**
 * Telegram Guild Bot - Configuration File
 * Replace YOUR_BOT_TOKEN_HERE with your actual bot token from BotFather
 */

// Bot Configuration
define('BOT_TOKEN', '8190286562:AAHlS3diTjJ6kluTk5JAS7dpfwWgRuDxMt4'); // Get this from @BotFather
define('ADMIN_TELEGRAM_ID', '6481253895');   // Admin telegram ID

// Build API URL
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN);

// Paths
define('DATA_DIR', __DIR__ . '/data');
define('LOG_DIR', __DIR__ . '/logs');

// Create directories if they don't exist
if (!file_exists(DATA_DIR)) { mkdir(DATA_DIR, 0755, true); }
if (!file_exists(LOG_DIR)) { mkdir(LOG_DIR, 0755, true); }

// Bot settings
define('BOT_NAME', 'Guild Registration Bot');
define('BOT_VERSION', '1.1.0');

// Rate limiting (basic)
define('MAX_REQUESTS_PER_MINUTE', 20);
define('RATE_LIMIT_ENABLED', true);
