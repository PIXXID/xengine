<?php
/**
 * Gestion des dao
 */

namespace xEngine\Console;

require_once(__DIR__ . '/helper.php');
require_once(dirname(__DIR__) . '/database/types/column.class.php');
require_once(dirname(__DIR__) . '/database/types/constraint.class.php');
require_once(dirname(__DIR__) . '/database/types/fieldDate.class.php');
require_once(dirname(__DIR__) . '/database/types/fieldDecimal.class.php');
require_once(dirname(__DIR__) . '/database/types/fieldFloat.class.php');
require_once(dirname(__DIR__) . '/database/types/fieldInt.class.php');
require_once(dirname(__DIR__) . '/database/types/fieldString.class.php');
require_once(dirname(__DIR__) . '/database/DbConnection.class.php');
require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'String_.class.php');
require_once(__DIR__ . '/dao/writeBusiness.class.php');
require_once(__DIR__ . '/dao/writeDao.class.php');
require_once(__DIR__ . '/dao/writeDaoCust.class.php');
require_once(__DIR__ . '/dao/writeFile.class.php');

use \xEngine\Database\DbConnection;
use \xEngine\Daogenerator\writeBusiness;
use \xEngine\Daogenerator\writeDao;
use \xEngine\Daogenerator\writeDaoCust;
use \xEngine\Daogenerator\writeFile;
use \xEngine\Database\column;
use \xEngine\Database\constraint;
use \xEngine\Database\fieldDate;
use \xEngine\Database\fieldDecimal;
use \xEngine\Database\fieldFloat;
use \xEngine\Database\fieldInt;
use \xEngine\Database\fieldString;
use \xEngine\Tools\String_;


class dao {

    /**
     * Connexion à la BDD
     * @var DbConnection
     */
    private $dbConnection;

    /**
     * Nom de la BDD
     * @var string
     */
    private $dbName;

    public function __construct() {
        $root = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR;
        $connFile = $root . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        // Lecture du fichier de connexion à la bdd
        if (file_exists($connFile)) {
            // Lecture du fichier
            $database = require($connFile);
            if (isset($database['connections'])) {
                // Connexion à la BDD
                try {
                    $this->dbConnection = new DbConnection($database['connections']['mysql']['driver'],
                        $database['connections']['mysql']['host'],
                        $database['connections']['mysql']['username'],
                        $database['connections']['mysql']['password'],
                        $database['connections']['mysql']['port'],
                        $database['connections']['mysql']['database'],
                        $database['connections']['mysql']['charset']);
                    $this->dbConnection->connect();
                    $this->dbName = $database['connections']['mysql']['database'];
                } catch (\Exception $e) {
                    echo helper::error($e->getMessage() . "\r\n");
                    return null;
                }
            } else {
                echo helper::error("Le config/database.php est mal configuré ! Le champ 'connections' est introuvable.\r\n");
            }
        } else {
            echo helper::error("Le fichier config/database.php est introuvable !\r\n");
        }
    }

