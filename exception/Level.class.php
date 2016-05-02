<?php

/**
 * Classe unifiant l'utilisation des LEVEL_*
 *
 * @name        Level
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\exception;

class Level {

    const LEVEL_WARN = 1;
    const LEVEL_ERR = 2;
    const LEVEL_CRITIC = 3;
    const LEVEL_DEBUG = 4;
    const LEVEL_CONSTRAINT = 5;
    const LEVEL_INFO = 10;
    const LEVEL_SUCCESS = 11;
}
