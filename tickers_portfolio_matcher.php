<?php
/**
 * Created by PhpStorm.
 * User: philipp
 * Date: 21.12.2017
 * Time: 23:11
 */


function match_tickers_to_portfolio(&$portfolio, $tickers, &$total, &$hightes, &$vias, &$eurChfTicker) {
    $hightesValue = 0;

    foreach($portfolio as $k => $v) {

        $selectedExchange = "kraken";

        if(isset($portfolio[$k]["exchange"])) {
            $selectedExchange = strtolower($portfolio[$k]["exchange"]);
        }

        if($selectedExchange == "none") {
            $portfolio[$k]["c"] = "0";
            $portfolio[$k]["diff_abs"] = "0";
            $portfolio[$k]["diff_rel"] = "0";
            $portfolio[$k]["cTotal"] = "0";
            $portfolio[$k]["cTotalCHF"] = "0";
            continue;
        }

        if($selectedExchange == TICK_KRAKEN) {
            if(!isset($tickers[TICK_KRAKEN][$v["altname_f"]]["c"][0])) return new CftError("Ticker " . $v["altname_f"] . "  in " . TICK_KRAKEN . " not found!");
            $portfolio[$k]["c"] = $tickers[TICK_KRAKEN][$v["altname_f"]]["c"][0];
        }else if($selectedExchange == TICK_HITBTC){
            if(!isset($tickers[TICK_HITBTC][$v["altname"]])) return new CftError("Ticker " . $v["altname"] . "  in " . TICK_HITBTC . " not found!");
            $portfolio[$k]["c"] = $tickers[TICK_HITBTC][$v["altname"]]["last"];
        }else if($selectedExchange == TICK_BITSTAMP){
            if(!isset($tickers[TICK_BITSTAMP][$v["altname"]])) return new CftError("Ticker " . $v["altname"] . "  in " . TICK_BITSTAMP . " not found!");
            $portfolio[$k]["c"] = $tickers[TICK_BITSTAMP][$v["altname"]]["last"];
        }
        else if($selectedExchange == TICK_BITTREX){
            if(!isset($tickers[TICK_BITTREX][$v["altname"]])) return new CftError("Ticker " . $v["altname"] . "  in " . TICK_BITTREX . " not found!");
            $portfolio[$k]["c"] = $tickers[TICK_BITTREX][$v["altname"]]["result"]["Last"];
        }
        else{
            echo "ERROR! INVALID EXCHANGE! (" . $selectedExchange . ")";exit;
        }

        if($portfolio[$k]["via"] !== false) {
            if(!isset($vias[$v["via"]])) {
                return new CftError("Could not match via: " . $portfolio[$k]["via"]);
            }else{
                $portfolio[$k]["c"] = $portfolio[$k]["c"] * $vias[$v["via"]];
            }

        }

        $portfolio[$k]["cTotal"] = $portfolio[$k]["c"] * $portfolio[$k]["amount"];
        $portfolio[$k]["cTotalCHF"] = $portfolio[$k]["cTotal"] * $eurChfTicker["rates"]["CHF"];
        $portfolio[$k]["buytotal"] = $portfolio[$k]["buyprice"] * $portfolio[$k]["amount"];
        $portfolio[$k]["diff_abs"] = ($portfolio[$k]["cTotal"] - $portfolio[$k]["buytotal"]) * $eurChfTicker["rates"]["CHF"];
        $portfolio[$k]["diff_rel"] = ($portfolio[$k]["cTotal"] / $portfolio[$k]["buytotal"] * 100) - 100;

        if(substr($k,0,1) != "!") {
            $total["cTotal"] = $total["cTotal"] +  $portfolio[$k]["cTotal"];
            $total["buytotal"] = $total["buytotal"] +  $portfolio[$k]["buytotal"];
            $total["cTotalCHF"] = $total["cTotalCHF"] +  $portfolio[$k]["cTotalCHF"];
        }

        if($hightes === false || $hightesValue < $portfolio[$k]["cTotal"]) {
            $hightesValue = $portfolio[$k]["cTotal"];
            $hightes = $k;
        }
    }

    $total["abs"] = ($total["cTotal"] - $total["buytotal"]) * $eurChfTicker["rates"]["CHF"];
    $total["rel"] = $total["cTotal"] / $total["buytotal"] * 100;

    return true;

}
