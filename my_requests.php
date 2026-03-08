<?php
require_once 'config.php';
requireAuth();
requireRole('operateur');

$user = getCurrentUser();

// Récupérer les demandes de l'utilisateur
$stmt = $pdo->prepare("
    SELECT tr.*, e.name as establishment_name
    FROM transport_requests tr
    JOIN establishments e ON tr.establishment_id = e.id
    WHERE tr.created_by = ?
    ORDER BY tr.created_at DESC
");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

// Traiter la suppression d'une demande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    // Vérifier que la demande appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM transport_requests WHERE id = ? AND created_by = ? AND status = 'crée'");
    $stmt->execute([$delete_id, $user['id']]);
    
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM transport_requests WHERE id = ?")->execute([$delete_id]);
        header('Location: my_requests.php?message=Demande supprimée avec succès');
        exit();
    }
}

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes demandes - AmbulancePro</title>
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

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .btn {
            padding: 8px 16px;
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

        .btn-danger {
            background: #e74c3c;
            padding: 4px 8px;
            font-size: 12px;
        }

        .btn-danger:hover {
            background: #c0392b;
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

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-créé {
            background: #fff3cd;
            color: #856404;
        }

        .status-affecté {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-en_cours {
            background: #cce5ff;
            color: #004085;
        }

        .status-terminé {
            background: #d4edda;
            color: #155724;
        }

        .status-annulé {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state p {
            margin-bottom: 20px;
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
                <li><a href="create_request.php">Créer une demande</a></li>
                <li><a href="my_requests.php" class="active">Mes demandes</a></li>
                <li><a href="establishments.php">Établissements</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Mes demandes de transport</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if (empty($requests)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez pas encore créé de demandes de transport.</p>
                        <a href="create_request.php" class="btn">Créer une demande</a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Patient</th>
                                <th>Établissement</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Créée le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($req['request_number']); ?></td>
                                    <td><?php echo htmlspecialchars($req['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($req['establishment_name']); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $req['transport_type'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo str_replace('_', '', $req['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $req['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?></td>
                                    <td>
                                        <?php if ($req['status'] === 'crée'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $req['id']; ?>">
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
