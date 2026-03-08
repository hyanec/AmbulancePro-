<?php
require_once 'config.php';
requireAuth();
requireRole('operateur');

// Récupérer tous les établissements
$stmt = $pdo->query("SELECT * FROM establishments ORDER BY name");
$establishments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Établissements - AmbulancePro</title>
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

        .est-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .est-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #667eea;
        }

        .est-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }

        .est-type {
            display: inline-block;
            padding: 4px 12px;
            background: #e6f3ff;
            color: #003;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .est-info {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-label {
            color: #999;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            margin-top: 2px;
            word-break: break-all;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .status-actif {
            background: #d4edda;
            color: #155724;
        }

        .status-inactif {
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

            .est-grid {
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
                <li><a href="create_request.php">Créer une demande</a></li>
                <li><a href="my_requests.php">Mes demandes</a></li>
                <li><a href="establishments.php" class="active">Établissements</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Établissements de santé</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <div class="est-grid">
                    <?php foreach ($establishments as $est): ?>
                        <div class="est-card">
                            <div class="est-name"><?php echo htmlspecialchars($est['name']); ?></div>
                            <span class="est-type"><?php echo ucfirst($est['type']); ?></span>

                            <div class="est-info">
                                <div class="info-label">Adresse</div>
                                <div class="info-value"><?php echo htmlspecialchars($est['address'] ?? 'Non renseignée'); ?></div>
                            </div>

                            <div class="est-info">
                                <div class="info-label">Téléphone</div>
                                <div class="info-value"><?php echo htmlspecialchars($est['phone'] ?? 'Non renseigné'); ?></div>
                            </div>

                            <div class="est-info">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($est['email'] ?? 'Non renseigné'); ?></div>
                            </div>

                            <span class="status-badge status-<?php echo $est['status']; ?>">
                                <?php echo ucfirst($est['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
