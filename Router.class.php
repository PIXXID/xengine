<?php

/**
 * Gestion du routage des url anonymes
 *
 * @name        Router
 * @copyright   PIXXID  28/07/2015
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

class Router {

    /**
     * Datacenter
     *
     * @access private
     * @var DataCenter
     */
    private $_DC = null;

    /**
     * Routage activé
     *
     * @access private
     * @var boolean
     */
    private $active = false;

    /**
     * Nom du controller demandée dans l'url
     *
     * @access private
     * @var boolean
     */
    private $controllerName = null;

    /**
     * Valeurs des paramètres envoyés dans l'url
     *
     * @access private
     * @var boolean
     */
    private $paramsValues = array();

    /**
     * Message d'erreur
     *
     * @access private
     * @var string
     */
    private $error = null;

    /**
     * Nom du fichier de config des routes
     * @access private
     * @var string
     */
    private $configFile = 'route.xml';

    /**
     * Fichiers de config à regénérer
     * @access private
     * @var array
     */
    private $cachePaths = array();

    /**
     * Modules en cache
     * @access private
     * @var array
     */
    private $cacheFiles = array();

    /**
     * Fichier de cache du route courant
     * @access private
     * @var string
     */
    private $routeCacheFile = null;

    /**
     * Données cachées après lecture des fichiers
     * @access private
     * @var array
     */
    private $cacheData = array();

    /**
     * Dernier route utilisé pour la génération d'une url
     * @access private
     * @var string
     */
    private $lastRoute = '';

    /**
     * Dernière controller utilisée pour la génération d'une url
     * @access private
     * @var string
     */
    private $lastController = '';

    /**
     * Dernière url de base générée
     * @access private
     * @var string
     */
    private $lastBaseUrl = '';

    /**
     * Initialisation du routeur.
     * Récupère l'ensemble des caches à regénérer, avec les routes correspondants
     * 
     * @param type $routes
     * @param type $debug
     */
    public function __construct($routes, $debug = false) {
        foreach ($routes as $name => $route) {
            $this->cacheFiles[] = $route['cache_name'];
            if ($name === XENGINE_APP_NAME) {
                $this->routeCacheFile = $route['cache_name'];
            }
            if ($debug || !file_exists(CONFIG_DIR . $route['cache_name'])) {
                if (!isset($this->cachePaths[$route['cache_name']])) {
                    $this->cachePaths[$route['cache_name']] = array($route['path']);
                } else {
                    $this->cachePaths[$route['cache_name']][] = $route['path'];
                }
            }
        }
        $this->cacheFiles = array_unique($this->cacheFiles);
    }

    /**
     * Génère les fichiers de cache des fichiers de configuration
     *
     * @name createCache
     * @access public
     *
     * @return bool
     */
    public function createCache() {
        $cache = array();
        foreach ($this->cachePaths as $path => $routes) {
            foreach ($routes as $route) {
                $configFile = DOCUMENT_ROOT . $route . $this->configFile;
                if (file_exists($configFile)) {
                    $config = simplexml_load_file($configFile);
                    if (isset($config->controllers)) {
                        $controllers = array();
                        foreach ($config->controllers[0]->controller as $controller) {
                            $params = array();
                            if (isset($controller->param)) {
                                foreach ($controller->param as $param) {
                                    if (isset($param['required']) && (string) $param['required'] === 'true') {
                                        $params[(string) $param['name']] = 'true';
                                    } else {
                                        $params[(string) $param['name']] = 'false';
                                    }
                                }
                            }
                            $controllers[(string) $controller['name']] = array('path' => $route, 'params' => $params);
                        }
                    }
                    $cache[(string) $config->config[0]['name']] = $controllers;
                }
            }
            // Ecriture du fichier de cache dans le répoertoire /config
            file_put_contents(CONFIG_DIR . $path, json_encode($cache));
            $cache = array();
        }
        // Lecture du cache du route courant
        $this->readCacheConfig();
    }

    /**
     * Retourne les chemins des fichiers de cache
     *
     * @name getCacheFiles
     * @access public
     *
     * @return array
     */
    public function getCacheFiles() {
        return $this->cacheFiles;
    }

    /**
     * Lit le cache du route courant
     *
     * @name readCacheConfig
     * @access public
     *
     * @return void
     */
    public function readCacheConfig() {
        if ($this->routeCacheFile !== null) {
            $file_path = CONFIG_DIR . $this->routeCacheFile;
            if (file_exists($file_path)) {
                $this->cacheData = json_decode(file_get_contents($file_path), true);
            }
        }
    }

    /**
     * Retourne le tableau du cache
     *
     * @name getCacheData
     * @access public
     *
     * @return array
     */
    public function getCacheData() {
        return $this->cacheData;
    }

    /*
     * Permet de lire dans une Url anonyme le controller demandée par l'utilisateur
     * ainsi que les paramètres suivant cette controller.
     *
     * @access public
     *
     * @name decomposeUrl
     * @access public
     * @return mixed string | false
     */

    public function decomposeUrl() {
        // On exclue la possibilité de saisir Le controller directement par l'url
        // TODO - A optimiser !!
        if (preg_match('/&|\?/', $_SERVER['REQUEST_URI'])) {
            $this->error = "URL ANONYME : L'url n'est pas conforme.";
            return false;
        }

        if (preg_match('/\<.*\>|%3C.*%3E/', $_SERVER['REQUEST_URI'])) {
            $this->error = "URL ANONYME : L'url contient des balises interdites.";
            return false;
        }

        // Nom du route - je supprime la racine du site pour ne garder que le route
        $route_name = str_ireplace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']);
        // On décompose le reste de l'url pour le traitement
        $route_name_array = explode('/', $route_name);
        // On supprime le nom du script (dernier élement)
        array_pop($route_name_array);
        // On recompose le nom du route => /apps/route1/sousroute2/controller/parametre/
        $route_name = implode('/', $route_name_array) . '/';
        // On supprime le nom du route dans l'url complète pour ne garder que Le controller et les params => /controller/parametre/
        $controllerName = str_ireplace($route_name, '', $_SERVER['REQUEST_URI']);
        // On sépare Le controller de la liste des paramètres
        $controllerNameArray = explode('/', $controllerName);
        // On enregistre le premier élément qui correspond à Le controller demandée.
        $this->controllerName = array_shift($controllerNameArray);

        // On enregistre les paramètres liés à Le controller
        if (isset($controllerNameArray)) {
            $this->paramsValues = $controllerNameArray;
        }

        return true;
    }

    /**
     * Génère l'url du route
     *
     * @name composeUrl
     * @access public
     *
     * @param string $controller
     * @param string $route
     * @return string
     */
    public function getUrl($controller, $route = null, $params_override = array()) {

        if ($route === null) {
            $route = XENGINE_APP_NAME;
        }

        if (!$this->active) {
            return '## Module urlAnonymous désactivé ##';
        }

        if (isset($this->cacheData[$route]) && isset($this->cacheData[$route][$controller])) {
            $controller_data = $this->cacheData[$route][$controller];

            $path = $controller_data['path'];
            $params = $controller_data['params'];

            if ($route !== $this->lastRoute || $controller !== $this->lastController) {
                $url = '//' . $_SERVER['HTTP_HOST'] . $path;

                // Supprime le dernier / si présent
                $url = preg_replace('/\/$/', '', $url);

                // sous la forme /home/controller/param
                $url .= '/' . $controller;

                $this->lastBaseUrl = $url;
                $this->lastRoute = $route;
                $this->lastController = $controller;
            } else {
                $url = $this->lastBaseUrl;
            }

            foreach ($params as $value => $required) {
                if (isset($params_override[$value])) {
                    $url .= '/' . $params_override[$value];
                } else if ($required == 'true' && $this->_DC->get($value) === null) {
                    return "## Le paramètre {$value} est obligatoire ##";
                } else {
                    $url .= '/' . $this->_DC->get($value);
                }
            }

            return $url;
        }

        return '## Pas de fichier en cache ##';
    }

    /**
     * Lecture du message d'erreur
     *
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Nom de Le controller après décomposition de l'url
     *
     * @name getControllerName
     * @access public
     * @return string
     */
    public function getControllerName() {
        return $this->controllerName;
    }

    /**
     * Liste des paramètres saisie dans l'url
     *
     * @name getParamsValues
     * @access public
     * @return array()
     */
    public function getParamsValues() {
        return $this->paramsValues;
    }

    /**
     * Positionne le DataCenter
     * @param \xEngine\DataCenter $_DC
     */
    public function setDc(DataCenter $_DC) {
        $this->_DC = $_DC;
    }

    /**
     * Retourne la valeur de l'activation du router
     * @return type
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Positionne la valeur d'activation du routeur
     * @param type $value
     */
    public function setActive($value) {
        $this->active = $value;
    }

}
