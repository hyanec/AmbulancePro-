<?php
// Script d'installation de la base de données
// Ce script crée la base de données et les tables si elles n'existent pas

// Inclure la configuration
require_once 'config.php';

echo "<h1>Installation de la base de données AmbulancePro</h1>";

try {
    // Connexion sans spécifier de base de données pour la créer
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<p>✓ Connexion au serveur MySQL réussie</p>";
    
    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ambulancepro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✓ Base de données 'ambulancepro' créée (si elle n'existait pas)</p>";
    
    // Sélectionner la base de données
    $pdo->exec("USE ambulancepro");
    
    // Lire et exécuter le fichier SQL
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Supprimer les lignes CREATE DATABASE et USE du fichier SQL car on l'a déjà fait
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Exécuter les instructions SQL
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && strpos($statement, '--') !== 0) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p>✓ Tables créées avec succès</p>";
    echo "<h2 style='color: green;'>Installation terminée avec succès!</h2>";
    echo "<p>Vous pouvez maintenant vous connecter avec les identifiants suivants:</p>";
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
