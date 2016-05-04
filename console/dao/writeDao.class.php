<?php

/**
 * Classe permettant l'écriture des classes Dao d'accès aux données
 *
 * @name      writeDao
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright D.M 04/04/2016
 * @version   1.0
 */

namespace xEngine\Daogenerator;

require_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'String_.class.php');

use \xEngine\Tools\String_;

class writeDao
{
    /**
     * Ecriture automatique du fichier Dao representant
     * les requettes de base d'acces a la tabele.
     * @param string $tableName Nom de la table
     * @param array() $columns Liste des champs de la table
     * @return int Code d'erreur
     */
    public static function write($tableName, $columns)
    {
        include(__DIR__ . DIRECTORY_SEPARATOR . 'datadict.inc.php');

        $class_name = String_::camelize($tableName);

        $folder =  dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR . 'ressources'
                . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR;
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

        $date = date('d/m/Y');

        // Entete
        $txt = <<<EOF
<?php

/**
 * Classe d'accès aux données generée automatiquement
 * ATTENTION : NE PAS LA MODIFIER !
 *
 * @name       {$class_name}Dao
 * @copyright  PIXXID SARL - {$date}
 * @licence    /LICENCE.txt
 * @since      1.0
 * @author     D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\Mapping\Dao;

require_once(XENGINE_DIR . '/exception/PixException.class.php');
require_once(__DIR__ . '{$businessClass}');

use \\xEngine\Exception\PixException;
use \\xEngine\Database\\fieldString;
use \\xEngine\Database\\fieldInt;
use \\xEngine\Database\\fieldFloat;
use \\xEngine\Database\\fieldDate;
use \\xEngine\Database\\fieldDecimal;
use \\xEngine\Mapping\Business\\{$class_name};

class {$class_name}Dao extends $class_name
{

    /**
     * Objet de connexion à la base de données
     * @access private
     * @var \PDO
     */
    private \$conn;
    private \$message;

    /**
     * Constructeur
     *
     * @name {$class_name}Dao::__construct()\
     * @access public
     * @param \\PDO  \$conn Connexion à la base de données
     * @return void
     */

    public function __construct(\\PDO \$conn)
    {
        parent::__construct();
        \$this->conn = \$conn;
    }

    public function getConn()
    {
        return \$this->conn;
    }

    public function setConn(\\PDO \$conn)
    {
        \$this->conn = \$conn;
    }

    public function getMessage()
    {
        return \$this->message;
    }

EOF;
        // Liste des champs de la table.
        if (is_array($columns) && !empty($columns)) {
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

        // On supprime la dernière virgule
        $sql_read = substr($sql_read, 0, strlen($sql_read) - 2);
        $param_insert = substr($param_insert, 0, strlen($param_insert) - 2);
        $array_insert = substr($array_insert, 0, strlen($array_insert) - 2);
        $param_update = substr($param_update, 0, strlen($param_update) - 2);

        // ================================================
        // Ecriture de la fonction readAll
        // ================================================
        $txt .= <<<EOF

    /**
     * Liste de tous les éléments de la table
     *
     * @name {$class_name}Dao::readAll()
     * @access public
     * @param int \$fetch_style \\PDO::FETCH_*
     *
     * @return mixed array | null
     */
    public function readAll(\$fetch_style = \\PDO::FETCH_ASSOC)
    {
        try {
            \$sql = "SELECT {$sql_read} FROM {$tableName} ";

            \$stmt = \$this->conn->prepare(\$sql);

            if (\$stmt->execute() === false) {
                throw new PixException(implode('-', \$stmt->errorInfo()));
            }

            return \$stmt->fetchAll(\$fetch_style);

        } catch (PixException \$e) {
            \$this->message = \$e->getMessage();
            return null;
        }
    }

EOF;

        // ================================================
        // Ecriture de la fonction read
        // ================================================
        $txt .= <<<EOF

    /**
     * Lecture d'un élement de la table
     *
     * @name {$class_name}Dao::read()
     * @access public
     *
     * @return boolean
     */
    public function read()
    {
        try {
            \$sql = "SELECT {$sql_read} FROM {$tableName} WHERE {$sql_where}";

            \$stmt = \$this->conn->prepare(\$sql);

            if (!\$stmt->execute(array({$param_array}))) {
                throw new PixException(implode('-', \$stmt->errorInfo()));
            }

            \$row = \$stmt->fetch(\\PDO::FETCH_ASSOC);

            if (\$row === false) {
                return false;
            }
$param_read

            return true;

        } catch (PixException \$e) {
            \$this->message = \$e->getMessage();
        }

        return false;
    }

EOF;

        // ================================================
        // Ecriture de la fonction insert
        // ================================================
        $txt .= <<<EOF

    /**
     * Insertion d'un élement dans la table
     *
     * @name {$class_name}Dao::insert()
     * @access public
     *
     * @return boolean
     */
    public function insert()
    {
        try {
            \$sql = "INSERT INTO {$tableName} ({$sql_read}) VALUES ({$param_insert})";

            \$stmt = \$this->conn->prepare(\$sql);

            if (\$stmt->execute(array({$array_insert})) === false) {
                throw new PixException(implode('-', \$stmt->errorInfo()));
            }

        } catch (PixException \$e) {
            \$this->message = \$e->getMessage();

            return false;
        }

        return true;
    }

EOF;

        // ================================================
        // Ecriture de la fonction update
        // ================================================
        $txt .= <<<EOF

    /**
     * Mise à jour d'un élément dans la table
     *
     * @name {$class_name}Dao::update()
     * @access public
     *
     * @return boolean
     */
    public function update()
    {
        try {
            \$sql = "UPDATE {$tableName} SET {$param_update}  WHERE {$sql_where} ";

            \$stmt = \$this->conn->prepare(\$sql);

            if (\$stmt->execute(array({$array_insert}, {$param_array})) === false) {
                throw new PixException(implode('-', \$stmt->errorInfo()));
            }

        } catch (PixException \$e) {
            \$this->message = \$e->getMessage();

            return false;
        }

        return true;
    }

EOF;

        // ================================================
        // Ecriture de la fonction delete
        // ================================================
        $txt .= <<<EOF

    /**
    * Suppression d'un élément dans la table
    *
    * @name {$class_name}Dao::delete()
    * @access public
    * @return boolean
    */
    public function delete()
    {
        try {
            \$sql = "DELETE FROM {$tableName} WHERE {$sql_where}";

            \$stmt = \$this->conn->prepare(\$sql);

            if (\$stmt->execute(array({$param_array})) === false) {
                throw new PixException(implode('-', \$stmt->errorInfo()));
            }

        } catch (PixException \$e) {
            \$this->message = \$e->getMessage();

            return false;
        }

        return true;
    }

}

EOF;

        // Ecriture du fichier
        return writeFile::write_r($folder, $class_name . "Dao.class.php", $txt);
    }
}

