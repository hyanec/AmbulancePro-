# Guide - Uploader sur GitHub pour la première fois

## Étape 1 : Créer un compte GitHub
1. Allez sur https://github.com
2. Cliquez sur "Sign up" pour créer un compte gratuit

## Étape 2 : Créer un nouveau dépôt (Repository)
1. Connectez-vous à votre compte GitHub
2. Cliquez sur le bouton "+" en haut à droite → "New repository"
3. Nom du dépôt : `AmbulancePro`
4. Description : "Système de gestion des transports médicaux"
5. Choisissez "Public" (gratuit)
6. Cliquez "Create repository"

## Étape 3 : Uploader les fichiers depuis votre ordinateur

### Option A : Avec Git Bash (recommandé)

Ouvrez votre terminal et exécutez ces commandes :

```bash
# Aller dans le dossier du projet
cd c:/xampp/htdocs/AmbulancePro

# Initialiser git (si pas encore fait)
git init

# Ajouter tous les fichiers
git add .

# Créer un premier commit
git commit -m "Premier upload - AmbulancePro"

# Renommer la branche principale
git branch -M main

# Ajouter le dépôt distant (remplacez VOTRE_NOM par votre pseudo)
git remote add origin https://github.com/VOTRE_NOM/AmbulancePro.git

# Envoyer vers GitHub
git push -u origin main
```

### Option B : Via l'interface web de GitHub (plus simple)

1. Allez sur votre dépôt créé (https://github.com/VOTRE_NOM/AmbulancePro)
2. Cliquez sur "uploading an existing file"
3. Glissez-déposez tous vos fichiers PHP et autres depuis votre dossier local
4. Ajoutez un message de commit : "Premier upload"
5.

Cliquez "Commit changes"

## Après l'upload成功✅✅✅✅✅✅!

Votre code sera maintenant disponiblesurGitHubetvouspourrez:
- Leclonerdesde n'importequelordinate ur- Ledéployersurun hébergeurcomme Railway, Render, etc.
- Collaboreravec d'autres développeurs-

## Notes importantes⚠️⚠️⚠️⚠️⚠️⚠️:

❌ Nesupprimezpaslevotreordi ginal! Gardeztoujoursune copie locale.

❌ Lefichier .env ou config.php contient vos mots de passe base dedonnées- Assurez-vousde ne pasles uploader si vous avez des informations sensibles! Le fichier .gitignore devrait exclure ces fichiers automatiquement.

❌ Lebase dedonnées database.sql estincluse mais sans données sensibles car c'est juste lastructure+ donnéesdetest.

C'est tout! 🎉🎉🎉🎉🎉🎉 Vous savez maintenant comment utiliserGit!
