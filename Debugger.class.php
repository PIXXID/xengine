<?php

/**
 * Debugueur
 *
 * @name        Debugger
 * @copyright   D.M 04/02/2013
 * @license     /LICENCE.txt
 * @since       1.0
 * @author      D.M <dmeireles@pixxid.fr>
 */

namespace xEngine;

class Debugger {

    /**
     * Permet de mesurer le temps d'execution d'un controller
     * startTime enregistrera le temps de référence
     * @access private
     * @var microtime
     */
    private $startTime = null;
    private $breakpoint = array();
    private $alert = array();
    private $active = false;

    public function __construct() {
        $this->startTime = microtime(true);
    }

    public function __tostring() {
        return "debugueur";
    }

    /**
     * Retourne le temps d'execution du script du debut de Le controller
     * au moment ou vous afficher le temps à l'aide de cette methode
     *
     * @name Debugger::getExecTime()
     * @access public
     * @return double
     */
    public function getExecTime() {
        $temps_fin = microtime(true);
        return round($temps_fin - $this->startTime, 3);
    }

    /**
     * Ajout d'un point d'arrêt permettant d'avoir un
     * temps d'éxécution intermédiaire.
     *
     * @name Debugger::addBreakPoint()
     * @access public
     * @return boolean
     */
    public function addBreakPoint($name) {
        $this->breakpoint[sizeof($this->breakpoint)] = array($name, $this->getExecTime(), @memory_get_usage());
        return true;
    }

    public function getBreakPoint() {
        return $this->breakpoint;
    }

    public function printBreakPoint() {
        $i = 1;
        $old_temps = 0;
        $html = "<div id=\"xEngineDebugger\">
            <table cellspacing=\"5\" cellpadding=\"5\">
            <label>Temps d'execution : </label>";

        foreach ($this->breakpoint as $record) {
            $calc = round($record[1] - $old_temps, 3);
            $lMemory = round($record[2] / 1024);
            $html .= "<tr><td>" . $i++ . " - </td><td align=\"left\">&nbsp;" . $record[0] . "</td><td align='right'>&nbsp;" . $record[1] . "s</td><td align='right'>&nbsp;" . $calc . "s</td><td align='right'>&nbsp;" . $lMemory . " ko</td></tr>";
            $old_temps = $record[1];
        }
        $html .= "</table></div>";
        return $html;
    }

    /*
     * Ajout d'une alerte dans le Debugger
     */

    public function addAlert($alert) {
        $this->alert[sizeof($this->alert)] = $alert;
        return true;
    }

    public function getAlert() {
        return $this->alert;
    }

    public function setActive($value) {
        $this->active = $value;
    }

    public function getActive() {
        return $this->active;
    }

}
