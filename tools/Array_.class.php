<?php

/**
 * Boite à outils concernant les tableaux
 *
 * @name        Array_
 * @copyright   D.M  27/06/2008
 * @date        12/04/08
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\tools;

class Array_ {

    /**
     * Lecture de la valeur d'un champ contenu dans un tableau à 2 dimensions
     *
     * @access public
     * @param array  $lArray            Tableau 2 dimensions
     * @param string $lColumnSearch     Colonne dans laquelle il faut chercher
     * @param string $lColumnReturn     Colonne qui contient la valeur à retourner
     * @param string $lValue            Valeur pour laquel il faut retourner le label
     *
     * @return string
     */
    public static function searchInArray2($lArray, $lColumnSearch, $lColumnReturn, $lValue, $sep = " - ") {
        $lLabel = null;
        $lcpt = 1;
        if ($lArray == null) {
            return $lLabel;
        }

        // Si il y a des donnees dans le tableau
        foreach ($lArray as $key => $val) {
            // On cherche la valeur dans la colonne.
            if ($val[$lColumnSearch] == $lValue) {
                // Si on doit afficher plusieur colonne pour la valeur choisie
                if (is_array($lColumnReturn)) {
                    $lLabel = "";
                    foreach ($lColumnReturn as $key2 => $val2) {
                        $lLabel .= ($lcpt++ > 1 ? $sep : '') . $val[$val2];
                    }

                    $lcpt = 1;
                } else {
                    // Une seule colonne correspond a la valeur.
                    $lLabel = $val[$lColumnReturn];
                }
                break;
            }
        }

        return $lLabel;
    }

    /**
     * Renvoie la chaŵne de caractères d'un tableau de valeurs
     *
     * Exemple de retour pour plusieurs valeurs : array('bleu', 'jaune', 'vert');
     * en chaîne de caractères.
     *
     * @access public
     * @param array  $lArray    Tableau de paramètres
     *
     * @return string $lValue   Tableau sous forme de chaîne de caractères.
     */
    public static function toString($lArray) {
        $lValue = null;

        if (is_array($lArray)) {
            $lValue = "array(";
            for ($i = 0; $i < sizeof($lArray); $i++) {
                if ($i > 0) {
                    $lValue .= ', ';
                }
                $lValue .= "'" . $lArray[$i] . "'";
            }
            $lValue .= ');';
        } else {
            $lValue = $lArray;
        }

        return $lValue;
    }

}
