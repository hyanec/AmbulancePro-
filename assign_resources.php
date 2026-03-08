<?php
require_once 'config.php';
requireAuth();
requireRole('planning');

$request_id = $_GET['request_id'] ?? '';
$error = '';
$success = '';

// Récupérer la demande
if ($request_id) {
    $stmt = $pdo->prepare("
        SELECT tr.*, e.name as establishment_name
        FROM transport_requests tr
        JOIN establishments e ON tr.establishment_id = e.id
        WHERE tr.id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();

    if (!$request) {
        $error = 'Demande non trouvée';
        $request = null;
    }
} else {
    $request = null;
}

// Récupérer les véhicules disponibles
$stmt = $pdo->query("SELECT id, registration, type FROM vehicles WHERE status = 'disponible' ORDER BY type");
$vehicles = $stmt->fetchAll();

// Récupérer les chauffeurs disponibles
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'chauffeur' ORDER BY name");
$drivers = $stmt->fetchAll();

// Traiter l'affectation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request) {
    $vehicle_id = $_POST['vehicle_id'] ?? '';
    $driver1_id = $_POST['driver1_id'] ?? '';
    $driver2_id = $_POST['driver2_id'] ?? '';

    if (empty($vehicle_id) || empty($driver1_id)) {
        $error = 'Veuillez sélectionner un véhicule et au moins 1 chauffeur';
    } else if ($driver1_id === $driver2_id) {
        $error = 'Les deux chauffeurs doivent être différents';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE transport_requests 
                SET vehicle_id = ?, driver1_id = ?, driver2_id = ?, status = 'affecté', assigned_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$vehicle_id, $driver1_id, $driver2_id ?: null, $_SESSION['user_id'], $request_id]);

            // Créer une notification
            $pdo->prepare("INSERT INTO notifications (transport_request_id, message, type) 
                          VALUES (?, ?, 'affectation')")->execute([
                $request_id,
                "Ressources affectées par Planning"
            ]);

            $success = 'Ressources affectées avec succès!';
            
            // Rediriger vers la liste des demandes en attente
            header('Location: pending_requests.php?message=Affectation réussie');
            exit();
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'affectation: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affecter ressources - AmbulancePro</title>
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
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            background: #764ba2;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
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
                <li><a href="pending_requests.php">Demandes en attente</a></li>
                <li><a href="assign_resources.php" class="active">Affecter ressources</a></li>
                <li><a href="vehicles.php">Véhicules</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Affecter les ressources</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!$request): ?>
                    <div class="empty-state">
                        <p>Veuillez sélectionner une demande à affecter.</p>
                        <a href="pending_requests.php" class="btn btn-secondary">Retour aux demandes</a>
                    </div>
                <?php else: ?>
                    <div class="container">
                        <!-- Infos de la demande -->
                        <div class="card">
                            <h3>Détails de la demande</h3>
                            
                            <div class="detail">
                                <div class="detail-label">Numéro</div>
                                <div class="detail-value"><?php echo htmlspecialchars($request['request_number']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Patient</div>
                                <div class="detail-value"><?php echo htmlspecialchars($request['patient_name']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Établissement</div>
                                <div class="detail-value"><?php echo htmlspecialchars($request['establishment_name']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Type</div>
                                <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $request['transport_type'])); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Départ</div>
                                <div class="detail-value"><?php echo htmlspecialchars($request['departure_address']); ?></div>
                            </div>

                            <div class="detail">
                                <div class="detail-label">Arrivée</div>
                                <div class="detail-value"><?php echo htmlspecialchars($request['arrival_address']); ?></div>
                            </div>

                            <?php if ($request['medical_info']): ?>
                                <div class="detail">
                                    <div class="detail-label">Infos médicales</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($request['medical_info']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Formulaire d'affectation -->
                        <div class="card">
                            <h3>Affecter les ressources</h3>
                            
                            <form method="POST" action="assign_resources.php?request_id=<?php echo $request_id; ?>">
                                <div class="form-group">
                                    <label for="vehicle_id">Véhicule *</label>
                                    <select id="vehicle_id" name="vehicle_id" required>
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($vehicles as $v): ?>
                                            <option value="<?php echo $v['id']; ?>">
                                                <?php echo htmlspecialchars($v['registration']) . ' (' . ucfirst($v['type']) . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="driver1_id">Chauffeur 1 *</label>
                                    <select id="driver1_id" name="driver1_id" required>
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($drivers as $d): ?>
                                            <option value="<?php echo $d['id']; ?>">
                                                <?php echo htmlspecialchars($d['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="driver2_id">Chauffeur 2 (optionnel)</label>
                                    <select id="driver2_id" name="driver2_id">
                                        <option value="">-- Aucun --</option>
                                        <?php foreach ($drivers as $d): ?>
                                            <option value="<?php echo $d['id']; ?>">
                                                <?php echo htmlspecialchars($d['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="buttons">
                                    <button type="submit" class="btn btn-primary">Affecter</button>
                                    <a href="pending_requests.php" class="btn btn-secondary">Annuler</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
