<?php

/**
 * Colonne de type "Chaine de caractere"
 *
 * @name    colString
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 14/09/2006
 * @package    xEngine.database.types
 * @version    1.0
 */

namespace xEngine\Database;

class fieldString extends column {

    /**
     * Constructeur
     *
     * @name colString::__construct()
     * @access public
     * @param string  $name Nom de la colonne
     * @param int  $length Longeur de la colonne
     * @param boolean $notnull Colonne obligatoire
     * @param string $defaultValue Valeur par defaur
     * @return void
     */
    public function __construct($name, $type, $length, $notnull, $defaultValue) {
        $this->setName($name);
        $this->setLength($length);
        $this->setNotnull($notnull);
        $this->setDefaut($defaultValue);
        $this->setValue($defaultValue);
        $this->setType($type);
    }

    /**
     * Lecture de la valeur de la colonne
     *
     * @name colString::readValue()
     * @access public
     * @return string
     */
    public function readValue() {
        return $this->getValue();
    }

    /**
     * Ecriture de la valeur de la colonne
     *
     * @name colString::writeValue()
     * @access public
     * @param string $value Valeur de la colonne
     * @return void
     */
    public function writeValue($value) {
        $this->setValue($value);
    }

}
