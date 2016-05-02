<?php

/**
 * Classe permettant l'ecriture des classes Dao personnalisable
 * d'acces aux donnees.
 *
 * @name    writeDaoCust
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 19/09/2006
 * @package    xEngine.database.daogenerator
 * @version    1.0
 */

namespace xEngine\daogenerator;

require_once(XENGINE_DIR . '/tools/String_.class.php');

use \xEngine\tools\String_;

class writeDaoCust
{
    /**
     * Ecriture automatique du fichier Dao personalise representant
     * une requette qui sera utilisee pour la personalisation.
     * @param string $driver Type de la base de donnee sur laquel on va
     * generer le script (ex : mysql)
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
        $folder = $_SERVER['DOCUMENT_ROOT'] . "/" . $appName . "/daoCust/";
        $businessClass = '/../business/' . $class_name . ".class.php";
        $sql_read = "";
        $i = 0;

        // Entete
        $txt .= "<?php\n";

        $txt .= "/**\n";
        $txt .= " * Classe d'accès aux données generée automatiquement\n";
        $txt .= " * par daoGenerator.\n";
        $txt .= " * Cette classe a pour but d'être personnalisée, une fois\n";
        $txt .= " * la personalisation effectuée, elle ne doit plus être regenerée.\n";
        $txt .= " *\n";
        $txt .= " * @name      " . $class_name . "DaoCust\n";
        $txt .= " * @copyright PIXXID SARL - " . date("d/m/Y") . "\n";
        $txt .= " * @licence   /LICENCE.txt\n";
        $txt .= " * @since     1.0\n";
        $txt .= " * @author    D.M <dmeireles@pixxid.fr>\n";
        $txt .= " */\n";
        $txt .= "\n";

        $txt .= "namespace xEngine\Models\DaoCust;\n";
        $txt .= "\n";

        //$txt .= "require_once(XENGINE_DIR . '/exception/Exception_.class.php');\n";
        $txt .= "require_once(__DIR__ . '{$businessClass}');\n";
        $txt .= "\n";

        $txt .= "use \xEngine\Exception\Exception_;\n";
        $txt .= "use \xEngine\Models\Business\\{$class_name};\n";

        $txt .= "\n";
        $txt .= "class " . $class_name . "DaoCust extends " . $class_name . "\n";
        $txt .= "{\n";

        $txt .= "    /**\n";
        $txt .= "     * Objet de connexion à la base de données\n";
        $txt .= "     * @access private\n";
        $txt .= "     * @var PDO\n";
        $txt .= "     */\n";
        $txt .= "    private \$conn;\n";
        $txt .= "    private \$message;\n";
        $txt .= "\n";

        $txt .= "    /**\n";
        $txt .= "     * Constructeur\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "DaoCust::__construct()\n";
        $txt .= "     * @access public\n";
        $txt .= "     * @param PDO \$conn\n";
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
        $txt .= "    }\n";
        $txt .= "    public function getMessage()\n";
        $txt .= "    {\n";
        $txt .= "        return \$this->message;\n";
        $txt .= "    }\n";

        // Liste des champs de la table.
        if ((!empty($columns)) && (is_array($columns))) {
            foreach ($columns as $record) {
                $sql_read .= $record->getName() . ", ";
            }
        }

        // Suppression de la dernière virgule
        $sql_read = substr($sql_read, 0, strlen($sql_read) - 2);

        // ================================================
        // Ecriture de la fonction readAll
        // ================================================
        $txt .= "\n";
        $txt .= "    /**\n";
        $txt .= "     * Liste de tous les éléments de la table\n";
        $txt .= "     *\n";
        $txt .= "     * @name " . $class_name . "DaoCust::readAll()\n";
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
        $txt .= "}\n\n";

        // Ecriture du fichier
        return writeFile::write_r($folder, $class_name . 'DaoCust.class.php', $txt);
    }
}

