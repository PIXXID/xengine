<?php

/**
 * Objet permettant la diffusion des données, messages et autres informations
 * entre le controller et les vues.
 *
 * @name        DataCenter
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

use \xEngine\database\DbConnection;
use \xEngine\exception\Exception_;
use \xEngine\exception\Level;
use \xEngine\exception\MessageError;
use \xEngine\exception\MessageNotice;
use \xEngine\html\head;
use \xEngine\tools\String_;

class DataCenter {

    /**
     * Toutes les données à destination des vues devront être enregistrée ici.
     * @access private
     * @var array
     */
    private $vars = array();

    /**
     * Gestion de multiple connection aux bases de données
     * @access private
     * @var array
     */
    private $conn = array();

    /**
     * Toutes les prorpiétés renseignée dans le fichier de config xml.
     * @access private
     * @var array
     */
    private $properties = array();

    /**
     * Nom du controller
     * @access private
     * @var string
     */
    private $controller = null;

    /**
     * Permet de modifier la vue
     * d'affichage.
     * @access private
     * @var string
     */
    private $view = "";

    /**
     * Permet d'effectuer une redirection
     * avant que la vue ne soit affichée.
     * @access private
     * @var string
     */
    private $redirect = "";

    /**
     * Fait partie d'une redirection
     * @access private
     * @var string
     */
    public $duringRedirect = false;

    /**
     * Nom du répertoire de destination des vues
     * @access private
     * @var string
     */
    private $folderView = null;

    /**
     * Nom du répertoire de destination des controllers
     * @access private
     * @var string
     */
    private $folderController = null;

    /**
     * Nom de la session qui gère l'identification (sécurité)
     * @access private
     * @var string
     */
    private $signupName = null;

    /**
     * Gestion du theme
     * @access private
     * @var string
     */
    private $theme = null;

    /**
     * Gestion de l'entete Html
     * @access private
     * @var string
     */
    private $head = null;

    /**
     * Gestion de l'internationalisation
     * @access private
     * @var Object
     */
    private $lang = null;

    /**
     * Gestion de la reecriture des urls
     * @access private
     * @var Object
     */
    private $urlRewrite = null;

    /**
     * Gestion des urls anonymes
     * @access private
     * @var Object
     */
    private $router = null;

    /**
     * Gestion du debugger
     * @access private
     * @var debugger
     */
    private $debugger = null;

    /**
     * Retourne la chaine de caractere lorsque l'on essai
     * d'afficher l'objet
     *
     * @name DataCenter::__tostring()
     * @access public
     * @return string
     */
    public function __tostring() {
        return "DataCenter - Framework Pixxid v" . XENGINE_VERSION;
    }

    /**
     * Retourne le nom de l'application renseigne dans le fichier de configuration
     *
     * @name DataCenter::getApp_name()
     * @access public
     * @return string
     */
    public function getApp_name() {
        return XENGINE_APP_NAME;
    }

    public function getApp_version() {
        return XENGINE_APP_VERSION;
    }

    public function getController() {
        return $this->controller;
    }

    public function setController($value) {
        $this->controller = $value;
    }

    /**
     * Ajoute une nouvelle valeur dans le DataCenter, en JSON
     *
     * @name DataCenter::setJSON()
     * @param string  $name    Nom de la variable
     * @param string  $value Valeur
     * @param string  $options Options de conversion
     * @access public
     * @return void
     */
    public function setJSON($name, $value, $options = JSON_UNESCAPED_UNICODE) {
        $this->vars[$name] = json_encode($value, $options);
    }

    /**
     * Ajoute une nouvelle valeur dans le DataCenter
     *
     * @name DataCenter::set()
     * @param string  $name    Nom de la variable
     * @param string  $value Valeur
     * @access public
     * @return void
     */
    public function set($name, $value) {
        // Si la méthode HTTP utilisée est GET, on repositionne les paramètres dans la variable globale
        // $_GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $_GET[$name] = $value;
        }
        $this->vars[$name] = $value;
    }

    /**
     * Ajoute un ensemble de donnee (Array) au DataCenter
     *
     * @name DataCenter::setArray()
     * @param array  $data    Tableau contenant les donnees
     * @access public
     * @return void
     */
    public function setArray($data) {
        if ((isset($data)) && (is_array($data))) {
            $this->vars = array_merge($this->vars, $data);
        }
    }

    /**
     * Ajoute les donnees recuperer a partir des attributs
     * de la class DAO generer par daoGenerator dans le
     * DataCenter
     *
     * @name DataCenter::setADOAttributes()
     * @param object  $obj    Class DAO auto.
     * @param String  $detail Mode d'envoi de la valeur des attributs (false = simple, true = detaille -> Objet column);
     * @access public
     * @return void
     */
    public function setADOAttributes($obj, $detail = false) {

        // Liste des methodes de l'objet
        foreach (get_class_methods(get_parent_class($obj)) as $methodName) {

            $controller = substr($methodName, 0, 3);     // set ou get
            $endController = substr($methodName, -5, 5);     // Mode detaille ou non
            $nameAttr = substr($methodName, 3, strlen($methodName) - 3);
            $varName = $nameAttr;

            if (($controller === "get") && ((($detail === false) && ($endController === "Value")) || (($detail === true) && ($endController !== "Value")))) {
                // On supprime le "value" de la fin du nom de l'attribut si necessaire.
                if ($endController === "Value") {
                    $varName = substr($nameAttr, 0, -5);
                }

                // Appel de la methode
                $data = call_user_func(array(&$obj, $methodName));
                // Enregistrement du resultat dans le DataCenter
                $this->set(String_::uncamelize($varName), $data);
            }
        }
    }

    /**
     * Lecture d'une donnée presente dans le DataCenter
     *
     * @name DataCenter::get()
     * @param string  $name    Nom de la variable
     * @param boolean $protect Echappe les données affichés par le datacenter
     * @param string  $method  Méthode d'achappement SPECIALCHARS | ENTITIES | STRIPTAGS | SLASHES
     * @access public
     * @return mixed
     */
    public function get($name, $protect = true, $method = "SPECIALCHARS") {
        $value = null;

        if (isset($this->vars[$name])) {

            // Protection de la donnée retournée par le DataCenter
            if ($protect === true) {
                $value = $this->escape($this->vars[$name], $method);
            } else {
                $value = $this->vars[$name];
            }
        }

        return $value;
    }

    /**
     * Protection d'une donnée à afficher
     *
     * @name DataCenter::escape()
     * @param string  $value   Valeur à afficher
     * @param string  $method  Méthode d'achappement SPECIALCHARS | ENTITIES | STRIPTAGS | SLASHES
     * @access public
     * @return mixed
     */
    public function escape($value, $method = "SPECIALCHARS") {

        // Protection de la donnée retournée par le DataCenter
        if (is_string($value)) {
            switch ($method) {
                case "ENTITIES" :
                    $value = htmlentities($value);
                    break;
                case "STRIPTAGS" :
                    $value = strip_tags($value);
                    break;
                case "SLASHES" :
                    $value = addslashes($value);
                    break;
                case "SPECIALCHARS" :
                default :
                    $value = htmlspecialchars($value);
            }
        }

        return $value;
    }

    /**
     * Lecture de l'ensemble des donnees presente dans le DataCenter
     *
     * @name DataCenter::getAll()
     * @param boolean  $tri    Tri du tableau
     * @access public
     * @return mixed
     */
    public function getAll($tri = true) {
        if ($tri == true) {
            ksort($this->vars);
        }
        return $this->vars;
    }

    /**
     * Suppression d'une des valeurs enregistrees dans le DataCenter
     *
     * @name DataCenter::delete()
     * @access public
     * @return void
     */
    public function delete($name) {

        if (isset($this->vars[$name])) {
            // On revient au debut du tableau
            reset($this->vars);
            for ($i = 0; $i < sizeof($this->vars); $i++) {
                // Si on retrouve la cle a supprimer
                if (key($this->vars) == $name) {
                    // On ne garde que le champ a supprimer
                    $todelete[$name] = array();
                    // On supprime le champ dans le tableau final
                    $this->vars = array_diff_key($this->vars, $todelete);
                    break;
                }
                next($this->vars);
            }
        }
    }

    /**
     * Suppression de toutes les valeurs enregistrees dans le DataCenter
     *
     * @name DataCenter::raz()
     * @access public
     * @return void
     */
    public function raz() {
        $this->vars = array();
    }

    /**
     * Ajoute une nouvelle valeur persistante (session)
     * dans le DataCenter
     *
     * @name DataCenter::setP()
     * @param string  $name    Nom de la variable
     * @param string  $value Valeur
     * @access public
     * @return void
     */
    public function setP($name, $value) {
        $_SESSION[$name] = $value;
    }

    public function getP($name) {
        $value = null;
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];
        } else {
            $value = null;
        }

        return $value;
    }

    public function getPAll() {
        return $_SESSION;
    }

    public function deleteP($name) {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    public function razP() {
        $_SESSION = array();
    }

    /**
     * Ajoute une propertie dans le DataCenter
     *
     * @name DataCenter::setProperty()
     * @param string  $name    Nom de la propertie
     * @param string  $value Valeur
     * @access public
     * @return void
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    public function getProperty($name) {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        } else {
            return null;
        }
    }

    public function getProperties() {
        return $this->properties;
    }

    /**
     * Ajoute une nouvelle valeur dans un cookie
     *
     * @name DataCenter::setCookie()
     * @param string  $name    Nom de la variable
     * @param string  $value Valeur
     * @param int     $delay Nombre de jour de validite
     * @access public
     * @return void
     */
    public function setCookie($name, $value, $delay = 1) {
        setcookie($name, $value, time() + ($delay * 24 * 3600), "/");
        // Permet d'utilise le cookie de suite
        $_COOKIE[$name] = $value;
    }

    /**
     * Ajoute une nouvelle valeur dans un cookie sécurisé
     *
     * @name PixDataCenter::setCookieSecure()
     * @param string  $name    Nom de la variable
     * @param string  $value Valeur
     * @param int     $delay Nombre de jour de validite
     * @access public
     * @return void
     */
    public function setCookieSecure($name, $value, $delais = 1) {
        setcookie($name, $value, time() + ($delais * 24 * 3600), "/", $_SERVER["SERVER_NAME"], true, true);
        // Permet d'utilise le cookie de suite
        $_COOKIE[$name] = $value;
    }

    public function getCookie($name) {
        if (isset($_COOKIE[$name])) {
            return stripslashes($_COOKIE[$name]);
        } else {
            return null;
        }
    }

    /**
     * Remize a zero de l'ensemble du DataCenter (donnee, message, connection ...)
     *
     * @name DataCenter::cancel()
     * @access public
     * @return void
     */
    public function cancel() {
        if (!empty($this->vars)) {
            $this->vars = array();
        }
        if (!empty($_SESSION['xNotices'])) {
            $_SESSION['xNotices'] = array();
        }
        if (!empty($_SESSION['xErrors'])) {
            $_SESSION['xErrors'] = array();
        }
        if (!empty($this->conn)) {
            $this->conn = array();
        }
        if (!empty($this->properties)) {
            $this->properties = array();
        }

        $this->view = "";
        $this->redirect = "";
        $this->duringRedirect = false;
    }

    public function setView($value) {
        $this->view = $value;
    }

    public function getView() {
        return $this->view;
    }

    public function setRedirect($value) {
        $this->redirect = $value;
    }

    public function getRedirect() {
        return $this->redirect;
    }

    public function isRedirect() {
        return !empty($this->redirect);
    }

    /**
     * Ajoute un message d'erreur à la liste des messages d'erreur
     *
     * @name DataCenter::addMessageError()
     * @access public
     * @param string $message
     * @param mixed null | string $target
     * @param int $level
     *
     * @return void
     */
    public function addMessageError($message, $target = null, $level = Level::LEVEL_ERR) {
        $pix_message_error = new MessageError($message, $target, $level);

        if (!isset($_SESSION['xErrors'][$pix_message_error->getLevel()])) {
            $_SESSION['xErrors'][$pix_message_error->getLevel()] = array();
        }

        if ($target !== null) {
            $_SESSION['xErrors'][$pix_message_error->getLevel()][$target] = $pix_message_error;
        } else {
            $_SESSION['xErrors'][$pix_message_error->getLevel()][] = $pix_message_error;
        }
    }

    /**
     * Ajoute une notice à la liste des notices
     *
     * @name DataCenter::addMessageNotice()
     * @access public
     * @param string $message
     * @param mixed null | string $target
     * @param int $level
     *
     * @return void
     */
    public function addMessageNotice($message, $target = null, $level = Level::LEVEL_SUCCESS) {
        $pix_message_notice = new MessageNotice($message, $target, $level);

        if (!isset($_SESSION['xNotices'][$pix_message_notice->getLevel()])) {
            $_SESSION['xNotices'][$pix_message_notice->getLevel()] = array();
        }

        if ($target !== null) {
            $_SESSION['xNotices'][$pix_message_notice->getLevel()][$target] = $pix_message_notice;
        } else {
            $_SESSION['xNotices'][$pix_message_notice->getLevel()][] = $pix_message_notice;
        }
    }

    /*
     * Connexion aux bases de donnees
     */

    public function setConn($name, DbConnection $value) {
        $this->conn[$name] = $value;
    }

    /**
     * Retourne l'objet DbConnection
     * @param type $name
     * @return type
     */
    public function getDbConnection($name) {
        if (isset($this->conn[$name])) {
            return $this->conn[$name];
        }
        return null;
    }

    public function pConnect($name, $persistent = true) {
        if (isset($this->conn[$name])) {
            if ($persistent == true) {
                return $this->conn[$name]->PConnect();
            }
            return $this->conn[$name]->connect();
        }
        return null;
    }

    public function Disconnect($name) {
        if (isset($this->conn[$name])) {
            $this->conn[$name]->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gestion de l'identification requise
     * @access private
     * @var string
     */
    public function setSignupName($value) {
        $this->signupName = $value;
    }

    public function getSignupName() {
        return $this->signupName;
    }

    public function validSignup() {
        $this->setP($this->signupName, true);
    }

    public function unvalidSignup() {
        $this->setP($this->signupName, false);
    }

    public function getSignup() {
        return $this->getP($this->signupName);
    }

    /**
     * Retourne le nom du repertoire ou sont enregistree les vuew
     *
     * @name DataCenter::getFolderView()
     * @access public
     * @return string
     */
    public function getFolderView() {
        return $this->folderView;
    }

    /**
     * Enregistre le nom du repertoire des vues
     *
     * @name DataCenter::setFolderView()
     * @param string  $value    Nom du repertoire
     * @access public
     * @return void
     */
    public function setFolderView($value) {
        $this->folderView = $value;
    }

    /**
     * Retourne le chemin complet d'une vue
     *
     * @name DataCenter::includeView()
     * @access public
     * @return string
     */
    public function includeView($name, $folder = null) {
        if (!empty($folder)) {
            return DOCUMENT_ROOT . "/" . $folder . "/" . $name . '.view.php';
        } else {
            return DOCUMENT_ROOT . "/" . $this->folderView . "/" . $name . '.view.php';
        }
    }

    public function includeTpl($name, $folder = null) {
        $_DC = $this;
        if (!empty($folder)) {
            include(DOCUMENT_ROOT . "/" . $folder . "/" . $name . '.view.php');
        } else {
            include(DOCUMENT_ROOT . "/" . $this->folderView . "/" . $name . '.view.php');
        }
        return true;
    }

    /**
     * Retourne le nom du repertoire ou sont enregistre les controllers
     *
     * @name DataCenter::getFolderController()
     * @access public
     * @return string
     */
    public function getFolderController() {
        return $this->folderController;
    }

    /**
     * Enregistre le nom du repertoire des controllers
     *
     * @name DataCenter::setFolderController()
     * @param string  $value    Nom du repertoire
     * @access public
     * @return void
     */
    public function setFolderController($value) {
        $this->folderController = $value;
    }

    /**
     * Lance l'execution manuelle d'un controller
     *
     * @name DataCenter::executeController()
     * @access public
     * @return string
     */
    public function executeController($name, $folder = null) {

        if (!empty($folder)) {
            $file_name = DOCUMENT_ROOT . $folder . $name . '.controller.php';
        } else {
            $file_name = DOCUMENT_ROOT . $this->folderController . $name . '.controller.php';
        }

        if (file_exists($file_name)) {
            try {
                // On inclut le filtre a executer
                include_once($file_name);
                $controller = new $name();
                $controller->execute($this);
            } catch (\Exception $e) {
                $this->addMessageError($e->getMessage(), 'executeController');
                return false;
            }
        } else {
            throw new Exception_("Le fichier controller <Include> &nbsp; \"" . $file_name . "\" est introuvable !");
        }

        return true;
    }

    /**
     * Retourne l'url complete de l'emplacement de Le controller
     * en cours
     *
     * @return string
     */
    public function getUrlController() {
        $lUrl = "//" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"];
        return $lUrl;
    }

    /**
     * Affichage en format html (liste) du message d'erreur
     *
     * @name DataCenter::printMessage()
     * @access public
     * @return string
     */
    public function printMessage() {
        $html = null;
        $i = 0;
        if ($this->errMsg != null) {
            $html = "<div id=\"pixxidError\" class=\"pixxidError\">";
            $html .= "<label>" . $this->errMsg[$i] . "</label>";

            if (sizeof($this->errMsg) > 1) {
                $html .= "<ol>";
                for ($i = 1; $i < sizeof($this->errMsg); $i++) {
                    if (!empty($this->errMsg[$i])) {
                        $html .= "<li>" . $this->errMsg[$i] . "</li>";
                    }
                }
                $html .= "</ol>";
            }
            $html .= "</div>";
        }
        return $html;
    }

    /**
     * Génère un token pour sécuriser les requête intersite (CSRF)
     * @name tokenInit
     * @access public
     * @param string token_id   Permet de gérer plusieur Token simultanément
     * @param bool $force       Force la regénération du token
     *
     * @return void
     */
    public function tokenInit($token_id = null, $force = false) {
        if (($token_id != null) && ($force === true || !isset($_SESSION[$token_id . '_token']))) {
            $_SESSION[$token_id . '_token'] = String_::getRandom(16, null, null, 2);
        } else if ($force === true || !isset($_SESSION['_token'])) {
            $_SESSION['_token'] = String_::getRandom(16, null, null, 2);
        }
    }

    /**
     * Vérifie le paramètre d'entrée avec le token en $_SESSION
     *
     * @name tokenCheck
     * @access public
     * @param string $value
     * @param string token_id
     *
     * @return bool
     */
    public function tokenCheck($value, $token_id = null) {
        // On vérifie le token en session
        if ($token_id != null) {
            $check = ($_SESSION[$token_id . '_token'] == $value);
        } else {
            $check = ($_SESSION['_token'] == $value);
        }

        // On regénère automatiquement le token
        $this->tokenInit($token_id, true);

        return $check;
    }

    /**
     * Retourne la valeur du token en session
     *
     * @name token
     * @access public
     * @param string token_id
     *
     * @return string
     */
    public function token($token_id = '') {
        return $_SESSION[$token_id . '_token'];
    }

    /**
     * Lecture du nom du theme
     *
     * @name DataCenter::getTheme()
     * @access public
     * @return pixxidTheme
     */
    public function getTheme() {
        return $this->theme;
    }

    public function setTheme(Theme $value) {
        $this->theme = $value;
    }

    /**
     * Lecture de l'entete Html
     *
     * @name DataCenter::getHead()
     * @access public
     * @return head
     */
    public function getHead() {
        return $this->head;
    }

    public function setHead(head $value) {
        $this->head = $value;
    }

    /**
     * Lecture des informations d'internalionalisation
     *
     * @name DataCenter::getLang()
     * @access public
     * @return pixxidTheme
     */
    public function getLang() {
        return $this->lang;
    }

    public function setLang(lang $value) {
        $this->lang = $value;
    }

    /**
     * Lecture du libellé traduit
     *
     * @param type $label
     * @param type $domain
     * @param type $lang
     * @return type
     */
    public function getText($label, $domain = null, $lang = null) {
        return $this->lang->getText($label, $domain, $lang);
    }

    /**
     * Lecture des informations de la reecriture des Urls
     *
     * @name DataCenter::getLang()
     * @access public
     * @return pixxidTheme
     */
    public function getUrlRewrite() {
        return $this->urlRewrite;
    }

    public function setUrlRewrite(UrlRewrite $value) {
        $this->urlRewrite = $value;
    }

    public function encryptUrl($url) {
        return $this->urlRewrite->encryptUrl($url);
    }

    public function decryptUrl($url) {
        return $this->urlRewrite->decryptUrl($url);
    }

    /**
     * Retourne les messages de succès sous forme HTML
     *
     * @name DataCenter::printNotices()
     * @access public
     * @param int $level
     * @param mixed null|string $label le label alternatif à afficher
     * @param bool $flush vider ou non les messages de la session
     *
     * @return string
     */
    public function printNotices($level = Level::LEVEL_SUCCESS, $label = null, $flush = true) {
        if (!isset($_SESSION['xNotices'][$level]) || sizeof($_SESSION['xNotices'][$level]) < 1) {
            return '';
        }

        $level_html = ($level === Level::LEVEL_INFO) ? 'infos' : 'successes';
        if ($label === null) {
            $label = ($level === 2) ? $this->getText('Succès :') : $this->getText('Informations :');
        }

        $html = "\n<fieldset class=\"pix-messages pix-{$level_html}\">";
        if ($label != null) {
            $html .= "\n    <legend>{$label}</legend>";
        }
        // Liste des erreurs
        foreach ($_SESSION['xNotices'][$level] as $notice) {
            $html .= "\n    {$notice->render()}";
        }
        $html .= "\n</fieldset>\n";

        if ($flush) {
            $_SESSION['xNotices'][$level] = array();
        }

        return $html;
    }

    /**
     * Retourne les messages d'erreur sous forme HTML
     *
     * @name DataCenter::printErrors()
     * @access public
     * @param int $level afficher les LEVEL_WARN
     * @param mixed null|string $label le label alternatif à afficher
     * @param bool $flush vider ou non les messages de la session
     *
     * @return string
     */
    public function printErrors($level = Level::LEVEL_ERR, $label = null, $flush = true) {
        if (!isset($_SESSION['xErrors'][$level]) || sizeof($_SESSION['xErrors'][$level]) < 1) {
            return '';
        }

        $level_html = ($level === Level::LEVEL_WARN) ? 'warnings' : 'errors';
        if ($label === null) {
            $label = ($level === 2) ? $this->getText('Les erreurs suivantes sont apparues :') : $this->getText('Les avertissements suivants sont apparus :');
        }

        $html = "\n<fieldset class=\"pix-messages pix-{$level_html}\">";
        if ($label != null) {
            $html .= "\n    <legend>{$label}</legend>";
        }
        // Liste des erreurs
        foreach ($_SESSION['xErrors'][$level] as $error) {
            $html .= "\n    {$error->render()}";
        }
        $html .= "\n</fieldset>\n";

        if ($flush) {
            $_SESSION['xErrors'][$level] = array();
        }

        return $html;
    }

    /**
     * Retourne le tableau des notices
     * @name DataCenter::getNotices()
     * @access public
     *
     * @return array
     */
    public function getNotices() {
        if (!isset($_SESSION['xNotices'])) {
            $_SESSION['xNotices'] = array();
        }

        return $_SESSION['xNotices'];
    }

    /**
     * Retourne une notice particulière, en fonction de sa cible et de son
     * niveau
     * @name DataCenter::getNotice()
     * @access public
     * @param string $target
     * @param int $level
     *
     * @return MessageNotice
     */
    public function getNotice($target, $level = Level::LEVEL_SUCCESS) {
        if (isset($_SESSION['xNotices'][$level]) && isset($_SESSION['xNotices'][$level][$target])) {
            return $_SESSION['xNotices'][$level][$target];
        }

        return null;
    }

    /**
     * Retourne le message d'une notice particulière, en fonction de sa cible
     * et de son niveau
     * @name DataCenter::getMessageNotice()
     * @access public
     * @param string $target
     * @param int $level
     *
     * @return string
     */
    public function getMessageNotice($target, $level = Level::LEVEL_SUCCESS) {
        if (isset($_SESSION['xNotices'][$level]) && isset($_SESSION['xNotices'][$level][$target])) {
            return $_SESSION['xNotices'][$level][$target]->getMessage();
        }

        return null;
    }

    /**
     * Retourne les notices de succes
     * @name DataCenter::getMessageNotices()
     * @access public
     * @param int $level
     *
     * @return string
     */
    public function getMessageNotices($level = Level::LEVEL_SUCCESS) {
        if (isset($_SESSION['xNotices'][$level])) {
            return $_SESSION['xNotices'][$level];
        }

        return null;
    }

    /**
     * Permet de redéfinir le message d'une notice ciblée
     * @name DataCenter::setMessageNotice()
     * @access public
     * @param string $target
     * @param string $message
     * @param int $level
     *
     * @return bool true si la notice a été trouvée, false sinon
     */
    public function setMessageNotice($target, $message, $level = Level::LEVEL_SUCCESS) {
        if (isset($_SESSION['xNotices'][$level]) && isset($_SESSION['xNotices'][$level][$target])) {
            $_SESSION['xNotices'][$level][$target]->setMessage($message);
            return true;
        }

        return false;
    }

    /**
     * Retourne le tableau des erreurs
     * @name DataCenter::getErrors()
     * @access public
     *
     * @return array
     */
    public function getErrors() {
        if (!isset($_SESSION['xErrors'])) {
            $_SESSION['xErrors'] = array();
        }
        return $_SESSION['xErrors'];
    }

    /**
     * Retourne une erreur particulière, en fonction de sa cible et de son
     * niveau
     * @name DataCenter::getError()
     * @access public
     * @param string $target
     * @param int $level
     *
     * @return MessageError
     */
    public function getError($target, $level = Level::LEVEL_ERR) {
        if (isset($_SESSION['xErrors'][$level]) && isset($_SESSION['xErrors'][$level][$target])) {
            return $_SESSION['xErrors'][$level][$target];
        }

        return null;
    }

    /**
     * Retourne le message d'une erreur particulière, en fonction de sa cible
     * et de son niveau
     * @name DataCenter::getMessageError()
     * @access public
     * @param string $target
     * @param int $level
     *
     * @return string
     */
    public function getMessageError($target, $level = Level::LEVEL_ERR) {
        if (isset($_SESSION['xErrors'][$level]) && isset($_SESSION['xErrors'][$level][$target])) {
            return $_SESSION['xErrors'][$level][$target]->getMessage();
        }

        return null;
    }

    /**
     * Retourne les messages d'erreur
     * @name DataCenter::getMessageErrors()
     * @access public
     * @param int $level
     *
     * @return string
     */
    public function getMessageErrors($level = Level::LEVEL_ERR) {
        if (isset($_SESSION['xErrors'][$level])) {
            return $_SESSION['xErrors'][$level];
        }

        return null;
    }

    /**
     * Permet de redéfinir le message d'une erreur ciblée
     * @name DataCenter::setMessageError()
     * @access public
     * @param string $target
     * @param string $message
     * @param int $level
     *
     * @return bool true si l'erreur a été trouvée, false sinon
     */
    public function setMessageError($target, $message, $level = Level::LEVEL_ERR) {
        if (isset($_SESSION['xErrors'][$level]) && isset($_SESSION['xErrors'][$level][$target])) {
            $_SESSION['xErrors'][$target]->setMessage($message);
            return true;
        }

        return false;
    }

    /**
     * Retourne l'ensemble des messages sous forme HTML
     *
     * @name DataCenter::displayMessages()
     * @access public
     *
     * @param int $error_level
     * @param int $notice_level
     *
     * @return string
     */
    public function displayMessages($error_level = Level::LEVEL_ERR, $notice_level = Level::LEVEL_SUCCESS) {
        $html = $this->printErrors($error_level);
        $html .= $this->printNotices($notice_level);

        return $html;
    }

    /**
     * Retourne le router pour la gestion des urls
     *
     * @name DataCenter::getRouter()
     * @access public
     * @return pixxidTheme
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     *
     * @name setRouter
     * @access public
     * @param $value
     *
     * @return void
     */
    public function setRouter(router $value) {
        $this->router = $value;
        $this->router->setDc($this);
    }

    /**
     * Génère une url
     *
     * @name getUrl
     * @access public
     * @param string $controller
     * @param mixed string | null $module
     *
     * @return string
     */
    public function getUrl($controller, $module = null, $params = array()) {
        return $this->router->getUrl($controller, $module, $params);
    }

    /**
     * Gestion du debugger
     *
     * @name DataCenter::getDebugger()
     * @access public
     * @return debugger
     */
    public function getDebugger() {
        return $this->debugger;
    }

    public function setDebugger(debugger $value) {
        $this->debugger = $value;
    }

    /**
     * Lecture des données envoyées en JSON en post
     * @name DataCenter::setPHPInput()
     * @access public
     * @return void
     */
    public function setPHPInput() {
        $input = file_get_contents('php://input');
        $json_input = json_decode($input, true);

        // On positionne toutes les valeurs dans le datacenter
        if (is_array($json_input)) {
            foreach ($json_input as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Hash la chaîne de caractères passées en paramètre
     * @name DataCenter::hash()
     * @access public
     *
     * @param string $string
     * @param mixed null | string $hash the hash method (default sha256)
     * @param mixed null | string $salt the salt to use
     *
     * @return string
     */
    public function hash($string, $hash = null, $salt = null) {
        if ($hash === null) {
            $hash = 'sha256';
        }

        if ($salt !== null && is_string($salt)) {
            $string = $string . $salt;
        }

        return hash($hash, $string);
    }

}
