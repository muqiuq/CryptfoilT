<?php
/**
 * Created by PhpStorm.
 * User: philipp
 * Date: 06/07/17
 * Time: 22:16
 */

// https://api.kraken.com/0/public/AssetPairs

$applicationFolder = realpath(dirname(__FILE__));

require_once ($applicationFolder . "/define.php");
require_once ($applicationFolder . "/helperfunctions.php");
require_once ($applicationFolder . "/Error.php");
require_once ($applicationFolder . "/tickers_portfolio_matcher.php");
require_once ($applicationFolder . "/ticker_graber.php");
require_once ($applicationFolder . "/print_portfolio.php");
require_once ($applicationFolder . "/database.php");

$userhome = getenv("HOME");



eaf("\n");

/// ==================================================
/// FETCH SETTINGS FROM CLI
/// ==================================================

$settings = fetch_cli_args_to_settings($argv);

/// ==================================================
/// SET SOME GLOBAL VARIABLES
/// ==================================================

$is_offline = array();

$total = array(
    "cTotal" => 0,
    "buytotal" => 0,
    "cTotalCHF" => 0
);

/// ==================================================
/// GET DATABASE
/// ==================================================


$database = get_database();

/// ==================================================
/// LOAD PORTFOLIO / CONFIGURATION
/// ==================================================


$portfolioFileName = $userhome . "/.cft_portfolio.json";
if(!file_exists($portfolioFileName)) {
    $portfolioFileName = $applicationFolder . "/portfolio.json";
}

$portfolio = json_decode(@file_get_contents($portfolioFileName), true);

if($portfolio == NULL) {
    die("\nERROR: Portfolio could not be found at " . $portfolioFileName . " or is invalid!\n");
}

// Set default exchange
portfolio_set_default_exchange($portfolio, "kraken");

/// ==================================================
/// GRAB TICKERS FROM EXCHANGES API
/// ==================================================

$eurChfTicker = get_euro_ticker($settings, 0.98);

$tickers = grab_tickers($portfolio, $settings);

/// ==================================================
/// SET VIAS
/// FOR SPECIAL FUNCTION OF INDIRECT CALCULATION
/// ==================================================


require_once ($applicationFolder . "/via.php");

$hightes = false;

/// ==================================================
/// MAP THE TICKER DATA TO THE PORTFOLIO
/// ==================================================

$error = match_tickers_to_portfolio($portfolio, $tickers, $total, $hightes, $vias, $eurChfTicker);

if($error instanceof CftError) {
    die_print_error($error);
}

/// ==================================================
/// HANDLING OF SPECIAL CLI FUNCTIONS
/// SAVING DATA FOR PERSISTENCE
/// ==================================================

if(count($is_offline) == 0 && $settings[SETTING_LIVEDATA]) {
    $database["environment"]["last"] = date("Y-m-d H:i:s");
    $database["history"][$database["environment"]["last"]] = $portfolio;
}

file_put_contents(FILE_DB, json_encode($database));

/// END DATA FETCH <=> NOW DISPLAY STUFF

$portfolio_columns = array_keys($portfolio[array_keys($portfolio)[0]]);

if($settings[SETTING_SORT] !== false && array_search($settings[SETTING_SORT], $portfolio_columns) === false) {
    $settings[SETTING_DISPLAYCOLUMNS] = true;
}

if($settings[SETTING_DISPLAYCOLUMNS]) {
    echo "Possible Columns: \n";
    print_r($portfolio_columns);
    exit();
}

if($settings[SETTING_SORT] !== false) {
    sort_portfolio($portfolio, $settings[SETTING_SORT]);
}


/// ==================================================
/// PRINT OTHER STATUS INFORMATION
/// ==================================================


echo COLOR_RESET;
if(count($is_offline) > 0) {
    echo "\n";
    foreach($is_offline as $name => $v) {
        echo COLOR_RED . strtoupper($name) . " IS OFFLINE!!!, ";
    }
    echo "\n";
    echo COLOR_RESET;
}


/// ==================================================
/// MAKE A NICE PRINT OF THE PORTFOLIO
/// ==================================================


print_portfolio($database, $portfolio, $total, $eurChfTicker, $hightes);

// BEGIN OUTPUT

