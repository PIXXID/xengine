<?php

/**
 * Objet representant une connexion a une base de donnee.
 *
 * exemple : $mconn = new DbConnection("mysqli", "localhost", "root", "", "3306", "trapecws");
 *
 * @name        DbConnection
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine\database;

use \xEngine\exception\Exception_;

class DbConnection {

    /**
     * Connection a la base de donnee
     * @access private
     * @var ADOConnection
     */
    private $conn = null;

    /**
     * Driver de base de donnee a utiliser
     * @access private
     * @var string
     */
    private $driver = null;

    /**
     * Serveur de base de donnee
     * @access private
     * @var string
     */
    private $server = null;

    /**
     * Utilisateur de connexion a la base de donnee
     * @access private
     * @var string
     */
    private $user = null;

    /**
     * Mot de passe de l'utilisateur
     * @access private
     * @var string
     */
    private $password = null;

    /**
     * Port du serveur de base de donnee
     * @access private
     * @var string
     */
    private $port = "3306";

    /**
     * Nom de la base de donnee
     * @access private
     * @var string
     */
    private $database = null;

    /**
     * Format de date par defaut
     * @access private
     * @var string
     */
    private $dateFormat = "YYYY-MM-DD HH24:MI:SS";

    /**
     * Charset du client PHP
     * @access private
     * @var string
     */
    private $charset = 'UTF8';

    /**
     * Mode de debug actif.
     * @access private
     * @var boolean
     */
    private $debug = false;

    /**
     * Message d'erreur lors de l'acces a la base
     * @access private
     * @var string
     */
    private $message = null;

    /**
     * Constructeur
     *
     * @name DbConnection::__construct()
     *
     * @access public
     * @param string $driver Driver de la base de donnee (mysqli, oracle ...)
     * @param string $server Serveur de base de donnee (IP ou DNS)
     * @param string $user Utilisateur de base de donnee
     * @param string $password Mot de passe de l'utilisateur
     * @param string $port Numero de port du serveur de bdd
     * @param string $database Nom de la base de donnee
     *
     * @return void
     */
    public function __construct($driver, $server, $user, $password, $port, $database, $charset = 'utf8') {
        $this->driver = $driver;
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->database = $database;
        $this->charset = $charset;
        //$this->conn = NewADOConnection($this->driver);
    }

    /**
     * Retourne la chaine de caractere lorsque l'on essai d'afficher l'objet
     *
     * @name DbConnection::__tostring()
     * @access public
     *
     * @return string
     */
    public function __tostring() {
        return 'Connexion à une base de données';
    }

    /**
     * Prise de connection simple
     *
     * @name DbConnection::connect()
     * @access public
     *
     * @return ADOConnection
     */
    public function connect() {
        try {

            //Connexion
            // TODO - Prévoir personnalisation du DSN
            $dsn = "{$this->driver}:dbname={$this->database};host={$this->server};port={$this->port}";
            $attributes = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->charset}'",
                'ATTR_PERSISTENT' => false
            );
            $this->conn = new \PDO($dsn, $this->user, $this->password, $attributes);
            $this->message = "Connexion à la base de données effectuée !!";
        } catch (Exception $e) {
            $this->conn = null;
            $this->message = "Connexion à la base de donnée impossible , vérifiez les identifiants de connection : " . $e->message;
            throw new Exception_($this->message);
        }

        return $this->conn;
    }

    /**
     * Prise de connection persistante
     *
     * @name DbConnection::getConnection()
     * @access public
     *
     * @return ADOConnection
     */
    public function PConnect() {
        try {
            $dsn = "{$this->driver}:dbname={$this->database};host={$this->server};port={$this->port}";
            $attributes = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->charset}'",
                'ATTR_PERSISTENT' => true
            );
            $this->conn = new \PDO($dsn, $this->user, $this->password, $attributes);
            $this->message = "Connexion à la base de données effectuée !!";
        } catch (Exception $e) {
            $this->conn = null;
            $this->message = "Connexion à la base de donnée impossible , vérifiez les identifiants de connection : "
                    . $e->getMessage();
            throw new Exception_($this->message);
        }

        return $this->conn;
    }

    /**
     * Ouverture d'une transaction
     *
     * @return void
     */
    public function beginTransaction() {
        $this->conn->beginTransaction();
    }

    /**
     * Commit de la transaction
     *
     * @return void
     */
    public function commit() {
        $this->conn->commit();
    }

    /**
     * Rollback de la transaction
     *
     * @return void
     */
    public function rollBack() {
        $this->conn->rollBack();
    }

    /**
     * Fermeture de la connection a la base de donnee
     *
     * @return void
     */
    public function close() {
        return;
    }

    /**
     * Getter de l'attribut $conn
     *
     * @return ADOConnection Connection active a la base de donnee
     */
    public function getConn() {
        return $this->conn;
    }

    /**
     * Getter de l'attribut $driver
     *
     * @return string Driver d'acces a la base de donnee
     */
    public function getDriver() {
        return $this->driver;
    }

    /**
     * Getter de l'attribut $server
     *
     * @return string Serveur de base de donnee
     */
    public function getServer() {
        return $this->server;
    }

    /**
     * Getter de l'attribut $user
     *
     * @return string Utilisateur d'acces a la base de donnee
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Getter de l'attribut $password
     *
     * @return string Mot de passe de l'utilisateur
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Getter de l'attribut $port
     *
     * @return string Port d'ecoute du serveur de base de donnee
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Getter de l'attribut $database
     *
     * @return string Nom de la base de donnee
     */
    public function getDatabase() {
        return $this->database;
    }

    /**
     * Getter de l'attribut $dateFormat
     *
     * @return string Format des dates par defaut
     */
    public function getDateFormat() {
        return $this->dateFormat;
    }

    /**
     * Getter de l'attribut $charset
     *
     * @return string Charset
     */
    public function getCharset() {
        return $this->charset;
    }

    /**
     * Getter de l'attribut $debug
     *
     * @return boolean Mode debug actif
     */
    public function getDebug() {
        return $this->debug;
    }

    /**
     * Getter de l'attribut $message
     *
     * @return boolean Message de retour lors de la connection a la base de donnee
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Setter de l'attribut $conn
     *
     * @param ADOConnection $value Objet de connection
     *
     * @return void
     */
    public function setConn(ADOConnection $value) {
        $this->conn = $value;
    }

    /**
     * Setter de l'attribut $driver
     *
     * @param string $value Nom du driver a utiliser
     *
     * @return void
     */
    public function setDriver($value) {
        $this->driver = $value;
    }

    /**
     * Setter de l'attribut $server
     *
     * @param string $value Nom su serveur de base de donnee
     *
     * @return void
     */
    public function setServer($value) {
        $this->server = $value;
    }

    /**
     * Setter de l'attribut $user
     *
     * @param string $value Nom de l'utilisateur
     *
     * @return void
     */
    public function setUser($value) {
        $this->user = $value;
    }

    /**
     * Setter de l'attribut $password
     *
     * @param string $value Mot de passe de l'utilisateur
     *
     * @return void
     */
    public function setPassword($value) {
        $this->password = $value;
    }

    /**
     * Setter de l'attribut $port
     *
     * @param string $value Port d'ecoute du serveur
     *
     * @return void
     */
    public function setPort($value) {
        $this->port = $value;
    }

    /**
     * Setter de l'attribut $database
     *
     * @param string $value Nom de la base de donnee
     *
     * @return void
     */
    public function setDatabase($value) {
        $this->database = $value;
    }

    /**
     * Setter de l'attribut $dateFormat
     *
     * @param string $value Format de date par defaut
     *
     * @return void
     */
    public function setDateFormat($value) {
        $this->dateFormat = $value;
    }

    /**
     * Setter de l'attribut $charset
     *
     * @param string $value Charset
     *
     * @return void
     */
    public function setCharset($value) {
        $this->charset = $value;
    }

    /**
     * Setter de l'attribut $debug
     *
     * @param boolean $value Activation du mode debug
     *
     * @return void
     */
    public function setDebug($value) {
        $this->debug = $value;
    }

}
