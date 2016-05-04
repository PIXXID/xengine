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

        $msg .= helper::success("php xengine init")
             . helper::standard("  --  Initialisation du projet\r\n");

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
            $msg .= helper::success("php xengine module ")
                 . helper::warning("[create|destroy|add|remove] moduleName (controllerName)")
                 . "\r\n";
        }

        if ($section === null || $section === 'create') {
            $msg .= helper::warning("[create]\r\n")
                . helper::success("  php xengine module create moduleName ")
                . helper::standard("  --  Création de l'arborescence du module 'moduleName'")
                . "\r\n";
        }

        if ($section === null || $section === 'destroy') {
            $msg .= helper::warning("[destroy]\r\n")
                . helper::success("  php xengine module destroy moduleName ")
                . helper::standard("  --  Suppression du module 'moduleName'")
                . "\r\n";
        }

        if ($section === null || $section === 'add') {
            $msg .= helper::warning("[add]\r\n")
                . helper::success("  php xengine module add moduleName controllerName")
                . helper::standard("  --  Ajout de l'action 'controllerName' dans le module 'moduleName'")
                . "\r\n";
        }

        if ($section === null || $section === 'remove') {
            $msg .= helper::warning("[remove]\r\n")
                . helper::success("  php xengine module remove moduleName controllerName")
                . helper::standard("  --  Suppression de l'action 'controllerName' dans le module 'moduleName'")
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
            $msg .= helper::success("php xengine dao ")
                 . helper::warning("[generate|update] (modelName)")
                 . "\r\n";
        }

        if ($section === null || $section === 'generate') {
            $msg .= helper::warning("[generate]")
                 . helper::success("  php xengine generate (modelName)")
                 . helper::standard("  --  Génère tous les DAO non générés, ou bien seulement celui de 'modelName'")
                 . "\r\n";
        }

        if ($section === null || $section === 'update') {
            $msg .= helper::warning("[update]")
                 . helper::success("  php xengine update (modelName)")
                 . helper::standard("  --  Met à jour tous les DAO, ou bien seulement celui de 'modelName'")
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
    public function color($color, $str) {
        return "{$color}{$str}" . helper::colorEnd;
    }
}
