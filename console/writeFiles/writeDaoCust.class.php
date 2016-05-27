<?php

/**
 * Classe permettant l'écriture des classes Dao personnalisable
 * d'acces aux donnees.
 *
 * @name      writeDaoCust
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright D.M 04/04/2016
 * @version   1.0
 */

namespace xEngine\Daogenerator;

require_once(dirname(__DIR__) . '/helper.php');
require_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'String_.class.php');

use \xEngine\Tools\String_;
use \xEngine\Console\helper;

class writeDaoCust
{
    /**
     * Ecriture automatique du fichier Dao personalise representant
     * une requette qui sera utilisee pour la personalisation.
     * generer le script (ex : mysql)
     * @param string $tableName Nom de la table
     * @param array() $columns Liste des champs de la table
     * @param bool $overWrite écrase le fichier si déjà existant
     * @param bool $verbose affiche l'action en cours
     * @return int Code d'erreur
     */
    public static function write($tableName, $columns, $overWrite = false, $verbose = false)
    {
        include(__DIR__ . DIRECTORY_SEPARATOR . 'datadict.inc.php');

        $class_name = String_::camelize($tableName);

        $folder =  dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR . 'ressources'
                . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'daoCust' . DIRECTORY_SEPARATOR;

        // Avant de générer le daoCust, on vérifie que le fichier n'existe pas déjà
        if (file_exists($folder . $class_name . "DaoCust.class.php") && !$overWrite) {
            if ($verbose) {
                echo helper::warning("Le fichier daoCust de {$tableName} existe déjà.\r\n");
            }
            return 1;
        }

        if ($verbose) {
            echo helper::info("Génération du fichier daoCust de {$tableName}\r\n");
        }

        $businessClass = '/../business/' . $class_name . ".class.php";

        $sql_read = "";
        $i = 0;

        $date = date('d/m/Y');

        // Entete
        $txt = <<<EOF
<?php

/**
 * Classe d'accès aux données generée automatiquement
 * par daoGenerator.
 * Cette classe a pour but d'être personnalisée, une fois
 * la personalisation effectuée, elle ne doit plus être regenerée.
 *
 * @name      {$class_name}DaoCust
 * @copyright PIXXID SARL - {$date}
 * @licence   /LICENCE.txt
 * @since     1.0
 * @author    D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\Models\DaoCust;

require_once(XENGINE_DIR . '/exception/Exception_.class.php');
require_once(__DIR__ . '{$businessClass}');

use \\xEngine\Exception\Exception_;
use \\xEngine\Models\Business\\{$class_name};

class {$class_name}DaoCust extends {$class_name}
{

    /**
     * Objet de connexion à la base de données
     * @access private
     * @var \\PDO
     */
    private \$conn;
    private \$message;

    /**
     * Constructeur
     *
     * @name {$class_name}DaoCust::__construct()
     * @access public
     * @param PDO \$conn
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
        $txt .= <<<EOF

    /**
     * Liste de tous les éléments de la table
     *
     * @name " . $class_name . "DaoCust::readAll()
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
                throw new Exception_(implode('-', \$stmt->errorInfo()));
            }

            return \$stmt->fetchAll(\$fetch_style);

        } catch (Exception_ \$e) {
            \$this->message = \$e->getMessage();
            return null;
        }
    }
}

EOF;

        // Ecriture du fichier
        return writeFile::write_r($folder, $class_name . 'DaoCust.class.php', $txt);
    }
}

