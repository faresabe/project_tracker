<?php
require_once '../../authentication/config.php';

require_auth();
$user_id = get_current_user_id();

try {
    // Get projects for the current user only
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate status percentages
    $total = count($projects);
    $completed = 0;
    $pending = 0;
    $ongoing = 0;
    
    foreach ($projects as $project) {
        switch ($project['status']) {
            case 'completed':
                $completed++;
                break;
            case 'pending':
                $pending++;
                break;
            case 'ongoing':
                $ongoing++;
                break;
        }
    }
    
    $response = [
        'projects' => $projects,
        'stats' => [
            'total' => $total,
            'completed' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'pending' => $total > 0 ? round(($pending / $total) * 100) : 0,
            'ongoing' => $total > 0 ? round(($ongoing / $total) * 100) : 0
        ]
    ];
    
    json_response($response);
} catch(PDOException $e) {
    json_response(['error' => $e->getMessage()], 500);
}
?>