<?php

/**
 * Classe permettant la gestion de notices au sein de l'application
 *
 * @name      MessageNotice
 * @copyright PIXXID SARL - 08/03/2013
 * @licence   /LICENCE.txt
 * @since     1.0
 * @author    D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\exception;

use \xEngine\exception\Level;

class MessageNotice extends message {

    /**
     * Retrouve le message sous forme HTML
     *
     * @name message::render()
     * @access public
     *
     * @return string
     */
    public function render() {
        if ($this->level === Level::LEVEL_INFO) {
            $level = 'info';
        } else {
            $level = 'success';
        }

        return '<div class="pix-message pix-msg-notice pix-msg-level-' . $level . '">' . $this->message . '</div>';
    }

}
