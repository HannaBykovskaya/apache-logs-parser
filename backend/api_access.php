<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$logFile = '/var/log/apache2/access.log';


function parseLogs($file, $filters = [], $sort = null, $page = 1, $perPage = 10) {
    $lines = file($file);
    $logs = [];
    
    foreach ($lines as $line) {
        preg_match('/^(\S+) (\S+) (\S+) \[([^]]+)\] "(\S+) (\S+) (\S+)" (\d+) (\d+)/', $line, $matches);
        
        if (count($matches) === 10) {
            $log = [
                'ip' => $matches[1],
                'identity' => $matches[2],
                'user' => $matches[3],
                'date' => $matches[4],
                'method' => $matches[5],
                'path' => $matches[6],
                'protocol' => $matches[7],
                'status' => $matches[8],
                'size' => $matches[9],
                'raw' => $line
            ];
            
            $add = true;
            foreach ($filters as $key => $value) {
                if (!empty($value) && stripos($log[$key], $value) === false) {
                    $add = false;
                    break;
                }
            }
            
            if ($add) {
                $logs[] = $log;
            }
        }
    }
    
    if ($sort) {
        usort($logs, function($a, $b) use ($sort) {
            $field = ltrim($sort, '-');
            $order = $sort[0] === '-' ? -1 : 1;
            
            if ($a[$field] == $b[$field]) return 0;
            return ($a[$field] < $b[$field]) ? -1 * $order : 1 * $order;
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
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $filters = [
            'ip' => $_GET['ip'] ?? '',
            'method' => $_GET['method'] ?? '',
            'status' => $_GET['status'] ?? '',
            'path' => $_GET['path'] ?? ''
        ];
        
        $sort = $_GET['sort'] ?? null;
        $page = intval($_GET['page'] ?? 1);
        $perPage = intval($_GET['perPage'] ?? 10);
        
        $result = parseLogs($logFile, $filters, $sort, $page, $perPage);
        echo json_encode($result);
    } else {
        throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
