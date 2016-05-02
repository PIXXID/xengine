<?php

/**
 * Colonne de type "Entier"
 *
 * @name        colInt
 * @author        D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 14/09/2006
 * @package        xEngine.database.types
 * @version        1.0
 */

namespace xEngine\database;

class fieldInt extends column {

    /**
     * Constructeur
     *
     * @name colInt::__construct()
     * @access public
     * @param string  $name Nom de la colonne
     * @param int  $length Longeur de la colonne
     * @param boolean $notnull Colonne obligatoire
     * @param int $defaultValue Valeur par defaur
     * @return void
     */
    public function __construct($name, $type, $length, $notnull, $defaultValue) {
        $this->setName($name);
        $this->setLength($length);
        $this->setNotnull($notnull);
        $this->setDefaut((int) $defaultValue);
        $this->setValue((int) $defaultValue);
        $this->setType($type);
    }

    /**
     * Lecture de la valeur de la colonne
     *
     * @name colInt::readValue()
     * @access public
     * @return int
     */
    public function readValue() {
        return $this->getValue();
    }

    /**
     * Ecriture de la valeur de la colonne
     *
     * @name colInt::writeValue()
     * @access public
     * @param int $value Valeur de la colonne
     * @return void
     */
    public function writeValue($value) {
        $this->setValue($value);
    }

}
