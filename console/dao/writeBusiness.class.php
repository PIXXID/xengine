<?php

/**
 * Classe permettant l'écriture des classes métiers
 * d'accès aux données.
 *
 * @name      writeBusiness
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright D.M 04/04/2016
 * @version   1.0
 */

namespace xEngine\Daogenerator;

require_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'String_.class.php');

use \xEngine\Tools\String_;

class writeBusiness
{
    /**
     * Ecriture automatique du fichier Metier representant
     * la structure d'une table de base de donnee.
     * @param string $tableName Nom de la table
     * @param array() $columns Liste des champs de la table
     * @return int Code d'erreur
     */
    public static function write($tableName, $columns)
    {
        include(__DIR__ . DIRECTORY_SEPARATOR . 'datadict.inc.php');

        $folder =  dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR . 'ressources'
                . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'business' . DIRECTORY_SEPARATOR;
        $field = "";
        $construct = "    public function __construct()\n    {\n";
        $getter = "";
        $setter = "";
        $primary = "";

        $class_name = String_::camelize($tableName);

        $date = date('d/m/Y');

        // Entete
        $txt = <<<EOF
<?php

/**
 * Classe d'accès aux données generée automatiquement par daoGenerator.
 * ATTENTION : NE PAS LA MODIFIER !
 *
 * @name       $class_name
 * @copyright  PIXXID SARL - $date
 * @licence    /LICENCE.txt
 * @since      1.0
 * @author     D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\Mapping\Business;

require_once(XENGINE_DIR . '/database/types/fieldString.class.php');
require_once(XENGINE_DIR . '/database/types/fieldInt.class.php');
require_once(XENGINE_DIR . '/database/types/fieldFloat.class.php');
require_once(XENGINE_DIR . '/database/types/fieldDate.class.php');
require_once(XENGINE_DIR . '/database/types/fieldDecimal.class.php');

use xEngine\Database\\fieldString;
use xEngine\Database\\fieldInt;
use xEngine\Database\\fieldFloat;
use xEngine\Database\\fieldDate;
use xEngine\Database\\fieldDecimal;


class $class_name {

EOF;

        // Liste des champs de la table
        if (is_array($columns) &&  !empty($columns)) {
            foreach ($columns as $record) {

                // Recherche de la correspondance du type de la base
                $key = array_search($record->getType(), $databaseType);
                $type = $businessType[$key];

                // Champ not null
                if ($record->getNotnull() == true)
                    $notNull = "true";
                else
                    $notNull = "false";

                // Champ Valuer par defaut
                if ($record->getDefaut() == null)
                    $default = "null";
                else
                    $default = "\"" . $record->getDefaut() . "\"";

                // On efface la valeur par défaut pour certains types
                if ((strtolower($record->getType()) == "set") || (strtolower($record->getType()) == "enum")) {
                    $record->setLength(0);
                }

                // Liste des champs
                $field .= "    private $" . strtolower($record->getName()) . ";\n";
                if ($type != "decimal") {
                    $construct .= "        \$this->" . strtolower($record->getName()) . " = new field" . ucfirst($type) . "(\"" . strtolower($record->getName()) . "\", \"" . $record->getType() . "\", " . $record->getLength() . ", " . $notNull . ", " . $default . ");\n";
                } else {
                    $construct .= "        \$this->" . strtolower($record->getName()) . " = new field" . ucfirst($type) . "(\"" . strtolower($record->getName()) . "\", \"" . $record->getType() . "\", " . $record->getLength() . ", " . $record->getScale() . "," . $notNull . ", " . $default . ");\n";
                }

                // Primary key
                if (($record->getConstraint() != null) && ($record->getConstraintType() == "PRI")) {
                    $primary .= "        \$this->" . strtolower($record->getName()) . "->setConstraintValues(\"PRI\", \"" . $tableName . "\", \"" . strtolower($record->getName()) . "\");\n";
                }

                // Getter
                $methode_name = String_::camelize($record->getName());
                $record_name = strtolower($record->getName());

                $getter .= <<<EOF

    public function get{$methode_name}()
    {
        return \$this->{$record_name};
    }

    public function get{$methode_name}Value()
    {
        return \$this->{$record_name}->readValue();
    }

EOF;
                $ucType = ucfirst($type);
                $setter .= <<<EOF

    public function set{$methode_name}(field{$ucType} \$value)
    {
        \$this->{$record_name} = \$value;
    }

    public function set{$methode_name}Value(\$value)
    {
        \$this->{$record_name}->writeValue(\$value);
    }

EOF;
            }
        }

        $txt .= <<<EOF
$field

$construct
       // Ajout des clés primaires
$primary
    }

    // Getter et Setter
$getter
$setter
}

EOF;
        // Ecriture du fichier
        return writeFile::write_r($folder, $class_name . ".class.php", $txt);
    }
}

