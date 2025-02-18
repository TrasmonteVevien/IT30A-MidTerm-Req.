<?php

// Sample log data (Replace this with real-time logs from a system)
$log_data = [
    "User login successful - IP: 192.168.1.1",
    "Multiple failed login attempts - IP: 203.0.113.5",
    "Suspicious script execution detected - /var/www/html/malware.sh",
    "User access denied - IP: 198.51.100.23",
    "High data transfer detected - 50GB uploaded to unknown server",
    "Unusual borrowing pattern detected - User ID: 1023 borrowed 10 rare books in 1 minute",
    "Multiple renewal attempts detected - User ID: 2045 tried renewing overdue books 5 times",
    "Unauthorized database access attempt - Admin Panel",
    "Repeated late returns detected - User ID: 3056 has returned 5 books late consecutively",
    "Fake account creation attempt - Multiple accounts registered with same IP 192.168.1.50"
];

// Define malicious activity patterns
$malicious_patterns = [
    "/failed login attempts/i",
    "/suspicious script execution/i",
    "/access denied/i",
    "/high data transfer/i",
    "/unusual borrowing pattern/i",
    "/multiple renewal attempts/i",
    "/unauthorized database access/i",
    "/repeated late returns/i",
    "/fake account creation attempt/i"
];

function detect_malicious_activity($log_entry, $patterns) {
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $log_entry)) {
            return true;
        }
    }
    return false;
}

function prevent_malicious_activity($log) {
    if (strpos($log, "failed login attempts") !== false || strpos($log, "unauthorized database access") !== false) {
        echo "ACTION: Locking user account and notifying admin.\n";
    } elseif (strpos($log, "unusual borrowing pattern") !== false) {
        echo "ACTION: Limiting user borrowing privileges.\n";
    } elseif (strpos($log, "multiple renewal attempts") !== false) {
        echo "ACTION: Enforcing cooldown period for renewals.\n";
    } elseif (strpos($log, "repeated late returns") !== false) {
        echo "ACTION: Issuing warning and applying penalties.\n";
    } elseif (strpos($log, "fake account creation attempt") !== false) {
        echo "ACTION: Blocking IP and flagging accounts for review.\n";
    } else {
        echo "ACTION: Logging and flagging for manual review.\n";
    }
}

function monitor_system($logs, $patterns) {
    echo "Monitoring system for malicious activity...\n";
    foreach ($logs as $log) {
        sleep(1); // Simulate real-time monitoring
        if (detect_malicious_activity($log, $patterns)) {
            echo "ALERT [" . date("Y-m-d H:i:s") . "]: Suspicious activity detected! -> $log\n";
            prevent_malicious_activity($log);
        } else {
            echo "LOG [" . date("Y-m-d H:i:s") . "]: $log\n";
        }
    }
}

monitor_system($log_data, $malicious_patterns);

?>
