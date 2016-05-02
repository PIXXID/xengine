<?php
/**
 * daoGenerator :: permet la generation automatique
 * des classes PHP d'acces au donnees stockee en base
 * de donnee.
 *
 * @name    daoGenerator
 * @author    D.M <dmeireles@pixxid.fr>
 * @copyright    D.M 17/09/2006
 * @package    xEngine.database.daogenerator
 * @version    1.0
 */
define('XENGINE_DIR', __DIR__ . '/../../');

require_once(XENGINE_DIR . '/database/DbConnection.class.php');
require_once(XENGINE_DIR . '/exception/Exception_.class.php');
require_once(XENGINE_DIR . '/database/types/column.class.php');
require_once(XENGINE_DIR . '/database/daogenerator/writeBusiness.class.php');
require_once(XENGINE_DIR . '/database/daogenerator/writeDao.class.php');
require_once(XENGINE_DIR . '/database/daogenerator/writeDaoCust.class.php');
require_once(XENGINE_DIR . '/database/daogenerator/writeFile.class.php');

use \xEngine\database\DbConnection;
use \xEngine\daogenerator\writeBusiness;
use \xEngine\daogenerator\writeDao;
use \xEngine\daogenerator\writeDaoCust;

@session_start();

// ----------------------------------------------------
// Initialisation des variables
// ----------------------------------------------------
$msg_alert = "";
$listTables = array();
$columns = array();
$i = 0;
$j = 0;
$mconn = null;

if (empty($_SESSION["bddip"]))
    $_SESSION["bddip"] = "localhost";
if (empty($_SESSION["bddport"]))
    $_SESSION["bddport"] = "3306";



