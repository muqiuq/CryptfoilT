<?php
/**
 * Created by PhpStorm.
 * User: philipp
 * Date: 21.12.2017
 * Time: 23:18
 */

function print_portfolio($database, $portfolio, $total, $eurChfTicker, $hightes) {

    $leftMask = "| %5.5s | %-8.8s | %-8.8s | %-8.8s | %-8.8s | %-8.8s |";
    $rightMask = " %-8.8s | %-8.8s | %5.5s | %3.3s\n";
    $leftMask = str_replace("%-8.8s", "%-16.16s", $leftMask);
    $rightMask = str_replace("%-8.8s", "%-16.16s", $rightMask);
    $mask = $leftMask . $rightMask;

    p_sl();
    print(" <<< " . $database["environment"]["last"] . " >>>                   ");
    print("1 EUR = " . $eurChfTicker["rates"]["CHF"] . " CHF\n\n");

    printf($mask, "NAME", "AMOUNT", "BUYPRICE", "C", "CTOT","CT CHF", "ABS", "REL","+/-","M");
    printf($mask, "---------------","---------------","---------------","---------------","---------------","---------------","---------------","---------------","---------------","---------------");
    foreach($portfolio as $k => $v) {
        $good = $portfolio[$k]["diff_rel"] > 0;
        $lineColor = "\033[31m";
        $sign = "";
        if($good) {
            $lineColor = "\033[32m";
            $sign = "+";
        }

        if($hightes == $k) {
            echo "\033[1;34m";
        }

        printf($leftMask, $portfolio[$k]["name"],
            nf($portfolio[$k]["amount"],4) . " " . $v["right"],
            nf($portfolio[$k]["buyprice"],6). " " . $v["left"],
            nf($portfolio[$k]["c"],6). " " . $v["left"],
            nf($portfolio[$k]["cTotal"],4). " " . $v["left"],
            nf($portfolio[$k]["cTotalCHF"],4). " CHF");
        echo $lineColor;
        printf($rightMask,
            $sign . nf($portfolio[$k]["diff_abs"],2). " CHF",
            $sign . nf($portfolio[$k]["diff_rel"],0) . " %",
            $good ? "+" : "-",
            exchange_to_spitz($portfolio[$k]["exchange"])
        );
        echo COLOR_RESET;
    }
    printf($mask, "---------------","---------------","---------------","---------------","---------------","---------------","---------------","---------------","---------------","---------------");
    echo "\033[1;35m";
    printf($mask, "", "TOTAL",
        nf($total["buytotal"],2) . " EUR",
        "", nf($total["cTotal"],2) . " EUR",
        nf($total["cTotalCHF"],2) . " CHF",
        nf($total["abs"],2) . " CHF"
        , nf($total["rel"],0) . " %",
        "","");
    echo COLOR_RESET;
    p_sl();
}