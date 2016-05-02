<?php

/**
 * Classe permettant la gestion de messages d'erreur au sein de l'application
 *
 * @name      MessageError
 * @copyright PIXXID SARL - 08/03/2013
 * @licence   /LICENCE.txt
 * @since     1.0
 * @author    D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\exception;

use \xEngine\exception\Level;

class MessageError extends message {

    /**
     * Retrouve le message sous forme HTML
     *
     * @name message::render()
     * @access public
     *
     * @return string
     */
    public function render() {
        if ($this->level === Level::LEVEL_WARN) {
            $level = 'warn';
        } else {
            $level = 'err';
        }

        return '<div class="pix-message pix-msg-error pix-msg-level-' . $level . '">' . $this->message . '</div>';
    }

}
