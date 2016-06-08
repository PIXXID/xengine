# Micro framework PHP #

## Installation ##

### Via composer ###

Ajouter la ligne suivante dans le fichier composer.json
```

{
    "require": {
        ...
        "pixxid/xengine": "1.*"
    }
}

```

### Manuellement ###

Installer le dossier `xengine/` dans `vendor/pixxid/xengine/`.

## Ligne de commande ##

```

$ cd vendor/pixxid/xengine
$ ./console/xengine init

```

Un lien symbolique vers le script `vendor/pixxid/xengine/console/xengine` est alors créé à la racine du projet

```

$ ./xengine [module|dao] options

```

### xengine init ###
Initialisation du projet

### xengine module [create|add|remove|redirect] moduleName (controllerName) ###

* `xengine module create moduleName` Création de l'arborescence du module 'moduleName'
* `xengine module add moduleName controllerName [controllerRedirect]` Ajoute le controller 'controllerName' au module 'moduleName'
* `xengine module remove moduleName controllerName` Supprime le controller 'controllerName' du module 'moduleName'
* `xengine module redirect moduleName` Définit le module 'moduleName' comme module par défaut dans le fichier public/index.php

### xengine dao generate [--all|modelName] [--business] [--dao] [--daocust] [--verbose] ###
* `xengine dao generate moduleName` Génère tous les DAO non générés ou bien seulement celui de 'modelName'
    - --all Tous les modèles, sans demande à l'utilisateur
    - --business Fichiers business
    - --dao Fichiers dao
    - --daocust Fichiers daoCust
    - --verbose Affiche le détail

### xengine theme add themeName ###
* `xengine theme add themeName` Génère le répertoire et les fichiers principaux du thème 'themeName'


### Autocomplétion ###

Le fichier `console/xengine.autocomplete` est disponible pour permettre l'autocomplétion des commandes.
