<?php

/**
 * Small Framework développé afin de fournir une méthode de développement pour les
 * applications Web et Mobile.
 *
 * @name        Xengine
 *
 * @copyright   PIXXID  26/05/2016
 * @license     /LICENCE.txt
 *
 * @since       1.0
 *
 * @author      D.M <dmeireles@pixxid.fr>
 */
namespace xEngine;

// CONSTANTS DU FRAMEWORK
 define('XENGINE_DIR', dirname(__FILE__));
 define('XENGINE_VERSION', '4');
 define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
 define('APPS_ROOT', dirname(dirname(dirname(XENGINE_DIR))));
 define('CONFIG_DIR', APPS_ROOT.'/config/');
 define('LOGS_DIR', APPS_ROOT.'/logs/');
 define('RESSOURCES_DIR', APPS_ROOT.'/ressources/');
 define('ASSETS_DIR', APPS_ROOT.'/ressources/assets/');
 define('LOCALE_DIR', APPS_ROOT.'/ressources/locale/');
 define('MODELS_DIR', APPS_ROOT.'/ressources/models/');
 define('VENDOR_DIR', APPS_ROOT.'/vendor/');
 define('PUBLIC_DIR', APPS_ROOT.'/public/');
 define('PUBLIC_ASSETS_DIR', APPS_ROOT.'/public/assets/');

 require XENGINE_DIR.'/database/DbConnection.class.php';
 require XENGINE_DIR.'/database/types/column.class.php';
 require XENGINE_DIR.'/database/types/constraint.class.php';
 require XENGINE_DIR.'/DataCenter.class.php';
 require XENGINE_DIR.'/Debugger.class.php';
 require XENGINE_DIR.'/exception/Exception_.class.php';
 require XENGINE_DIR.'/exception/Message.class.php';
 require XENGINE_DIR.'/exception/MessageError.class.php';
 require XENGINE_DIR.'/exception/MessageNotice.class.php';
 require XENGINE_DIR.'/exception/Level.class.php';
 require XENGINE_DIR.'/html/Head.class.php';
 require XENGINE_DIR.'/html/Meta.class.php';
 require XENGINE_DIR.'/html/Link.class.php';
 require XENGINE_DIR.'/html/Script.class.php';
 require XENGINE_DIR.'/Lang.class.php';
 require XENGINE_DIR.'/Router.class.php';
 require XENGINE_DIR.'/Theme.class.php';
 require XENGINE_DIR.'/tools/String_.class.php';
 //require(XENGINE_DIR . '/UrlRewrite.class.php');

 use xEngine\database\DbConnection;
 use xEngine\exception\Exception_;
 use xEngine\html\Head;

 class App
 {
     /**
      * Version de l'application.
      *
      * @var name
      */
     public $version = 1;
     /**
      * Nom du fichier de l'application regroupant les routes des modules.
      *
      * @var name
      */
     public $router = 'router';
     /**
      * Nom du fichier de configuration de la base de données.
      *
      * @var name
      */
     public $database = null;
     /**
      * Activation du mode debug.
      *
      * @var name
      */
     public $debug = false;
     /**
      * Controller de l'application.
      *
      * @var name
      */
     public $controller = null;
     /**
      * Vues de l'application.
      *
      * @var name
      */
     public $view = null;
     /**
      * Force l'authentification.
      *
      * @var name
      */
     public $signup = null;
     /**
      * Gestion des thèmes.
      *
      * @var name
      */
     public $theme = null;
     /**
      * Gestion dela traduction (GetText).
      *
      * @var name
      */
     public $lang = null;
     /**
      * Propriétés configurable pour le module.
      *
      * @var name
      */
     public $properties = null;
     /**
      * Lien sur le Datacenter.
      *
      * @var name
      */
     public $_DC = null;

     /**
      * Initialise le Framework.
      */
     public function __construct()
     {
         $this->controller = new \StdClass();
         $this->view = new \StdClass();
         $this->signup = new \StdClass();
         $this->theme = new \StdClass();
         $this->lang = new \StdClass();
         $this->properties = array();

         $this->controller->path = null;
         $this->controller->default = null;
         $this->controller->before = null;
         $this->controller->after = null;

         $this->view->path = null;

         $this->signup->required = false;
         $this->signup->action = null;
         $this->signup->suffix = null;

         $this->theme->path = '/assets/';
         $this->theme->name = '/default/';

         $this->lang->active = false;
         $this->lang->domain = 'common';
     }

     /**
      * Démarrage de l'application.
      */
     public function run()
     {
         $cfgDbConnection = array();
         $cfgSignupRequire = false;
         $cfgSignupController = '';
         $cfgSignupSuffix = '';
         $cfgUrlAnomynousValues = array();
         $cfgRequestMethod = $_SERVER['REQUEST_METHOD'];
         $cfgRoute = null;
         $actJump = false;
         $actName = null;
         $initController = 0;

         try {
             $_DC = new DataCenter();
             $_DC->setHead(new Head());
             $_DC->setDebugger(new Debugger());
             $_DC->getDebugger()->addBreakPoint('XEngine Start');

             // Enregistrement de tous les parametres GET/POST dans le DataCenter.
             $_DC->setArray($_GET);
             $_DC->setArray($_POST);
             $this->_DC = $_DC;

             // Données globale de l'applications
             (!empty($this->version)) ? define('XENGINE_APP_VERSION', $this->version) : define('XENGINE_APP_VERSION', '?');
             ((!empty($this->debug)) && ($this->debug == true)) ? $_DC->getDebugger()->setActive(true) : $_DC->getDebugger()->setActive(false);

             // Paramètrage des controllers
             $cfgCtrlFolder = (!empty($this->controller->path)) ? $this->controller->path : '';
             $cfgCtrlDefault = (!empty($this->controller->default)) ? $this->controller->default : '';
             $cfgCtrlBefore = (!empty($this->controller->before)) ? $this->controller->before : null;
             $cfgCtrlAfter = (!empty($this->controller->after)) ? $this->controller->after : null;
             $_DC->setFolderController($cfgCtrlFolder);

             // Paramétrage des vues
             $cfgViewPath = (!empty($this->view->path)) ? $this->view->path : '';
             $_DC->setFolderView($cfgViewPath);

             // Gestion du thème
             if (!empty($this->theme)) {
                 $_DC->setTheme(new Theme($this->theme->name, PUBLIC_ASSETS_DIR));
                 if (empty($this->theme->css)) {
                     $this->theme->css = 'default.css';
                 }
                 if (empty($this->theme->js)) {
                     $this->theme->js = 'default.js';
                 }
                 $_DC->getHead()->setThemeLink($_DC->getTheme()->getUrl().'/'.$this->theme->css);
                 $_DC->getHead()->setThemeScript($_DC->getTheme()->getUrl().'/'.$this->theme->js);
             } else {
                 $_DC->setTheme(new Theme('defaut', null));
             }

             // Congiguration de la Traduction gettext
             if (isset($this->lang)) {
                 $_DC->setLang(new Lang((string) $this->lang->active));
                 if (empty($this->lang->default)) {
                     $this->lang->default = 'fr_FR';
                 }
                 if (empty($this->lang->encoding)) {
                     $this->lang->encoding = 'UTF-8';
                 }
                 if ($_DC->getLang()->localize(LOCALE_DIR, $this->lang->domain, $this->lang->default, $this->lang->encoding) == false) {
                     $_DC->getDebugger()->addAlert($_DC->getLang()->getError());
                 }
                 // On marque les mots sui doivent etre traduits.
                 if ($_DC->getDebugger()->getActive() === true) {
                     $_DC->getLang()->marqueur = true;
                 }
             } else {
                 $_DC->setLang(new Lang(false));
             }

             // Connexion à la base de données
             if (!empty($this->database)) {
                 $_database = require CONFIG_DIR.$this->database.'.php';
                 if (isset($_database['connections'])) {
                     foreach ($_database['connections'] as $name => $conn) {
                         $_DC->setConn($name, new DbConnection($conn['driver'], $conn['host'], $conn['username'], $conn['password'], $conn['port'], $conn['database'], $conn['charset']));
                         if ($conn['auto-connect'] === true) {
                             $_DC->pConnect($name, false);
                             $cfgDbConnection[] = $name;
                         }
                     }
                 } else {
                     throw new Exception_("Le fichier de configuration '".CONFIG_DIR.$this->database.".php' n'est pas conforme");
                 }
             }

             // Authentification
             if (!empty($this->signup)) {
                 $cfgSignupRequire = $this->signup->required;
                 $cfgSignupController = $this->signup->action;
                 $cfgSignupSuffix = $this->signup->suffix;
                 // Nom de la session d'authentification a verifier
                 if (empty($cfgSignupSuffix)) {
                     $_DC->setSignupName('xEngine_signup');
                 } else {
                     $_DC->setSignupName('xEngine_signup_'.$cfgSignupSuffix);
                 }
                 // Positionne l'authentification comme non effectuée.
                 if ($_DC->getSignup() == null) {
                     $_DC->unvalidSignup();
                 }
             }

             // Chargement du fichier des configuration des routes.
             if (file_exists('./route.php')) {
                 $cfgRoute = require './route.php';

                 // Nom de l'application
                 (!empty($cfgRoute['name'])) ? define('XENGINE_APP_NAME', $cfgRoute['name']) : define('XENGINE_APP_NAME', '?');

                 // Propriété ajoutées dans la config
                 if (!empty($cfgRoute['properties'])) {
                     foreach ($cfgRoute['properties'] as $name => $value) {
                         $_DC->setProperty($name, $value);
                     }
                 }
             } else {
                 throw new Exception_("Le fichier de configuration 'route.php' est obligatoire !");
             }

             // Gestion du routeur
             if (!empty($this->router)) {
                 $_router = require CONFIG_DIR.$this->router.'.php';
                 if (isset($_router['routes'])) {
                     $_DC->setRouter(new Router($_router['routes'], $_DC->getDebugger()->getActive()));
                     $_DC->getRouter()->setActive(true);
                     // Interprétation de l'url anonyme pour décomposition dans le DataCenter
                     if ($_DC->getRouter()->decomposeUrl() != false) {
                         $_DC->set('controller', $_DC->getRouter()->getControllerName());
                         $cfgUrlAnomynousValues = $_DC->getRouter()->getParamsValues();
                     } else {
                         throw new Exception_($_DC->getRouter()->getError());
                     }
                     $_DC->getRouter()->createCache();
                 } else {
                     throw new Exception_("Le fichier de configuration '".CONFIG_DIR.$this->router.".php' n'est pas conforme");
                 }
             } else {
                 $_DC->setRouter(new Router(array(), false));
                 $_DC->getRouter()->setActive(false);
             }

             // Execution du controller "Before" (cf route.php)
             if (!empty($cfgCtrlBefore)) {
                 $_DC->getDebugger()->addBreakPoint("Ctrl Before Start {$cfgCtrlBefore}");
                 $_DC->executeController($cfgCtrlBefore);
                 $_DC->getDebugger()->addBreakPoint("Ctrl Before Stop {$cfgCtrlBefore}");
             }

             // Determine le controller à executer soit par url anonyme soit par ?controller=.
             // OWASP : Protection XSS en utilisant strip_tags
             ($_DC->get('controller') != null) ? $controllerToRun = strip_tags($_DC->get('controller')) : $controllerToRun = $cfgCtrlDefault;
             $_DC->setController($controllerToRun);
             // Jeton de sécurité (Token)
             // OWASP : CSRF
             $_DC->tokenInit();

             // Execution des controllers
             for ($cptControllers = 0; $cptControllers <= $initController; ++$cptControllers) {
                 $actFind = false;

                 // Lecture des informations de la route demandée
                 if (isset($cfgRoute['controllers'][$controllerToRun])) {
                     // Enregistrement des informations de configuration liées à Le controller
                     $ctrl = $cfgRoute['controllers'][$controllerToRun];
                     $actName = $controllerToRun;
                     (!empty($ctrl['signup'])) ? $actSignup = $ctrl['signup'] : $actSignup = $cfgSignupRequire;
                     (!empty($ctrl['view'])) ? $_DC->setView($ctrl['view']) : $_DC->setView('');
                     if (!empty($ctrl['folder'])) {
                         $lFolderController = $ctrl['folder'];
                         $lFolderView = $ctrl['folder'];
                     } else {
                         $lFolderController = $cfgCtrlFolder;
                         $lFolderView = $cfgViewPath;
                     }
                     // On positionne la redirection si necessaire
                     if (!empty($ctrl['redirect'])) {
                         $_DC->setRedirect($ctrl['redirect']);
                     }

                     // Utilisation des routeurs : Paramètres de Le controller
                     if ($_DC->getRouter()->getActive() == true && !$_DC->duringRedirect) {
                         $cfgUrlCount = 0;
                         if (isset($ctrl['params'])) {
                             foreach ($ctrl['params'] as $paramName => $paramValues) {
                                 $paramRequire = isset($paramValues['required']) ? $paramValues['required'] : false;
                                 $paramRegexp = isset($paramValues['regexp']) ? $paramValues['regexp'] : '.*';

                                 if (isset($cfgUrlAnomynousValues[$cfgUrlCount])) {
                                     $paramValue = $cfgUrlAnomynousValues[$cfgUrlCount];
                                     // Enregistrement dans le DataCenter
                                     $_DC->set($paramName, $paramValue);
                                 } elseif ($_DC->get($paramName) !== null) {
                                     // Si la valeur existe (setRedirect), on l'utilise
                                     $paramValue = $_DC->get($paramName);
                                 } else {
                                     $paramValue = null;
                                 }
                                 // Contrôle le paramètre obligatoire (option)
                                 if ($paramRequire == true && (!isset($paramValue) || $paramValue === '')) {
                                     throw new Exception_("URL ANONYME : Le paramètre {$paramName} est obligatoire.");
                                 }
                                 // Contrôle le format (option)
                                 if (isset($paramValue) && $paramValue !== '' && !preg_match('/'.$paramRegexp.'/', $paramValue)) {
                                     throw new Exception_("URL ANONYME : Le paramètre {$paramName} n'est pas au bon format.");
                                 }
                                 ++$cfgUrlCount;
                             }
                         }
                     }

                     // Force l'authentification si le module signup le demande
                     if (($actSignup === true) && ($_DC->getSignup() == false)) {
                         // Si je ne recois pas de login, j'affiche la vue qui contient le formulaire
                         if (!isset($_POST['login'])) {
                             $_DC->setView($cfgSignupController);
                             $actJump = true;     // On saute Le controller demandee si aucune identification et pas de login
                         } else {
                             // Si je ne suis pas identifié et que je recois login
                             // je l'enregistre dans le DataCenter en Session
                             if (isset($_POST['login'])) {
                                 $_DC->setP('login', (string) $_POST['login']);
                                 unset($_POST['login']);
                             }
                             // et j'execute Le controller '$cfgSignupController'
                             $controllerToRun = $cfgSignupController;
                             $_DC->setRedirect($cfgCtrlDefault);
                             $actJump = false;
                         }
                     }

                     // Execution du controller demandé
                     if ($actJump == false) {
                         $_DC->getDebugger()->addBreakPoint("Ctrl Start '".$controllerToRun."'");
                         $_DC->executeController($controllerToRun, $lFolderController);
                         $_DC->getDebugger()->addBreakPoint("Ctrl Stop '".$controllerToRun."'");
                     }

                     // Enregitrement du controller en cours pour la vue
                     $_DC->setController($controllerToRun);

                     // Affiche de la vue par défaut
                     if (!$_DC->isRedirect()) {
                         // Vue modifiée dans le fichier de config ou dans le controller
                         if ($_DC->getView() != '') {
                             $viewFileName = $_DC->getView();
                             $lFolderView = null;
                         } else {
                             // Vue correspondante au controller d'originie
                             $viewFileName = $controllerToRun;
                         }
                         // On inclue la vue
                         if (file_exists($_DC->includeView($viewFileName, $lFolderView)) == true) {
                             $_DC->getDebugger()->addBreakPoint("View Start '".$viewFileName."'");
                             $_DC->includeTpl($viewFileName, $lFolderView);
                             $_DC->getDebugger()->addBreakPoint("View Stop '".$viewFileName."'");
                         } else {
                             throw new Exception_('La Vue <Include>  "'.$viewFileName.'" est introuvable !');
                         }
                     } else {
                         $controllerToRun = $_DC->getRedirect();
                         $_DC->setRedirect('');
                         $_DC->setView('');
                         $_DC->duringRedirect = true;
                         // L'incrementation permet de repasser dans la boucle des controllers.
                         ++$initController;
                     }
                 } else {
                     throw new Exception_('Le controller "'.$controllerToRun."\" n'est pas definie dans le fichier de configuration !");
                 }
             }

             // Execution du controller "Before" (cf route.php)
             if (!empty($cfgCtrlAfter)) {
                 $_DC->getDebugger()->addBreakPoint("Ctrl After Start '".$cfgCtrlAfter."'");
                 $_DC->executeController($cfgCtrlAfter);
                 $_DC->getDebugger()->addBreakPoint("Ctrl After Start '".$cfgCtrlAfter."'");
             }

             // Fermeture des connexions ouvertes automatiquement
             foreach ($cfgDbConnection as $conn) {
                 $_DC->Disconnect($conn);
             }

             // Trace de fin
             $_DC->getDebugger()->addBreakPoint('XEngine Stop');
             if ($_DC->getDebugger()->getActive() === true) {
                 echo $_DC->getDebugger()->printBreakPoint();
                 echo '<br/>Alertes :';
                 print_r($_DC->getDebugger()->getAlert());
             }
         } catch (Exception_ $e) {
             header('HTTP/1.0 400 Bad Request');
             echo "<!DOCTYPE html><html><head><title>HTTP/1.0 400 Bad Request</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /></head><body>{$e->getHtmlMessage()}</body></html>";
             die();
         }

         return;
     }
 }
