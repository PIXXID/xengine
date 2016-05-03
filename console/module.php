<?php
/**
 * Gestion des modules (création, suppression, ajout d'action...)
 */

namespace xEngine\Console;

require_once(__DIR__ . '/helper.php');

class module {

    /*
     * Racine du projet
     * @var string
     */
    private $root;

    /*
     * Répertoire des modules
     * @var string
     */
    private $modulesDir;

    /**
     * Constructeur
     */
    public function __construct() {
        $this->root = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR;
        $this->modulesDir = $this->root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    /**
     * Création d'un nouveau module
     * @return bool
     */
    public function create($moduleName) {
        // On vérifie qu'un même module n'existe pas déjà
        if (!file_exists($this->moduleDir . $moduleName)) {
            /*
             * On crée l'arborescence :
             * -- public/
             *    -- moduleName/
             *       -- controllers/
             *       -- views/
             *       index.php
             *       route.xml
             */

            // Création du répertoire du module
            if (mkdir($this->modulesDir . $moduleName, 0755)) {
                // Création du répertoire controllers/
                $controllersDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
                if (!mkdir($controllersDir, 0755)) {
                    echo helper::warning("Impossible de créer le répertoire {$controllersDir} !\r\n");
                    return false;
                }

                // Création du répertoire views/
                $viewsDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
                if (!mkdir($viewsDir, 0755)) {
                    echo helper::warning("Impossible de créer le répertoire {$viewsDir} !\r\n");
                    return false;
                }

                // Création du fichier index.php
                if (file_put_contents($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'index.php', $this->getIndexFile()) !== false) {
                    // Création du fichier route.xml
                    if (file_put_contents($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'route.xml', $this->getRouteFile($moduleName)) !== false) {
                        // Création du controller de base
                        if (file_put_contents($controllersDir . "home.controller.php", $this->getControllerFile("home")) !== false) {
                            // Création de la vue du controller de base
                            if (file_put_contents($viewsDir . "home.view.php", $this->getViewFile("view")) !== false) {
                                // Mise à jour du fichier config/router.php
                                if ($this->updateRouterFile($moduleName)) {
                                    echo helper::success("Le module {$moduleName} a été initialisé !\r\n");
                                    echo helper::success("L'arborescence suivante a été créée :\r\n");
                                    echo helper::info("-- public/
    -- {$moduleName}/
       -- controllers/
       -- views/
       index.php
       route.xml
");
                                    return true;
                                }

                                echo helper::warning("Impossible de mettre à jour le fichier config/router.php !\r\n");
                                return false;
                            }

                            echo helper::warning("Impossible de créer le fichier {$viewsDir}home.view.php !\r\n");
                            return false;
                        }

                        echo helper::warning("Impossible de créer le fichier {$controllersDir}home.controller.php !\r\n");
                        return false;
                    }

                    echo helper::warning("Impossible de créer le fichier {$this->modulesDir}{$moduleName}{DIRECTORY_SEPARATOR}route.xml !\r\n");
                    return false;
                }

                echo helper::warning("Impossible de créer le fichier {$this->modulesDir}{$moduleName}{DIRECTORY_SEPARATOR}index.php !\r\n");
                return false;
            }

            echo helper::warning("Impossible de créer le répertoire {$this->modulesDir}{$moduleName} !\r\n");
            return false;
        }

        echo helper::warning("Un module du nom {$moduleName} existe déjà !\r\n");
        return false;
    }

    /**
     * @return bool
     */
    public function destroy() {
    }

    /**
     * @return bool
     */
    public function add() {
    }

    /**
     * @return bool
     */
    public function remove() {
    }

    /**
     * __toString retourne l'aide du module
     */
    public function __toString() {
        return helper::module();
    }

    /*
     * Retourne le template du fichier index.php
     * @return string
     */
    public function getIndexFile() {
        $date = date('d/m/Y');
        $str = <<<EOF
<?php
/**
 * Controlleur générique
 *
 * @name      index.php
 * @copyright x.x. $date
 * @licence   /LICENCE.txt
 * @since     1.0
 * @author    x.x. <xx@pixxid.fr>
 */
include('./../../vendor/pixxid/xengine/index.php');

EOF;

        return $str;
    }

    /*
     * Retourne le template du fichier route.xml
     * @return string
     */
    public function getRouteFile($moduleName) {
        $str = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<document>
    <config name="$moduleName" version="1" debug="false" router="router" database="database">
        <controllers path="/$moduleName/controllers/" default="home" />
        <views path="/$moduleName/views/" />
        <themes name="default" path="/themes/" />
        <language active="true" domain="common" default="fr_FR" encoding="UTF-8" />
    </config>
    <controllers>
        <controller name="home" lib="Page d'accueil du module" />
    </controllers>
    <properties>
        <!--property name="" value="" /-->
    </properties>
</document>
EOF;

        return $str;
    }

    /**
     * Retourne le template d'un controller
     * @param string $controllerName

     * @return string
     */
    public function getControllerFile($controllerName) {
        $date = date('d/m/Y');
        $str = <<<EOF
<?php

/**
 *
 *
 * @name      $controllerName.action.php
 *
 * @copyright x.x. $date
 * @licence   /LICENCE.txt
 *
 * @since     1.0
 *
 * @author    x.x. <xx@pixxid.fr>
 */


use xEngine\DataCenter;

class $controllerName
{
    public function execute(DataCenter \$_DC)
    {
        try {
        } catch (\\Exception \$e) {
        }

        return;
    }
}
EOF;

        return $str;
    }

    /**
     * Retourne le template d'un controller
     * @param string $controllerName

     * @return string
     */
    public function getViewFile($viewName) {
        $str = <<<EOF
<!-- Vue $viewName -->
EOF;

        return $str;
    }

    /**
     * Mise à jour du fichier config/router.php
     * @param string $moduleName
     *
     * @return bool
     */
    public function updateRouterFile($moduleName) {
        // On lit le contenu du fichier, chaque ligne un élément de tableau
        if (($lines = file($this->root . 'config/router.php')) !== false) {
            // Contenu qui va être ajouté
            $newLines = <<<EOF
        '$moduleName' => [
            'path' => '/$moduleName/',
            'cache_name' => 'router.cache.json'
        ],

EOF;
            // On boucle sur les lignes pour trouver 'routes' => [
            foreach ($lines as $index => $line) {
                if (strpos($line, "'routes' => [") !== false) {
                    array_splice($lines, $index + 1, 0, [$newLines]);
                    break;
                }
            }

            $fileContent = implode($lines);

            // On ré écrit le fichier
            if (file_put_contents($this->root . 'config/router.php', $fileContent) !== false) {
                return true;
            }
        }

        return false;
    }
}

