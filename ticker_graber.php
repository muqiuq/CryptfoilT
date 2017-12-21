<?php


function tickers_fetch_kraken($portfolio) {

    $pairs = array();

    foreach($portfolio as $k => $v) {
        if($v["altname_f"] !== false && $v["exchange"] == "kraken") {
            array_push($pairs, $v["altname_f"]);
        }
    }

    if(count($pairs) == 0) return array("result" => [], "error" => []);

    $pairsStr = implode(",",$pairs);

    $krakenTickers = array();

    $krakenTickersRaw = @file_get_contents("https://api.kraken.com/0/public/Ticker?pair=" . $pairsStr);

    if($krakenTickersRaw === false) {
        return false;
    }else{
        $krakenTickers = json_decode($krakenTickersRaw, true);
    }

    if($krakenTickers["error"] != array()) {
        return new CftError(TICK_KRAKEN . ": " . json_encode($krakenTickers["error"]));
    }

    return $krakenTickers["result"];
}

function tickers_fetch_hitbtc() {

    $raw = @file_get_contents("https://api.hitbtc.com/api/2/public/ticker");

    if($raw === false) {
        return false;
    }

    $rawDecoded = json_decode($raw, true);

    $ticker = array();

    foreach($rawDecoded as $v) {
        $ticker[strtolower($v["symbol"])] = $v;
    }

    return $ticker;

}

function grab_tickers($portfolio, $settings) {


    $tickers = array();

    if(file_exists(FILE_TICKERS)) {
        $tickers = json_decode(file_get_contents(FILE_TICKERS),true);
    }

    if($settings[SETTING_LIVEDATA]) {
        eaf(exchange_to_spitz(TICK_KRAKEN));
        $krakenTickers = tickers_fetch_kraken($portfolio);
        if($krakenTickers !== false) {
            if($krakenTickers instanceof CftError) {
                die_print_error($krakenTickers);
            }else{
                $tickers[TICK_KRAKEN] = $krakenTickers;
                eaf("*");
            }
        }else{
            eaf("-");
        }

        eaf(exchange_to_spitz(TICK_HITBTC));
        $hitbtc_tickers = tickers_fetch_hitbtc();
        if($hitbtc_tickers !== false) {
            $tickers[TICK_HITBTC] = $hitbtc_tickers;
            eaf("*");
        }else{
            eaf("-");
        }

    }

    if(!isset($tickers[TICK_BITSTAMP])) $tickers[TICK_BITSTAMP] = array();
    if(!isset($tickers[TICK_HITBTC])) $tickers[TICK_HITBTC] = array();

    if($settings[SETTING_LIVEDATA]) {
        foreach($portfolio as $k => $v) {
            if(strtolower($v["exchange"]) == TICK_BITSTAMP && $v["altname"] !== false) {
                eaf(exchange_to_spitz(TICK_BITSTAMP));
                $bitstampTickerRaw = @file_get_contents("https://www.bitstamp.net/api/v2/ticker_hour/" . strtolower($v["altname"]));
                if($bitstampTickerRaw !== false) {
                    eaf("*");
                    $bitstampTicker = json_decode($bitstampTickerRaw,true);
                    $tickers[TICK_BITSTAMP][$v["altname"]] = $bitstampTicker;
                }else{
                    eaf("-");
                    $is_offline[TICK_BITSTAMP] = true;
                }
            }
            if(strtolower($v["exchange"]) == TICK_BITTREX && $v["altname"] !== false) {
                eaf(exchange_to_spitz(TICK_BITTREX));
                $bittrexTickerRaw = @file_get_contents("https://bittrex.com/api/v1.1/public/getticker?market=" . strtolower($v["altname"]));
                if($bittrexTickerRaw !== false) {
                    eaf("*");
                    $bTicker = json_decode($bittrexTickerRaw,true);
                    $tickers[TICK_BITTREX][$v["altname"]] = $bTicker;
                }else{
                    eaf("-");
                    $is_offline[TICK_BITTREX] = true;
                }
            }
        }

        file_put_contents(FILE_TICKERS, json_encode($tickers));
    }

    return $tickers;
}

function get_euro_ticker($settings, $adjustment = 1) {

    $eurChfTicker = array();

    eaf("E");

    $eurChfTickerRaw = false;
    if($settings[SETTING_LIVEDATA]) {
        $eurChfTickerRaw = @file_get_contents("http://api.fixer.io/latest?base=EUR&symbols=CHF");
    }

    if($eurChfTickerRaw === false) {
        $eurChfTicker = json_decode(file_get_contents(FILE_EURCHF), true);
        eaf("-");
    }else{
        $eurChfTicker = json_decode($eurChfTickerRaw, true);
        file_put_contents(FILE_EURCHF, $eurChfTickerRaw);
        eaf("*");
    }

    $eurChfTicker["rates"]["CHF"] = $eurChfTicker["rates"]["CHF"] * $adjustment;

    return $eurChfTicker;
}