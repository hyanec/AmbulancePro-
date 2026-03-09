<?php
// Ne pas inclure config.php ici pour éviter les redirections de session

session_start();

// Use Railway environment variables
$db_url = parse_url($_ENV['DATABASE_URL'] ?? 'mysql://root@localhost/ambulancepro');

define('DB_HOST', $db_url['host'] ?? 'localhost');
define('DB_USER', $db_url['user'] ?? 'root');
define('DB_PASS', $db_url['pass'] ?? '');
define('DB_NAME', ltrim($db_url['path'] ?? '/ambulancepro', '/'));





$error = '';
$success = '';

// Si déjà authentifié, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Traiter le formulaire de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("SELECT id, email, password, name, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $success = 'Connexion réussie! Redirection...';
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Email ou mot de passe incorrect';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmbulancePro - Connexion</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #999;
            font-size: 14px;
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

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
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

        .test-credentials {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .test-credentials h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .credentials {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #555;
            line-height: 1.6;
        }

        .credentials strong {
            display: block;
            margin-top: 8px;
            color: #333;
        }

        .credentials strong:first-child {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>AmbulancePro</h1>
            <p>Système de gestion des transports médicaux</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="test-credentials">
            <h3>Identifiants de test:</h3>
            <div class="credentials">
                <strong>Opérateur:</strong>
                operateur@example.com / password123

                <strong>Planning:</strong>
                planning@example.com / password123

                <strong>Chauffeur:</strong>
                chauffeur1@example.com / password123

                <strong>Facturation:</strong>
                facturation@example.com / password123
            </div>
        </div>
    </div>
</body>
</html>
