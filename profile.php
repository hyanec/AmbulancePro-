<?php
// Page de profil utilisateur
require_once 'config.php';

$error = '';
$success = '';

if (!isAuthenticated()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Mettre à jour le profil
        $prenom = $_POST['prenom'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $cin = $_POST['cin'] ?? '';
        $statut = $_POST['statut'] ?? 'celibataire';
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET prenom = ?, adresse = ?, telephone = ?, cin = ?, statut = ? WHERE id = ?");
            $stmt->execute([$prenom, $adresse, $telephone, $cin, $statut, $_SESSION['user_id']]);
            $success = 'Profil mis à jour avec succès!';
            $user = getCurrentUser(); // Rafraîchir les données
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
    } elseif (isset($_POST['change_password'])) {
        // Changer le mot de passe
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Veuillez remplir tous les champs';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Les nouveaux mots de passe ne correspondent pas';
        } elseif (strlen($new_password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères';
        } else {
            // Vérifier le mot de passe actuel
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $success = 'Mot de passe modifié avec succès!';
                $user = getCurrentUser();
            } else {
                $error = 'Mot de passe actuel incorrect';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - AmbulancePro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .btn {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
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
        .user-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        .info-item label {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .info-item span {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Retour au tableau de bord</a>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Mon Profil</h1>
            <p>Bienvenue, <?php echo htmlspecialchars($user['name']); ?>!</p>
        </div>
        
        <div class="card">
            <h2>Informations du personnel</h2>
            <form method="POST" action="profile.php">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="role">Rôle</label>
                        <input type="text" id="role" value="<?php echo htmlspecialchars($user['role']); ?>" readonly>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="cin">CIN</label>
                        <input type="text" id="cin" name="cin" value="<?php echo htmlspecialchars($user['cin'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="statut">Statut matrimonial</label>
                    <select id="statut" name="statut">
                        <option value="celibataire" <?php echo ($user['statut'] ?? 'celibataire') === 'celibataire' ? 'selected' : ''; ?>>Célibataire</option>
                        <option value="mariee" <?php echo ($user['statut'] ?? 'celibataire') === 'mariee' ? 'selected' : ''; ?>>Marié(e)</option>
                        <option value="divorcee" <?php echo ($user['statut'] ?? 'celibataire') === 'divorcee' ? 'selected' : ''; ?>>Divorcé(e)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Enregistrer les modifications</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Changer le mot de passe</h2>
            <form method="POST" action="profile.php">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="btn">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</body>
</html>
