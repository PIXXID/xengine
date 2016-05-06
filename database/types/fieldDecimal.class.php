<?php

/**
 * Colonne de type "Decimal"
 *
 * @name    colDecimal
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 25/09/2006
 * @package    xEngine.database.types
 * @version    1.0
 */

namespace xEngine\Database;

class fieldDecimal extends column {

    /**
     * Constructeur
     *
     * @name fieldDecimal::__construct()
     * @access public
     * @param string  $name Nom de la colonne
     * @param int  $length Longeur de la colonne
     * @param int  $scale Nombre de decimal apres la virgule
     * @param boolean $notnull Colonne obligatoire
     * @param decimal $defaultValue Valeur par defaur
     * @return void
     */
    public function __construct($name, $type, $length, $scale, $notnull, $defaultValue) {
        $this->setName($name);
        $this->setType($type);
        $this->setLength($length);
        $this->setScale($scale);
        $this->setNotnull($notnull);
        $this->setDefaut($defaultValue);
        $this->setValue($defaultValue);
    }

    /**
     * Lecture de la valeur de la colonne
     *
     * @name fieldDecimal::readValue()
     * @access public
     * @return decimal
     */
    public function readValue() {
        return $this->getValue();
    }

    /**
     * Ecriture de la valeur de la colonne
     *
     * @name fieldDecimal::writeValue()
     * @access public
     * @param decimal $value Valeur de la colonne
     * @return void
     */
    public function writeValue($value) {
        $this->setValue($value);
    }

}
