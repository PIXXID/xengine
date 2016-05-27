<?php

/**
 * Initialisation d'un projet.
 * Création de l'arborescence nécessaire :
 * -- config/
 *      -- database.php
 *      -- router.php
 * -- logs/
 * -- public/
 *      -- vendor/
 *      -- robots.txt
 *      -- .htacess
 * -- ressources/
 *      -- assets/
 *          -- less/
 *          -- js/
 *          -- sass/
 *      -- locale/
 *          -- fr_FR/
 *      -- models/
 *
 */

namespace xEngine\Console;

require_once(__DIR__ . '/helper.php');

class init {

    public function execute() {
        // Racine du projet
        $root = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR;
        $msg = '';
        $exists = false;

        // Vérifie d'abord que les dossiers/fichiers n'existent pas déjà
        if (file_exists($root . 'config/')) {
            $msg .= helper::warning("Le répertoire 'config/' existe, risque d'écrasement de fichiers existants.\r\n");
            $exists = true;
        }

        if (file_exists($root . 'public/')) {
            $msg .= helper::warning("Le répertoire 'public/' existe, risque d'écrasement de fichiers existants.\r\n");
            $exists = true;
        }

        if (file_exists($root . 'ressources/')) {
            $msg .= helper::warning("Le répertoire 'ressources/' existe, risque d'écrasement de fichiers existants.\r\n");
            $exists = true;
        }

        if (file_exists($root . 'logs/')) {
            $msg .= helper::warning("Le répertoire 'logs/' existe, risque d'écrasement de fichiers existants.\r\n");
            $exists = true;
        }

        if (file_exists($root . 'xengine')) {
            $msg .= helper::warning("Le lien symbolique 'xengine' existe, risque d'écrasement de fichiers existants.\r\n");
            $exists = true;
        }

        // Si les dossiers existaient déjà, on s'arrête
        if ($exists) {
            $msg = helper::error("Une erreur est survenue !\r\n") . $msg;
            echo $msg;

            return -1;
        }

        // Génération de l'arborescence
        if ($this->generateTree($root)) {
            echo helper::success("Initialisation terminée !\r\n");
            echo helper::success("L'arborescence suivante a été créée :");
            echo helper::info("
-- config/
     -- database.php
     -- router.php
-- logs/
-- public/
     -- assets/
     -- vendor/
     -- robots.txt
     -- .htacess
     -- index.php
-- ressources/
     -- assets/
         -- less/
         -- sass/
         -- js/
     -- locale/
         -- fr_FR/
            -- LC_MESSAGES/
     -- models/
-- .projections.json\r\n");
            echo helper::success("Un lien symbolique a été créé vers le binaire vendor/pixxid/xengine/console/xengine dans la racine de votre projet.\r\n");

            return true;
        }

        return false;

    }

    /**
     * Génération des différents répertoires
     *
     * @return bool
     */
    public function generateTree($root) {
        if ($this->generateConfig($root)) {
            if ($this->generateLogs($root)) {
                if ($this->generatePublic($root)) {
                    if ($this->generateRessources($root)) {
                        if ($this->generateSymLink($root)) {
                            return $this->generateProjectionsFile($root);
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Création du répertoire config/ et de ses fichiers
     *
     * @return bool
     */
    public function generateConfig($root) {
        if (mkdir($root . 'config', 0755)) {
            // Création du fichier database.php
            if (file_put_contents($root . 'config' . DIRECTORY_SEPARATOR . 'database.php', $this->getDatabaseFile()) !== false) {
                // Création du fichier router.php
                if (file_put_contents($root . 'config' . DIRECTORY_SEPARATOR . 'router.php', $this->getRouterFile()) !== false) {
                    return true;
                }

                echo helper::warning("Impossible de créer le fichier config/router.php !\r\n");
                return false;
            }

            echo helper::warning("Impossible de créer le fichier config/database.php !\r\n");
            return false;

        }

        echo helper::warning("Impossible de créer le répertoire config !\r\n");
        return false;
    }

    /**
     * Génération du répertoire logs/
     * @return bool
     */
    public function generateLogs($root) {
        if (mkdir($root . 'logs', 0777)) {
            return true;
        }

        echo helper::warning("Impossible de créer le répertoire logs/ !\r\n");
        return false;
    }

    /**
     * Génération du répertoire public/
     * @return bool
     */
    public function generatePublic($root) {
        // Création du répertoire public/
        if (mkdir($root . 'public', 0755)) {

            // Création du répertoire public/vendor
            if (!mkdir($root . 'public' . DIRECTORY_SEPARATOR . 'assets', 0755)) {
                echo helper::warning("Impossible de créer le répertoire public/assets/ !\r\n");
                return false;
            }

            // Création du répertoire public/vendor
            if (mkdir($root . 'public' . DIRECTORY_SEPARATOR . 'vendor', 0755)) {
                // Création du fichier public/robots.txt
                if (file_put_contents($root . 'public' . DIRECTORY_SEPARATOR . 'robots.txt',
                    "User-agent: *\r\nDisallow:") !== false) {
                    // Création du fichier public/.htaccess
                    if (file_put_contents($root . 'public' . DIRECTORY_SEPARATOR . '.htaccess',
                        "Options +FollowSymLinks\r\nOptions -Indexes\r\nRewriteEngine On\r\nRewriteRule ^/admin/(.*)$ /admin/index.php [L]") !== false) {
                        // Création du fichier public/index.php
                        if (file_put_contents($root . 'public' . DIRECTORY_SEPARATOR . 'index.php',
                            "<?php\r\n// Redirection sur le module par défaut\r\nheader(\"Location: /path/to/module/\");\r\nexit();") !== false) {
                            return true;
                        }

                        echo helper::warning("Impossible de générer le fichier public/index.php !\r\n");
                        return false;
                    }

                    echo helper::warning("Impossible de créer le fichier public/.htaccess !\r\n");
                    return false;

                }

                echo helper::warning("Impossible de créer le fichier public/robots.txt !\r\n");
                return false;

            }

            echo helper::warning("Impossible de créer le répertoire public/vendor/ !\r\n");
            return false;
        }

        echo helper::warning("Impossible de créer le répertoire public/ !\r\n");
        return false;
    }

    /**
     * Génération du répertoire ressources/
     * @return bool
     */
    public function generateRessources($root) {
        // Création du répertoire ressources/
        if (mkdir($root . 'ressources', 0755)) {
            // Création du répertoires ressources/assets
            if (mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'assets', 0755)) {
                // Création du répertoire ressources/assets/less/
                if (!mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'less', 0755)) {
                    echo helper::warning("Impossible de créer le répertoire ressources/assets/less/ !\r\n");
                    return false;
                }

                // Création du répertoire ressources/assets/js/
                if (!mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js', 0755)) {
                    echo helper::warning("Impossible de créer le répertoire ressources/assets/js/ !\r\n");
                    return false;
                }
                // Création du répertoire ressources/assets/sass/
                if (!mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'sass', 0755)) {
                    echo helper::warning("Impossible de créer le répertoire ressources/assets/sass/ !\r\n");
                    return false;
                }

                // Création du répertoire ressources/locale/
                if (mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'locale', 0755)) {
                    // Création du répertoire ressources/locale/fr_FR
                    if (mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . 'fr_FR', 0755)) {
                        // Création du répertoire ressources/models
                        if (mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . 'fr_FR' . DIRECTORY_SEPARATOR . 'LC_MESSAGES', 0755)) {
                            // Création du répertoire ressources/models
                            if (mkdir($root . 'ressources' . DIRECTORY_SEPARATOR . 'models', 0755)) {
                                return true;
                            }

                            echo helper::warning("Impossible de créer le répertoire ressources/models/ !\r\n");
                            return false;
                        }

                        echo helper::warning("Impossible de créer le répertoire ressources/locale/fr_FR/LC_MESSAGES !\r\n");
                        return false;
                    }

                    echo helper::warning("Impossible de créer le répertoire ressources/locale/fr_FR/ !\r\n");
                    return false;
                }

                echo helper::warning("Impossible de créer le répertoire ressources/locale/ !\r\n");
                return false;
            }

            echo helper::warning("Impossible de créer le répertoire ressources/assets/ !\r\n");
            return false;
        }

        echo helper::warning("Impossible de créer le répertoire ressources/ !\r\n");
        return false;
    }

    /**
     * Retourne le template du fichier config/database.php
     */
    public function getDatabaseFile() {
        $str = <<<EOF
<?php
/*
  |--------------------------------------------------------------------------
  | Configuration de l'accès aux bases de données
  |--------------------------------------------------------------------------
  | L'accès à la base de données se fait via PHP PDO.
  |
  | Paramètres supportés : "driver", "host", "port", "database", "username",
  |            "password", "charset", "auto-connect"
  |
 */
return [
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'database',
            'username' => 'user',
            'password' => 'password',
            'charset' => 'UTF8',
            'auto-connect' => true
        ]
    ]
];
EOF;

