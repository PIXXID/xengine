<?php

/**
 * Classe de gestion de fichier.
 *
 * @name      writeFile
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright D.M 04/04/2016
 * @version   1.0
 */

namespace xEngine\Daogenerator;

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

        try {

            // Si le fichier n'a pas encore été crée, on crée le répertoire de destination
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            // Création du fichier en mode écriture, effacement de l'ancien contenu
            if (!$handle = fopen($folder . "" . $file, 'w')) {
                $code = -1;
                throw new Exception("Impossible d'ouvrir le fichier.");
            }

            // On écrit le contenu
            if (fwrite($handle, $txt) === FALSE) {
                $code = -2;
                throw new Exception("Impossible d'écrire dans le fichier.");
            }

            fclose($handle);
        } catch (Exception $e) {
            @fclose($handle);
            return $code;
        }

        return 1;
    }
}

