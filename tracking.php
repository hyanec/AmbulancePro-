<?php
require_once 'config.php';
requireAuth();
requireRole('chauffeur');

$user = getCurrentUser();
$transport_id = $_GET['transport_id'] ?? '';

if ($transport_id) {
    $stmt = $pdo->prepare("
        SELECT tr.*, e.name as establishment_name, v.registration as vehicle_registration,
               u1.name as driver1_name, u2.name as driver2_name
        FROM transport_requests tr
        JOIN establishments e ON tr.establishment_id = e.id
        JOIN vehicles v ON tr.vehicle_id = v.id
        LEFT JOIN users u1 ON tr.driver1_id = u1.id
        LEFT JOIN users u2 ON tr.driver2_id = u2.id
        WHERE tr.id = ? AND (tr.driver1_id = ? OR tr.driver2_id = ?)
    ");
    $stmt->execute([$transport_id, $user['id'], $user['id']]);
    $transport = $stmt->fetch();

    if ($transport) {
        // Récupérer les notifications
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE transport_request_id = ? ORDER BY created_at ASC");
        $stmt->execute([$transport_id]);
        $notifications = $stmt->fetchAll();
    }
} else {
    $transport = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi en temps réel - AmbulancePro</title>
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

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 1000px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }

        .detail {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .detail-label {
            color: #999;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            margin-top: 2px;
            font-weight: 500;
        }

        .map-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            margin: 20px 0;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: -24px;
            top: 20px;
            width: 2px;
            height: 30px;
            background: #ddd;
        }

        .timeline-time {
            font-size: 12px;
            color: #999;
        }

        .timeline-message {
            font-size: 14px;
            color: #333;
            margin-top: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn:hover {
            background: #764ba2;
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

            .container {
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
                <li><a href="active_transports.php">Transports actifs</a></li>
                <li><a href="tracking.php" class="active">Suivi en temps réel</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Suivi en temps réel</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php if (!$transport): ?>
                    <div class="empty-state">
                        <p>Sélectionnez un transport à suivre.</p>
                        <a href="active_transports.php" class="btn">Retour aux transports</a>
                    </div>
                <?php else: ?>
                    <div class="container">
                        <!-- Carte et infos -->
                        <div class="card">
                            <h3>Suivi géographique</h3>
                            <div class="map-placeholder">
                                Simulation GPS: Trajet en cours
                            </div>

                            <div class="detail">
                                <div class="detail-label">Statut</div>
                                <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $transport['status'])); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Véhicule</div>
                                <div class="detail-value"><?php echo htmlspecialchars($transport['vehicle_registration']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">De</div>
                                <div class="detail-value"><?php echo htmlspecialchars($transport['departure_address']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">À</div>
                                <div class="detail-value"><?php echo htmlspecialchars($transport['arrival_address']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Départ</div>
                                <div class="detail-value">
                                    <?php echo $transport['departure_time'] ? date('d/m H:i', strtotime($transport['departure_time'])) : 'En attente'; ?>
                                </div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Arrivée</div>
                                <div class="detail-value">
                                    <?php echo $transport['arrival_time'] ? date('d/m H:i', strtotime($transport['arrival_time'])) : 'En attente'; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="card">
                            <h3>Historique des événements</h3>

                            <?php if (empty($notifications)): ?>
                                <p style="color: #999; font-size: 14px;">Aucun événement pour le moment.</p>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($notifications as $notif): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-time">
                                                <?php echo date('d/m H:i', strtotime($notif['created_at'])); ?>
                                            </div>
                                            <div class="timeline-message">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <a href="active_transports.php" class="btn">Retour aux transports</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
