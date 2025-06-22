# Mon Premier Blog en PHP

Ce projet est un blog simple développé en PHP. Il utilise Composer pour la gestion des dépendances et dotenv pour la gestion des variables d'environnement. Un Makefile est également fourni pour simplifier les tâches courantes.

## Table des Matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Fonctionnalités](#fonctionnalités)
- [Configuration](#configuration)
- [Commandes Makefile](#commandes-makefile)
- [Contribution](#contribution)
- [Contact](#contact)

## Installation

Pour installer le projet, suivez ces étapes :

1. Clonez le dépôt :

   ```bash
   git clone https://github.com/thomaschagneux/mon-premier-blog-en-php.git
   cd mon-premier-blog-en-php
   ```

2. Installez les dépendances via Composer :

   ```bash
   make install
   ```

3. Copiez le fichier `.env.example` en `.env` et configurez les variables d'environnement nécessaires :

   ```bash
   cp .env.example .env
   ```

## Utilisation

Pour démarrer le projet, vous pouvez utiliser le serveur de développement PHP intégré :


   ```bash
   make start
   ```

Ensuite, ouvrez votre navigateur et accédez à http://localhost:8000.

## Fonctionnalités

Création et gestion des articles de blog
Affichage des articles avec pagination
Système de commentaires
Authentification utilisateur

## Configuration

Le fichier .env contient les variables d'environnement nécessaires pour configurer le projet. Voici un exemple de configuration :

APP_URL=http://localhost:8000

DB_HOST=127.0.0.1 

DB_NAME=first_blog

DB_USER=first_blog

DB_PASS=first_blog

## Commandes Makefile

Le projet inclut un Makefile pour simplifier les tâches courantes. Voici les commandes disponibles :


make help : Afficher la liste des commandes make.

## Contribution

Les contributions sont les bienvenues ! Pour contribuer, suivez ces étapes :

Forker le projet
Créer une nouvelle branche pour vos modifications
Committer vos modifications
Pousser vos modifications vers votre fork
Ouvrir une Pull Request

## Contact

Thomas Chagneux - thomaschagneux@gmail.com

Projet - https://github.com/thomaschagneux/mon-premier-blog-en-php
