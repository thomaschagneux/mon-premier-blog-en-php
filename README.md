# Mon Premier Blog en PHP

Ce projet est un blog simple développé en PHP. Il utilise Composer pour la gestion des dépendances et dotenv pour la gestion des variables d'environnement. Un Makefile est également fourni pour simplifier les tâches courantes.

## Table des Matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Fonctionnalités](#fonctionnalités)
- [Configuration](#configuration)
- [Commandes Makefile](#commandes-makefile)
- [Contribution](#contribution)
- [Licence](#licence)
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

APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mon_premier_blog
DB_USERNAME=root
DB_PASSWORD=

## Commandes Makefile

Le projet inclut un Makefile pour simplifier les tâches courantes. Voici les commandes disponibles :

make install : Installer les dépendances du projet.
make start : Démarrer le serveur de développement PHP.
make clean : Nettoyer les fichiers générés.
make help : Afficher ce message d'aide.
make test : Analyser le code avec phpStan.
make insert_data : Insérer des données dans la base de données.
make create_db : Créer la base de données.
make create_tables : Créer les tables de la base de données.
make drop_db : Supprimer la base de données.
make reset : Réinitialiser la base de données (supprimer, créer, insérer des données).

## Contribution

Les contributions sont les bienvenues ! Pour contribuer, suivez ces étapes :

Forker le projet
Créer une nouvelle branche pour vos modifications
Committer vos modifications
Pousser vos modifications vers votre fork
Ouvrir une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Contact

Thomas Chagneux - thomaschagneux@gmail.com

Projet - https://github.com/thomaschagneux/mon-premier-blog-en-php
