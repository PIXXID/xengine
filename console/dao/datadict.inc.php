<?php

/**
 * Permettant la transformation du type des champs de la tables
 * en type d'objet du framework (ex : colString, colInt)
 *
 * @name	datadict.inc.php
 * @author	D.M <dmeireles@pixxid.fr>
 * @copyright	D.M 18/09/2006
 * @package	PixEngine.database.daogenerator
 * @version	1.0
 */
$databaseType = array("char",
    "varchar",
    "varchar2",
    "text",
    "set",
    "tinyint",
    "smallint",
    "mediumint",
    "int",
    "integer",
    "bigint",
    "double",
    "float",
    "decimal",
    "date",
    "datetime",
    "timestamp");

$businessType = array("string",
    "string",
    "string",
    "string",
    "string",
    "int",
    "int",
    "int",
    "int",
    "int",
    "int",
    "int",
    "float",
    "decimal",
    "date",
    "date",
    "date");

$paramLetters = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
    "k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
    "u", "v", "w", "x", "y", "z", "0", "aa", "ab", "ac",
    "ad", "ae", "af", "ag", "ah", "ai", "aj", "ak", "al", "am",
    "an", "ao", "ap", "ak", "ar", "as", "at", "au", "av", "aw",
    "ax", "ay", "az", "ba", "bb", "bc", "bd", "be", "bf", "bg",
    "bh", "bi", "bj", "bk", "bl", "bm", "bn", "bo", "bq", "br");
?>