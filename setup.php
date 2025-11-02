<?php
/**
 * Webhook Setup Script
 * Run this file once to set up the webhook with Telegram
 */

require_once 'config.php';

// Check if bot token is set
if (BOT_TOKEN === '8190286562:AAHlS3diTjJ6kluTk5JAS7dpfwWgRuDxMt4') {
    die("❌ Please set your bot token in config.php first!\n");
}

// Get your domain and webhook URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['REQUEST_URI']);
$webhookUrl = $protocol . '://' . $domain . $scriptPath . '/index.php';

echo "<h1>🔧 Telegram Bot Webhook Setup</h1>";
echo "<p><strong>Bot Token:</strong> " . substr(BOT_TOKEN, 0, 10) . "...</p>";
echo "<p><strong>Webhook URL:</strong> $webhookUrl</p>";

// Set webhook
$url = TELEGRAM_API_URL . '/setWebhook';
$postData = [ 'url' => $webhookUrl ];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

echo "<div style='background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>Webhook Setup Result:</h3>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";
echo "</div>";

if ($result && $result['ok']) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>✅ Success!</h3>";
    echo "<p>Webhook has been set successfully!</p>";
    echo "<p>Your bot is now ready to receive messages.</p>";
    echo "</div>";

    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h4>🔒 Security Notice:</h4>";
    echo "<p>For security reasons, please:</p>";
    echo "<ul>";
    echo "<li>Delete or rename this setup.php file</li>";
    echo "<li>Make sure your data/ and logs/ directories are not publicly accessible</li>";
    echo "<li>Use HTTPS for your domain</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error!</h3>";
    echo "<p>Failed to set webhook. Please check token, HTTPS, and public accessibility.</p>";
    echo "</div>";
}
