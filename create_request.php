<?php
require_once 'config.php';
requireAuth();
requireRole('operateur');

$user = getCurrentUser();
$success = '';
$error = '';

// Récupérer les établissements de santé
$stmt = $pdo->query("SELECT id, name FROM establishments WHERE status = 'actif' ORDER BY name");
$establishments = $stmt->fetchAll();

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $establishment_id = $_POST['establishment_id'] ?? '';
    $patient_name = $_POST['patient_name'] ?? '';
    $patient_phone = $_POST['patient_phone'] ?? '';
    $departure_address = $_POST['departure_address'] ?? '';
    $arrival_address = $_POST['arrival_address'] ?? '';
    $transport_type = $_POST['transport_type'] ?? 'non_urgent';
    $medical_info = $_POST['medical_info'] ?? '';
    $special_equipment = $_POST['special_equipment'] ?? '';

    // Validation
    if (empty($establishment_id) || empty($patient_name) || empty($departure_address) || empty($arrival_address)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } else {
        try {
            // Générer un numéro de demande unique
            $request_number = 'REQ-' . date('Ym') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare("INSERT INTO transport_requests 
                (request_number, establishment_id, patient_name, patient_phone, departure_address, arrival_address, 
                 transport_type, medical_info, special_equipment, created_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'crée')");
            
            $stmt->execute([
                $request_number,
                $establishment_id,
                $patient_name,
                $patient_phone,
                $departure_address,
                $arrival_address,
                $transport_type,
                $medical_info,
                $special_equipment,
                $user['id']
            ]);

            $success = "Demande créée avec succès! Numéro: $request_number";

            // Créer une notification
            $request_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO notifications (transport_request_id, message, type) 
                          VALUES (?, ?, 'création')")->execute([
                $request_id,
                "Demande de transport créée par " . $user['name']
            ]);

            // Réinitialiser le formulaire
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création de la demande: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une demande - AmbulancePro</title>
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

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            max-width: 600px;
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
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

        .required::after {
            content: ' *';
            color: #e74c3c;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-buttons {
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }

            .main {
                margin-left: 0;
            }

            .form-container {
                max-width: 100%;
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
                <li><a href="create_request.php" class="active">Créer une demande</a></li>
                <li><a href="my_requests.php">Mes demandes</a></li>
                <li><a href="establishments.php">Établissements</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Créer une nouvelle demande de transport</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <div class="form-container">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="create_request.php">
                        <div class="form-group">
                            <label for="establishment_id" class="required">Établissement de santé</label>
                            <select id="establishment_id" name="establishment_id" required>
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($establishments as $est): ?>
                                    <option value="<?php echo $est['id']; ?>" <?php echo ($_POST['establishment_id'] ?? '') == $est['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($est['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="patient_name" class="required">Nom du patient</label>
                                <input type="text" id="patient_name" name="patient_name" required value="<?php echo htmlspecialchars($_POST['patient_name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="patient_phone">Téléphone du patient</label>
                                <input type="tel" id="patient_phone" name="patient_phone" value="<?php echo htmlspecialchars($_POST['patient_phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="departure_address" class="required">Adresse de départ</label>
                            <input type="text" id="departure_address" name="departure_address" required value="<?php echo htmlspecialchars($_POST['departure_address'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="arrival_address" class="required">Adresse d'arrivée</label>
                            <input type="text" id="arrival_address" name="arrival_address" required value="<?php echo htmlspecialchars($_POST['arrival_address'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="transport_type">Type de transport</label>
                            <select id="transport_type" name="transport_type">
                                <option value="non_urgent" <?php echo ($_POST['transport_type'] ?? 'non_urgent') === 'non_urgent' ? 'selected' : ''; ?>>Non urgent</option>
                                <option value="urgent" <?php echo ($_POST['transport_type'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="medical_info">Informations médicales</label>
                            <textarea id="medical_info" name="medical_info"><?php echo htmlspecialchars($_POST['medical_info'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="special_equipment">Équipements spéciaux requis</label>
                            <input type="text" id="special_equipment" name="special_equipment" value="<?php echo htmlspecialchars($_POST['special_equipment'] ?? ''); ?>">
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">Créer la demande</button>
                            <a href="my_requests.php" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
