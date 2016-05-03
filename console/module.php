<?php

namespace xEngine\Console;

require_once(__DIR__ . '/helper.php');

class module {

    public function __construct() {
    }

    public function create($label) {
        echo "\e[32mInitialisation du module {$label}\r\n";
        echo "c'est pas bien\r\n";

        echo $this;
    }

    public function __toString() {
        return helper::module();
    }
}

