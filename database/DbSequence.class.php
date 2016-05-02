<?php

/**
 * Objet permettant d'obtenir la sequence suivante pour
 * les tables ayant une cle primaire de type INT
 *
 * @name        DbSequence
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\database;

class DbSequence {

    /**
     * Fonction static permettant d'obtenir la séquence suivante
     *
     * @name DbSequence::next()
     *
     * @access public
     * @static
     * @param ADOConnection $conn Connexion valide à une base de données
     * @param string $table Table sur laquelle on doit obtenir la sequence suivante
     * @param string $column Nom de la colonne Primary Key de type numerique
     *
     * @return int Numero de séquence suivante
     */
    public static function next(ADOConnection $conn, $table, $column) {
        try {

            $sql = "SELECT {$column} FROM {$table} ORDER BY 1 DESC";
            $rs = $conn->SelectLimit($sql, 1);

            // Si il n'y a aucun enregistrement : insertion du premier
            if ($rs->RecordCount() == 0) {
                $id = 1;
            } else {
                while (!$rs->EOF) {
                    $id = $rs->fields[0];
                    break;
                }
                $id++;
            }
        } catch (exception $e) {
            return null;
        }

        return $id;
    }

}
