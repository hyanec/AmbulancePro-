<?php
require_once 'config.php';
requireAuth();
requireRole('chauffeur');

$user = getCurrentUser();

// Récupérer les transports assignés au chauffeur
$stmt = $pdo->prepare("
    SELECT tr.*, e.name as establishment_name, v.registration as vehicle_registration,
           u1.name as driver1_name, u2.name as driver2_name
    FROM transport_requests tr
    JOIN establishments e ON tr.establishment_id = e.id
    JOIN vehicles v ON tr.vehicle_id = v.id
    LEFT JOIN users u1 ON tr.driver1_id = u1.id
    LEFT JOIN users u2 ON tr.driver2_id = u2.id
    WHERE (tr.driver1_id = ? OR tr.driver2_id = ?) AND tr.status IN ('affecté', 'en_cours')
    ORDER BY tr.created_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$transports = $stmt->fetchAll();

// Traiter le démarrage d'un transport
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $transport_id = $_POST['transport_id'];
    $action = $_POST['action'];

    $stmt = $pdo->prepare("SELECT id FROM transport_requests WHERE id = ? AND (driver1_id = ? OR driver2_id = ?)");
    $stmt->execute([$transport_id, $user['id'], $user['id']]);
    
    if ($stmt->fetch()) {
        if ($action === 'start') {
            $pdo->prepare("UPDATE transport_requests SET status = 'en_cours', departure_time = NOW() WHERE id = ?")->execute([$transport_id]);
            
            // Notification
            $pdo->prepare("INSERT INTO notifications (transport_request_id, message, type) 
                          VALUES (?, ?, 'départ')")->execute([$transport_id, "Transport démarré par chauffeur"]);
        } elseif ($action === 'complete') {
            $pdo->prepare("UPDATE transport_requests SET status = 'terminé', arrival_time = NOW() WHERE id = ?")->execute([$transport_id]);
            
            // Notification
            $pdo->prepare("INSERT INTO notifications (transport_request_id, message, type) 
                          VALUES (?, ?, 'arrivée')")->execute([$transport_id, "Transport terminé par chauffeur"]);
        }
        
        header('Location: active_transports.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transports actifs - AmbulancePro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 30px;
            color: #667eea;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            color: #bbb;
            text-decoration: none;
            padding: 12px;
            display: block;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #667eea;
            color: white;
        }

        .main {
            flex: 1;
            margin-left: 250px;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
        }

        .btn-logout {
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .transports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .transport-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
        }

        .transport-card.en-cours {
            border-left-color: #f39c12;
        }

        .transport-number {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .patient-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-affecté {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-en_cours {
            background: #fff3cd;
            color: #856404;
        }

        .details {
            font-size: 13px;
            margin-bottom: 15px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }

        .detail-label {
            color: #999;
            font-weight: 500;
            width: 70px;
        }

        .detail-value {
            color: #555;
            flex: 1;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.3s;
            flex: 1;
            text-align: center;
        }

        .btn:hover {
            background: #764ba2;
        }

        .btn-success {
            background: #27ae60;
        }

        .btn-success:hover {
            background: #229954;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }

            .main {
                margin-left: 0;
            }

            .transports-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <h2>AmbulancePro</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="active_transports.php" class="active">Transports actifs</a></li>
                <li><a href="tracking.php">Suivi en temps réel</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Mes transports actifs</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php if (empty($transports)): ?>
                    <div class="empty-state">
                        <p>Aucun transport assigné pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="transports-grid">
                        <?php foreach ($transports as $t): ?>
                            <div class="transport-card <?php echo str_replace('_', '-', $t['status']); ?>">
                                <div class="transport-number"><?php echo htmlspecialchars($t['request_number']); ?></div>
                                <div class="patient-name"><?php echo htmlspecialchars($t['patient_name']); ?></div>
                                
                                <span class="status-badge status-<?php echo str_replace('_', '', $t['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $t['status'])); ?>
                                </span>

                                <div class="details">
                                    <div class="detail-row">
                                        <span class="detail-label">Établ.:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(substr($t['establishment_name'], 0, 20)); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Véhicule:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($t['vehicle_registration']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Départ:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(substr($t['departure_address'], 0, 20)); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Arrivée:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(substr($t['arrival_address'], 0, 20)); ?></span>
                                    </div>
                                </div>

                                <div class="actions">
                                    <?php if ($t['status'] === 'affecté'): ?>
                                        <form method="POST" style="flex: 1;">
                                            <input type="hidden" name="transport_id" value="<?php echo $t['id']; ?>">
                                            <input type="hidden" name="action" value="start">
                                            <button type="submit" class="btn btn-success" style="width: 100%;">Démarrer</button>
                                        </form>
                                    <?php elseif ($t['status'] === 'en_cours'): ?>
                                        <form method="POST" style="flex: 1;">
                                            <input type="hidden" name="transport_id" value="<?php echo $t['id']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="btn btn-success" style="width: 100%;">Terminer</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="tracking.php?transport_id=<?php echo $t['id']; ?>" class="btn">Suivi</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
