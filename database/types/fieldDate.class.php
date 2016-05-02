<?php

/**
 * Colonne de type "Date" au format par default YYYY-MM-DD HH:II:SS
 *
 * @name    colDate
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 14/09/2006
 * @package    xEngine.database.types
 * @version    1.0
 */

namespace xEngine\database;

class fieldDate extends column {

    /**
     * Constructeur
     *
     * @name colDate::__construct()
     * @access public
     * @param string  $name Nom de la colonne
     * @param int  $length Longeur de la colonne
     * @param boolean $notnull Colonne obligatoire
     * @param date $defaultValue Valeur par defaur
     * @return void
     */
    public function __construct($name, $type, $length, $notnull, $defaultValue) {
        $this->setName($name);
        $this->setLength($length);
        $this->setNotnull($notnull);
        $this->setDefaut($defaultValue);
        $this->setType($type);
    }

    /**
     * Lecture de la valeur de la colonne
     *
     * @name colDate::readValue()
     * @access public
     * @return date au format YYYY-MM-DD HH:II:SS
     */
    public function readValue() {
        return $this->getValue();
    }

    /**
     * Ecriture de la valeur de la colonne
     *
     * @name colDate::writeValue()
     * @access public
     * @param date $value Valeur de la colonne au format YYYY-MM-DD HH:II:SS
     * @return void
     */
    public function writeValue($value) {
        $this->setValue($value);
    }

}
