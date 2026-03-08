<?php
require_once 'config.php';
requireAuth();
requireRole('planning');

// Récupérer les demandes en attente d'affectation
$stmt = $pdo->query("
    SELECT tr.*, e.name as establishment_name, u.name as creator_name
    FROM transport_requests tr
    JOIN establishments e ON tr.establishment_id = e.id
    JOIN users u ON tr.created_by = u.id
    WHERE tr.status IN ('crée', 'affecté')
    ORDER BY tr.transport_type DESC, tr.created_at ASC
");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes en attente - AmbulancePro</title>
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

        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .request-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }

        .request-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .request-card.urgent {
            border-left-color: #e74c3c;
        }

        .request-number {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .request-patient {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }

        .request-establishment {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .request-details {
            margin-bottom: 15px;
            font-size: 13px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }

        .detail-label {
            color: #999;
            font-weight: 500;
            width: 80px;
        }

        .detail-value {
            color: #555;
            flex: 1;
        }

        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .type-urgent {
            background: #ffe6e6;
            color: #c33;
        }

        .type-non-urgent {
            background: #e6f3ff;
            color: #003;
        }

        .btn {
            padding: 10px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
            width: 100%;
            margin-top: 15px;
        }

        .btn:hover {
            background: #764ba2;
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

            .requests-grid {
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
                <li><a href="pending_requests.php" class="active">Demandes en attente</a></li>
                <li><a href="assign_resources.php">Affecter ressources</a></li>
                <li><a href="vehicles.php">Véhicules</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Demandes de transport en attente</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <p>Aucune demande en attente.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-grid">
                        <?php foreach ($requests as $req): ?>
                            <div class="request-card <?php echo $req['transport_type'] === 'urgent' ? 'urgent' : ''; ?>">
                                <div class="request-number"><?php echo htmlspecialchars($req['request_number']); ?></div>
                                <div class="request-patient"><?php echo htmlspecialchars($req['patient_name']); ?></div>
                                <div class="request-establishment"><?php echo htmlspecialchars($req['establishment_name']); ?></div>
                                
                                <span class="type-badge type-<?php echo str_replace('_', '-', $req['transport_type']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $req['transport_type'])); ?>
                                </span>

                                <div class="request-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Départ:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(substr($req['departure_address'], 0, 30)); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Arrivée:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars(substr($req['arrival_address'], 0, 30)); ?></span>
                                    </div>
                                    <?php if ($req['medical_info']): ?>
                                        <div class="detail-row">
                                            <span class="detail-label">Infos:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars(substr($req['medical_info'], 0, 30)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Créée:</span>
                                        <span class="detail-value"><?php echo date('d/m H:i', strtotime($req['created_at'])); ?></span>
                                    </div>
                                </div>

                                <a href="assign_resources.php?request_id=<?php echo $req['id']; ?>" class="btn">
                                    Affecter ressources
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
