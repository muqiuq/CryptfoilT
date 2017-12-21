<?php

function exchange_to_spitz($exchange) {
if($exchange == TICK_BITSTAMP) return "B";
if($exchange == TICK_BITTREX) return "T";
if($exchange == TICK_KRAKEN) return "K";
if($exchange == TICK_HITBTC) return "H";
return "?";
}

function nf($v,$decimals) {
return number_format($v, $decimals,".","'");
}

function p_sl() {
echo "\n";for($a = 0; $a < 154; $a++) echo "*";echo "\n\n";
}

function eaf($v) {
echo $v;
flush();
}

function portfolio_set_default_exchange(&$portfolio, $exchange) {
    foreach($portfolio as $k => $v) {
        if(!isset($portfolio[$k]["exchange"])) {
            $portfolio[$k]["exchange"] = $exchange;

        }
        $portfolio[$k]["altname"] = strtolower($portfolio[$k]["altname"]);
        $portfolio[$k]["name"] = $k;
    }
}

function sort_portfolio(&$portfolio, $column) {
    $keys = array_keys($portfolio);

    for($a = 0; $a < count($keys); $a++) {
        $setRowKey = array();
        $highestValue = false;
        for($b = count($keys) - (count($keys) - $a); $b < count($keys); $b++) {
            if($highestValue === false || $portfolio[$keys[$b]][$column] > $highestValue) {
                $setRowKey = $keys[$b];
                $highestValue =  $portfolio[$keys[$b]][$column];
            }
        }
        $oldEntry = $portfolio[$keys[$a]];
        $portfolio[$keys[$a]] = $portfolio[$setRowKey];
        $portfolio[$setRowKey] = $oldEntry;
    }

    return true;
}

function fetch_cli_args_to_settings($argv) {
    $settings = array(
        SETTING_LIVEDATA => true,
        SETTING_SORT => "c",
        SETTING_DISPLAYCOLUMNS => false
    );

    foreach($argv as $a) {
        if($a == "--offline") {
            $settings[SETTING_LIVEDATA] = false;
        }
        if (strpos($a, '--sort=') === 0 || strpos($a, '-s=') === 0) {
            $p = explode("=",$a,2);
            if(count($p) == 2) {
                $settings[SETTING_SORT] = trim($p[1]);
            }
        }
        if (strpos($a, '--no-sort') === 0 || strpos($a, '-n') === 0) {
            $settings[SETTING_SORT] = false;
        }
        if($a == "--columns") {
            $settings[SETTING_DISPLAYCOLUMNS] = true;
        }
    }

    return $settings;
}

function die_print_error(CftError $error) {
    die("\nERROR: " . $error->errorMessage . "\n");
}