<?php
/**
 * Gestion des thèmes
 */

namespace xEngine\Console;

require_once(__DIR__ . '/helper.php');

class theme {

    /*
     * Racine du projet
     * @var string
     */
    private $root;

    /*
     * Dossier des thèmes
     * @var string
     */
    private $themesDir;

    /**
     * Constructeur
     */
    public function __construct() {
        $this->root = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR;
        $this->themesDir = $this->root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    }

    /**
     * Ajoute un nouveau theme
     * @param string $themeName
     *
     * @return bool
     */
    public function add($themeName = null) {
        if ($themeName === null) {
            echo helper::theme(false, 'add');
            return false;
        }

        $themeDir = $this->themesDir . $themeName . DIRECTORY_SEPARATOR;
        // On vérifie que le thème n'existe pas déjà
        if (!file_exists($themeDir)) {
            // On crée le dossier du thème
            if (mkdir($themeDir, 0755, true)) {
                // On crée le fichier js par défaut
                if (file_put_contents($themeDir . 'default.js', $this->getDefaultJS($themeName))) {
                    // On crée le fichier css par défaut
                    if (file_put_contents($themeDir . 'default.css', $this->getDefaultCSS($themeName))) {
                        // On crée le répertoire des images
                        if (mkdir($themeDir . 'imgs', 0755, true)) {
                            echo helper::success("Le thème {$themeName} a été initialisé !\r\n");
                            echo helper::success("L'arborescence suivante a été créée :\r\n");
                            echo helper::info("-- public
    -- assets/
        -- {$themeName}/
            -- imgs/
            default.css
            default.js
");
                            return true;
                        }

                        echo helper::warning("Impossible de créer le répertoire {$themeDir}imgs !\r\n");
                        return false;
                    }

                    echo helper::warning("Impossible de créer le fichier default.css !\r\n");
                    return false;
                }

                echo helper::warning("Impossible de créer le fichier default.js !\r\n");
                return false;
            }

            echo helper::warning("Impossible de créer le répertoire {$themeDir} !\r\n");
            return false;
        }

        echo helper::warning("Un thème du nom {$themeName} existe déjà !\r\n");
        return false;
    }

    /**
     * Retourne le template du fichier default.js
     * @param string $themeName
     *
     * @return string
     */
    public function getDefaultJS($themeName) {
        $content = <<<EOF
/**
 * Fichier js par défaut du thème {$themeName}
 * @name      default.js
 *
 * @copyright PIXXID $date
 *
 * @author    x.x. <xx@pixxid.fr>
 */

EOF;

        return $content;
    }


    /**
     * Retourne le template du fichier default.css
     * @param string $themeName
     *
     * @return string
     */
    public function getDefaultCSS($themeName) {
        $content = <<<EOF
/**
 * Fichier css par défaut du thème {$themeName}
 * @name      default.css
 *
 * @copyright PIXXID $date
 *
 * @author    x.x. <xx@pixxid.fr>
 */
@charset "UTF-8";

EOF;

        return $content;
    }

    /**
     * __toString retourne l'aide du module
     */
    public function __toString() {
        return helper::theme();
    }

}
