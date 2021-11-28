# YSPI CLOUD RH V5 BACKEND API

YPSI CLOUD RH V5 BACKEND API  est l'api backend de l'application Ypsi cloud RH.
Il est écrit en PHP via SYMFONY 3.5.10

## Environnement de développement

### Pré-requis 
   * PHP 8.0.10
   * Composer 
   * Symfony CLI 
   * Docker 
   * Docker-compose
   * Api Platform


Vérification possible avec la commande CLI de Symfony (optionnel).

```bash
symfony check:requirements
```

### Lancer l'environnement de développement

```bash
composer install
docker-compose up -d 
symfony serve -d
```

### Connexion à la base de données docker

```bash
docker exec -it ypsicloudrh_database_5 mysql -u root -p
docker exec -it ypsicloudrh_database_5 bash
```

### Supprimer l'environnement de développement docker

docker-compose stop pour stop les conteneurs.
docker-compose down pour stopper et la desctrucion du conteneur.

```bash
docker-compose stop 
docker-compose down
```

### Lancer les tests 

**étape 1 : Lancer les fixtures sur le serveur de test
```bash
symfony console doctrine:fixture:load --env=test
```

**étape 2: Lancer tous les tests .
```bash
php bin\phpunit --testdox
```

**étape 3 : lancer un test en particulié
```bash
php bin\phpunit <le fichier test> --testdox
```
exemple de fichier test : "tests\Entity\UserEnityTest.php"

#### Lancer des commandes dans l'environnement de test

```bash
php bin\console --env=test + la commande
```

#### Pour reset la base de donnée à chaque test

```bash
composer require --dev dama/doctrine-test-bundle
```
voir la doc pour plus d'information car il faut ajouter des lignes en xml dans le fichier phpunit.xml.dist

