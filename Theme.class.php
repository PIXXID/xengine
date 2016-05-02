<?php

/**
 * Gestion des thèmes
 *
 * @name        theme
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

class Theme {

    /**
     * Nom du theme
     * @access private
     * @var string
     */
    private $name = null;

    /**
     * Répertoire du thème
     * @access private
     * @var string
     */
    private $folder = null;

    /**
     * Constructeur de la class
     *
     * @param string $name
     * @param string $folder
     */
    public function __construct($name = "defaut", $folder = null) {
        $this->name = $name;
        $this->folder = $folder;
    }

    public function __tostring() {
        return "Theme";
    }

    /**
     * Url du theme utilisé
     *
     * @access public
     * @return string
     */
    public function getUrl() {
        $lUrl = "//" . $_SERVER['HTTP_HOST'];
        if (!empty($this->folder)) {
            $lUrl .= $this->folder;
        }
        $lUrl .= $this->name . "";
        return $lUrl;
    }

    /**
     * Url du répoertoire "Pictures" du theme utilisé
     * @access public
     * @return string
     */
    public function getPicturesUrl() {
        $url = $this->getUrl() . "/pictures";
        return $url;
    }

    /**
     * Retourne le chemin du répertoire des templates du thème désiré
     * @access public
     * @return string
     */
    public function getTemplatesFolder($name = null) {
        if (!empty($name)) {
            $lFolder = $this->folder . $name . "/templates";
        } else {
            $lFolder = $this->folder . $this->name . "/templates";
        }

        return $lFolder;
    }

    /**
     * Getter et Setter
     */
    public function getName() {
        return $this->name;
    }

    public function getFolder() {
        return $this->folder;
    }

}