    /**
     * Génère les dao pour l'ensemble des tables (ou seulement moduleName)
     * @param string $option1
     * @param string $option2
     * @param string $option3
     * @param string $option4
     *
     * @return bool
     */
    public function generate($option1 = null, $option2 = null, $option3 = null, $option4 = null) {

        // On gère les différentes options passées
        $generateAllModels = false;
        $generateDao = false;
        $generateDaoCust = false;
        $generateBusiness = false;
        $model = null;

        // On génère tous les modèles ?
        if ($option1 === '-a' || $option2 === '-a' || $option2 === '-a' || $option4 === '-a') {
            $generateAllModels = true;
        // On regarde maintenant si un modèle particulier a été demandé
        } elseif ($option1 != null && !in_array($option1, '-a', '-b', '-d', '-dc')) {
            $model = $option1;
        } elseif ($option2 != null && !in_array($option2, '-a', '-b', '-d', '-dc')) {
            $model = $option2;
        } elseif ($option3 != null && !in_array($option3, '-a', '-b', '-d', '-dc')) {
            $model = $option3;
        } elseif ($option4 != null && !in_array($option4, '-a', '-b', '-d', '-dc')) {
            $model = $option4;
        }

        // On génère les dao ?
        if ($option1 === '-d' || $option2 === '-d' || $option2 === '-d' || $option4 === '-d') {
            $generateDao = true;
        }

        // On génère les daoCust ?
        if ($option1 === '-dc' || $option2 === '-dc' || $option2 === '-dc' || $option4 === '-dc') {
            $generateDaoCust = true;
        }

        // On génère les business ?
        if ($option1 === '-b' || $option2 === '-b' || $option2 === '-b' || $option4 === '-b') {
            $generateDaoCust = true;
        }

        // Si aucune option n'a été passée, on génère tous les dao
        if (!$generateBusiness && !$generateDao && !$generateDaoCust) {
            $generateBusiness = true;
            $generateDao = true;
            $generateDaoCust = true;
        }

        $models = array();
        // Récupère l'ensemble des tables de la base
        $tables = $this->listTables();

        // On liste les tables afin que l'utilisateur valide celles qu'il veut, si l'option -a n'a pas été passée
        if (!$generateAllModels && $model === null) {
            echo helper::warning("Veuillez sélectionner les modèles à générer. Tappez [ENTER]/[o] pour valider le modèle, [n] pour le rejeter.\r\n");
            foreach ($tables as $table) {
                echo helper::success($table[0] . "\r\n");
                echo helper::info(">> ");
                $input = trim(fgets(STDIN));
                if (!in_array($input, array('n', 'N', 'no', 'NO', 'NON', 'non'))) {
                    $models[] = $table[0];
                }
            }
        // Si c'est un modèle particulier demandé
        } elseif ($model !== null) {
            // On vérifie que le modèle demandé est présent dans la liste des tables
            foreach ($tables as $table) {
                if (strtolower($model) === strtolower($table[0])) {
                    $models[] = $table[0];
                    break;
                }
            }
        // Sinon on les prend tous automatiquement
        } else {
            foreach ($tables as $table) {
                $models[] = $table[0];
            }
        }


        // On va créer chaque modèle
        foreach ($models as $model) {
            try {
            $columns = $this->listColumns($model);
            $fullColumns = $this->prepareColumns($model, $columns);
            // Création du fichier business
            if ($generateBusiness) {
                writeBusiness::write($model, $fullColumns);
            }

            // Création du fichier dao
            if ($generateDao) {
                writeDao::write($model, $fullColumns);
            }

            // Création du fichier daoCust
            if ($generateDaoCust) {
                writeDaoCust::write($model, $fullColumns);
            }

            } catch (\Exception $e) {
                echo helper::error("Génération pour {$model} : {$e->getMessage()}\r\n");
                return false;
            }

        }

        return true;
    }

    /**
     * __toString retourne l'aide du module
     */
    public function __toString() {
        return helper::dao();
    }

    /**
     * Liste les tables de la base de données

     * @return mixed array | null
     */
    public function listTables() {
        try {
            $sql = "SHOW TABLES FROM {$this->dbName}";
            $stmt = $this->dbConnection->getConn()->prepare($sql);
            if ($stmt->execute()) {
                return $stmt->fetchAll(\PDO::FETCH_NUM);
            }
        } catch (\Exception $e) {
            echo helper::error($e->getMessage() . "\r\n");
            return null;
        }
    }

    /**
     * Liste les colonnes d'une table
     * @param string $tableName
     *
     * @return mixed array | null
     */
    public function listColumns($tableName) {
        try {
            $sql = "SHOW FIELDS FROM {$tableName}";
            $stmt = $this->dbConnection->getConn()->prepare($sql);
            if ($stmt->execute()) {
                return $stmt->fetchAll(\PDO::FETCH_NUM);
            }
        } catch (\Exception $e) {
            echo helper::error($e->getMessage() . "\r\n");
            return null;
        }
    }

    /**
     * Prépare la liste des colonnes pour la génération des fichiers
     * @param string $tableName
     * @param array $columns
     *
     * @return array
     */
    public function prepareColumns($tableName, $columns) {
        $fullColumns = array();
        foreach ($columns as $item) {

            $column = new column();
            $column->setName($item[0]);
            $column->setLabel($item[0]);

            // type du champ
            $types = explode("(", $item[1]);
            $column->setType($item[0]);

            // Longeur du champ
            if ((is_array($types)) && (sizeof($types) > 1)) {
                $length = explode(")", $types[1]);

                if ($column->getType() == "decimal") {
                    $scale = explode(",", $length[0]);
                    $column->setLength($scale[0]);
                    $column->setScale($scale[1]);
                } else {
                    $column->setLength($length[0]);
                }
            }

            // Le champ peut etre Null
            if ((!empty($item[2])) && ($item[2] == "YES")) {
                $column->setNotnull(false);
            } else {
                $column->setNotnull(true);
            }

            // Valeur par défaut
            if (!empty($item[4])) {
                $column->setDefaut($item[4]);
            }

            // Mise a jour des primary key
            if ((!empty($item[3])) && ($item[3] == "PRI")) {
                $column->setConstraintValues("PRI", $tableName, $item[0]);
            }

            // On ajoute la colonne a la liste.
            $fullColumns[] = $column;
        }

        return $fullColumns;
    }
}
