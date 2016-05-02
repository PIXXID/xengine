<?php

/**
 * Boite a outils concernants les commandes systemes.
 * ex : copy recursive..
 *
 * @date    14/12/07
 * @version     1.0
 * @author    d.meireles
 */

namespace xEngine\tools;

class System_ {

    /**
     * Copie d'un repertoire et de son contenu
     * @param String $source    - Repertoire a copier
     * @param String $dirDest    - Repertoire de destination
     * @return void
     */
    public static function copy($source, $dirDest) {
        @mkdir($dirDest, true);
        $hdir = @opendir($source);
        if (!empty($hdir)) {
            while ($item = readdir($hdir)) {
                if ($item == "." | $item == "..")
                    continue;
                if (is_dir($source . '/' . $item)) {
                    //     Repertoire => recursion
                    /* traitements */
                    SystemP::_copy($source . '/' . $item, $dirDest . '/' . $item);
                } else {
                    if (!@copy($source . '/' . $item, $dirDest . '/' . $item))
                        return false;
                }
            }
        }
        @closedir($hdir);

        return true;
    }

    /**
     * Permet de supprimer les emplacement des fichiers informatiques
     */
    public static function remove_dir($dir) {

        if (is_dir($dir)) {
            $dir = (substr($dir, -1) != "/") ? $dir . "/" : $dir;
            $openDir = @opendir($dir);

            while ($file = readdir($openDir)) {
                if (!in_array($file, array(".", ".."))) {
                    if (!is_dir($dir . $file)) {
                        if (@unlink($dir . $file) == false) {
                            return false;
                        }
                    } else {
                        SystemP::remove_dir($dir . $file);
                    }
                }
            }

            @closedir($openDir);
            if (@rmdir($dir) == false)
                return false;
        }

        return true;
    }

    /**
     * On verifie qu'un document ou/et un repertoire est present
     * dans le dossier
     *
     * si mode = 1 : on compte tous les fichiers et repertoires
     * si mode = 2 : on ne compte que les fichiers
     * si mode = 3 : on ne compte que les reportoires.
     *
     * @param String $lPath
     * @param int $mode
     * @return int
     */
    public static function ifExists($lPath, $mode = 1) {
        $lCount = 0;
        $lExit = false;
        $excludeFilesOrFolder = array(".", "..", "thumbs.db");

        if ((file_exists($lPath)) && ($handle = @opendir($lPath))) {
            while (($file = readdir($handle)) !== false) {

                // Ne pas comptabiliser les fichiers ou dossiers Exclus
                if (array_search(strtolower($file), $excludeFilesOrFolder) === FALSE) {
                    switch ($mode) {
                        case 1 : $lCount++;
                            $lExit = true;
                            break;
                        case 2 : if (is_file($lPath . $file)) {
                                $lCount++;
                                $lExit = true;
                            };
                            break;
                        case 3 : if (is_dir($lPath . $file)) {
                                $lCount++;
                                $lExit = true;
                            };
                            break;
                    }
                }
                if ($lExit == true)
                    break;
            }
            @closedir($handle);
        }
        return $lCount;
    }

    /**
     * Temps d'acces moyen a la ressource ( repertoire )
     *
     * @param string $lPath
     * @return String    Temp d'acces aux repertoire
     */
    /*
      public static function Accesstimesss($lPath) {

      // Start
      $timeStart = microtime(true);

      if ((file_exists($lPath))
      && ($handle = @opendir($lPath))) {
      while (($file = readdir($handle)) !== false) {
      if ($file != "." && $file != "..") {
      break;
      }
      }
      @closedir($handle);
      }

      $timeEnd = microtime(true);
      return round($timeEnd - $timeStart, 4);
      }
     *
     */

    public static function Accesstime($lPath) {

        // Start
        $timeStart = microtime(true);
        if (file_exists($lPath)) {
            if (($dir = scandir($lPath)) !== false) {
                /**/
            }
        }
        $timeEnd = microtime(true);
        return round($timeEnd - $timeStart, 4);
    }

}
