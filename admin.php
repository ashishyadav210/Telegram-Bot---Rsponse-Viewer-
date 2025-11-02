<?php
/**
 * Admin Panel for Guild Registration Bot
 * Provides statistics and monitoring capabilities
 */

require_once 'config.php';
require_once 'database.php';

// Simple authentication (change this password!)
$adminPassword = 'Naresh'; // CHANGE THIS!

session_start();

if (!isset($_POST['password']) && !isset($_SESSION['admin_logged_in'])) {
    showLoginForm();
    exit;
}

if (isset($_POST['password']) && $_POST['password'] === $adminPassword) {
    $_SESSION['admin_logged_in'] = true;
} elseif (!isset($_SESSION['admin_logged_in'])) {
    showLoginForm('Invalid password');
    exit;
}

// Initialize database
$db = new SimpleDatabase();

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'clean_old_states':
            $cleaned = $db->cleanOldStates();
            $message = "Cleaned $cleaned old user states.";
            break;
        case 'logout':
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
    }
}

// Get statistics
$stats = $db->getStats();
$allUsers = $db->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Guild Bot Admin Panel</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;background:#f5f5f5;padding:20px}
.container{max-width:1200px;margin:0 auto}
.header{background:#fff;padding:20px;border-radius:8px;margin-bottom:20px;box-shadow:0 2px 4px rgba(0,0,0,0.1)}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:20px}
.stat-card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);text-align:center}
.stat-number{font-size:2em;font-weight:bold;color:#4CAF50;margin-bottom:10px}
.stat-label{color:#666;font-size:.9em}
.users-table{background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);overflow:hidden}
.table-header{background:#4CAF50;color:#fff;padding:15px;font-weight:bold}
.table-row{padding:15px;border-bottom:1px solid #eee;display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;align-items:center}
.status{padding:5px 10px;border-radius:20px;font-size:.8em;font-weight:bold}
.status.completed{background:#d4edda;color:#155724}
.status.active{background:#fff3cd;color:#856404}
.status.idle{background:#f8d7da;color:#721c24}
.actions{margin:20px 0}
.btn{padding:10px 20px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:4px;margin-right:10px;display:inline-block;border:none;cursor:pointer}
.btn:hover{background:#45a049}
.btn.danger{background:#f44336}
.btn.danger:hover{background:#da190b}
.message{padding:15px;background:#d4edda;color:#155724;border-radius:4px;margin-bottom:20px}
.logs{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin-top:20px}
.log-content{background:#f8f9fa;padding:15px;border-radius:4px;font-family:monospace;font-size:.9em;max-height:300px;overflow-y:auto}
@media (max-width:768px){.table-row{grid-template-columns:1fr;gap:5px}.stats-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🤖 Guild Registration Bot - Admin Panel</h1>
        <p>Monitor bot activity and manage registrations</p>
        <div style="float:right;">
            <a href="?action=logout" class="btn danger">Logout</a>
        </div>
        <div style="clear:both;"></div>
    </div>

    <?php if (isset($message)): ?>
    <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['active_registrations']; ?></div>
            <div class="stat-label">Active Registrations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['completed_registrations']; ?></div>
            <div class="stat-label">Completed Registrations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['today_registrations']; ?></div>
            <div class="stat-label">Today's Registrations</div>
        </div>
    </div>

    <div class="actions">
        <a href="?action=clean_old_states" class="btn" onclick="return confirm('Clean old user states (7+ days)?')">Clean Old States</a>
        <a href="https://api.telegram.org/bot<?php echo BOT_TOKEN; ?>/getWebhookInfo" target="_blank" class="btn">Check Webhook</a>
    </div>

    <div class="users-table">
        <div class="table-header">Recent User Activity</div>
        <?php if (empty($allUsers)): ?>
        <div class="table-row">
            <div colspan="4" style="text-align:center;color:#666;">No users found</div>
        </div>
        <?php else: ?>
            <?php foreach (array_slice($allUsers, -20, 20, true) as $chatId => $user): ?>
            <div class="table-row">
                <div><strong>Chat ID:</strong> <?php echo htmlspecialchars($chatId); ?></div>
                <div>
                    <span class="status <?php echo $user['step']; ?>">
                        <?php echo ucfirst($user['step']); ?>
                    </span>
                </div>
                <div><strong>Updated:</strong> <?php echo date('M j, H:i', $user['updated_at'] ?? $user['created_at']); ?></div>
                <div>
                    <?php if (isset($user['data']) && !empty($user['data'])): ?>
                    <small>
                        <?php
                        $data = $user['data'];
                        if (isset($data['ff_uid'])) echo "UID: " . htmlspecialchars($data['ff_uid']) . " ";
                        if (isset($data['rank'])) echo "(" . htmlspecialchars($data['rank']) . ")";
                        ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="logs">
        <h3>Recent Bot Activity</h3>
        <div class="log-content">
            <?php
            $logFile = LOG_DIR . '/bot.log';
            if (file_exists($logFile)) {
                $logs = file($logFile);
                $recentLogs = array_slice($logs, -20);
                foreach ($recentLogs as $log) {
                    echo htmlspecialchars($log) . "<br>";
                }
            } else {
                echo "No logs found.";
            }
            ?>
        </div>
    </div>

    <div style="margin-top:20px;text-align:center;color:#666;font-size:.9em;">
        <p>Guild Registration Bot Admin Panel v1.1.0</p>
        <p>Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</div>
</body>
</html>

<?php
function showLoginForm($error = '') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Guild Bot</title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}
.login-form{background:#fff;padding:40px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.1);width:100%;max-width:400px}
.form-group{margin-bottom:20px}
.form-label{display:block;margin-bottom:5px;font-weight:bold;color:#333}
.form-control{width:100%;padding:12px;border:1px solid #ddd;border-radius:4px;font-size:16px}
.btn{width:100%;padding:12px;background:#4CAF50;color:white;border:none;border-radius:4px;font-size:16px;cursor:pointer}
.btn:hover{background:#45a049}
.error{background:#f8d7da;color:#721c24;padding:10px;border-radius:4px;margin-bottom:20px}
h2{text-align:center;margin-bottom:30px;color:#333}
</style>
</head>
<body>
    <div class="login-form">
        <h2>🔐 Admin Login</h2>
        <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="text-align:center;margin-top:20px;color:#666;font-size:.9em;">
            Default password: admin123<br>
            <small>Change this in admin.php file!</small>
        </p>
    </div>
</body>
</html>
<?php
}
