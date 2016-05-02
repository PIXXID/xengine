<?php

/**
 * Classe permettant l'écriture des classes métiers
 * d'accès aux données.
 *
 * @name    writeBusiness
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 18/09/2006
 * @package    xEngine.database.daogenerator
 * @version    1.0
 */

namespace xEngine\daogenerator;

require_once(XENGINE_DIR . '/tools/String_.class.php');

use \xEngine\tools\String_;

class writeBusiness {
    /**
     * Ecriture automatique du fichier Metier representant
     * la structure d'une table de base de donnee.
     * @param string $driver Type de la base de donnee sur laquel on va generer le script (ex : mysql)
     * @param string $appName Nom de l'application
     * @param string $tableName Nom de la table
     * @param array() $columns Liste des champs de la table
     * @return int Code d'erreur
     */
    public static function write($driver, $appName, $tableName, $columns)
    {
        include(XENGINE_DIR . '/database/daogenerator/datadict.inc.php');

        $txt = "";
        $folder = $_SERVER['DOCUMENT_ROOT'] . "/" . $appName . "/business/";
        $champ = "";
        $construct = "    public function __construct()\n    {\n";
        $getter = "";
        $setter = "";
        $primary = "";
        $tableName = $tableName;

        $class_name = String_::camelize($tableName);


        // Entete
        $txt .= "<?php\n";

        $txt .= "/**\n";
        $txt .= " * Classe d'accès aux données generée automatiquement par daoGenerator.\n";
        $txt .= " * ATTENTION : NE PAS LA MODIFIER !\n";
        $txt .= " *\n";
        $txt .= " * @name       " . $class_name . "\n";
        $txt .= " * @copyright  PIXXID SARL - " . date("d/m/Y") . "\n";
        $txt .= " * @licence    /LICENCE.txt\n";
        $txt .= " * @since      1.0\n";
        $txt .= " * @author     D.M <dmeireles@pixxid.fr>\n";
        $txt .= " */\n";
        $txt .= "\n";

        $txt .= "namespace xEngine\Models\Business;\n";
        $txt .= "\n";

        $txt .= "require_once(XENGINE_DIR . '/database/types/fieldString.class.php');\n";
        $txt .= "require_once(XENGINE_DIR . '/database/types/fieldInt.class.php');\n";
        $txt .= "require_once(XENGINE_DIR . '/database/types/fieldFloat.class.php');\n";
        $txt .= "require_once(XENGINE_DIR . '/database/types/fieldDate.class.php');\n";
        $txt .= "require_once(XENGINE_DIR . '/database/types/fieldDecimal.class.php');\n";
        $txt .= "\n";

        $txt .= "use \xEngine\database\\fieldString;\n";
        $txt .= "use \xEngine\database\\fieldInt;\n";
        $txt .= "use \xEngine\database\\fieldFloat;\n";
        $txt .= "use \xEngine\database\\fieldDate;\n";
        $txt .= "use \xEngine\database\\fieldDecimal;\n";

        $txt .= "\n";

        $txt .= "class " . $class_name . "\n{\n";

        // Liste des champs de la table.
        if ((!empty($columns)) && (is_array($columns))) {
            foreach ($columns as $record) {

            // Recherche de la correspondance du type de la base
            $key = array_search($record->getType(), $databaseType);
            $ctype = $businessType[$key];

            // Champ not null
            if ($record->getNotnull() == true)
                $cnotnull = "true";
            else
                $cnotnull = "false";

            // Champ Valuer par defaut
            if ($record->getDefaut() == null)
                $cdefaut = "null";
            else
                $cdefaut = "\"" . $record->getDefaut() . "\"";

            // On efface la valeur par defaur pour certain type
            if ((strtolower($record->getType()) == "set") || (strtolower($record->getType()) == "enum")) {
                $record->setLength(0);
            }

            // Liste des champs
            $champ .= "    private $" . strtolower($record->getName()) . ";\n";
            if ($ctype != "decimal") {
                $construct .= "        \$this->" . strtolower($record->getName()) . " = new field" . ucfirst($ctype) . "(\"" . strtolower($record->getName()) . "\", \"" . $record->getType() . "\", " . $record->getLength() . ", " . $cnotnull . ", " . $cdefaut . ");\n";
            } else {
                $construct .= "        \$this->" . strtolower($record->getName()) . " = new field" . ucfirst($ctype) . "(\"" . strtolower($record->getName()) . "\", \"" . $record->getType() . "\", " . $record->getLength() . ", " . $record->getScale() . "," . $cnotnull . ", " . $cdefaut . ");\n";
            }

            // Primary key
            if (($record->getConstraint() != null) && ($record->getConstraintType() == "PRI")) {
                $primary .= "        \$this->" . strtolower($record->getName()) . "->setConstraintValues(\"PRI\", \"" . $tableName . "\", \"" . strtolower($record->getName()) . "\");\n";
            }

            // Getter
            $methode_name = String_::camelize($record->getName());

            $getter .= "    public function get" . $methode_name. "()\n";
            $getter .= "    {\n";
            $getter .= "        return \$this->" . strtolower($record->getName()) . ";\n";
            $getter .= "    }\n";
            $getter .= "\n";
            $getter .= "    public function get" . $methode_name . "Value()\n";
            $getter .= "    {\n";
            $getter .= "        return \$this->" . strtolower($record->getName()) . "->readValue();\n";
            $getter .= "    }\n";


            // Setter
            $setter .= "    public function set" . $methode_name . "(field" . ucfirst($ctype) . " \$value)\n";
            $setter .= "    {\n";
            $setter .= "        \$this->" . strtolower($record->getName()) . " = \$value;\n";
            $setter .= "    }\n";
            $setter .= "\n";
            $setter .= "    public function set" . $methode_name . "Value(\$value)\n";
            $setter .= "    {\n";
            $setter .= "        \$this->" . strtolower($record->getName()) . "->writeValue(\$value);\n";
            $setter .= "    }\n";
            }
        }

        $txt .= $champ;
        $txt .= "\n";

        $txt .= $construct . "\n";
        $txt .= "        # Ajout des clés primaires\n";
        $txt .= $primary;
        $txt .= "    }";

        $txt .= "\n";
        $txt .= "\n";
        $txt .= "    # Getter et Setter\n";
        $txt .= $getter . "\n";
        $txt .= $setter;

        $txt .= "}\n\n";

        // Ecriture du fichier
        return writeFile::write_r($folder, $class_name . ".class.php", $txt);
    }
}

