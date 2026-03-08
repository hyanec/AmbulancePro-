<?php
// Script pour mettre à jour les mots de passe hashés

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ambulancepro');

echo "<h1>Mise à jour des mots de passe</h1>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✓ Connexion à la base de données réussie</p>";
    
    // Nouveau mot de passe pour tous les utilisateurs
    $newPassword = 'password123';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    echo "<p>Nouveau hash: " . $hashedPassword . "</p>";
    
    // Mettre à jour tous les mots de passe
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$hashedPassword]);
    
    echo "<p>✓ Mot de passe mis à jour pour tous les utilisateurs</p>";
    echo "<h2 style='color: green;'>Terminé!</h2>";
    echo "<p>Vous pouvez maintenant vous connecter avec:</p>";
    echo "<ul>";
    echo "<li>Email: operateur@example.com</li>";
    echo "<li>Mot de passe: password123</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Aller à la page de connexion</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Erreur!</h2>";
    echo "<p>Message d'erreur: " . $e->getMessage() . "</p>";
}
?>
