<?php

/**
 * Contrainte sur une colonne de table de base de donnee
 * deux type possiblt : PK ou FK
 *
 * @name        constraint
 * @author        D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 15/09/2006
 * @package        xEngine.database.types
 * @version        1.0
 */

namespace xEngine\database;

class constraint {

    /**
     * Type de contrainte
     * @access private
     * @var string
     */
    private $type = null;

    /**
     * Nom de la table sur laquel la contrainte est active
     * @access private
     * @var string
     */
    private $tableName = null;

    /**
     * Nom de la colonne sur laquel la contrainte est active
     * @access private
     * @var string
     */
    private $columnName = null;

    /**
     * Constructeur
     *
     * @name constraint::__construct()
     * @access public
     * @param string $type Type de contrainte
     * @param string $tableName Nom de la table cible
     * @param string $columnName Nom de la colonne cible
     * @return void
     */
    public function __construct($type, $tableName, $columnName) {
        $this->setType($type);
        $this->setTableName($tableName);
        $this->setColumnName($columnName);
    }

    /**
     * Getter de l'attribut $type
     * @return string Type de la contrainte
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Getter de l'attribut $tableName
     * @return string Nom de la table cible
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * Getter de l'attribut $columnName
     * @return string Nom de la colonne cible
     */
    public function getColumnName() {
        return $this->columnName;
    }

    /**
     * Setter de l'attribut $type
     * @param string $value Type de la contrainte
     * @return void
     */
    public function setType($value) {
        $this->type = $value;
    }

    /**
     * Setter de l'attribut $tableName
     * @param string $value Nom de la table cible
     * @return void
     */
    public function setTableName($value) {
        $this->tableName = $value;
    }

    /**
     * Setter de l'attribut $columnName
     * @param string $value Nom de la colonne cible
     * @return void
     */
    public function setColumnName($value) {
        $this->columnName = $value;
    }

}

?>
