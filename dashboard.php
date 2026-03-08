<?php
require_once 'config.php';
requireAuth();

$user = getCurrentUser();
$userRole = $user['role'];

// Récupérer les statistiques selon le rôle
$stats = [];

if ($userRole === 'operateur') {
    // Demandes créées
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transport_requests WHERE created_by = " . $user['id']);
    $stats['requests_created'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transport_requests WHERE created_by = " . $user['id'] . " AND status = 'crée'");
    $stats['requests_pending'] = $stmt->fetch()['count'];
} elseif ($userRole === 'planning') {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transport_requests WHERE status IN ('crée', 'affecté')");
    $stats['pending_assignments'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transport_requests WHERE status = 'affecté'");
    $stats['assigned'] = $stmt->fetch()['count'];
} elseif ($userRole === 'chauffeur') {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transport_requests WHERE (driver1_id = " . $user['id'] . " OR driver2_id = " . $user['id'] . ") AND status IN ('affecté', 'en_cours')");
    $stats['active_transports'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM transport_requests WHERE (driver1_id = " . $user['id'] . " OR driver2_id = " . $user['id'] . ") AND status = 'terminé'");
    $stats['completed'] = $stmt->fetch()['count'];
} elseif ($userRole === 'facturation') {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'en_attente'");
    $stats['pending_invoices'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM invoices WHERE status = 'validée'");
    $stats['validated_invoices'] = $stmt->fetch()['count'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmbulancePro - Tableau de bord</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            font-size: 14px;
            color: #666;
        }

        .user-info strong {
            display: block;
            color: #333;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
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
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: #c0392b;
        }

        .btn-settings {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-settings:hover {
            background: #5568d3;
        }

        .content {
            padding: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-card .number {
            font-size: 32px;
            color: #667eea;
            font-weight: 700;
        }

        .quick-actions {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .quick-actions h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
            display: inline-block;
        }

        .btn:hover {
            background: #764ba2;
        }

        .btn-secondary {
            background: #95a5a6;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
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

            .header {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
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
                <?php if ($userRole === 'operateur'): ?>
                    <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                    <li><a href="create_request.php">Créer une demande</a></li>
                    <li><a href="my_requests.php">Mes demandes</a></li>
                    <li><a href="establishments.php">Établissements</a></li>
                <?php elseif ($userRole === 'planning'): ?>
                    <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                    <li><a href="pending_requests.php">Demandes en attente</a></li>
                    <li><a href="assign_resources.php">Affecter ressources</a></li>
                    <li><a href="vehicles.php">Véhicules</a></li>
                <?php elseif ($userRole === 'chauffeur'): ?>
                    <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                    <li><a href="active_transports.php">Transports actifs</a></li>
                    <li><a href="tracking.php">Suivi en temps réel</a></li>
                <?php elseif ($userRole === 'facturation'): ?>
                    <li><a href="dashboard.php" class="active">Tableau de bord</a></li>
                    <li><a href="invoices.php">Factures</a></li>
                    <li><a href="validate_invoices.php">Valider factures</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Déconnexion</a></li>
                <li><a href="profile.php">Paramètres</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Tableau de bord</h1>
                <div class="header-right">
                    <div class="user-info">
                        <p>Connecté en tant que</p>
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        <small style="color: #999;"><?php echo ucfirst($user['role']); ?></small>
                    </div>
                    
                </div>
            </div>

            <div class="content">
                <div class="stats-grid">
                    <?php if ($userRole === 'operateur'): ?>
                        <div class="stat-card">
                            <h3>Demandes créées</h3>
                            <div class="number"><?php echo $stats['requests_created']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>En attente de traitement</h3>
                            <div class="number"><?php echo $stats['requests_pending']; ?></div>
                        </div>
                    <?php elseif ($userRole === 'planning'): ?>
                        <div class="stat-card">
                            <h3>Demandes en attente</h3>
                            <div class="number"><?php echo $stats['pending_assignments']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Affectations faites</h3>
                            <div class="number"><?php echo $stats['assigned']; ?></div>
                        </div>
                    <?php elseif ($userRole === 'chauffeur'): ?>
                        <div class="stat-card">
                            <h3>Transports actifs</h3>
                            <div class="number"><?php echo $stats['active_transports']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Transports terminés</h3>
                            <div class="number"><?php echo $stats['completed']; ?></div>
                        </div>
                    <?php elseif ($userRole === 'facturation'): ?>
                        <div class="stat-card">
                            <h3>Factures en attente</h3>
                            <div class="number"><?php echo $stats['pending_invoices']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Factures validées</h3>
                            <div class="number"><?php echo $stats['validated_invoices']; ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="quick-actions">
                    <h3>Actions rapides</h3>
                    <div class="action-buttons">
                        <?php if ($userRole === 'operateur'): ?>
                            <a href="create_request.php" class="btn">Créer une demande</a>
                            <a href="my_requests.php" class="btn btn-secondary">Voir mes demandes</a>
                        <?php elseif ($userRole === 'planning'): ?>
                            <a href="pending_requests.php" class="btn">Voir les demandes en attente</a>
                            <a href="assign_resources.php" class="btn btn-secondary">Affecter ressources</a>
                        <?php elseif ($userRole === 'chauffeur'): ?>
                            <a href="active_transports.php" class="btn">Mes transports</a>
                            <a href="tracking.php" class="btn btn-secondary">Suivi en temps réel</a>
                        <?php elseif ($userRole === 'facturation'): ?>
                            <a href="validate_invoices.php" class="btn">Valider factures</a>
                            <a href="invoices.php" class="btn btn-secondary">Voir factures</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
