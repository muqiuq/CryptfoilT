<?php
/**
 * Created by PhpStorm.
 * User: philipp
 * Date: 22.12.2017
 * Time: 00:22
 */

$vias = array();

if(isset($tickers[TICK_BITSTAMP]["btceur"])) {
    $vias["XBT"] = $tickers[TICK_BITSTAMP]["btceur"]["last"];
}else{
    die_print_error(new CftError("Missing XBT VIA on " . TICK_BITSTAMP));
}

if(isset($tickers[TICK_BITSTAMP]["etheur"])) {
    $vias["ETH"] = $tickers[TICK_BITSTAMP]["etheur"]["last"];
}else{
    die_print_error(new CftError("Missing ETH VIA on " . TICK_BITSTAMP));
}