// ----------------------------------------------------
// Traitement suivant les eapes de la generation
// ----------------------------------------------------
if (!empty($_POST["etape"])) {
    switch ($_POST["etape"]) {

        case "step1" : // Affichage de la liste des tables
            $_SESSION["bdddriver"] = $_POST["bdddriver"];
            $_SESSION["bddip"] = $_POST["bddip"];
            $_SESSION["bddport"] = $_POST["bddport"];
            $_SESSION["bddname"] = $_POST["bddname"];
            $_SESSION["username"] = $_POST["username"];
            $_SESSION["password"] = $_POST["password"];

            try {
                // Connection a la base de donnze
                $mconn = new DbConnection($_SESSION["bdddriver"], $_SESSION["bddip"], $_SESSION["username"], $_SESSION["password"], $_SESSION["bddport"], $_SESSION["bddname"]);
                $mconn->connect();

                // Liste des tables du schema selectionner
                if (!empty($_SESSION["bddname"])) {
                    $sql = "SHOW TABLES FROM " . $_SESSION["bddname"];
                    $stmt = $mconn->getConn()->prepare($sql);
                    $stmt->execute();
                    $listTables = $stmt->fetchAll(PDO::FETCH_BOTH);
                }
            } catch (Exception_ $e) {
                $msg_alert = $e->getError();
            }

            break;

        case "step2" : // Ecriture des classes pour les tables selectionnee
            try {
                $mconn = new DbConnection($_SESSION["bdddriver"], $_SESSION["bddip"], $_SESSION["username"], $_SESSION["password"], $_SESSION["bddport"], $_SESSION["bddname"]);
                $mconn->PConnect();
            } catch (Exception_ $e) {
                $msg_alert = $e->getError();
            }
            break;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>phpFramework::daoGenerator</title>
        <style type="text/css">
            body, input, select { font-family:verdana; font-size:10px; }
            th { text-align:right; width:150px; }
            li { width:300px;float:left; padding-left:10px; }
            li#choose { width:400px; border-left:1px #000000 dashed; background-color:#FFFFCC; }
            span#folderDao { padding-left:30px; color:#0033CC; }
            #criteres, ul { width:730px; }
        </style>

        <script language="javascript1.2">
			function writeFolder(elt) {
				document.getElementById('folderDao').innerHTML = "Les fichiers seront ecrits dans le répertoire :<br/><?php echo $_SERVER['DOCUMENT_ROOT'] ?>/" + elt.value;
				document.getElementById('generateFiles').disabled = false;
			}

			function switchChkbox(form, flag) {

				for (var i = 0; i < form.elements.length; i++) {
					if (form.elements[i].type == "checkbox") {
						if (flag == 1)
							form.elements[i].checked = true;
						else if (flag == 2)
							form.elements[i].checked = false;
						else if (flag == 3)
							form.elements[i].checked = !form.elements[i].checked;
					}
				}

			}
			// End -->

        </script>
    </head>
    <body>

        <h1>daoGenerator</h1>
        <p>daoGenerator permet de cr&eacute;er automatiquement les classes PHP d'acc&egrave;s au donn&eacute;es (DAO) <br />
            des diff&eacute;rentes tables
            d'une base de donn&eacute;e, pour cela veuillez renseigner les informations<br />
            suivantes :</p>


        <div id="criteres">
            <ul><li>
                    <p><strong>ETAPE 1</strong> : Identifiant de la base de donn&eacute;e</p>
                    <form method="post">
                        <input type="hidden" name="etape" value="step1" />
                        <table width="300" border="0" cellspacing="3" cellpadding="0">
                            <tr><th scope="row">Driver ADODB</th>
                                <td><select name="bdddriver">
                                        <option value="mysql" <?php if ((!empty($_SESSION["bdddriver"])) && ($_SESSION["bdddriver"] == "mysql"))
    echo "selected=\"selected\"";
?>>mysql</option>
                                    </select></td></tr>
                            <tr><th scope="row">Nom de l'h&ocirc;te </th>
                                <td><input name="bddip" type="text" id="bddip" size="18" maxlength="30" <?php if (!empty($_SESSION["bddip"]))
    echo "value=\"" . $_SESSION["bddip"] . "\"";
?> /></td></tr>
                            <tr><th scope="row">Port</th>
                                <td><input name="bddport" type="text" id="bddport" size="5" maxlength="4" <?php if (!empty($_SESSION["bddport"]))
                                               echo "value=\"" . $_SESSION["bddport"] . "\"";
?> /></td></tr>
                            <tr><th scope="row">Nom de la base de donn&eacute;e </th>
                                <td><input name="bddname" type="text" id="bddname" <?php if (!empty($_SESSION["bddname"]))
                                               echo "value=\"" . $_SESSION["bddname"] . "\"";
?> /></td></tr>
                            <tr><th scope="row">Nom d'utilisateur </th>
                                <td><input name="username" type="text" id="username"  size="16" maxlength="20" <?php
                                           if (!empty($_SESSION["username"])) {
                                               echo "value=\"" . $_SESSION["username"] . "\"";
                                           } else {
                                               echo "value=\"root\"";
                                           }
                                           ?> /></td></tr>
                            <tr><th scope="row">Mot de passe </th>
                                <td><input name="password" type="password" id="password" size="16" maxlength="16" <?php if (!empty($_SESSION["password"]))
                                               echo "value=\"" . $_SESSION["password"] . "\"";
                                           ?> /></td></tr>
                            <tr><td colspan="2" align="center"><input type="submit" value="Voir la liste des tables" /></td></tr>

                        </table>
                    </form>
                </li>
                        <?php
                        // --------------------------------------------------------
                        // ACCES A L'ETAPE 2
                        // --------------------------------------------------------
                        if ((!empty($_POST["etape"])) && ($_POST["etape"] == "step1")) {
                            ?>
                    <li id="choose">
                        <form method="post" id="formulaire">
                            <input type="hidden" name="etape" value="step2" />
                            <p><strong>ETAPE 2</strong> : S&eacute;lectionnez les tables pour lesquelles vous voulez g&eacute;n&eacute;rer les classes d'acc&egrave;s. </p>
                            <p><a href="javascript:void(0);" onclick="switchChkbox(document.getElementById('formulaire'), 3);">Cocher / D&eacute;cocher</a></p>
                                        <?php
                                        // Liste des tables
                                        if (sizeof($listTables) > 0) {
                                            ?>
                                <div id="listeTables">
                                    <table border="0" cellpadding="0" cellspacing="1">
                                        <tr>
                                            <?php
                                            // Liste des tables
                                            if ((!empty($listTables)) && (is_array($listTables))) {
                                                foreach ($listTables as $record) {

                                                    if (($i != 0) && (($i % 2) == 0))
                                                        echo "</tr><tr>";
                                                    ?><td><input type="checkbox" name="tables[]" id="table_<?php echo $record[0] ?>" checked="checked" value="<?php echo $record[0] ?>" /> <?php echo $record[0] ?>  </td>
                <?php
                $i++;
            }
        }
        ?>
                                        </tr>
                                    </table>
                                </div>

                                <p><strong>Quels &eacute;lements voulez vous g&eacute;n&eacute;rer ?</strong><br />
                                    Classes : <input type="checkbox" name="classMetier" value="Y" checked="checked" /> M&eacute;tier
                                    <input type="checkbox" name="classDao" value="Y" checked="checked" /> Dao
                                    <input type="checkbox" name="classDaoCust" value="Y" /> DaoCust
                                    <br/>
                                    <strong>Nom de l'application *</strong> <input type="text" name="appName" size="30"  onkeyup="writeFolder(this);"/><br />
                                    <span id="folderDao"></span>
                                </p>

                                <input type="submit" id="generateFiles" value="G&eacute;n&eacute;rer les fichiers" disabled="disabled" />
                                <input type="submit" name="etape" value="Annuler" />
                    <?php } else { ?>
                                Aucune table(s) pour la base de donn&eacute;e, v&eacute;rifiez le nom de la base que vous avez saisie.
                    <?php } ?>

                        </form>
                    </li>


                        <?php
                    }
                    // --------------------------------------------------------
                    // ACCES A L'ETAPE 3
                    // --------------------------------------------------------
                    else if ((!empty($_POST["etape"])) && ($_POST["etape"] == "step2")) {
                        ?>
                    <li id="choose">
                        <p><strong>ETAPE 3 </strong> : Ecriture des fichers d'acc&egrave;s aux donn&eacute;es. </p>
                        <?php
                        // Liste des tables selectionnes.
                        if ((!empty($_POST["tables"])) && (is_array($_POST["tables"]))) {
                            foreach ($_POST["tables"] as $tableName) {

                                echo "<dt><label>&loz; <b>" . $tableName . "</b></label>";

                                unset($columns);
                                $columns = array();

                                // Liste des champs de la table
                                $sql = "SHOW FIELDS FROM " . $tableName;
                                //$listColumns = $mconn->getConn()->GetArray($sql);
                                $stmt = $mconn->getConn()->prepare($sql);
                                $stmt->execute();
                                $listColumns = $stmt->fetchAll(PDO::FETCH_BOTH);


                                if ((!empty($listColumns)) && (is_array($listColumns))) {
                                    foreach ($listColumns as $cols) {

                                        $col = new column();
                                        $col->setName($cols[0]);
                                        $col->setLabel($cols[0]);

                                        // type du champ
                                        $types = explode("(", $cols[1]);
                                        $col->setType($types[0]);

                                        // Longeur du champ
                                        if ((is_array($types)) && (sizeof($types) > 1)) {
                                            $lgth = explode(")", $types[1]);

                                            if ($col->getType() == "decimal") {
                                                $scale = explode(",", $lgth[0]);
                                                $col->setLength($scale[0]);
                                                $col->setScale($scale[1]);
                                            } else {
                                                $col->setLength($lgth[0]);
                                            }
                                        }

                                        // Le champ peut etre Null
                                        if ((!empty($cols[2])) && ($cols[2] == "YES"))
                                            $col->setNotnull(false);
                                        else
                                            $col->setNotnull(true);

                                        // Valeur par defaur
                                        if (!empty($cols[4]))
                                            $col->setDefaut($cols[4]);

                                        // Mise a jour des primary key
                                        if ((!empty($cols[3])) && ($cols[3] == "PRI")) {
                                            $col->setConstraintValues("PRI", $tableName, $cols[0]);
                                        }

                                        // On ajoute la colonne a la liste.
                                        $columns[$j++] = $col;
                                    }
                                }


                                // Creation de la classe metier
                                if (!empty($_POST["classMetier"])) {
                                    echo "<dl>" . strtolower($tableName) . ".php : ";
                                    if (writeBusiness::write($_SESSION["bdddriver"], $_POST["appName"], $tableName, $columns) == 1)
                                        echo "&radic;";
                                    else
                                        echo "<font color=\"red\">Erreur !</font>";
                                    echo "</dl>";
                                }
                                // Creation de la classe DAO
                                if (!empty($_POST["classDao"])) {
                                    echo "<dl>" . strtolower($tableName) . "Dao.php : ";
                                    if (writeDao::write($_SESSION["bdddriver"], $_POST["appName"], $tableName, $columns) == 1)
                                        echo "&radic;";
                                    else
                                        echo "<font color=\"red\">Erreur !</font>";
                                    echo "</dl>";
                                }
                                // Creation de la classe Dao personalisee
                                if (!empty($_POST["classDaoCust"])) {
                                    echo "<dl>" . $tableName . "DaoCust.php : ";
                                    if (writeDaoCust::write($_SESSION["bdddriver"], $_POST["appName"], $tableName, $columns) == 1)
                                        echo "&radic;";
                                    else
                                        echo "<font color=\"red\">Erreur !</font>";
                                    echo "</dl>";
                                }

                                echo "</dt>";
                            }
                        }

                        unset($columns);
                        unset($col);
                        ?>

                        <p>Les fichiers ont &eacute;t&eacute; g&eacute;n&eacute;r&eacute; &agrave; l'emplacement : <strong><?php echo $_SERVER['DOCUMENT_ROOT'] ?>/<?php echo $_POST["appName"] ?> </strong></p>

                        <form method="post">
                            <p align="center">
                                <input type="hidden" name="etape" value="step0" />
                                <input type="submit" value="Cliquez-ici pour terminer" />
                            </p>
                        </form>
                    </li>
<?php } ?>
            </ul>
        </div>


        <div id="msg">
            <p><?php if (!empty($msg_alert))
    echo $msg_alert;
?> </p>
        </div>


    </body>
</html>
