<?php

/**
 * Class pour la generation de l'objet <HEAD>
 *
 * @name        head
 * @copyright   D.M  07/01/2008
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\html;

class Head {

    private $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    private $xmlns = 'http://www.w3.org/1999/xhtml';
    private $title = null;
    private $contentType = null;
    private $keywords = null;
    private $description = null;
    private $themeLink = null;
    private $links = array();
    private $themeScript = null;
    private $scripts = array();
    private $metas = array();

    /**
     * Constructeur de l'objet <head>
     *
     * @name head::__construct()
     * @access public
     * @param string $title Titre de la page
     *
     * @return void
     */
    public function __construct($title = 'Pas de titre') {
        $this->title = $title;
    }

    /**
     * Affiche le code au format demandé
     *
     * @name head::get()
     * @access public
     * @param string $format    Format de sortie ( HTML ... )
     *
     * @return string
     */
    public function get($format = "HTML") {
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
     * @name head::getHTML()
     * @access public
     *
     * @return string
     */
    public function getHTML() {
        $html = $this->doctype . "\r\n";

        $html .= '<html';
        if (!empty($this->xmlns)) {
            $html .= " xmlns=\"" . $this->getXmlns() . "\"";
        }
        $html .= ">\n";

        $html .= "    <head>\n";
        if (!empty($this->title)) {
            $html .= "        <title>" . $this->getTitle() . "</title>\n";
        }
        if (!empty($this->contentType)) {
            $html .= '        ' . $this->getContentType()->get() . "\n";
        }
        if (!empty($this->description)) {
            $html .= '        ' . $this->getDescription()->get() . "\n";
        }
        if (!empty($this->keywords)) {
            $html .= '        ' . $this->getKeywords()->get() . "\n";
        }

        // Tous les autres meta
        for ($i = 0; $i < sizeof($this->metas); $i++) {
            $html .= '        ' . $this->metas[$i]->get() . "\n";
        }

        // Toutes les balises Link
        reset($this->links);
        foreach ($this->links as $key => $val) {
            $html .= '        ' . $val->get() . "\n";
        }

        // Balise CSS du theme a la fin des CSS
        if ($this->themeLink != null) {
            $html .= '        ' . $this->themeLink->get();
        }

        $html .= "\n";

        // Toutes les balises Script
        reset($this->scripts);
        foreach ($this->scripts as $key => $val) {
            $html .= '        ' . $val->get() . "\n";
        }
        // Balise Javascript du theme a la fin
        if ($this->themeScript != null) {
            $html .= '        ' . $this->themeScript->get();
        }

        $html .= "\n    </head>\n";

        return $html;
    }

    /**
     * Ajout d'un nouveau <meta>
     *
     * @name head::addMeta
     * @access public
     * @param $http_equiv
     * @param $content
     *
     * @return void
     */
    public function addMeta($http_equiv, $content) {
        $this->metas[] = new Meta($http_equiv, $content);
    }

    /**
     * Ajout du fichier CSS par defaut pour le theme.
     *
     * @name head::setThemeLink
     * @access public
     * @param type $href
     * @param type $rel
     * @param type $type
     * @param type $media
     *
     * @return void
     */
    public function setThemeLink($href, $rel = "stylesheet", $type = "text/css", $media = "all") {
        $this->themeLink = new Link($href, $rel, $type, $media);
    }

    /**
     * Suppression du CSS du theme.
     *
     * @name head::deleteThemeLink
     * @access public
     *
     * @return void
     */
    public function deleteThemeLink() {
        $this->themeLink = null;
    }

    /**
     * Ajoute le fichier Javascript par defaut pour le thème
     *
     * @name head::setThemeScript
     * @access public
     * @param string $src
     * @param string $type
     * @param string $language
     *
     * @return void
     */
    public function setThemeScript($src, $type = 'text/javascript', $language = null) {
        $this->themeScript = new Script($src, $type, $language);
    }

    /**
     * Supprime le JS du thème
     *
     * @name head::deleteThemeScript
     * @access public
     *
     * @return void
     */
    public function deleteThemeScript() {
        $this->themeScript = null;
    }

    /**
     * Ajoute un nouveau <link>
     *
     * @name head::addLink
     * @access public
     * @param string $href
     * @param string $rel
     * @param string $type
     * @param string $media
     *
     * @return void
     */
    public function addLink($href, $rel = 'stylesheet', $type = 'text/css', $media = 'all') {
        $this->links[] = new Link($href, $rel, $type, $media);
    }

    /**
     *  Supprime le <link> fourni
     *
     *  @name head::deleteLink
     *  @access public
     *  @param int $key
     *
     *  @return void
     */
    public function deleteLink($key) {
        $lArray = array($key => 1);
        $this->links = array_diff_key($this->links, $lArray);
    }

    /**
     * Ajoute un nouveau <script>
     *
     * @name head::addScript
     * @access public
     * @param string $src
     * @param string $type
     * @param string $language
     *
     * @return void
     */
    public function addScript($src, $type = 'text/javascript', $language = null) {
        $this->scripts[] = new Script($src, $type, $language);
    }

    /**
     * Supprime un script <script>
     *
     * @name head::deleteScript
     * @access public
     * @param int $key
     *
     * @return void
     */
    public function deleteScript($key) {
        $lArray = array($key => 1);
        $this->scripts = array_diff_key($this->scripts, $lArray);
    }

    /* Getters et setters */

    public function getDoctype() {
        return $this->doctype;
    }

    public function getXmlns() {
        return $this->xmlns;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getContentType() {
        return $this->contentType;
    }

    public function getKeywords() {
        return $this->keywords;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getMetas() {
        return $this->metas;
    }

    public function getLinks() {
        return $this->links;
    }

    public function getScripts() {
        return $this->scripts;
    }

    public function setDoctype($value) {
        $this->doctype = $value;
    }

    public function setXmlns($value) {
        $this->xmlns = $value;
    }

    public function setTitle($value) {
        $this->title = $value;
    }

    public function setContentType($value) {
        $this->contentType = new Meta('Content-Type', $value);
    }

    public function setKeywords($value) {
        $this->keywords = new Meta('keywords', $value);
    }

    public function setDescription($value) {
        $this->description = new Meta('description', $value);
    }

}
