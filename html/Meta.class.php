<?php

/**
 * Classe pour la génération de l'objet <META>
 *
 * @name    Meta
 * @copyright    D.M  07/01/2008
 * @license     /LICENCE.txt
 * @since       1.0
 * @author    D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\html;

class Meta {

    private $httpEquiv = null;
    private $content = null;
    private $name = null;

    /**
     * Constructeur de l'objet <meta>
     *
     * @name meta::__construct()
     * @access public
     * @param string $httpEquiv
     * @param string $content
     *
     * @return void
     */
    public function __construct($httpEquiv = 'Content-Type', $content = 'text/html; charset=UTF-8') {
        $this->httpEquiv = $httpEquiv;
        $this->name = $httpEquiv;
        $this->content = $content;
    }

    /**
     * Affiche le code au format demandé
     *
     * @name meta::get()
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
     * @name meta::getHTML()
     * @access public
     *
     * @return string
     */
    public function getHTML() {
        $html = '<meta';
        switch (strtolower($this->httpEquiv)) {
            case 'content-type' : $html .= " http-equiv=\"" . $this->getHttpEquiv() . "\"";
                break;
            case 'refresh' : $html .= " http-equiv=\"" . $this->getHttpEquiv() . "\"";
                break;
            case 'robots' : $html .= " http-equiv=\"" . $this->getHttpEquiv() . "\"";
                break;
            case 'window-target' : $html .= " http-equiv=\"" . $this->getHttpEquiv() . "\"";
                break;
            default : $html .= " name=\"" . $this->getName() . "\"";
        }

        if (!empty($this->content)) {
            $html .= " content=\"" . $this->getContent() . "\"";
        }
        $html .= " />";

        return $html;
    }

    /* Getters et setters */

    public function getHttpEquiv() {
        return $this->httpEquiv;
    }

    public function getContent() {
        return $this->content;
    }

    public function getName() {
        return $this->name;
    }

    public function setHttpEquiv($value) {
        $this->httpEquiv = $value;
    }

    public function setContent($value) {
        $this->content = $value;
    }

    public function setName($value) {
        $this->name = $value;
    }

}
