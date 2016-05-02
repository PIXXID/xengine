<?php

/**
 * Classe permettant la gestion de messages au sein de l'application
 *
 * @name      Message
 * @copyright PIXXID SARL - 08/03/2013
 * @licence   /LICENCE.txt
 * @since     1.0
 * @author    D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\exception;

use \xEngine\exception\Level;

class Message {

    /**
     * Le message
     * @access protected
     * @var string
     */
    protected $message;

    /**
     * L'élément cible si existant
     * @access protected
     * @var string
     */
    protected $target;

    /**
     * Le niveau du message
     * @access protected
     * @var int
     */
    protected $level;

    /**
     * Crée l'objet message
     *
     * @access public
     * @param string $message
     * @param mixed null | string $target
     * @param int $level
     *
     * @return void
     */
    public function __construct($message, $target = null, $level = Level::LEVEL_INFO) {
        $this->message = $message;
        $this->level = $level;
        $this->target = $target;
    }

    /**
     * Définit le message
     *
     * @name message::setMessage()
     * @access public
     * @param string $message
     *
     * @return void
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * Retrouve le message
     *
     * @name message::getMessage()
     * @access public
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Définit l'élément cible
     * @name message::setTarget()
     * @access public
     * @param int $level
     *
     * @return void
     */
    public function setTarget($target) {
        $this->target = $target;
    }

    /**
     * Retourne la cible du message
     * @name message::getTarget()
     * @access public
     *
     * @return string
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * Définit le niveau du message
     * @name message::setLevel()
     * @access public
     * @param int $level
     *
     * @return void
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * Retourne le niveau du message
     * @name message::getLevel()
     * @access public
     *
     * @return int
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * Retrouve le message sous forme HTML
     *
     * @name message::render()
     * @access public
     *
     * @return string
     */
    public function render() {
        return '<div class="pix-message">' . $this->message . '</div>';
    }

}
