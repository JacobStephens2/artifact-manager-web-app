<?php

class AppLogger {

  private $log_file;
  private $log_dir;
  private $max_file_size = 10485760; // 10MB in bytes

  public function __construct() {
    // PROJECT_PATH is defined in the main initialize.php; fall back to
    // deriving it from this file's location (private/ is one level below project root).
    $project_path = defined('PROJECT_PATH') ? PROJECT_PATH : dirname(__DIR__);
    $this->log_dir = $project_path . '/logs';
    $this->log_file = $this->log_dir . '/app.log';
    $this->ensureLogDirectory();
  }

  /**
   * Ensures the logs directory exists.
   */
  private function ensureLogDirectory() {
    if (!is_dir($this->log_dir)) {
      mkdir($this->log_dir, 0755, true);
    }
  }

  /**
   * Rotates the log file if it exceeds the max file size.
   * Renames app.log -> app.log.1, app.log.1 -> app.log.2, etc.
   */
  private function rotateIfNeeded() {
    if (!file_exists($this->log_file)) {
      return;
    }
    if (filesize($this->log_file) < $this->max_file_size) {
      return;
    }

    // Shift existing rotated files up by one
    for ($i = 9; $i >= 1; $i--) {
      $older = $this->log_file . '.' . ($i + 1);
      $current = $this->log_file . '.' . $i;
      if (file_exists($current)) {
        rename($current, $older);
      }
    }

    // Rename the current log file to .1
    rename($this->log_file, $this->log_file . '.1');
  }

  /**
   * Gets the current user ID from the session, if available.
   */
  private function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
  }

  /**
   * Gets the client IP address.
   */
  private function getIpAddress() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      // May contain multiple IPs; take the first one
      $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      return trim($ips[0]);
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
  }

  /**
   * General purpose logging method.
   *
   * @param string $level   Log level: 'info', 'warning', or 'error'
   * @param string $action  Description of the action being logged
   * @param array  $details Additional context data
   */
  public function log($level, $action, $details = []) {
    $entry = [
      'timestamp'  => date('c'),
      'level'      => $level,
      'user_id'    => $this->getUserId(),
      'action'     => $action,
      'details'    => $details,
      'ip_address' => $this->getIpAddress(),
    ];

    $this->writeEntry($entry);
  }

  /**
   * Logs authentication events (login, logout, etc.).
   *
   * @param string $action  Auth action (e.g. 'login_success', 'login_failed', 'logout')
   * @param array  $details Additional context data
   */
  public function logAuth($action, $details = []) {
    $entry = [
      'timestamp'  => date('c'),
      'level'      => 'info',
      'user_id'    => $this->getUserId(),
      'action'     => $action,
      'category'   => 'auth',
      'details'    => $details,
      'ip_address' => $this->getIpAddress(),
    ];

    $this->writeEntry($entry);
  }

  /**
   * Logs data change events (CRUD operations).
   *
   * @param string $action      The action performed (e.g. 'create', 'update', 'delete')
   * @param string $entity_type The type of entity (e.g. 'artifact', 'user')
   * @param mixed  $entity_id   The ID of the entity
   * @param array  $details     Additional context data
   */
  public function logDataChange($action, $entity_type, $entity_id, $details = []) {
    $entry = [
      'timestamp'   => date('c'),
      'level'       => 'info',
      'user_id'     => $this->getUserId(),
      'action'      => $action,
      'category'    => 'data_change',
      'entity_type' => $entity_type,
      'entity_id'   => $entity_id,
      'details'     => $details,
      'ip_address'  => $this->getIpAddress(),
    ];

    $this->writeEntry($entry);
  }

  /**
   * Logs API request events.
   *
   * @param string $endpoint The API endpoint being called
   * @param array  $details  Additional context data
   */
  public function logApiRequest($endpoint, $details = []) {
    $entry = [
      'timestamp'  => date('c'),
      'level'      => 'info',
      'user_id'    => $this->getUserId(),
      'action'     => 'api_request',
      'category'   => 'api',
      'endpoint'   => $endpoint,
      'details'    => $details,
      'ip_address' => $this->getIpAddress(),
    ];

    $this->writeEntry($entry);
  }

  /**
   * Writes a log entry to the log file as a JSON line.
   *
   * @param array $entry The log entry data
   */
  private function writeEntry($entry) {
    $this->rotateIfNeeded();
    $json = json_encode($entry, JSON_UNESCAPED_SLASHES);
    file_put_contents($this->log_file, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
  }
}

?>
