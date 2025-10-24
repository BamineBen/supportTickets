# Gestion de Tickets de Support — Laravel 12 & Vue.js 3


---

##  Sommaire

- [Description du projet](#description-du-projet)
- [Fonctionnalités principales](#fonctionnalités-principales)
- [Technologies utilisées](#technologies-utilisées)
- [Pré-requis](#pré-requis)
- [Installation du backend (Laravel)](#installation-du-backend-laravel)
- [Installation du frontend (Vue.js)](#installation-du-frontend-vuejs)
- [Utilisation](#utilisation)
- [Configuration de l'envoi d'e-mails](#configuration-de-lenvoi-demails)
- [Astuces de développement](#astuces-de-développement)
- [Exemples d'utilisation de l'API](#exemples-dutilisation-de-lapi)
- [Arborescence du projet](#arborescence-du-projet)
- [Difficultés rencontrées](#difficultés-rencontrées)
- [Licence](#licence)

---

## Description du projet

Application web de gestion de tickets de support.  
- **Utilisateurs** : soumettent des tickets d’assistance, suivent leur état.
- **Administrateurs** : valident, traitent, clôturent les tickets, gèrent les utilisateurs.

---

## Fonctionnalités principales

- Authentification (email/mot de passe, Google OAuth)
- Rôles : admin / utilisateur
- CRUD sur les tickets (création, suivi, commentaires)
- Dashboard admin et utilisateur
- Filtres dynamiques, pagination
- Gestion des utilisateurs (admin)
- Notifications par e-mail (inscription, réinitialisation de mot de passe, notifications)
- Page contact (envoi de message)
- Sécurité : validation, protection des routes, permissions par rôles

---

## Technologies utilisées

- **Backend** : Laravel 12, Sanctum, Spatie Laravel Permission, MySQL / SQLite
- **Frontend** : Vue.js 3, Vue Router, Pinia, Axios, Bootstrap 5
- **Emails** : Mailtrap (test), possibilité de configurer Gmail, Mailgun, SendGrid
- **Versionnage** : Git & Bitbucket

---

## Pré-requis

- PHP >= 8.2
- Composer
- Node.js >= 18
- npm ou yarn
- MySQL ou SQLite
- Compte Mailtrap (pour tests e-mail)

---

## Installation du backend (Laravel)

1. Clonez le dépôt :
    ```bash
    git clone <lien-bitbucket>
    cd backend_Laravel/support_tickets
    ```

2. Installez les dépendances PHP :
    ```bash
    composer install
    ```

3. Copiez le fichier d’exemple `.env` :
    ```bash
    cp .env.example .env
    ```

4. Générez la clé d’application :
    ```bash
    php artisan key:generate
    ```

5. Configurez votre base de données dans `.env` :
    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ticket_db
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6. Configurez le service mail dans `.env` (exemple Mailtrap) :
    ```
    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=XXX
    MAIL_PASSWORD=XXX
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=noreply@example.com
    MAIL_FROM_NAME="Support Tickets"
    ```

7. Lancez les migrations et seeders :
    ```bash
    php artisan migrate --seed
    ```

8. Lancez le serveur de développement :
    ```bash
    php artisan serve
    ```
    Par défaut sur `http://localhost:8000`

---

## Installation du frontend (Vue.js)

1. Ouvrez un second terminal, puis :
    ```bash
    cd frontend_Vuejs/ticket-support-frontend
    ```

2. Installez les dépendances :
    ```bash
    npm install
    # ou
    yarn install
    ```

3. Lancez le serveur de développement :
    ```bash
    npm run dev
    # ou
    yarn dev
    ```
    Par défaut sur `http://localhost:3000`

---

## Utilisation

- Accédez à [http://localhost:3000](http://localhost:3000)
- Créez un compte utilisateur ou admin (un compte admin est créé par défaut via les seeders)
- Connectez-vous pour accéder à l’interface utilisateur ou admin selon votre rôle

---

## Configuration de l'envoi d'e-mails

- **Mailtrap** est configuré pour les tests (voir `.env`)
- Pour recevoir de vrais e-mails :
  - Modifier le service mail dans `.env` (voir [docs Laravel](https://laravel.com/docs/12.x/mail))
  - Support de Gmail, Mailgun, SendGrid, etc.

---

## Astuces de développement

- Les routes API sont protégées par `auth:sanctum` et les middlewares de rôles (`role:admin`)
- Pour tester les rôles, modifiez l’attribution dans la table `model_has_roles` ou via les seeders
- Pour ajouter d’autres fonctionnalités (OAuth, Google Captcha, etc.), suivez la doc Laravel et Vue.js

---

## Exemples d'utilisation de l'API

- `POST /api/register` : inscription utilisateur
- `POST /api/login` : connexion
- `GET /api/tickets` : liste des tickets
- `POST /api/tickets` : création ticket
- `PUT /api/tickets/{id}` : modification ticket
- `DELETE /api/tickets/{id}` : suppression ticket
- `GET /api/users` : liste des utilisateurs (admin)
- Voir `/routes/api.php` pour l’ensemble des routes

---


