<?php

/**
 * Small Framework développé afin de fournir une méthode de développement pour les
 * applications Web et Mobile
 *
 * @name        index
 * @copyright   PIXXID 27/07/2015
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

// CONSTANTS DU FRAMEWORK
define('XENGINE_DIR', dirname(__FILE__));
define('XENGINE_VERSION', "4");
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('APPS_ROOT', dirname(dirname(XENGINE_DIR)));
define('CONFIG_DIR', APPS_ROOT . '/config/');
define('LOGS_DIR', APPS_ROOT . '/logs/');
define('RESSOURCES_DIR', APPS_ROOT . '/ressources/');
define('ASSETS_DIR', APPS_ROOT . '/ressources/assets/');
define('LOCALE_DIR', APPS_ROOT . '/ressources/locale/');
define('MODELS_DIR', APPS_ROOT . '/ressources/models/');
define('VENDOR_DIR', APPS_ROOT . '/vendor/');

require(XENGINE_DIR . '/database/DbConnection.class.php');
require(XENGINE_DIR . '/database/types/column.class.php');
require(XENGINE_DIR . '/database/types/constraint.class.php');
require(XENGINE_DIR . '/DataCenter.class.php');
require(XENGINE_DIR . '/Debugger.class.php');
require(XENGINE_DIR . '/exception/Exception_.class.php');
require(XENGINE_DIR . '/exception/Message.class.php');
require(XENGINE_DIR . '/exception/MessageError.class.php');
require(XENGINE_DIR . '/exception/MessageNotice.class.php');
require(XENGINE_DIR . '/exception/Level.class.php');
require(XENGINE_DIR . '/html/Head.class.php');
require(XENGINE_DIR . '/html/Meta.class.php');
require(XENGINE_DIR . '/html/Link.class.php');
require(XENGINE_DIR . '/html/Script.class.php');
require(XENGINE_DIR . '/Lang.class.php');
require(XENGINE_DIR . '/Router.class.php');
require(XENGINE_DIR . '/Theme.class.php');
require(XENGINE_DIR . '/tools/String_.class.php');
//require(XENGINE_DIR . '/UrlRewrite.class.php');

use \xEngine\database\DbConnection;
use \xEngine\exception\Exception_;
use \xEngine\html\Head;

$cfgDbConnection = array();
$cfgSignupRequire = false;
$cfgSignupController = "";
$cfgSignupSuffix = "";
$cfgUrlAnomynousValues = array();
$cfgRequestMethod = $_SERVER['REQUEST_METHOD'];
$cfgXml = null;
$actFind = false;
$actFolder = "";
$actJump = false;
$actLib = "";
$actName = "";
$actRedirect = "";
$initController = 0;


try {

    $_DC = new DataCenter();
    $_DC->setHead(new Head());
    $_DC->setDebugger(new Debugger());
    $_DC->getDebugger()->addBreakPoint("XEngine Start");

    // Enregistrement de tous les parametres GET/POST dans le DataCenter.
    $_DC->setArray($_GET);
    $_DC->setArray($_POST);

    /*
      |--------------------------------------------------------------------------
      | Lecture du fichier de configuration
      |--------------------------------------------------------------------------
      | Lecture du fichier route.xml contenant toutes les informations de
      | configuration de l'application.
      |
     */
    if (file_exists('./route.xml')) {
        $cfgXml = simplexml_load_file('./route.xml');
    } else {
        throw new Exception_("Le fichier de configuration 'route.xml' est obligatoire !");
    }

    if (is_object($cfgXml)) {

        /*
         * GLOBAL
         */
        (!empty($cfgXml->config[0]["name"])) ? define('XENGINE_APP_NAME', (string) $cfgXml->config[0]["name"]) : define('XENGINE_APP_NAME', '?');
        (!empty($cfgXml->config[0]["version"])) ? define('XENGINE_APP_VERSION', (string) $cfgXml->config[0]["version"]) : define('XENGINE_APP_VERSION', '?');
        ((string) $cfgXml->config[0]["debug"] === 'true') ? $_DC->getDebugger()->setActive(true) : $_DC->getDebugger()->setActive(false);

        /*
         * CONNEXION BDD
         */
        if ((string) $cfgXml->config[0]["database"] !== 'false') {
            $_database = require CONFIG_DIR . (string) $cfgXml->config[0]["database"] . '.php';
            if (isset($_database['connections'])) {
                foreach ($_database['connections'] as $name => $conn) {
                    $_DC->setConn($name, new DbConnection($conn['driver'], $conn['host'], $conn['username'], $conn['password'], $conn['port'], $conn['database'], $conn['charset']));
                    if ($conn['auto-connect'] === true) {
                        $_DC->pConnect($name, false);
                        $cfgDbConnection[] = $name;
                    }
                }
            } else {
                throw new Exception_("Le fichier de configuration '" . CONFIG_DIR . (string) $cfgXml->config[0]["database"] . ".php' n'est pas conforme");
            }
        }

        /*
         * ROUTER
         */
        if ((string) $cfgXml->config[0]["router"] !== 'false') {
            $_router = require CONFIG_DIR . (string) $cfgXml->config[0]["router"] . '.php';
            if (isset($_router['routes'])) {
                $_DC->setRouter(new Router($_router['routes'], $_DC->getDebugger()->getActive()));
                $_DC->getRouter()->setActive(true);
                // Interprétation de l'url anonyme pour décomposition dans le DataCenter
                if ($_DC->getRouter()->decomposeUrl() != false) {
                    $_DC->set("controller", $_DC->getRouter()->getControllerName());
                    $cfgUrlAnomynousValues = $_DC->getRouter()->getParamsValues();
                } else {
                    throw new Exception_($_DC->getRouter()->getError());
                }
                $_DC->getRouter()->createCache();
            } else {
                throw new Exception_("Le fichier de configuration '" . CONFIG_DIR . (string) $cfgXml->config[0]["router"] . ".php' n'est pas conforme");
            }
        } else {
            $_DC->setRouter(new Router(array(), false));
            $_DC->getRouter()->setActive(false);
        }

        /*
         * CONTROLLERS
         */
        $cfgCtrlFolder = (!empty($cfgXml->config[0]->controllers["path"])) ? (string) $cfgXml->config[0]->controllers["path"] : "";
        $cfgCtrlDefault = (!empty($cfgXml->config[0]->controllers["default"])) ? (string) $cfgXml->config[0]->controllers["default"] : "";
        $cfgCtrlBefore = (!empty($cfgXml->config[0]->controllers["before"])) ? (string) $cfgXml->config[0]->controllers["before"] : null;
        $cfgCtrlAfter = (!empty($cfgXml->config[0]->controllers["after"])) ? (string) $cfgXml->config[0]->controllers["after"] : null;
        $_DC->setFolderController($cfgCtrlFolder);

        /*
         * VIEWS
         */
        $cfgViewPath = (string) $cfgXml->config[0]->views["path"];
        $_DC->setFolderView($cfgViewPath);

        /*
         * THEMES
         */
        if (isset($cfgXml->config[0]->themes)) {
            $_DC->setTheme(new Theme((string) $cfgXml->config[0]->themes["name"], (string) $cfgXml->config[0]->themes["path"]));
            $_DC->getHead()->setThemeLink($_DC->getTheme()->getUrl() . "/default.css");
            $_DC->getHead()->setThemeScript($_DC->getTheme()->getUrl() . "/default.js");
        } else {
            $_DC->setTheme(new Theme("defaut", null));
        }

        /*
         * LANG GETTEXT
         */
        if (isset($cfgXml->config[0]->language)) {
            $_DC->setLang(new Lang((string) $cfgXml->config[0]->language["active"]));
            if ($_DC->getLang()->localize(LOCALE_DIR, (string) $cfgXml->config[0]->language["domain"], (string) $cfgXml->config[0]->language["default"], (string) $cfgXml->config[0]->language["encoding"]) == false) {
                $_DC->getDebugger()->addAlert($_DC->getLang()->getError());
            }
            // On marque les mots sui doivent etre traduits.
            if ($_DC->getDebugger()->getActive() === true) {
                $_DC->getLang()->marqueur = true;
            }
        } else {
            $_DC->setLang(new Lang(false));
        }

        /*
         * AUTHENTIFICATION
         */
        if (!empty($cfgXml->config[0]->signup["required"])) {
            $cfgSignupRequire = (string) $cfgXml->config[0]->signup["required"];
            $cfgSignupController = (string) $cfgXml->config[0]->signup["controller"];
            $cfgSignupSuffix = (string) $cfgXml->config[0]->signup["suffix"];
            // Nom de la session d'authentification a verifier
            if (empty($cfgSignupSuffix)) {
                $_DC->setSignupName("xEngine_signup");
            } else {
                $_DC->setSignupName("xEngine_signup_" . $cfgSignupSuffix);
            }
            // Positionne l'authentification comme non effectuée.
            if ($_DC->getSignup() == null) {
                $_DC->unvalidSignup();
            }
        }

        /*
         * URLREWRITE
         * Gestion de la reecriture d'url
         */
        /*
          if (isset($cfgXml->config[0]->urlrewrite)) {
          $_DC->setUrlRewrite(new UrlRewrite((string) $cfgXml->config[0]->urlrewrite["active"]));

          if ($_DC->getUrlRewrite()->localize((string) $cfgXml->config[0]->urlrewrite["folder"], (string) $cfgXml->config[0]->urlrewrite["domain"], (string) $cfgXml->config[0]->urlrewrite["default"]) == false) {
          // Ajout d'une alerte dans la console de debug.
          $_DC->getDebugger()->addAlert($_DC->getUrlRewrite()->getError());
          }

          if ($_DC->getUrlRewrite()->getActive() == true) {
          $_DC->getUrlRewrite()->setDataCenter($_DC);

          // Decryptage de l'url si c'est possible.
          $_DC->getUrlRewrite()->decryptUrl($_SERVER["REQUEST_URI"]);
          }
          } */

        /*
         * PROPERTIES
         */
        foreach ($cfgXml->properties->property as $prop) {
            $_DC->setProperty((string) $prop['name'], (string) $prop['value']);
        }
    } else {
        throw new Exception_("Un problème est survenu lors de la lecture du fichier de configuration 'routes.xml' !<br/>Vérifiez la syntaxe du fichier.");
    }


    /*
      |--------------------------------------------------------------------------
      | Gestion des evenements (controller)
      |--------------------------------------------------------------------------
      | Controllers demandees par l'utilisateur et renseigne dans le fichier
      | de configuration.
      |
     */
    // Execution du controller "Before" (cf route.xml)
    if (!empty($cfgCtrlBefore)) {
        $_DC->getDebugger()->addBreakPoint("Ctrl Before Start '" . $cfgCtrlBefore . "'");
        $_DC->executeController($cfgCtrlBefore);
        $_DC->getDebugger()->addBreakPoint("Ctrl Before Stop '" . $cfgCtrlBefore . "'");
    }

    // Determine le controller à executer soit par url anonyme soit par ?controller=.
    // OWASP : Protection XSS en utilisant strip_tags
    ($_DC->get("controller") != null) ? $controllerToRun = strip_tags($_DC->get("controller")) : $controllerToRun = $cfgCtrlDefault;
    $_DC->setController($controllerToRun);

    //  Possiblite d'enchener plusieurs controllers (utilisation des $_DC->setRedirect()
    for ($cptControllers = 0; $cptControllers <= $initController; $cptControllers++) {
        $actFind = false;
        // Recherche de Le controller demandée dans la liste des controllers
        foreach ($cfgXml->controllers->controller as $ctrl) {
            if ($ctrl['name'] == $controllerToRun) {
                // Trouvé !
                $actFind = true;
                // Enregistrement des informations de configuration liées à Le controller
                $actName = (string) $ctrl['name'];
                (!empty($ctrl['lib'])) ? $actLib = (string) $ctrl['lib'] : $actLib = "";
                (!empty($ctrl['signup'])) ? $actSignup = (string) $ctrl['signup'] : $actSignup = $cfgSignupRequire;
                (!empty($ctrl['redirect'])) ? $actRedirect = (string) $ctrl['redirect'] : $actRedirect = "";
                (!empty($ctrl['view'])) ? $_DC->setView((string) $ctrl['view']) : $_DC->setView("");
                (!empty($ctrl['folder'])) ? $actFolder = (string) $ctrl['folder'] : $actFolder = "";
                // Désactivation de l'affichage des traces du mode debug
                if (!empty($ctrl['debug-trace']) && $ctrl['debug-trace'] == "false") {
                    $_DC->getDebugger()->setActive(false);
                }
                // On positionne la redirection si necessaire
                $_DC->setRedirect($actRedirect);
                // Utilisation des routerus : Paramètres de Le controller
                if ($_DC->getRouter()->getActive() == true && !$_DC->duringRedirect) {
                    $cfgUrlCount = 0;
                    if (isset($ctrl->param)) {
                        foreach ($ctrl->param as $ctrlParam) {
                            $paramName = (string) $ctrlParam['name'];
                            $paramRequire = (string) $ctrlParam['required'];
                            $paramRegexp = isset($ctrlParam['regexp']) ? (string) $ctrlParam['regexp'] : '.*';

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
                            if ($paramRequire == "true" && (!isset($paramValue) || $paramValue === '')) {
                                throw new Exception_("URL ANONYME : Le paramètre {$paramName} est obligatoire.");
                            }
                            // Contrôle le format (option)
                            if (isset($paramValue) && $paramValue !== '' && !preg_match('/' . $paramRegexp . '/', $paramValue)) {
                                throw new Exception_("URL ANONYME : Le paramètre {$paramName} n'est pas au bon format.");
                            }
                            $cfgUrlCount++;
                        }
                    }
                }
                break;
            }
        }

        // Si Le controller demande n'a pas ete trouve dans le fichier de config
        if ($actFind == false) {
            throw new Exception_("Le controller \"" . $controllerToRun . "\" n'est pas definie dans le fichier de configuration !");
        }

        /*
          |--------------------------------------------------------------------------
          | Sécurisation des modules
          |--------------------------------------------------------------------------
          | Contrôle si l'authentification a été validé, sinon on force la redirection
          | sur le controller en charge de l'identification
          |
         */
        if (($actSignup === 'true') && ($_DC->getSignup() == false)) {
            // Si je ne recois pas de login, j'affiche la vue qui contient le formulaire
            if (!isset($_POST["login"])) {
                $_DC->setView($cfgSignupController);
                $actJump = true;     // On saute Le controller demandee si aucune identification et pas de login
            } else {
                // Si je ne suis pas identifié et que je recois login
                // je l'enregistre dans le DataCenter en Session
                if (isset($_POST["login"])) {
                    $_DC->setP("login", (string) $_POST["login"]);
                    unset($_POST["login"]);
                }
                // et j'execute Le controller '$cfgSignupController'
                $controllerToRun = $cfgSignupController;
                $_DC->setRedirect($cfgCtrlDefault);
                $actJump = false;
            }
        }

        // Jeton de sécurité (Token)
        // OWASP : CSRF
        $_DC->tokenInit();

        /*
          |--------------------------------------------------------------------------
          | Execution du controller demandé
          |--------------------------------------------------------------------------
          | Si l'on trouve le fichier *.controller.php
          | $actJump = true force a ne pas executer le controller
          |
         */
        if (empty($actFolder)) {
            $lFolderController = $cfgCtrlFolder;
        } else {
            $lFolderController = $actFolder;
        }
        if ($actJump == false) {
            $_DC->getDebugger()->addBreakPoint("Ctrl Start '" . $controllerToRun . "'");
            $_DC->executeController($controllerToRun, $lFolderController);
            $_DC->getDebugger()->addBreakPoint("Ctrl Stop '" . $controllerToRun . "'");
        }

        // Redirection forcée a partir du controller ou du fichier de config.
        if ($_DC->isRedirect()) {
            $controllerToRun = $_DC->getRedirect();
            $_DC->setRedirect("");
            $_DC->setView("");
            $_DC->duringRedirect = true;
            // L'incrementation permet de repasser dans la boucle des controllers.
            $initController++;
        } else {

            // Enregitrement du controller en cours pour la vue
            $_DC->setController($controllerToRun);

            /*
              |--------------------------------------------------------------------------
              | Affichage de la vue
              |--------------------------------------------------------------------------
              | Si aucune redirection demandée, afffichage de la vue correspondante
              | au controller OU vue demandée dans le fichier de config
              |
             */
            if ($_DC->getView() != "") {
                // Vue modifiée dans le fichier de config ou dans le controller
                $viewFileName = $_DC->getView();
                $lFolderView = null;
            } else {
                // Vue correspondante au controller d'originie
                $viewFileName = $controllerToRun;
                if (empty($actFolder)) {
                    $lFolderView = $cfgViewPath;
                } else {
                    $lFolderView = $actFolder;
                }
            }
            // On inclue la vue
            if (file_exists($_DC->includeView($viewFileName, $lFolderView)) == true) {
                $_DC->getDebugger()->addBreakPoint("View Start '" . $viewFileName . "'");
                $_DC->includeTpl($viewFileName, $lFolderView);
                $_DC->getDebugger()->addBreakPoint("View Stop '" . $viewFileName . "'");
            } else {
                throw new Exception_("La Vue <Include>  \"" . $viewFileName . "\" est introuvable !");
            }
        }
    }
    // Supprime l'objet simplexml_load_file
    unset($cfgXml);

    // Execution du controller "Before" (cf route.xml)
    if (!empty($cfgCtrlAfter)) {
        $_DC->getDebugger()->addBreakPoint("Ctrl After Start '" . $cfgCtrlAfter . "'");
        $_DC->executeController($cfgCtrlAfter);
        $_DC->getDebugger()->addBreakPoint("Ctrl After Start '" . $cfgCtrlAfter . "'");
    }

    // Fermeture des connexions ouvertes automatiquement
    foreach ($cfgDbConnection as $conn) {
        $_DC->Disconnect($conn);
    }
} catch (Exception_ $e) {
    header("HTTP/1.0 400 Bad Request");
    echo "<!DOCTYPE html><html><head><title>HTTP/1.0 400 Bad Request</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /></head><body>{$e->getHtmlMessage()}</body></html>";
    die();
}


/*
  |--------------------------------------------------------------------------
  | Mode Debug activé
  |--------------------------------------------------------------------------
  | Affichage du temps d'execution du script dans le mode debug.
  |
 */
$_DC->getDebugger()->addBreakPoint("XEngine Stop");
if ($_DC->getDebugger()->getActive() === true) {
    echo $_DC->getDebugger()->printBreakPoint();
    echo("<br/>Alertes :");
    print_r($_DC->getDebugger()->getAlert());
}
// On remet a 0 le centre de données.
$_DC->cancel();
unset($_DC);
