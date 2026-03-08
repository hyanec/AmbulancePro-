<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ambulancepro');

// Configuration de l'application
define('APP_NAME', 'AmbulancePro');
define('APP_URL', '');

// Démarrer la session
session_start();

// Créer la connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
    
}

// Fonction pour vérifier l'authentification
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Fonction pour rediriger si non authentifié
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit();
    }
}

// Fonction pour obtenir l'utilisateur courant
function getCurrentUser() {
    global $pdo;
    if (!isAuthenticated()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Fonction pour envoyer une réponse JSON
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

// Fonction pour vérifier le rôle
function requireRole($requiredRole) {
    $user = getCurrentUser();
    if (!$user || $user['role'] !== $requiredRole) {
        sendJSON(['error' => 'Accès refusé'], 403);
    }
}
?>
