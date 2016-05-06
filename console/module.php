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
     * @param string $moduleName

     * @return bool
     */
    public function create($moduleName) {
        // On regarde si le module est un sous module
        $moduleName = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $moduleName);

        // On vérifie qu'un même module n'existe pas déjà
        if (!file_exists($this->modulesDir . $moduleName)) {
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
            if (mkdir($this->modulesDir . $moduleName, 0755, true)) {
                // Création du répertoire controllers/
                $controllersDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
                if (!mkdir($controllersDir, 0755, true)) {
                    echo helper::warning("Impossible de créer le répertoire {$controllersDir} !\r\n");
                    return false;
                }

                // Création du répertoire views/
                $viewsDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
                if (!mkdir($viewsDir, 0755, true)) {
                    echo helper::warning("Impossible de créer le répertoire {$viewsDir} !\r\n");
                    return false;
                }

                // Création du fichier index.php
                if (file_put_contents($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'index.php', $this->getIndexFile($moduleName)) !== false) {
                    // Création du fichier route.xml
                    if (file_put_contents($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'route.xml', $this->getRouteFile($moduleName)) !== false) {
                        // Création du controller de base
                        if (file_put_contents($controllersDir . "home.controller.php", $this->getControllerFile("home")) !== false) {
                            // Création de la vue du controller de base
                            if (file_put_contents($viewsDir . "home.view.php", $this->getViewFile("view")) !== false) {
                                // Mise à jour du fichier config/router.php
                                if ($this->updateRouterFile($moduleName)) {
                                    // Mise à jour du fichier .htaccess
                                    if (file_put_contents($this->root . 'public' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . '.htaccess',
                                        "\r\nRewriteRule ^/{$moduleName}/(.*)$ /{$moduleName}/index.php [L]", FILE_APPEND)) {
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
                                    echo helper::warning("Impossible de mettre à jour le fichier public/vendor/.htaccess !\r\n");
                                    return false;
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
     * TODO
     * Suppression d'un module complet
     * @param string $moduleName
     *
     * @return bool
     */
    public function destroy($moduleName = null) {
        echo helper::info("Fonctionnalité non disponible !\r\n");
        return false;

        // On vérifie les paramètres
        if ($moduleName == null) {
            echo helper::module(false, 'destroy');
            return false;
        }

        // On regarde si le module existe
        if (file_exists($this->modulesDir . $moduleName)) {
            // On demande confirmation à l'utilisateur
            echo helper::warning("ATTENTION - Vous êtes sur le point de supprimer le module {$thisd->modulesDir}{$moduleName}.\r\n")
                . helper::warning("Entrez 'oui' pour confirmer.\r\n")
                . helper::info(">> ");
            $input = trim(fgets(STDIN));

            // On vérifie l'input fourni par l'utilisateur
            if ($input === 'oui') {
                // On supprime le répertoire du module
                // TODO recursive
                if (unlink($this->modulesDir . $moduleName)) {
                    // On indique qu'il faut mettre à jour le fichier config/route.php

                    echo helper::success("Le module {$this->modulesDir}{$moduleName} a bien été supprimé !\r\n");
                    echo helper::info("Pensez à modifier le fichier config/route.php en conséquence.\r\n");
                    return true;
                }

                echo helper::warning("Erreur lors de la suppression du répertoire {$this->modulesDir}{$moduleName} !\r\n");
                return false;
            }

            echo helper::info("Suppression annulée.\r\n");
            return false;
        }

        echo helper::warning("Le module {$this->modulesDir}{$moduleName} n'existe pas !\r\n");
        return false;
    }

    /**
     * Ajoute un controller au module
     * @param string $moduleName
     * @param string $controllerName
     *
     * @return bool
     */
    public function add($moduleName = null, $controllerName = null) {
        // On vérifie les paramètres
        if ($moduleName == null || $controllerName == null) {
            echo helper::module(false, 'add');
            return false;
        }

        // Répertoires des controllers et des vues
        $controllersDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
        $viewsDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

        // On vérifie que le module existe
        if (file_exists($this->modulesDir . $moduleName)) {
            // On vérifie qu'un controller du même nom n'existe pas déjà
            if (!file_exists($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'controllers'
                            . DIRECTORY_SEPARATOR . $controllerName . '.controller.php')) {
                // On crée le controller controllers/*.controller.php
                if (file_put_contents($controllersDir . "{$controllerName}.controller.php", $this->getControllerFile($controllerName)) !== false) {
                    // On crée la vue views/*.view.php
                    if (file_put_contents($viewsDir . "{$controllerName}.view.php", $this->getViewFile("view")) !== false) {
                        // On met à jour le fichier route.xml
                        if ($this->updateRouteFile($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'route.xml', $controllerName)) {
                            echo helper::success("Le controller {$controllerName} a été crée !\r\n");
                            return true;
                        }

                        echo helper::warning("Impossible de mettre à jour le fichier {$this->modulesDir}{$moduleName}/route.xml !\r\n");
                        return false;
                    }

                    echo helper::warning("Impossible de créer le fichier {$viewsDir}{$controllerName}.view.php !\r\n");
                    return false;
                }

                echo helper::warning("Impossible de créer le fichier {$controllersDir}{$controllerName}.controller.php !\r\n");
                return false;
            }

            echo helper::warning("Le controller {$moduleName}/controllers/{$controllerName}.controller.php existe déjà !\r\n");
            return false;
        }

        echo helper::warning("Le module {$moduleName} n'existe pas !\r\n");
        return false;
    }

    /**
     * Supprime un controller du module
     * @param string $moduleName
     * @param string $controllerName
     *
     * @return bool
     */
    public function remove($moduleName = null, $controllerName = null) {
        // On vérifie les paramètres
        if ($moduleName == null || $controllerName == null) {
            echo helper::module(false, 'remove');
            return false;
        }

        // Répertoires des controllers et des vues
        $controllersDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
        $viewsDir = $this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;

        // On vérifie que le controller existe
        if (file_exists($controllersDir . "{$controllerName}.controller.php") !== false) {
            // On demande confirmation à l'utilisateur
            echo helper::warning("ATTENTION - Vous êtes sur le point de supprimer le controller {$controllersDir}{$controllerName}.controller.php.\r\n")
                . helper::warning("Entrez 'oui' pour confirmer.\r\n")
                . helper::info(">> ");
            $input = trim(fgets(STDIN));

            // On vérifie l'input fourni par l'utilisateur
            if ($input === 'oui') {
                // Supprime le fichier *.controller.php
                if (unlink($controllersDir . "{$controllerName}.controller.php")) {
                    // On supprime la vue associée si elle existe
                    if (file_exists($viewsDir . "{$controllerName}.view.php") !== false) {
                        if (!unlink($viewsDir . "{$controllerName}.view.php")) {
                            echo helper::warning("Impossible de supprimer le fichier {$viewsDir}{$controllerName}.view.php !\r\n");
                            return false;
                        }
                    }

                    // On met à jour le fichier route.xml
                    if ($this->updateRouteFile($this->modulesDir . $moduleName . DIRECTORY_SEPARATOR . 'route.xml', $controllerName, false)) {
                        echo helper::success("Le controller a bien été supprimé.\r\n");
                        return true;
                    }

                    echo helper::warning("Impossible de mettre à jour le fichier {$this->modulesDir}{$moduleName}/route.xml !\r\n");
                    return false;
                }

                echo helper::warning("Impossible de supprimer le fichier {$controllersDir}{$controllerName}.controller.php !\r\n");
                return false;
            }

            echo helper::info("Suppression annulée.\r\n");
            return false;
        }

        echo helper::warning("Le controller {$controllersDir}{$controllerName}.controller.php n'existe pas !\r\n");
        return false;
    }

    /**
     * Définit le module par défaut vers lequel redirige /public/index.php
     * @param string $moduleName
     *
     * @return bool
     */
    public function redirect($moduleName) {
        // On vérifie que le module existe
        if (file_exists($this->modulesDir . $moduleName)) {
            if (file_put_contents($this->modulesDir . 'index.php',
                "<?php\r\n// Redirection sur le module par défaut\r\nheader(\"Location: /{$moduleName}/\");\r\nexit();") !== false) {
                echo helper::success("Le module {$moduleName} a été défini comme module par défaut dans le fichier index.php.\r\n");
                return true;
            }

            echo helper::warning("Impossible d'écrire le fichier index.php !\r\n");
            return false;
        }

        echo helper::warning("Le module {$this->modulesDir}{$moduleName} n'existe pas !");
        return false;
    }

    /**
     * __toString retourne l'aide du module
     */
    public function __toString() {
        return helper::module();
    }

    /*
     * Retourne le template du fichier index.php
     * @param string $moduleName
     * @return string
     */
    public function getIndexFile($moduleName) {
        $date = date('d/m/Y');

        // On calcule de combien de répertoires le fichier index.php doit remonter pour inclure le fichier index.php de
        // xengine
        $depth = '.' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        for ($i = 0; $i < substr_count($moduleName, DIRECTORY_SEPARATOR); $i++) {
            $depth .= '..' . DIRECTORY_SEPARATOR;
        }

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
include('{$depth}vendor/pixxid/xengine/index.php');

EOF;

        return $str;
    }

    /*
     * Retourne le template du fichier route.xml
     * @return string
     */
    public function getRouteFile($moduleName) {
        // On remplace common/login par common.login (par exemple)
        $moduleNameConfig = str_replace(DIRECTORY_SEPARATOR, '.', $moduleName);

        $str = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<document>
    <config name="$moduleNameConfig" version="1" debug="false" router="router" database="database">
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
 * @name      $controllerName.action.php
 *
 * @copyright x.x. $date
 * @licence   /LICENCE.txt
 *
 * @since     1.0
 *
 * @author    x.x. <xx@pixxid.fr>
 */


use \\xEngine\DataCenter;

class $controllerName
{
    public function execute(DataCenter \$_DC)
    {
        try {
        } catch (\\Exception \$e) {
            \$_DC->addMessageError(\$e->getMessage());
        }

        return;
    }
}
EOF;

        return $str;
    }

    /**
     * Retourne le template de la vue d'un controller
     * @param string $viewName

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

    /**
     * Mise à jour du fichier route.xml
     * @param string $routeFile
     * @param string $controllerName
     * @param bool $add
     *
     * @return bool
     */
    public function updateRouteFile($routeFile, $controllerName, $add = true) {
        // On charge le fichier xml
        if (($xml = simplexml_load_file($routeFile)) !== false) {
            // Si c'est un ajout
            if ($add) {
                // On ajoute le controller
                $child = $xml->controllers->addChild('controller');

                // On positionne les attributs
                $child->addAttribute('name', $controllerName);
                $child->addAttribute('lib', "Controller pour {$controllerName}");
            // Sinon c'est une suppression
            } else {
                $index = 0;
                // On boucle sur les $controllers jusqu'à trouver celui qui correspond
                foreach ($xml->controllers->controller as $controller) {
                    // On le supprime
                    if ((string)$controller['name'] === $controllerName) {
                        unset($xml->controllers->controller[$index]);
                        break;
                    }

                    $index++;
                }
            }

            // On enregistre
            if ($xml->asXML($routeFile) !== false) {
                return true;
            }

            echo helper::warning("Impossible d'enregistrer le fichier {$routeFile} !\r\n");
            return false;
        }

        echo helper::warning("Impossible de lire le fichier {$routeFile} !\r\n");
        return false;
    }
}

