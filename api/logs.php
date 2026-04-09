<?php

namespace api;

session_start();

require_once 'tools.php';

// TODO: Remove this line in production
ini_set('display_errors', 1);

header('Content-Type: application/json');

Tools::checkPermission('p_log');

$methode = $_SERVER['REQUEST_METHOD'];

# On accepte le format multipart/form-data UNIQUEMENT sur les requetes POST et PATCH
# Sinon, il faudrait coder un parser de multipart/form-data
switch ($methode) {
    case 'GET':                      # READ
        get_logs();
        break;
    default:
        # 405 Method Not Allowed
        http_response_code(405);
        break;
}

class TechnicalLogGenerator
{
    private $components = [
        'DataProcessor', 'MemoryManager', 'NetworkStack', 'SecurityModule', 'CacheHandler',
        'QueryOptimizer', 'LoadBalancer', 'FileSystem', 'AuthenticationService', 'SessionManager',
        'DatabaseConnector', 'APIGateway', 'MessageQueue', 'EventBus', 'ConfigurationManager',
        'MetricsCollector', 'LogRotator', 'BackupService', 'IndexManager', 'CronScheduler',
        'RoutingEngine', 'ProxyServer', 'ValidationEngine', 'NotificationService', 'StreamProcessor',
        'Apache', 'MySQL', 'PhpServer', 'Docker'
    ];

    private $actions = [
        'initializing', 'analyzing', 'processing', 'optimizing', 'validating', 'synchronizing',
        'maintaining', 'monitoring', 'executing', 'deploying', 'rolling back', 'scaling',
        'reindexing', 'compressing', 'archiving', 'authenticating', 'authorizing', 'dispatching',
        'streaming', 'batching', 'persisting', 'caching', 'purging', 'replicating', 'sharding', 'restarting',
        'starting', 'stopping', 'encrypting', 'compressing', 'serializing',
    ];

    private $statuses = [
        'SUCCESS' => 0,
        'WARNING' => 1,
        'ERROR' => 2,
        'INFO' => 3,
        'DEBUG' => 4,
        'TRACE' => 5,
        'FATAL' => 6,
        'NOTICE' => 7
    ];

    private $technicalTerms = [
        'heap allocation', 'garbage collection', 'thread pool', 'socket binding', 'cache invalidation',
        'mutex lock', 'buffer overflow', 'memory pagination', 'race condition', 'deadlock prevention',
        'stack trace', 'kernel panic', 'connection pool', 'thread dump', 'memory leak', 'cpu throttling',
        'network latency', 'disk i/o', 'cache miss', 'index fragmentation', 'connection timeout',
        'database deadlock', 'query timeout', 'ssl handshake', 'dns resolution', 'tcp backlog',
        'memory swapping', 'load average', 'inode usage', 'zombie process', 'orphan process',
        'resource exhaustion', 'buffer underflow', 'stack overflow', 'heap fragmentation',
        'memory corruption', 'disk failure', 'network partition', 'file descriptor leak',
    ];

    private $errorMessages = [
        'Connection refused', 'Invalid credentials', 'Resource not available', 'Timeout exceeded',
        'Maximum retries reached', 'Invalid configuration', 'Resource limit exceeded', 'Permission denied',
        'Protocol version mismatch', 'Invalid checksum', 'Data corruption detected', 'Version conflict',
        'Incompatible protocol', 'Service unavailable', 'Circuit breaker open', 'Rate limit exceeded',
        'Invalid state transition', 'Deadlock detected', 'Insufficient resources', 'Quota exceeded',
        'Invalid request', 'Invalid response', 'Invalid payload', 'Invalid signature', 'Invalid token',
        'Nil pointer dereference', 'Segmentation fault', 'Stack overflow', 'Heap corruption',
        'Memory leak detected', 'Buffer overflow', 'Buffer underflow', 'Invalid opcode', 'Invalid memory access',
    ];

    private $subsystems = [
        'CORE', 'NET', 'IO', 'SEC', 'DB', 'CACHE', 'AUTH', 'API', 'QUEUE', 'CRON', 'METRICS',
        'BACKUP', 'INDEX', 'PROXY', 'VALID', 'NOTIF', 'STREAM', 'CONFIG', 'CLUSTER', 'DEPLOY',
        'ROUTER', 'LOG', 'MONITOR', 'DISPATCH', 'PERSIST', 'ENCRYPT', 'COMPRESS', 'ARCHIVE',
        'REPL', 'SHARD', 'RESTART', 'START', 'STOP', 'SCALE', 'ROLLBACK', 'REINDEX', 'PURGE',
        'SYNC', 'MAINT', 'ANALYZE', 'INIT', 'AUTHZ', 'AUTHN', 'DISCARD', 'RENEW', 'REVOKE',
    ];

