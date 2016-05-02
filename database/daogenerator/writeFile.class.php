<?php

/**
 * Classe de gestion de fichier.
 *
 * @name    writeFile
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 18/09/2006
 * @package    xEngine.database.daogenerator
 * @version    1.0
 */

namespace xEngine\daogenerator;

class writeFile
{

    /**
     * Ecriture d'un fichier dans un repertoire (gestion de la recursivite
     * lors de la creation du repertoire).
     *
     * @param string $folder Repertoire de destination
     * @param string $file Nom du fichier
     * @param string $txt Texte a ecrire dans le fichier
     * @return int Code d'erreur
     */
    public static function write_r($folder, $file, $txt)
    {
        $code = 0;

        // Si on ce trouve sur windows, on remplace les "/" par des "\" pour que la creation
        // des repertoires en mode recursif fonctionne.
        if ((!empty($_SERVER["SystemRoot"])) && (substr_count(strtoupper($_SERVER["SystemRoot"]), 'WINDOW') > 0)) {
            $folder = str_replace("/", "\\", $folder);
        }

        try {
            // Si le fichier n'a pas encore ete cree, on cre le repertoire de destination
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }


            // Creation du fichier en mode ecriture, effacement de l'ancien contenu
            if (!$handle = fopen($folder . "" . $file, 'w')) {
                $code = -1;
                throw new Exception("Impossible d'ouvrir le fichier.");
            }
            // Ecrivons quelque chose dans notre fichier.
            if (fwrite($handle, $txt) === FALSE) {
                $code = -2;
                throw new Exception("Impossible d'ecrire dans le fichier.");
            }

            fclose($handle);
        } catch (Exception $e) {
            @fclose($handle);
            return $code;
        }

        return 1;
    }
}

