<?php

/**
 * Gère la ré-écriture d'url
 *
 * Les Url de correspondance seront cherchées dans ./$folder/$lang/LC_URL/$domain.url.php
 *
 * Pour activer le module de réécriture :
 * 1 - Activer la ligne "urlrewrite" dans la fichier de configuration.
 * 2 - Ajouter le fichier de correspondance des controllers du framework qui sont
 *     prise en charge par la réécriture. ( ex : $folder/fr_FR/LC_URL/boursea.website.url.php )
 *     $LC_URL["home"] = "formulaire";
 *     $LC_PARAM["home"] = array("met_id","spe_id");
 * 3 - Ajouter les fichiers de correspondance pour l'encryptage de l'url
 *     ex : /$folder/fr_FR/LC_URL/spe_id.url.php
 *     $LC_DATA    = array("1"=>"1ere valeur","2"=>"2eme valeur");
 *
 * @name        UrlRewrite
 * @copyright   D.M  27/06/2008
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

class UrlRewrite {

    /**
     * On passe le DataCenter en pointeur pour bénéficier de l'ensemble des
     * données du framework
     *
     * @var _DC
     */
    private $_DC = null;

    /**
     * La réécriture est elle activée ?
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
     * Domaine de réécriture ( Nom de l'application )
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
     * Message d'erreur
     * @access private
     * @var string
     */
    private $error = null;

    /**
     * Table de correspondance des controllers
     * @access private
     * @var array
     */
    private $LC_ACTIONS = null;
    private $LC_PARAMS = null;
    private $LC_PREFIX = null;

    /**
     * Table de correspondance des paramètres
     * @access private
     * @var array
     */
    private $LC_DATAS = null;

    /**
     * Permet de protéger la ré-écriture en supprimant les caractères
     * accentués et les caractères spéciaux.
     * @access private
     * @var boolean
     */
    private $escape = true;
    private $extention = ".html";
    private $absolute = true;
    private $lScript = array("index.php5", "index.php4", "index.php", "default.php5", "default.php4", "default.php");
    private $lRacine = null;
    private $lController = null;
    private $lElements = null;

    public function __construct($active = "false") {
        ($active == "true") ? $this->active = true : $this->active = false;
    }

    public function __tostring() {
        return "urlRewrite";
    }

    /*
     * Indique s'il faut traduire le fichier de ré-écriture
     *
     * @access public
     *
     * @param string
     * @param string
     * @param string
     *
     * @return bool
     */

    public function localize($folder = null, $domain = null, $lang = null) {

        if ($this->active == false) {
            $this->error = "urlRewrite non activé.";
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

        /*
         * Enregistrement de la table de réécriture des controllers
         */
        $lFile = $this->getFile($this->domain, $this->lang);
        if (file_exists($lFile) === false) {
            $this->error = "Le fichier '{$lFile}' est introuvable.";
            $this->active = false;

            return false;
        } else {
            include_once($lFile);
            $this->LC_ACTIONS = $LC_URL;
            $this->LC_PARAMS = $LC_PARAM;
            if (!empty($LC_PREFIX)) {
                $this->LC_PREFIX = $LC_PREFIX;
                unset($LC_PREFIX);
            }
            unset($LC_URL);
            unset($LC_PARAM);
        }

        return true;
    }

    /**
     * Charge un fichier de correspondace d'Url
     *
     * @access public
     *
     * @param string $file
     *
     * @return boolean
     */
    public function loadFile($param) {

        if ($this->active == false) {
            return false;
        }

        // Optimisation : si les données sont déja chargées après un décryptage
        if ((is_array($this->LC_DATAS)) && (isset($this->LC_DATAS[$param]))) {
            return true;
        }

        // Chargement des nouvelles données.
        $lFile = $this->getFile($param, $this->lang);
        if (file_exists($lFile) === FALSE) {
            $this->error = "Le fichier '{$lFile}' est introuvable.";
            return false;
        } else {
            $_DC = $this->_DC;
            include($lFile);
            $this->LC_DATAS[$param] = $LC_DATA;
            unset($LC_DATA);
        }

        return true;
    }

    /**
     * Encryptage complète de l'url en fonction des paramètres envoyés
     * et du contenue des fichiers de ré-écriture
     *
     * @access public
     * @param string $url
     *
     * @return string
     */
    public function encryptUrl($url) {

        // Pas d'encodage si pas activé.
        if ($this->active == false) {
            return $url;
        }

        $lUrl = null;
        $lCpt = 0;

        // Si l'encodage est activé : Découpage de l'url
        $this->splitUrl($url);

        // Si le split ééchoué, pas d'encryption.
        if (isset($this->LC_ACTIONS[$this->lController])) {
            $this->error = 'Aucune correspondance de ré-écriture.';
            return $url;
        }

        // Lecture de la traduction de Le controller ( ex : metiers- )
        $lUrl = $this->LC_ACTIONS[$this->lController];

        $lMaps = $this->LC_PARAMS[$this->lController];

        // ex : $record = met_id
        foreach ($lMaps as $record) {

            // Valeur envoyée dans le paramètre
            if (isset($this->lElements[$record])) {
                $lValue = $this->lElements[$record];

                // Si on retrouve la valeur
                if (isset($this->LC_DATAS[$record][$lValue])) {
                    // On a la possibilité d'ajouter un prefix.
                    if (isset($this->LC_PREFIX[$this->lController])) {
                        $lUrl .= $this->LC_PREFIX[$this->lController][$lCpt];
                    }

                    // Ajout du libéllé de l'url
                    // Suppression des accents et des caractères spéciaux
                    if ($this->escape == true) {
                        $lUrl .= urlRewrite::encodeUrl($this->LC_DATAS[$record][$lValue]) . '/';
                    } else {
                        $lUrl .= $this->LC_DATAS[$record][$lValue] . '/';
                    }
                } else {
                    // Si un des elements ne se réécrit pas, on n'encrype rien.
                    echo $this->error = "Le paramètre '{$lValue}' est introuvable dans le fichier '{$this->getFile($record, $this->lang)}'";
                    return $url;
                }
            } else {
                // Si un des elements ne se réécrit pas, on n'encrype rien.
                //$this->error = "Vérifier l'url à encrypter, vos paramètres sont éronnés.";
                //return $url;
            }

            $lCpt++;
        }

        // Suppression du dernier "/"
        $lUrl = substr($lUrl, 0, -1);

        // Reconstitution de l'url - en abolue ( a partir de la racine ) ou  en relatif.
        ($this->absolute) ? $lUrl = "http://" . $_SERVER["HTTP_HOST"] . "/" . $this->lRacine . $lUrl . $this->extention : $lUrl = $this->lRacine . $lUrl . $this->extention;

        return $lUrl;
    }

    /**
     * Décrypte l'url envoyée
     *
     * @access public
     * @param string $url
     * @return void
     */
    public function decryptUrl($url) {
        if ($this->active == false) {
            return;
        }

        $_DC = $this->_DC;
        $lControllerValue = null;
        $lControllerKey = null;
        $lParams = null;
        $lMaps = null;
        $lCpt = 0;

        // Recherche quelle action est à decrypter,
        // ce mot clé qui permet de trouver quel script action du framework doit être executé
        while ($value = current($this->LC_ACTIONS)) {

            if (strpos($url, '/' . $value) !== false) {
                $lControllerValue = $value;
                $lControllerKey = key($this->LC_ACTIONS);
                break;
            }
            next($this->LC_ACTIONS);
        }

        if ($lControllerKey == null) {
            $this->error = 'Pas de correspondance trouvée.';
            return;
        }

        /*
         * On décompose l'url pour lire l'ensemble des données envoyées par l'url.
         * on supprime le nom de Le controller de l'url à traiter
         */
        $lUrl = explode('/' . $lControllerValue, $url);
        if (sizeof($lUrl) > 1) {

            // On récupère les valeurs envoyés par l'url encryptée
            $lParams = explode('/', $lUrl[1]);

            // On utilise le fichier de description des url encripté pour
            // avoir l'ordre de passage des paramètres ($LC_PARAM ).
            $lMaps = $this->LC_PARAMS[$lControllerKey];
            if (isset($this->LC_PREFIX[$lControllerKey])) {
                $lPrefix = $this->LC_PREFIX[$lControllerKey];
            }

            foreach ($lMaps as $record) {

                // Chargement des fichiers MAP utile pour le décryptage ( ex: spe_id.url.php )
                if ($this->loadFile($record) == true) {

                    // Suppression du prefix rajouter dans l'element de l'url ( ex : prefix-mon-premier-parametre = mon-premier-parametre)
                    if ((isset($lParams[$lCpt])) && (isset($lPrefix[$lCpt])) && (!empty($lPrefix[$lCpt]))) {
                        $lParams[$lCpt] = substr($lParams[$lCpt], strlen($lPrefix[$lCpt]), strlen($lParams[$lCpt]));
                    }

                    // Suppression de l'extention rajouté pendant l'encryption de l'url sur le dernier element (ex : .html)
                    if (isset($lParams[$lCpt])) {
                        $lParams[$lCpt] = str_replace($this->extention, "", $lParams[$lCpt]);

                        // Lecture des données pour le fichier de correspondance du paramètre.
                        $lArray = $this->LC_DATAS[$record];
                        while ($value = current($lArray)) {
                            // Valeur de correspondance dans le fichier des données de ré-écriture ( ex: met_id, for_id ...)
                            $value = urlRewrite::encodeUrl($value);

                            // Si on trouve la correspondance (identifiant du libellé envoyé par l'url).
                            if ($lParams[$lCpt] == $value) {
                                // Ajout des paramètres et de leur id decrypté.
                                $_DC->set($record, key($lArray));
                                break;
                            }
                            next($lArray);
                        }
                    }
                }

                $lCpt++;
            }
        }

        // On positionne Le controller pour la navigation au sein de contrôleur
        $_DC->set('action', $lControllerKey);

        return;
    }

    /**
     * Découpe l'url
     *
     * @access public
     * @param string $url
     * @param string $key
     *
     * @return void
     */
    public function splitUrl($url, $key = '?action=') {
        if ($url != null) {
            parse_str($url, $lElements);
            $this->lController = array_shift($lElements);
            $this->lElements = $lElements;
        }
        return;
    }

    /**
     * Lecture du chemin complet vers le fichier .url.php de réécriture
     *
     * @access public
     * @param mixed string|null $domain
     * @param mixed string|null $lang
     *
     * @return string
     */
    public function getFile($domain = null, $lang = null) {
        return $_SERVER['DOCUMENT_ROOT'] . '/' . $this->folder . '/' . $lang . '/LC_URLS/' . $domain . '.url.php';
    }

    /**
     * Encode l'url.
     *
     * @access private
     * @param string $url
     *
     * @return string
     */
    private function encodeUrl($lUrl) {
        $lUrl = String_::unaccents($lUrl);
        $lUrl = strtolower($lUrl);
        $lUrl = preg_replace('/([^.a-z\/0-9]+)/i', '-', $lUrl);
        $lUrl = strtr($lUrl, '/', '-');
        return $lUrl;
    }

    /*
     * Getters et setters
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

    public function setEscape($value) {
        $this->escape = $value;
    }

    public function getError() {
        return $this->error;
    }

    public function getActive() {
        return $this->active;
    }

    public function setDataCenter($value) {
        $this->_DC = $value;
    }

    public function setExtention($value) {
        $this->extention = $value;
    }

    public function getAbsolute() {
        return $this->absolute;
    }

    public function setAbsolute($value) {
        $this->absolute = $value;
    }

    public function getRacine() {
        return $this->lRacine;
    }

    public function setRacine($value) {
        (empty($value)) ? $this->lRacine = '' : $this->lRacine = $value;
    }

}
