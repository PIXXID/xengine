<?php

/**
 * Gestion de l'internationalisation
 * Utilisation de la fonction gettext()
 * La traduction sera cherchée dans ./$folder/$lang/LC_MESSAGES/$domain.mo
 *
 * @name        lang
 * @copyright   PIXXID 28/07/2015
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

class Lang {

    /**
     * La traduiction est elle activée ?
     * @access private
     * @var string
     */
    private $active = false;

    /**
     * Dossier des traductions
     * @access private
     * @var string
     */
    private $folder = "/locale";

    /**
     * Domaine de traduction ( Nom du dictionnaire )
     * @access private
     * @var string
     */
    private $domain = "default";

    /**
     * Langue par defaut
     * @access private
     * @var string
     */
    private $lang = "fr_FR";

    /**
     * Encodage par defaut
     * @access private
     * @var string
     */
    private $encoding = "UTF-8";

    /**
     * Message d'erreur
     * @access private
     * @var string
     */
    private $error = null;

    /**
     * Ajout un caractère pour indiquer les textes sur lesquels on applique
     * la traduction.
     * @access private
     * @var boolean
     */
    public $marqueur = false;

    /**
     * Contructeur de la class Lang
     * @param string $active
     */
    public function __construct($active = "false") {
        ($active == "true") ? $this->active = true : $this->active = false;
    }

    public function __tostring() {
        return "Lang";
    }

    /*
     * Localisation du fichier de traduction à travers le composant
     * Zend Translate.
     */

    public function localize($folder = null, $domain = null, $lang = null, $encoding = null) {
        if ($this->active == false) {
            $this->error = "Lang non activé.";
            return false;
        }

        if (!empty($folder)) {
            $this->folder = $folder;
        }
        if (!empty($domain)) {
            $this->domain = $domain;
        }
        if (!empty($lang)) {
            $this->lang = $lang;
        }
        if (!empty($encoding)) {
            $this->encoding = $encoding;
        }

        // Variable d'environnement pour windows
        putenv("LANG=" . $this->lang . '.' . $this->encoding);
        putenv("LANGUAGE=" . $this->lang . '.' . $this->encoding);

        bind_textdomain_codeset($this->domain, $this->encoding);
        bindtextdomain($this->domain, $this->folder);
        setlocale(LC_ALL, $this->lang . '.' . $this->encoding);
        textdomain($this->domain);

        return true;
    }

    /*
     * Retourne la traduction dans le cas ou elle est activée
     * Sinon retourne la chaine de caractère initiale.
     */

    public function getText($label, $domain = null, $lang = null) {
        if ($this->active == true) {
            $label = _($label);
            if ($this->marqueur == true) {
                $label = "&para;" . $label;
            }
        }
        return $label;
    }

    /*
     * Getter et Setter
     */

    public function getFolder() {
        return $this->folder;
    }

    public function setFolder($value) {
        $this->folder = $value;
    }

    public function getLang() {
        return $this->lang;
    }

    public function setLang($value) {
        $this->lang = $value;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function setDomain($value) {
        $this->domain = $value;
    }

    public function getEncoding() {
        return $this->encoding;
    }

    public function setEncoding($value) {
        $this->encoding = $value;
    }

    public function getError() {
        return $this->error;
    }

}
