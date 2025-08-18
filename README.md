# PM App - Laravel Project Management Application

## Description
PM App est une application de gestion de projet moderne développée avec Laravel. Elle permet de gérer des projets, des tâches, des équipes et des rapports quotidiens avec une interface intuitive.Cette application est intègre Un assistant IA via une api externe python a intégré .

## Prérequis
- PHP 8.2 ou supérieur
- Composer
- MySQL
- Node.js & NPM

## Installation et Configuration

    ### 1. Cloner le projet
    ```cmd
    git clone [https://github.com/Isaac0524/pm-app.git]
    cd pm-app
    ```

    ### 2. Installer les dépendances PHP
    ```cmd
    composer install
    ```

    ### 3. Installer les dépendances JavaScript
    ```cmd
    npm install
    ```

    ### 6. Migration de la base de données
    ```cmd
    php artisan migrate
    ```
    
    ### 8. Compilation des assets
    ```cmd
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
    Consulter les seeders pour avoir la structure du mail et du password des utilisateurs


## Commandes utiles

    ### Lancer le serveur de développement
    ```cmd
    php artisan serve
    ```
    ### 7. Seeders - Création des données de test
    ```cmd
    php artisan db:seed
    ```




