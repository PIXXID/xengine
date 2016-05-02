<?php

/**
 * Classe permettant l'écriture des classes Dao d'accès aux données
 *
 * @name    writeDao
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 19/09/2006
 * @package    xEngine.database.daogenerator
 * @version    1.0
 */

namespace xEngine\daogenerator;

require_once(XENGINE_DIR . '/tools/String_.class.php');

use \xEngine\tools\String_;

class writeDao
{
    /**
     * Ecriture automatique du fichier Dao representant
     * les requettes de base d'acces a la tabele.
     * @param string $driver Type de la base de donnee sur laquel on va generer le script (ex : mysql)
     * @param string $appName Nom de l'application
     * @param string $tableName Nom de la table
     * @param array() $columns Liste des champs de la table
     * @return int Code d'erreur
     */
    public static function write($driver, $appName, $tableName, $columns)
    {
        include(XENGINE_DIR . '/database/daogenerator/datadict.inc.php');

        $class_name = String_::camelize($tableName);

        $txt = "";
        $folder = $_SERVER['DOCUMENT_ROOT'] . "/" . $appName . "/dao/";
        $businessClass = '/../business/' . $class_name . ".class.php";
        $primary = array();
        $sql_read = "";
        $sql_where = "";
        $param_array = "";
        $param_read = "";
        $param_insert = "";
        $array_insert = "";
        $param_update = "";
        $nb_cols = 0;
        $i = 0;
        $j = 0;
        $k = 0;

        // Entete
        $txt .= "<?php\n";

        $txt .= "/**\n";
        $txt .= " * Classe d'accès aux données generée automatiquement\n";
        $txt .= " * ATTENTION : NE PAS LA MODIFIER !\n";
        $txt .= " *\n";
        $txt .= " * @name       " . $class_name . "Dao\n";
        $txt .= " * @copyright  PIXXID SARL - " . date("d/m/Y") . "\n";
        $txt .= " * @licence    /LICENCE.txt\n";
        $txt .= " * @since      1.0\n";
        $txt .= " * @author     D.M <dmeireles@pixxid.fr>\n";
        $txt .= " */\n";
        $txt .= "\n";

        $txt .= "namespace xEngine\Models\Dao;\n";
        $txt .= "\n";

        //$txt .= "require_once(XENGINE_DIR . '/exception/Exception_.class.php');\n";
        $txt .= "require_once(__DIR__ . '{$businessClass}');\n";
        $txt .= "\n";

        $txt .= "use \xEngine\Exception\Exception_;\n";
        $txt .= "use \xEngine\database\\fieldString;\n";
        $txt .= "use \xEngine\database\\fieldInt;\n";
        $txt .= "use \xEngine\database\\fieldFloat;\n";
        $txt .= "use \xEngine\database\\fieldDate;\n";
        $txt .= "use \xEngine\database\\fieldDecimal;\n";
        $txt .= "use \xEngine\Models\Business\\{$class_name};\n";

        $txt .= "\n";

        $txt .= "class " . $class_name . "Dao extends " . $class_name . "\n";
        $txt .= "{";
        $txt .= "\n";

        $txt .= "    /**\n";
        $txt .= "     * Objet de connexion à la base de données\n";
        $txt .= "     * @access private\n";
        $txt .= "     * @var \PDO\n";
        $txt .= "     */\n";
        $txt .= "    private \$conn;\n";
        $txt .= "    private \$message;\n";
        $txt .= "\n";

        $txt .= "    /**\n";
        $txt .= "     * Constructeur\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "Dao::__construct()\n";
        $txt .= "     * @access public\n";
        $txt .= "     * @param \PDO  \$conn Connexion à la base de données\n";
        $txt .= "     * @return void\n";
        $txt .= "     */\n";

        $txt .= "    public function __construct(\PDO \$conn)\n";
        $txt .= "    {\n";
        $txt .= "        parent::__construct();\n";
        $txt .= "        \$this->conn = \$conn;\n";
        $txt .= "    }\n\n";
        $txt .= "    public function getConn()\n";
        $txt .= "    {\n";
        $txt .= "        return \$this->conn;\n";
        $txt .= "    }\n\n";
        $txt .= "    public function setConn(\PDO \$conn)\n";
        $txt .= "    {\n";
        $txt .= "        \$this->conn = \$conn;\n";
        $txt .= "    }\n\n";
        $txt .= "    public function getMessage()\n";
        $txt .= "    {\n";
        $txt .= "        return \$this->message;\n";
        $txt .= "    }\n";

        // Liste des champs de la table.
        if ((!empty($columns)) && (is_array($columns))) {
            $nb_cols = sizeof($columns);

            foreach ($columns as $record) {
                $sql_read .= $record->getName() . ", ";
                $param_read .= "            \$this->set" . String_::camelize($record->getName()) . "Value(\$row[" . $i . "]);\n";
                $param_insert .= "?, ";
                $array_insert .= "\$this->get" . String_::camelize($record->getName()) . "Value(), ";

                $param_update .= $record->getName() . " = ?, ";

                // Primary key
                if (($record->getConstraint() != null) && ($record->getConstraintType() == "PRI")) {
                    $primary[$k] = $record->getName();

                    if ($k > 0) {
                        $sql_where .= " AND ";
                        $param_array .= ", ";
                    }
                    $sql_where .= $record->getName() . " = ?";
                    $param_array .= "\$this->get" . String_::camelize($record->getName()) . "Value()";
                    $k++;
                }

                $i++;
            }
        }

        // On supprime la derniere virgule
        $sql_read = substr($sql_read, 0, strlen($sql_read) - 2);
        $param_insert = substr($param_insert, 0, strlen($param_insert) - 2);
        $array_insert = substr($array_insert, 0, strlen($array_insert) - 2);
        $param_update = substr($param_update, 0, strlen($param_update) - 2);

        // ================================================
        // Ecriture de la fonction readAll
        // ================================================
        $txt .= "\n";
        $txt .= "    /**\n";
        $txt .= "     * Liste de tous les éléments de la table\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "Dao::readAll()\n";
        $txt .= "     * @access public\n";
        $txt .= "     * @param int \$fetch_style \PDO::FETCH_*\n";
        $txt .= "     *\n";
        $txt .= "     * @return mixed array | null\n";
        $txt .= "     */\n";
        $txt .= "    public function readAll(\$fetch_style = \PDO::FETCH_BOTH)\n";
        $txt .= "    {\n";
        $txt .= "        try {\n";
        $txt .= "            \$sql = \"SELECT " . $sql_read . " FROM " . $tableName . "\";\n";
        $txt .= "\n";
        $txt .= "            \$stmt = \$this->conn->prepare(\$sql);\n";
        $txt .= "            if (\$stmt->execute() === false) {\n";
        $txt .= "                throw new Exception_(implode('-', \$stmt->errorInfo()));\n";
        $txt .= "            }\n";
        $txt .= "            return \$stmt->fetchAll(\$fetch_style);\n";
        $txt .= "        } catch (Exception_ \$e) {\n";
        $txt .= "            \$this->message = \$e->getMessage();\n";
        $txt .= "            return null;\n";
        $txt .= "        }\n";
        $txt .= "    }\n";

        // ================================================
        // Ecriture de la fonction read
        // ================================================
        $txt .= "\n";
        $txt .= "    /**\n";
        $txt .= "     * Lecture d'un élement de la table\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "Dao::read()\n";
        $txt .= "     * @access public\n";
        $txt .= "     *\n";
        $txt .= "     * @return boolean\n";
        $txt .= "     */\n";
        $txt .= "    public function read()\n";
        $txt .= "    {\n";
        $txt .= "        try {\n";
        $txt .= "            \$sql = \"SELECT " . $sql_read . " FROM " . $tableName . "\n";
        $txt .= "                    WHERE " . $sql_where . "\";\n";
        $txt .= "\n";
        $txt .= "            \$stmt = \$this->conn->prepare(\$sql);\n";
        $txt .= "            if (!\$stmt->execute(array(" . $param_array . "))) {\n";
        $txt .= "                throw new Exception_(implode('-', \$stmt->errorInfo()));\n";
        $txt .= "            }\n";
        $txt .= "\n";
        $txt .= "            \$row = \$stmt->fetch(\PDO::FETCH_BOTH);\n";
        $txt .= "\n";
        $txt .= "            if (\$row === false) {\n";
        $txt .= "                return false;\n";
        $txt .= "            }\n";
        $txt .= $param_read;
        $txt .= "\n";
        $txt .= "            return true;\n";
        $txt .= "\n";
        $txt .= "        } catch (Exception_ \$e) {\n";
        $txt .= "            \$this->message = \$e->getMessage();\n";
        $txt .= "        }\n";
        $txt .= "\n";
        $txt .= "        return false;\n";
        $txt .= "    }\n";

        // ================================================
        // Ecriture de la fonction insert
        // ================================================
        $txt .= "\n";
        $txt .= "    /**\n";
        $txt .= "     * Insertion d'un élement dans la table\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "Dao::insert()\n";
        $txt .= "     * @access public\n";
        $txt .= "     *\n";
        $txt .= "     * @return boolean\n";
        $txt .= "     */\n";
        $txt .= "    public function insert()\n";
        $txt .= "    {\n";
        $txt .= "        try {\n";
        $txt .= "            \$sql = \"INSERT INTO " . $tableName . " (" . $sql_read . ")\n";
        $txt .= "                    VALUES (" . $param_insert . ")\";\n";
        $txt .= "\n";
        $txt .= "            \$stmt = \$this->conn->prepare(\$sql);\n";
        $txt .= "            if (\$stmt->execute(array(" . $array_insert . ")) === false) {\n";
        $txt .= "                throw new Exception_(implode('-', \$stmt->errorInfo()));\n";
        $txt .= "            }\n";
        $txt .= "        } catch (Exception_ \$e) {\n";
        $txt .= "            \$this->message = \$e->getMessage();\n";
        $txt .= "\n";
        $txt .= "            return false;\n";
        $txt .= "        }\n";
        $txt .= "\n";
        $txt .= "        return true;\n";
        $txt .= "    }\n";


        // ================================================
        // Ecriture de la fonction update
        // ================================================
        $txt .= "\n";
        $txt .= "    /**\n";
        $txt .= "     * Mise à jour d'un élément dans la table\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "Dao::update()\n";
        $txt .= "     * @access public\n";
        $txt .= "     *\n";
        $txt .= "     * @return boolean\n";
        $txt .= "     */\n";
        $txt .= "    public function update()\n";
        $txt .= "    {\n";
        $txt .= "        try {\n";
        $txt .= "            \$sql = \"UPDATE " . $tableName . " SET\n";
        $txt .= "                     " . $param_update . "\n";
        $txt .= "                     WHERE " . $sql_where . "\";\n";
        $txt .= "\n";
        $txt .= "            \$stmt = \$this->conn->prepare(\$sql);\n";
        $txt .= "            if (\$stmt->execute(array(" . $array_insert . ", " . $param_array . ")) === false) {\n";
        $txt .= "                throw new Exception_(implode('-', \$stmt->errorInfo()));\n";
        $txt .= "            }\n";
        $txt .= "        } catch (Exception_ \$e) {\n";
        $txt .= "            \$this->message = \$e->getMessage();\n";
        $txt .= "\n";
        $txt .= "            return false;\n";
        $txt .= "        }\n";
        $txt .= "\n";
        $txt .= "        return true;\n";
        $txt .= "    }\n";


        // ================================================
        // Ecriture de la fonction delete
        // ================================================
        $txt .= "\n";
        $txt .= "    /**\n";
        $txt .= "    * Suppression d'un élément dans la table\n";
        $txt .= "    *\n";
        $txt .= "    * @name " . $class_name . "Dao::delete()\n";
        $txt .= "    * @access public\n";
        $txt .= "    * @return boolean\n";
        $txt .= "    */\n";
        $txt .= "    public function delete()\n";
        $txt .= "    {\n";
        $txt .= "        try {\n";
        $txt .= "            \$sql = \"DELETE FROM " . $tableName . " WHERE " . $sql_where . "\";\n";
        $txt .= "\n";
        $txt .= "            \$stmt = \$this->conn->prepare(\$sql);\n";
        $txt .= "            if (\$stmt->execute(array(" . $param_array . ")) === false) {\n";
        $txt .= "                throw new Exception_(implode('-', \$stmt->errorInfo()));\n";
        $txt .= "            }\n";
        $txt .= "        } catch (Exception_ \$e) {\n";
        $txt .= "            \$this->message = \$e->getMessage();\n";
        $txt .= "\n";
        $txt .= "            return false;\n";
        $txt .= "        }\n";
        $txt .= "\n";
        $txt .= "        return true;\n";
        $txt .= "    }\n";

        $txt .= "}\n\n";

        // Ecriture du fichier
        return writeFile::write_r($folder, $class_name . "Dao.class.php", $txt);
    }
}

