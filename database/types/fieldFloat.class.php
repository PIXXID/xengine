<?php

/**
 * Colonne de type "Reel"
 *
 * @name    colFloat
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 14/09/2006
 * @package    xEngine.database.types
 * @version    1.0
 */

namespace xEngine\database;

class fieldFloat extends column {

    /**
     * Constructeur
     *
     * @name colFloat::__construct()
     * @access public
     * @param string  $name Nom de la colonne
     * @param int  $length Longeur de la colonne
     * @param int  $scale Nombre de decimal apres la virgule
     * @param boolean $notnull Colonne obligatoire
     * @param float $defaultValue Valeur par defaur
     * @return void
     */
    public function __construct($name, $type, $length, $scale, $notnull, $defaultValue) {
        $this->setName($name);
        $this->setLength($length);
        $this->setScale($scale);
        $this->setNotnull($notnull);
        $this->setDefaut($defaultValue);
        $this->setValue($defaultValue);
        $this->setType($type);
    }

    /**
     * Lecture de la valeur de la colonne
     *
     * @name colFloat::readValue()
     * @access public
     * @return float
     */
    public function readValue() {
        return $this->getValue();
    }

    /**
     * Ecriture de la valeur de la colonne
     *
     * @name colFloat::writeValue()
     * @access public
     * @param float $value Valeur de la colonne
     * @return void
     */
    public function writeValue($value) {
        $this->setValue($value);
    }

}
