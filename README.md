# AmbulancePro - Système de Gestion des Transports Médicaux

Application web complète pour gérer les demandes de transports sanitaires, l'affectation des ressources, le suivi en temps réel et la facturation.

## Architecture

- **Backend:** PHP 7.4+
- **Base de données:** MySQL/MariaDB 5.7+
- **Frontend:** HTML5, CSS3, JavaScript vanilla
- **Stack:** PHP + MySQL + HTML/CSS/JS (pas de framework)

## Installation

### Prérequis

- PHP 7.4 ou supérieur
- MySQL/MariaDB 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)

### Étapes d'installation

1. **Cloner/Télécharger le projet**
   ```bash
   git clone <url-du-repo>
   cd ambulancepro
   ```

2. **Créer la base de données**
   - Ouvrir phpMyAdmin ou un client MySQL
   - Importer le fichier `database.sql`
   ```sql
   mysql -u root -p < database.sql
   ```

3. **Configurer les paramètres de connexion**
   - Éditer `config.php`
   - Ajuster les constantes de connexion à la base de données:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'ambulancepro');
     ```

4. **Lancer le serveur**
   - Avec PHP intégré:
   ```bash
   php -S localhost:8000
   ```
   - Ou configurer un hôte virtuel avec Apache/Nginx

5. **Accéder à l'application**
   - Ouvrir http://localhost:8000/login.php

## Identifiants de test

Tous les mots de passe test sont: `password123`

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Opérateur | operateur@example.com | password123 |
| Planning | planning@example.com | password123 |
| Chauffeur 1 | chauffeur1@example.com | password123 |
| Chauffeur 2 | chauffeur2@example.com | password123 |
| Facturation | facturation@example.com | password123 |

## Structure du projet

```
ambulancepro/
├── config.php                 # Configuration et fonctions globales
├── database.sql              # Schéma et données de test
├── login.php                 # Page de connexion
├── logout.php                # Déconnexion
├── dashboard.php             # Tableau de bord
│
├── Opérateur (Créer demandes)
├── create_request.php        # Créer une nouvelle demande
├── my_requests.php           # Voir ses demandes
├── establishments.php        # Liste des établissements
│
├── Planning (Affecter ressources)
├── pending_requests.php      # Demandes en attente
├── assign_resources.php      # Affecter véhicules & chauffeurs
├── vehicles.php              # Gestion des véhicules
│
├── Chauffeur (Suivre transports)
├── active_transports.php     # Transports affectés
├── tracking.php              # Suivi en temps réel
│
├── Facturation (Générer factures)
├── invoices.php              # Liste des factures
└── validate_invoices.php     # Valider les factures
```

## Workflow principal

1. **Création de demande (Opérateur)**
   - L'opérateur crée une demande de transport via le formulaire
   - La demande reçoit un numéro unique (REQ-YYYYMM-XXXX)
   - Statut initial: "crée"

2. **Affectation des ressources (Planning)**
   - Planning voit les demandes en attente
   - Sélectionne un véhicule et 1-2 chauffeurs
   - La demande passe au statut "affecté"
   - Les chauffeurs reçoivent la notification

3. **Exécution du transport (Chauffeur)**
   - Le chauffeur voit ses transports affectés
   - Peut démarrer le transport (statut: "en_cours")
   - Peut terminer le transport (statut: "terminé")
   - Suivi en temps réel avec timeline d'événements

4. **Facturation (Facturation)**
   - Une facture est créée automatiquement à la fin du transport
   - Statut initial: "en_attente"
   - Peut être validée (statut: "validée")
   - Peut être marquée comme payée

## Fonctionnalités principales

✅ Authentification multi-rôles avec sessions sécurisées  
✅ Gestion complète des demandes de transport  
✅ Affectation intelligente de ressources  
✅ Suivi en temps réel avec GPS simulé  
✅ Système de notifications d'événements  
✅ Gestion des factures et validation  
✅ Interface responsive (mobile-friendly)  
✅ Design moderne avec Tailwind CSS équivalent  
✅ Base de données normalisée avec contraintes d'intégrité  

## Sécurité

- Mots de passe hachés avec `password_hash()` (bcrypt)
- Sessions PHP avec vérification de l'authentification
- Vérification des rôles sur chaque action
- Requêtes paramétrées (prepared statements) contre l'injection SQL
- Protection CSRF via session management

## Déploiement sur hébergement gratuit

### Options d'hébergement gratuit pour PHP + MySQL :

1. **Railway** (Recommandé)
   - Offre gratuite: 500h/mois, 1GB storage, MySQL inclus
   - Déployer avec GitHub: https://railway.app
   
2. **Render**
   - Offre gratuite: Services web avec PHP support
   - Base de données PostgreSQL/MySQL disponible
   
3. **000WebHost**
   - Hébergement gratuit avec PHP et MySQL inclus
   - https://000webhost.com
   
4. **InfinityFree**
   - Hébergement gratuit illimité en ressources
   - https://infinityfree.net

### Configuration pour hébergement distant :

Modifier `config.php` avec les identifiants de la base de données fournie par votre hébergeur :
```php
define('DB_HOST', 'nom_du_serveur');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_NAME', 'nom_de_la_base');
```

## Support et contribution

Pour toute question ou amélioration, veuillez contacter l'équipe de développement.

---

**Version:** 1.0  
**Dernière mise à jour:** 2026-02-12
