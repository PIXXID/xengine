<?php

/**
 * Boite a outils concernants les dates
 *
 * @date    05/04/07
 * @version     1.0
 * @author    d.meireles
 */

namespace xEngine\tools;

use \xEngine\Exception\Exception_;

class Date_ {

    private $lang_init = null;
    private $day = null;
    private $month = null;
    private $year = null;
    private $hour = null;
    private $minute = null;
    private $second = null;
    private $fixClosed = array();
    private $variants = array();
    private $separator = "/";
    private $saturdayIsClosed = false;
    private $sundayIsClosed = false;
    public $isvalid = false;

    /**
     * Constructeur - Instantiation de l'objet Date
     * @param String $date        - Date d'initialisation
     * @param String $lang_init    - Langue par defaut
     * @return void
     */
    public function __construct($date = null, $lang_init = "FR") {

        $this->lang_init = $lang_init;

        try {

            // Initialisation a l'heure system
            if ((empty($date)) && ($lang_init == "EN"))
                $date = date("Y-m-d H:i:s");
            else if ((empty($date)) && ($lang_init == "FR"))
                $date = date("d/m/Y H:i:s");

            $tmp = explode(" ", $date);
            $jour = $tmp[0];

            // Extraction des heures
            if (sizeof($tmp) > 1) {
                // Correction du 05/03/2012
                $heure = $tmp[1];
                $lTime = explode(":", $heure);
                if (isset($lTime[0]))
                    $this->hour = $lTime[0];
                if (isset($lTime[1]))
                    $this->minute = $lTime[1];
                if (isset($lTime[2]))
                    $this->seconde = $lTime[2];
                /*
                  if(strlen($heure) > 4) {
                  list($this->hour, $this->minute, $this->seconde) = explode(":", $heure);
                  } */
            }

            switch ($this->lang_init) {
                case "FR" : $this->separator = "/";
                    @list($this->day, $this->month, $this->year) = explode("/", $jour);
                    $this->fixClosed = array('Jour de l\'an' => '01/01',
                        'Fete du Travail' => '01/05',
                        'Armist. 1945' => '08/05',
                        'Fete nat' => '14/07',
                        'assomption' => '15/08',
                        'Toussaint' => '01/11',
                        'Armist. 1918' => '11/11',
                        'Noel' => '25/12');
                    break;
                case "EN" : $this->separator = "-";
                    @list($this->year, $this->month, $this->day) = explode("-", $jour);
                    break;
            }

            if ((empty($this->day)) || (empty($this->month)) || (empty($this->year))) {
                throw new Exception_('No date!');
            }

            $this->isvalid = true;
        } catch (Exception_ $e) {
            $this->isvalid = false;
        }
    }

    /**
     * Redefinition de la date, permet de ne pas creer
     * d'instance de classe systematiquement
     * @param String $date        - Date d'initialisation
     * @param String $lang_init    - Langue par defaut
     * @return void
     */
    public function setDate($date, $lang_init = "FR") {
        $this->__construct($date, $lang_init);
    }

    /**
     * Retourne la date au format texte, avec la possibilite de retourner
     * l'heure
     * @param String    $format    - Format de la date
     * @param boolean    $hour    - Retourne les heures
     * @return String
     */
    public function getDate($format, $hour = false) {
        $date = null;

        if (strlen($this->month) == 1)
            $this->month = "0" . $this->month;
        if (strlen($this->day) == 1)
            $this->day = "0" . $this->day;

        switch ($format) {
            case "FR" : $date = $this->day . "/" . $this->month . "/" . $this->year;
                break;
            case "EN" : $date = $this->year . "-" . $this->month . "-" . $this->day;
                break;
            case "YUI" : $date = $this->month . "/" . $this->day . "/" . $this->year;
                break;
        }

        if ($hour == true)
            $date .= " " . $this->hour . ":" . $this->minute . ":" . $this->seconde;


        return $date;
    }

    /**
     * Retourne les heures
     * @return String
     */
    public function getHours() {
        $date = $this->hour . ":" . $this->minute . ":" . $this->seconde;
        return $date;
    }

