<?php

/**
 * Class pour la generation de l'objet <SCRIPT>
 *
 * @name    Script
 * @copyright D.M  07/01/2008
 * @license     /LICENCE.txt
 * @since       1.0
 * @author    D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\html;

class Script {

    private $type = null;
    private $language = null;
    private $src = null;
    private $value = null;

    /**
     * Constructeur de l'objet <script>
     *
     * @name script::__construct()
     * @access public
     * @param string $type
     * @param string $src
     *
     * @return void
     */
    public function __construct($src = '', $type = 'text/javascript', $language = null) {
        $this->type = $type;
        $this->src = $src;
        $this->language = $language;
    }

    /**
     * Affiche le code au format demandé
     *
     * @name script::get()
     * @access public
     * @param string $format
     *
     * @return string
     */
    public function get($format = 'HTML') {
        $html = null;

        switch ($format) {
            default:
            case "HTML" : $html = $this->getHTML();
                break;
        }
        return $html;
    }

    /**
     * Affiche le code au format HTML
     *
     * @name script::getHTML()
     * @access public
     *
     * @return string
     */
    public function getHTML() {
        $html = '<script';
        if (!empty($this->type)) {
            $html .= " type=\"" . $this->getType() . "\"";
        }
        if (!empty($this->src)) {
            $html .= " src=\"" . $this->getSrc() . "\"";
        }
        if (!empty($this->language)) {
            $html .= " language=\"" . $this->getLanguage() . "\"";
        }
        $html .= ">";

        if (!empty($this->value)) {
            $html .= $this->getValue();
        }

        $html .= '</script>';

        return $html;
    }

    /* Getters et setters */

    public function getType() {
        return $this->type;
    }

    public function getSrc() {
        return $this->src;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getValue() {
        return $this->value;
    }

    public function setType($value) {
        $this->type = $value;
    }

    public function setSrc($value) {
        $this->src = $value;
    }

    public function setLanguage($value) {
        $this->language = $value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

}
