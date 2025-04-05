<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$errorLogFile = '/var/log/apache2/error.log';

function parseErrorLogs($file, $filters = [], $sort = null, $page = 1, $perPage = 20) {
    if (!file_exists($file)) {
        throw new Exception("Error log file not found: " . $file);
    }

    $lines = file($file);
    if ($lines === false) {
        throw new Exception("Failed to read error log file");
    }

    $logs = [];
    $errorPatterns = [
        '/^\[([^\]]+)\] \[([^\]]+)\] \[pid \d+\] (?:\[client ([^\]:]+)(?::\d+)?\])? (.*?)$/' => 
            ['date', 'error_level', 'client', 'message'],
        
        '/^\[([^\]]+)\] \[([^\]]+)\] \[pid \d+\] (.*?): (.*)$/' => 
            ['date', 'module', 'type', 'message'],
        
        '/^\[([^\]]+)\] \[(ssl):error\] \[pid \d+\] (.*?): (.*)$/' => 
            ['date', 'error_level', 'type', 'message']
    ];

    foreach (array_reverse($lines) as $line) {
        $log = ['raw' => $line, 'timestamp' => 0, 'client' => null];
        $matched = false;
        
        foreach ($errorPatterns as $pattern => $fields) {
            if (preg_match($pattern, $line, $matches)) {
                foreach ($fields as $i => $field) {
                    $log[$field] = $matches[$i+1] ?? null;
                }
                $matched = true;
                break;
            }
        }
        
        if (!$matched) {
            $log['message'] = $line;
            $log['error_level'] = 'unknown';
        }

        if (!empty($log['date'])) {
		$cleanDate = preg_replace('/\.\d+/', '', $log['date']);
		 $log['timestamp'] = strtotime($cleanDate) ?: 0;
        }

        if (!empty($filters['client'])) {
          if (empty($log['client']) || 
              strpos($log['client'], $filters['client']) === false) {
            continue;
          }
        }
        $add = true;
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                if ($key === 'date' && !empty($log['date'])) {
                    if (strpos($log['date'], $value) === false) {
                        $add = false;
                        break;
                    }
                } elseif (isset($log[$key]) && stripos($log[$key], $value) === false) {
                    $add = false;
                    break;
                }
            }
        }

        if ($add) {
            $logs[] = $log;
        }
    }

    if ($sort) {
        $field = ltrim($sort, '-');
        $direction = $sort[0] === '-' ? -1 : 1;
        
        usort($logs, function($a, $b) use ($field, $direction) {
            if ($field === 'timestamp' || $field === 'date') {
                return ($a['timestamp'] - $b['timestamp']) * $direction;
            }
            return strcmp($a[$field] ?? '', $b[$field] ?? '') * $direction;
        });
    }

    $total = count($logs);
    $offset = ($page - 1) * $perPage;
    $paginatedLogs = array_slice($logs, $offset, $perPage);

    return [
        'logs' => $paginatedLogs,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $filters = [
            'error_level' => $_GET['error_level'] ?? '',
            'client' => $_GET['client'] ?? '',
            'message' => $_GET['message'] ?? '',
            'date' => $_GET['date'] ?? ''
        ];
        
        $sort = $_GET['sort'] ?? '-timestamp';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = min(100, max(10, intval($_GET['perPage'] ?? 20)));
        
        $result = parseErrorLogs($errorLogFile, $filters, $sort, $page, $perPage);
        echo json_encode($result, JSON_PRETTY_PRINT);
    } else {
        throw new Exception('Only GET method is allowed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
