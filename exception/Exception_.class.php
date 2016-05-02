<?php

/**
 * Gestion personnalise des Exceptions
 *
 * @name        Exception_
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\exception;

use \xEngine\exception\Level;

class Exception_ extends \Exception {

    /**
     * Le message de l'exception
     * @access private
     * @var string
     */
    protected $xMessage = '';

    /**
     * Le niveau de l'exception
     * @access private
     * @var mixed null | int
     */
    protected $level = null;

    /**
     * Constructeur
     *
     * @name Exception_::__construct()
     * @access public
     * @param string $message Message d'erreur personnalise
     * @param mixed null|int $level Niveau de l'erreur
     *
     * @return void
     */
    public function __construct($message, $level = Level::LEVEL_WARN) {
        $this->xMessage = $message;
        $this->level = $level;

        parent :: __construct($message);
    }

    /**
     * Retourne l'heure systeme
     *
     * @name Exception_::getTime()
     * @access private
     *
     * @return string Heure du system
     */
    private function getTime() {
        return date('Y-m-d H:i:s');
    }

    /**
     * Methode retournant un message d'erreur complet et formate.
     *
     * @name Exception_::getError()
     * @access public
     *
     * @return string $msg Message d'erreur personnalise
     */
    public function getHtmlMessage() {
        // On retourne un message d'erreur complet pour nos besoins.
        $html = '<br/><font face="verdana" size="1" color="red">' . $this->getTime()
                . ' : <strong>' . $this->getmessage() . '</strong><br/>';

        return $html;
    }

    /**
     * Retourne le message de l'exception
     * pour les LEVEL_CRITIC
     * @name Exception_::getmessage()
     * @access public
     *
     * @return string
     */
    public function getMessage_() {
        return $this->xMessage;
    }

    /**
     * Retourne le niveau de l'exception
     * @name Exception_::getLevel()
     * @access public
     *
     * @return int
     */
    public function getLevel() {
        return $this->level;
    }

}
