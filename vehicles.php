<?php
require_once 'config.php';
requireAuth();
requireRole('planning');

// Récupérer tous les véhicules
$stmt = $pdo->query("SELECT * FROM vehicles ORDER BY registration");
$vehicles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Véhicules - AmbulancePro</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        thead {
            background: #f9f9f9;
            border-bottom: 2px solid #eee;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .type-ambulance {
            background: #ffe6e6;
            color: #c33;
        }

        .type-vsl {
            background: #e6f3ff;
            color: #003;
        }

        .type-autre {
            background: #f0e6ff;
            color: #660099;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-disponible {
            background: #d4edda;
            color: #155724;
        }

        .status-occupé {
            background: #fff3cd;
            color: #856404;
        }

        .status-maintenance {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main {
                margin-left: 0;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
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
                <li><a href="pending_requests.php">Demandes en attente</a></li>
                <li><a href="assign_resources.php">Affecter ressources</a></li>
                <li><a href="vehicles.php" class="active">Véhicules</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Gestion des véhicules</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php
                $available = 0;
                $occupied = 0;
                $maintenance = 0;
                
                foreach ($vehicles as $v) {
                    if ($v['status'] === 'disponible') $available++;
                    elseif ($v['status'] === 'occupé') $occupied++;
                    else $maintenance++;
                }
                ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Disponibles</h3>
                        <div class="number"><?php echo $available; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>En cours</h3>
                        <div class="number"><?php echo $occupied; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>En maintenance</h3>
                        <div class="number"><?php echo $maintenance; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total</h3>
                        <div class="number"><?php echo count($vehicles); ?></div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Immatriculation</th>
                            <th>Type</th>
                            <th>Capacité</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($v['registration']); ?></strong></td>
                                <td>
                                    <span class="type-badge type-<?php echo $v['type']; ?>">
                                        <?php echo ucfirst($v['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo $v['capacity']; ?> personnes</td>
                                <td>
                                    <span class="status-badge status-<?php echo $v['status']; ?>">
                                        <?php echo ucfirst($v['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
