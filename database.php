<?php
/**
 * Simple File-based Database for User States and Logs
 */

class SimpleDatabase {
    private $dataDir;

    public function __construct() {
        $this->dataDir = DATA_DIR;
        $this->ensureDataDirectory();
    }

    private function ensureDataDirectory() {
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function getUserState($chatId) {
        $filename = $this->dataDir . '/user_' . $chatId . '.json';

        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            $state = json_decode($data, true);

            if ($state !== null) {
                return $state;
            }
        }

        return [
            'step' => 'idle',
            'data' => [],
            'created_at' => time()
        ];
    }

    public function saveUserState($chatId, $state) {
        $filename = $this->dataDir . '/user_' . $chatId . '.json';
        $state['updated_at'] = time();

        $result = file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));

        if ($result === false) {
            throw new Exception("Failed to save user state for chat ID: $chatId");
        }

        return true;
    }

    public function deleteUserState($chatId) {
        $filename = $this->dataDir . '/user_' . $chatId . '.json';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function userExists($chatId) {
        $filename = $this->dataDir . '/user_' . $chatId . '.json';
        return file_exists($filename);
    }

    public function getAllUsers() {
        $users = [];
        $files = glob($this->dataDir . '/user_*.json');

        foreach ($files as $file) {
            $data = file_get_contents($file);
            $state = json_decode($data, true);

            if ($state !== null) {
                $chatId = basename($file, '.json');
                $chatId = str_replace('user_', '', $chatId);
                $users[$chatId] = $state;
            }
        }

        return $users;
    }

    public function cleanOldStates($days = 7) {
        $cutoff = time() - ($days * 24 * 60 * 60);
        $files = glob($this->dataDir . '/user_*.json');
        $cleaned = 0;

        foreach ($files as $file) {
            $data = file_get_contents($file);
            $state = json_decode($data, true);

            if ($state !== null && isset($state['updated_at']) && $state['updated_at'] < $cutoff) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    public function saveRegistration($chatId, $data) {
        $registration = [
            'chat_id' => $chatId,
            'data' => $data,
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s')
        ];

        $filename = $this->dataDir . '/registrations.json';
        $registrations = [];

        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $registrations = json_decode($content, true) ?: [];
        }

        $registrations[] = $registration;

        return file_put_contents($filename, json_encode($registrations, JSON_PRETTY_PRINT));
    }

    public function getStats() {
        $stats = [
            'total_users' => 0,
            'active_registrations' => 0,
            'completed_registrations' => 0,
            'today_registrations' => 0
        ];

        $users = $this->getAllUsers();
        $today = date('Y-m-d');

        foreach ($users as $chatId => $state) {
            $stats['total_users']++;

            if ($state['step'] !== 'idle' && $state['step'] !== 'completed') {
                $stats['active_registrations']++;
            }

            if ($state['step'] === 'completed') {
                $stats['completed_registrations']++;

                if (isset($state['completed_at'])) {
                    $completedDate = date('Y-m-d', $state['completed_at']);
                    if ($completedDate === $today) {
                        $stats['today_registrations']++;
                    }
                }
            }
        }

        return $stats;
    }

    // NEW: list all user chat IDs for broadcast
    public function listAllUserIds() {
        $files = glob($this->dataDir . '/user_*.json');
        $ids = [];
        foreach ($files as $f) {
            $chatId = basename($f, '.json');
            $chatId = str_replace('user_', '', $chatId);
            if ($chatId !== '') $ids[] = $chatId;
        }
        return $ids;
    }

    // NEW: append raw chat log per user
    public function appendChatLog($chatId, $update) {
        $fname = $this->dataDir . '/chat_' . $chatId . '.json';
        $entry = [
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'update' => $update
        ];
        $all = [];
        if (file_exists($fname)) {
            $raw = file_get_contents($fname);
            $all = json_decode($raw, true) ?: [];
        }
        $all[] = $entry;
        file_put_contents($fname, json_encode($all, JSON_PRETTY_PRINT));
    }
}
