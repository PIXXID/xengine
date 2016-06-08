<?php
/**
 * Affichage des différents messages d'aide des modules
 *
 */

namespace xEngine\Console;

class helper {

    // Couleurs des messages
    const colorError = "\e[1;31m";
    const colorSuccess = "\e[1;32m";
    const colorWarning = "\e[1;33m";
    const colorStandard = "\e[37m";
    const colorInfo = "\e[1;34m";

    // Fin de mise en forme
    const colorEnd = "\e[0m";

    /**
     * Affichage de toutes les aides
     */
    public static function help() {
        $msg = helper::init(true);
        $msg .= helper::module(true);
        $msg .= helper::dao(true);

        return $msg;
    }

    /**
     * Aide de l'init
     * @param bool $full
     *
     * @return string
     */
    public static function init($full = false) {
        if ($full) {
            $msg = helper::info("[init]\r\n");
        } else {
            $msg = helper::success("Usage : ");
        }

        $msg .= helper::success("xengine init\r\n")
             . helper::standard("    Initialisation du projet\r\n");

        return $msg;
    }

    /**
     * Aide de module, pour la création de module
     * @param bool $full
     * @param string $section
     *
     * @return string
     */
    public static function module($full = false, $section = null) {
        if ($full) {
            $msg = helper::info("[module]\r\n");
        } else {
            $msg = helper::success("Usage : ");
        }

        if ($section === null) {
            $msg .= helper::success("xengine module ")
                 . helper::warning("[create|destroy|add|remove|redirect] moduleName (controllerName)")
                 . "\r\n";
        }

        if ($section === null || $section === 'create') {
            $msg .= helper::warning("[create]\r\n")
                . helper::success("  xengine module create moduleName\r\n")
                . helper::standard("    Création de l'arborescence du module 'moduleName'")
                . "\r\n";
        }

        if ($section === null || $section === 'destroy') {
            $msg .= helper::warning("[destroy]\r\n")
                . helper::warning("  Non implémentée\r\n")
                . helper::success("  xengine module destroy moduleName\r\n")
                . helper::standard("    Suppression du module 'moduleName'")
                . "\r\n";
        }

        if ($section === null || $section === 'add') {
            $msg .= helper::warning("[add]\r\n")
                . helper::success("  xengine module add moduleName controllerName [controllerRedirect]\r\n")
                . helper::standard("    Ajout de l'action 'controllerName' dans le module 'moduleName'\r\n")
                . helper::standard("    [controllerRedirect] controller vers lequel 'controllerName' redirige dans route.php")
                . "\r\n";
        }

        if ($section === null || $section === 'remove') {
            $msg .= helper::warning("[remove]\r\n")
                . helper::success("  xengine module remove moduleName controllerName\r\n")
                . helper::standard("    Suppression de l'action 'controllerName' dans le module 'moduleName'")
                . "\r\n";
        }

        if ($section === null || $section === 'redirect') {
            $msg .= helper::warning("[redirect]\r\n")
                . helper::success("  xengine module redirect moduleName\r\n")
                . helper::standard("    Définit le module 'moduleName' comme module par défaut dans le fichier public/index.php")
                . "\r\n";
        }

        return $msg;
    }

    /**
     * Aide du dao
     * @param bool $full
     * @param string $section
     *
     * @return string
     */
    public static function dao($full = true, $section = null) {
        if ($full) {
            $msg = helper::info("[dao]\r\n");
        } else {
            $msg = helper::success("Usage : ");
        }

        if ($section === null) {
            $msg .= helper::success("xengine dao ")
                 . helper::warning("[generate] (modelName)")
                 . "\r\n";
        }

        if ($section === null || $section === 'generate') {
            $msg .= helper::warning("[generate]")
                 . helper::success("  xengine generate [--all|modelName] [--business] [--dao] [--daocust] [--verbose]\r\n")
                 . helper::standard("  Génère tous les DAO non générés, ou bien seulement celui de 'modelName'\r\n")
                 . helper::standard("    [--all] Tous les modèles\r\n")
                 . helper::standard("    [modelName] Pour le modèle 'modelName'\r\n")
                 . helper::standard("    [--business] Fichiers business\r\n")
                 . helper::standard("    [--dao] Fichiers dao\r\n")
                 . helper::standard("    [--daocust] Fichiers daoCust\r\n")
                 . helper::standard("    [--verbose] Affiche le détail")
                 . "\r\n";
        }

        return $msg;
    }

    /**
     * Aide du theme
     * @param bool $full
     * @param string|null $section

     * @return string
     */
    public static function theme($full = true, $section = null) {
        if ($full) {
            $msg = helper::info("[theme]\r\n");
        } else {
            $msg = helper::success("Usage : ");
        }

        if ($section === null) {
            $msg .= helper::success("xengine theme ")
                 . helper::warning("[add] (themeName)")
                 . "\r\n";
        }

        if ($section === null || $section === 'add') {
            $msg .= helper::warning("[add]")
                 . helper::success("  xengine theme add\r\n")
                 . helper::standard("  Crée le dossier pour le thème 'themeName'")
                 . "\r\n";
        }

        return $msg;
    }

    // Gestion de la mise en forme

    /**
     * Message d'erreur
     */
    public static function error($str) {
        return helper::color(helper::colorError, $str);
    }

    /**
     * Message info
     */
    public static function info($str) {
        return helper::color(helper::colorInfo, $str);
    }

    /**
     * Message standard
     */
    public static function standard($str) {
        return helper::color(helper::colorStandard, $str);
    }

    /**
     * Message d'information importante
     */
    public static function warning($str) {
        return helper::color(helper::colorWarning, $str);
    }

    /**
     * Message de succès
     */
    public static function success($str) {
        return helper::color(helper::colorSuccess, $str);
    }

    /**
     * Positionne la mise en forme
     */
    public static function color($color, $str) {
        return "{$color}{$str}" . helper::colorEnd;
    }
}
