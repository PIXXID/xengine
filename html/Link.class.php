<?php

/**
 * Class pour la generation de l'objet <LINK>
 *
 * @name    Link
 * @copyright    D.M  07/01/2008
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\html;

class Link {

    private $rel = null;
    private $type = null;
    private $href = null;
    private $media = null;

    /**
     * Constructeur de l'objet <link>
     *
     * @name link::__construct()
     * @access public
     * @param string $type
     * @param string $media
     *
     * @return void
     */
    public function __construct($href = null, $rel = 'stylesheet', $type = 'text/css', $media = 'all') {
        $this->type = $type;
        $this->rel = $rel;
        $this->href = $href;
        $this->media = $media;
    }

    /**
     * Affiche le code au format demandé
     *
     * @name link::get()
     * @access public
     * @param string $format
     *
     * @return string
     */
    public function get($format = 'HTML') {
        $html = null;

        switch ($format) {
            default:
            case 'HTML' : $html = $this->getHTML();
                break;
        }

        return $html;
    }

    /**
     * Affiche le code au format HTML
     *
     * @name link::getHTML()
     * @access public
     * @param void
     *
     * @return string
     */
    public function getHTML() {
        $html = '<link';

        if (!empty($this->rel)) {
            $html .= " rel=\"" . $this->getRel() . "\"";
        }
        if (!empty($this->type)) {
            $html .= " type=\"" . $this->getType() . "\"";
        }
        if (!empty($this->href)) {
            $html .= " href=\"" . $this->getHref() . "\"";
        }
        if (!empty($this->media)) {
            $html .= " media=\"" . $this->getMedia() . "\"";
        }
        $html .= ' />';

        return $html;
    }

    /* Getters et setters */

    public function getRel() {
        return $this->rel;
    }

    public function getType() {
        return $this->type;
    }

    public function getHref() {
        return $this->href;
    }

    public function getMedia() {
        return $this->media;
    }

    public function setRel($value) {
        $this->rel = $value;
    }

    public function setType($value) {
        $this->type = $value;
    }

    public function setHref($value) {
        $this->href = $value;
    }

    public function setMedia($value) {
        $this->media = $value;
    }

}