    /**
     * Ajoute un nombre de jours a la date courante
     * @param int    $nbre    - Nombre de jour a ajouter
     * @return void
     */
    public function addDays($nbre = 1) {
        $stampDay = $nbre * 86400; // 86400 = 24*60*60 = 1 jour
        $timeLocal = strtotime($this->year . "-" . $this->month . "-" . $this->day . " " . $this->hour . ":" . $this->minute . ":" . $this->seconde) + $stampDay;
        $this->year = date("Y", $timeLocal);
        $this->month = date("m", $timeLocal);
        $this->day = date("d", $timeLocal);
        $this->hour = date("H", $timeLocal);
        $this->minute = date("i", $timeLocal);
        $this->seconde = date("s", $timeLocal);
        return;
    }

    /**
     * Retourne le Timestamp de l'heure courante
     * @return timestamp
     */
    public function getTimestamp() {
        $timeLocal = strtotime($this->year . "-" . $this->month . "-" . $this->day . " " . $this->hour . ":" . $this->minute . ":" . $this->seconde);
        return $timeLocal;
    }

    /**
     * Initialise la date avec un valeur Timestamp
     * @return boolean
     */
    public function setTimestamp($mtime) {
        if ((!empty($mtime)) && (is_numeric($mtime))) {
            $this->year = date("Y", $mtime);
            $this->month = date("m", $mtime);
            $this->day = date("d", $mtime);
            $this->hour = date("H", $mtime);
            $this->minute = date("i", $mtime);
            $this->seconde = date("s", $mtime);
        } else {
            return false;
        }
        return true;
    }

    /**
     * Retourne le nom du jour de la semaine de la date courante
     * @param boolean $initial    - Retroune les trois premiers caracteres
     * @return String
     */
    public function getDayLabel($initial = false) {
        $lDay = null;
        $lArray = array('1' => 'Lundi', '2' => 'Mardi', '3' => 'Mercredi', '4' => 'Jeudi', '5' => 'Vendredi', '6' => 'Samedi', '0' => 'Dimanche');
        $lDay = $lArray[date("w", $this->getTimestamp())];
        if ($initial == true) {
            $lDay = substr($lDay, 0, 3);
        }
        return $lDay;
    }

