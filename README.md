# Projet8_ToDoList
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/8b3cbcf967c14764a14d996d2f0b9215)](https://www.codacy.com/manual/AurelBichop/Projet8_toDoList?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=AurelBichop/Projet8_toDoList&amp;utm_campaign=Badge_Grade)

Bichotte Aurélien

Amélioration d'une application web existante. Ce projet a été créé dans l'intérêt de se former au développement en php avec le framework Symfony, il fait parti du projet 8 de la formation Développeur d'application - PHP / Symfony d'openclassroom. [Formation open classroom](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony)

Le projet est actuellement en phase de test.

### Installation de l'application ###

Installer tous les fichiers sur le serveur en utilisant la commande :

**git clone https://github.com/AurelBichop/Projet8_toDoList**

### Configuration de l'application ###
Renseigner la base de données dans le fichier .env

### Mise en place des migrations ###
    php bin/console doctrine:migrations:migrate

### Mise en place des fixtures ###
    php bin/console doctrine:fixtures:load

### Administrateur par défault
login : **admin**
 
password : **passwordAdmin**

*pour des raisons de sécurités, penser à supprimer ce dernier dés lors que vous vous ête attribué les droits administrateur*
### Pour lancer les tests avec PhpUnit: ### 
    php bin/phpunit

*Avec un Coverage*

    php bin/phpunit --coverage-html <destination>

### Les Bundles utilisés pour cette application ###
    fzaninotto/faker
    PhpUnit 7.5


### Pour un hébergement avec OVH ###
Ajouter un .htaccess dans public/

```
===================================================

SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L]

==================================================
```

Technologie utilisée : Symfony 5.0.7, MYSQL v.5.6 ou MariaDB-10.4.6, langage PHP 7.3.2