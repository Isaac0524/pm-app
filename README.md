# PM App - Laravel Project Management Application

## Description
PM App est une application de gestion de projet moderne développée avec Laravel. Elle permet de gérer des projets, des tâches, des équipes et des rapports quotidiens avec une interface intuitive.

## Prérequis
- PHP 8.2 ou supérieur
- Composer
- MySQL
- Node.js & NPM

## Installation et Configuration

### 1. Cloner le projet
```bash
git clone [URL_DU_REPO]
cd pm-app
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Installer les dépendances JavaScript
```bash
npm install
```


### 6. Migration de la base de données
```bash
php artisan migrate
```

### 7. Seeders - Création des données de test
```bash
php artisan db:seed
```

### 8. Compilation des assets
```bash
npm run dev
```

## Utilisation

### Accès à l'application
L'application sera accessible à l'adresse : `http://localhost:8000`

### Identifiants de connexion
Après l'exécution des seeders, les utilisateurs suivants seront créés :

| Rôle | Email | Mot de passe |
|------|--------|--------------|
| Manager | manager@pm-app.com | password123 |


**Mot de passe par défaut pour tous les utilisateurs : `password123`**

### Structure des emails
Tous les utilisateurs créés ont des emails au format : `[role]@pm-app.com`
- manager@pm-app.com


## Commandes utiles

### Lancer le serveur de développement
```bash
php artisan serve
```




