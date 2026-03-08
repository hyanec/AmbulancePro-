<?php
// Script pour ajouter les nouvelles colonnes à la table users

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ambulancepro');

echo "<h1>Mise à jour de la structure de la base de données</h1>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✓ Connexion à la base de données réussie</p>";
    
    // Liste des colonnes à ajouter
    $columns = [
        "prenom VARCHAR(255) DEFAULT NULL",
        "adresse VARCHAR(255) DEFAULT NULL",
        "telephone VARCHAR(20) DEFAULT NULL",
        "cin VARCHAR(20) DEFAULT NULL",
        "statut ENUM('celibataire', 'mariee', 'divorcee') DEFAULT 'celibataire'"
    ];
    
    foreach ($columns as $column) {
        $columnName = explode(' ', $column)[0];
        try {
            $pdo->exec("ALTER TABLE users ADD $column");
            echo "<p>✓ Colonne '$columnName' ajoutée</p>";
        } catch (PDOException $e) {
            // La colonne existe peut-être déjà
            echo "<p>ℹ Colonne '$columnName' : " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2 style='color: green;'>Terminé!</h2>";
    echo "<p>Les nouvelles colonnes ont été ajoutées à la table users.</p>";
    echo "<p>Colonnes ajoutées:</p>";
    echo "<ul>";
    echo "<li>prenom - Prénom de l'utilisateur</li>";
    echo "<li>adresse - Adresse de l'utilisateur</li>";
    echo "<li>telephone - Numéro de téléphone</li>";
    echo "<li>cin - Numéro CIN</li>";
    echo "<li>statut - Statut matrimonial (celibataire, mariee, divorcee)</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Erreur!</h2>";
    echo "<p>Message d'erreur: " . $e->getMessage() . "</p>";
}
?>