    private $currentTimestamp;

    public function __construct()
    {
        $this->currentTimestamp = time();
    }

    private function getRandomMetrics()
    {
        $metrics = [
            'cpu' => rand(0, 100) . '%',
            'mem' => rand(64, 8192) . 'MB',
            'io' => rand(0, 1000) . 'ops/s',
            'lat' => rand(1, 500) . 'ms',
            'conn' => rand(1, 1000)
        ];
        return $metrics[array_rand($metrics)];
    }

    private function getRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
    }

    private function getRandomPort(): int
    {
        return rand(1024, 65535);
    }

    private function getRandomPID(): int
    {
        return rand(1000, 65535);
    }

    private function getRandomHex($length = 8)
    {
        return substr(md5(rand()), 0, $length);
    }

    private function getRandomElement($array)
    {
        return $array[array_rand($array)];
    }

    private function getNextTimestamp()
    {
        $this->currentTimestamp -= rand(1, 1800);
        return date('Y-m-d H:i:s.', $this->currentTimestamp) . sprintf('%03d', rand(0, 999));
    }

    private function getRandomVersionNumber()
    {
        return sprintf("%d.%d.%d", rand(0, 9), rand(0, 99), rand(0, 999));
    }

    private function getRandomThreadId()
    {
        return sprintf("t-%04x", rand(0, 65535));
    }

    public function generateLog()
    {
        $timestamp = $this->getNextTimestamp();
        $status = $this->getRandomElement(array_keys($this->statuses));
        $subsystem = $this->getRandomElement($this->subsystems);
        $component = $this->getRandomElement($this->components);
        $action = $this->getRandomElement($this->actions);
        $term = $this->getRandomElement($this->technicalTerms);

        $logFormats = [
            // Format standard
            fn() => sprintf(
                "[%s] [%s] [%s] [PID:%d] %s::%s - %s (metrics: %s, addr: %s:%d) [0x%s]",
                $timestamp,
                $status,
                $subsystem,
                $this->getRandomPID(),
                $component,
                $action,
                $term,
                $this->getRandomMetrics(),
                $this->getRandomIP(),
                $this->getRandomPort(),
                $this->getRandomHex()
            ),
            // Format détaillé avec thread
            fn() => sprintf(
                "[%s] [%s] [%s-%s] [v%s] %s::%s - %s (thread: %s, heap: %s) [trace: 0x%s]",
                $timestamp,
                $status,
                $subsystem,
                $component,
                $this->getRandomVersionNumber(),
                $action,
                $term,
                $this->getRandomElement($this->errorMessages),
                $this->getRandomThreadId(),
                $this->getRandomMetrics(),
                $this->getRandomHex(16)
            ),
            // Format concis
            fn() => sprintf(
                "[%s] [%s/%s] %s on %s [id: 0x%s]",
                $timestamp,
                $subsystem,
                $status,
                $action,
                $component,
                $this->getRandomHex(6)
            ),
            // Format technique
            fn() => sprintf(
                "[%s] [%s] [PID:%d] {component: %s, action: %s, status: %s, metric: %s, version: %s}",
                $timestamp,
                $subsystem,
                $this->getRandomPID(),
                $component,
                $action,
                $status,
                $this->getRandomMetrics(),
                $this->getRandomVersionNumber()
            ),

            fn() => sprintf(
                "[%s] {%s} %s || %s %s",
                $timestamp,
                $status,
                $subsystem,
                $component,
                $this->getRandomElement($this->errorMessages)
            ),

            fn () => sprintf(
                "[%s] [%s] %s occurred",
                $timestamp,
                $subsystem,
                $this->getRandomElement($this->errorMessages),
            )
        ];

        $format = $this->getRandomElement($logFormats);
        $message = $format();

        if ($status === 'ERROR' || $status === 'FATAL') {
            $message .= sprintf(
                " [ERROR] %s [stack: 0x%s]",
                $this->getRandomElement($this->errorMessages),
                $this->getRandomHex(16)
            );
        }

        return $message;
    }

    public function generateMultipleLogs($count = 10)
    {
        $logs = [];
        for ($i = 0; $i < $count; $i++) {
            $logs[] = $this->generateLog();
        }

        return join("\n", $logs);
    }
}

function get_logs(): void
{
    $generator = new TechnicalLogGenerator();
    $logs = $generator->generateMultipleLogs(100);

    echo json_encode(["logs" => $logs]);
}