    /**
     * Retourne le nom du mois de la date courante
     * @param boolean $initial    - Retroune les trois premiers caracteres
     * @return String
     */
    public function getMonthLabel($initial = false) {
        $lMonth = null;
        $lArray = array('1' => 'Janvier', '2' => 'Février', '3' => 'Mars', '4' => 'Avril', '5' => 'Mai', '6' => 'Juin', '7' => 'Juillet',
            '8' => 'Aout', '9' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre');
        $lMonth = $lArray[date("n", $this->getTimestamp())];
        if ($initial == true) {
            $lMonth = substr($lMonth, 0, 3);
        }
        return $lMonth;
    }

    /**
     * Permet de savoir si la date courante est un jour ouvrable
     * @param String $daymth    - Jour et mois a contreler
     * @return boolean
     */
    public function isClosed($daymth = null) {
        $lIsClosed = false;
        $lDay = date("w", $this->getTimestamp());

        if ($daymth == null)
            $daymth = $this->day . "/" . $this->month;
        $this->makeVariants();
        // Controle sur les jour ferie
        $lIsClosed = (in_array($daymth, $this->fixClosed) || in_array($daymth, $this->variants) );

        // Controle sur les week end
        if (($this->saturdayIsClosed == true) && ($lDay == 6))
            $lIsClosed = true;
        if (($this->sundayIsClosed == true) && ($lDay == 0))
            $lIsClosed = true;

        return $lIsClosed;
    }

    /**
     * Retourne le libelle du jour ferie
     * @param String $daymth    - Jour et mois a controler
     * @return String
     */
    public function getClosed($daymth = null) {
        if ($daymth == null)
            $daymth = $this->day . "/" . $this->month;
        $closedkey = array_keys(array_merge($this->fixClosed, $this->variants), $daymth);
        if (count($closedkey) > 0)
            return (array_pop($closedkey));
    }

    /**
     * Permet de rendre ouvrable ou non le samedi et le dimanche
     * @param boolean $saturday        - Le samedi est ouvrable
     * @param boolean $sunday        - Le dimanche est ouvrable
     * return void
     */
    public function weekIsClosed($saturday = true, $sunday = true) {
        $this->saturdayIsClosed = $saturday;
        $this->sundayIsClosed = $sunday;
    }

    /**
     * Desc: calcule paques,pentecote et jour feries algo de Oudin
     *
     */
    public function makeVariants() {
        //paques
        $year = $this->year;
        $G = $year % 19;
        $C = floor($year / 100);
        $C_4 = floor($C / 4);
        $E = floor((8 * $C + 13) / 25);
        $H = (19 * $G + $C - $C_4 - $E + 15) % 30;
        $K = floor($H / 28);
        $P = floor(29 / ($H + 1));
        $Q = floor((21 - $G) / 11);
        $I = ($K * $P * $Q - 1) * $K + $H;
        $B = floor($year / 4) + $year;
        $J1 = $B + $I + 2 + $C_4 - $C;
        $J2 = $J1 % 7;
        $R = 28 + $I - $J2;
        $day = (int) date('d', (mktime(0, 0, 0, 3, 1 + $R, $year)));
        $mth = (int) date('m', (mktime(0, 0, 0, 3, 1 + $R, $year)));

        if ($day < 10)
            $day = '0' . $day;
        if ($mth < 10)
            $mth = '0' . $mth;

        $val = $this->variants['paques'] = $day . '/' . $mth;

        //ascenssion
        $dasc = (int) date('d', (mktime(0, 0, 0, $mth, $day + 38, $this->year)));
        $masc = (int) date('m', (mktime(0, 0, 0, $mth, $day + 38, $this->year)));
        if ($dasc < 10)
            $dasc = '0' . $dasc;
        if ($masc < 10)
            $masc = '0' . $masc;
        $this->variants['ascenssion'] = $dasc . '/' . $masc;

        //pentecote
        $dasc = (int) date('d', (mktime(0, 0, 0, $mth, $day + 49, $this->year)));
        $masc = (int) date('m', (mktime(0, 0, 0, $mth, $day + 49, $this->year)));
        if ($dasc < 10)
            $dasc = '0' . $dasc;
        if ($masc < 10)
            $masc = '0' . $masc;
        $this->variants['Pentecote'] = $dasc . '/' . $masc;
    }

    /**
     * Retourne le numero de la semaine.
     * @return int
     */
    public function getWeekNum() {
        return date("W", $this->getTimestamp());
    }

    /**
     * Positionne la date au premier jour de la date courante.
     * @return void
     */
    public function gotoFirstDayOfWeek() {
        // Jour de la semaine courante de 0 a 6
        $lJour = date("w", $this->getTimestamp());
        if ($lJour == 0) {
            $ecart = 6;
        } else {
            $ecart = $lJour - 1;
        }

        $ecart = $ecart * -1; // on decompte

        $this->addDays($ecart);

        // On decompte le nombre de jour entre la date envoye et le lundi precedent.
        //$calcul = $this->getTimestamp() - ($ecart * 24 * 60 * 60);
        //$first = date("d/m/Y", $calcul);


        return;
    }

    /**
     * Positionne la date au dernier jour de la date courante.
     * @param $nb_jours        Nombre de jours dans la semaine.
     * @return void
     */
    public function gotoLastDayOfWeek($nb_jours = 7) {
        $lJour = date("w", $this->getTimestamp());

        if ($lJour == 0) {
            $ecart = $nb_jours - 7;
        } else {
            $ecart = $nb_jours - $lJour;
        }

        $this->addDays($ecart); // On ajoute

        return;
    }

    /** Getter * */
    public function getDay() {
        return $this->day;
    }

    public function getMonth() {
        return $this->month;
    }

    public function getYear() {
        return $this->year;
    }

    public function getHour() {
        return $this->hour;
    }

    public function getMinute() {
        return $this->minute;
    }

    public function getSecond() {
        return $this->second;
    }

    /** Setter * */
    public function setDay($value) {
        $this->day = $value;
    }

    public function setMonth($value) {
        $this->month = $value;
    }

    public function setYear($value) {
        $this->year = $value;
    }

    public function setHour($value) {
        $this->hour = $value;
    }

    public function setMinute($value) {
        $this->minute = $value;
    }

    public function setSecond($value) {
        $this->second = $value;
    }

}