        return $str;
    }

    /**
     * Retourne le tempalte du fichier config/router.php
     *
     * @return string
     */
    public function getRouterFile() {
        $str = <<<EOF
<?php
/*
  |--------------------------------------------------------------------------
  | Configuration des routes aux modules de l'application
  |--------------------------------------------------------------------------
  | Liste des routes à utiliser pour la gestion des URL de l'ensemble des
  | modules de votre application.
  | Un fichier de cache au format JSON sera généré automatiquement afin
  | d'améliorer les permformances de navigation inter-modules.
  |
  | À savoir :
  | Si votre application est mono-module, vous pouvez désactiver l'utilisation
  | du routage dans le fichier route.xml <config router="false">
  | L'appel du controller se fera alors par l'URL.
  | Ex : http://monappli.com/?controller=home
  |
 */
return [
    'routes' => [
        'admin' => [
            'path' => '/admin/',
            'cache_name' => 'router.cache.json'
        ]
    ]
];
EOF;
        return $str;
    }

    /**
     * __toString retourne l'aide du module
     *
     * @return string
     */
    public function __toString() {
        return helper::init();
    }

    /**
     * Génère le lien symbolique vers le binaire xengine
     * @param string $root
     *
     * @return bool
     */
    public function generateSymLink($root) {
        $target = $root . 'vendor' . DIRECTORY_SEPARATOR . 'pixxid' . DIRECTORY_SEPARATOR . 'xengine' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . 'xengine';
        $link = $root . 'xengine';

        return symlink($target, $link);
    }

    /**
     * Génération du fichier .projections.json pour vim
     * @param string $root
     *
     * @return bool
     */
    public function generateProjectionsFile($root) {
        $str = <<<EOF
{
    "*.controller.php": {
        "type": "action",
        "template": [
            "<?php",
            "",
            "/**",
            " *",
            " *",
            " * @copyright dd/mm/yyyy, Pixxid",
            " * @licence   /LICENCE",
            " *",
            " * @link      TODO",
            " * @since     V1",
            " *",
            " * @author    J.B. <jeremie@pixxid.fr>",
            " */",
            "",
            "use \\\\xEngine\\\\DataCenter;",
            "use \\\\xEngine\\\\Exception\\\\Exception_;",
            "",
            "class {basename}",
            "{",
            "    public function execute(DataCenter \$_DC)",
            "    {",
            "        try {",
            "        } catch (Exception_ \$e) {",
            "            \$_DC->addMessageError(\$e->getMessage());",
            "        }",
            "",
            "        return;",
            "    }",
            "}"
        ],
        "alternate": "{dirname}/../views/{basename}.view.php"
     },
     "*.view.php": {
         "type": "template",
         "alternate": "{dirname}/../controllers/{basename}.controller.php"
     }
}
EOF;

        return file_put_contents($root . '.projections.json', $str);
    }

}
