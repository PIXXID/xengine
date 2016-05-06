<?php

/**
 * Types de base d'une colonne de base de donnee.
 * Ce type sera etandu pour permettre de definir tous les
 * types disponible dans une table.
 *     ex : INT, VARCHAR, DATE ...
 *
 * @name    column
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 14/09/2006
 * @package    xEngine.database.types
 * @version    1.0
 */

namespace xEngine\Database;

class column {

    /**
     * Nom technique la colonne
     * @access private
     * @var string
     */
    private $name = null;

    /**
     * Libelle de la colonne ( commentaire )
     * @access private
     * @var string
     */
    private $label = null;

    /**
     * Type de la colonne
     * @access private
     * @var string
     */
    private $type = null;

    /**
     * Valeur de la colonne
     * @access private
     * @var object
     */
    private $value = null;

    /**
     * Taille de la colonne
     * @access private
     * @var int
     */
    private $length = 0;

    /**
     * Precision de la colonne
     * @access private
     * @var int
     */
    private $scale = 0;

    /**
     * Colonne obligatoire
     * @access private
     * @var boolean
     */
    private $notnull = false;

    /**
     * Valeur par defaut de la colonne
     * @access private
     * @var object
     */
    private $defaut = null;

    /**
     * Type de contrainte sur la colonne (PK, FK)
     * @access private
     * @var string
     */
    private $constraint = null;

    /**
     * Commentaire complet de la colonne
     * @access private
     * @var string
     */
    private $comment = null;

    /**
     * Getter de l'attribut $name
     * @return string Nom de la colonne
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Getter de l'attribut $label
     * @return string Label de la colonne
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Getter de l'attribut $type
     * @return string Type de la colonne
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Getter de l'attribut $value
     * @return object Valeur de la colonne
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Getter de l'attribut $length
     * @return int Longeur de le colonne
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * Getter de l'attribut $scale
     * @return int Precision de la colonne
     */
    public function getScale() {
        return $this->scale;
    }

    /**
     * Getter de l'attribut $notnull
     * @return boolean Valeur obligatoire
     */
    public function getNotnull() {
        return $this->notnull;
    }

    /**
     * Getter de l'attribut $defaut
     * @return object Valeur par defaut
     */
    public function getDefaut() {
        return $this->defaut;
    }

    /**
     * Getter de l'attribut $constraint
     * @return constraint Contrainte sur la colonne
     */
    public function getConstraint() {
        return $this->constraint;
    }

    /**
     * Getter de l'attribut $constraint->type
     * @return string Type de la contrainte sur la colonne
     */
    public function getConstraintType() {
        return $this->constraint->getType();
    }

    /**
     * Getter de l'attribut $comment
     * @return string Commentaire de la colonne
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Setter de l'attribut $name
     * @param string $value Nom de la colonne
     * @return void
     */
    public function setName($value) {
        $this->name = $value;
    }

    /**
     * Setter de l'attribut $label
     * @param string $value Label de la colonne
     * @return void
     */
    public function setLabel($value) {
        $this->label = $value;
    }

    /**
     * Setter de l'attribut $type
     * @param string $value Type de la colonne
     * @return void
     */
    public function setType($value) {
        $this->type = $value;
    }

    /**
     * Setter de l'attribut $value
     * @param object $value Valeur de la colonne
     * @return void
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * Setter de l'attribut $length
     * @param int $value Longueur de la colonne
     * @return void
     */
    public function setLength($value) {
        $this->length = $value;
    }

    /**
     * Setter de l'attribut $scale
     * @param int $value Precision de la colonne
     * @return void
     */
    public function setScale($value) {
        $this->scale = $value;
    }

    /**
     * Setter de l'attribut $notnull
     * @param boolean $value Valeur obligatoire
     * @return void
     */
    public function setNotnull($value) {
        $this->notnull = $value;
    }

    /**
     * Setter de l'attribut $defaut
     * @param object $value Valeur par defaut
     * @return void
     */
    public function setDefaut($value) {
        $this->defaut = $value;
    }

    /**
     * Setter de l'attribut $constraint
     * @param constraint $value Type de contrainte
     * @return void
     */
    public function setConstraint(constraint $value) {
        $this->constraint = $value;
    }

    /**
     * Setter de l'attribut $constraint
     * @param string $type Type de contrainte
     * @param string $tableName Nom de la table
     * @param string $columnName Nom de la colonne
     * @return void
     */
    public function setConstraintValues($type, $tableName, $columnName) {
        $this->constraint = new constraint($type, $tableName, $columnName);
    }

    /**
     * Setter de l'attribut $comment
     * @param string $value Commentaire
     * @return void
     */
    public function setComment($value) {
        $this->comment = $value;
    }

}
