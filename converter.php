<?php
function cls(): void {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('cls');
    } else {
        system('clear');
    }
}
function checkConnection($server): int
{

    $url = "https://" . $server . "/npm/@fawazahmed0/currency-api@latest/v1/currencies.min.json";
    $request = curl_init();

    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_exec($request);

    return curl_getinfo($request, CURLINFO_RESPONSE_CODE);
}
function fetchUrl(string $url): string
{
    $request = curl_init();
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

    if( ! $result = curl_exec($request))
    {
        trigger_error(curl_error($request));
    }
    curl_close($request);
    return $result;
}
function getCurrencyList(string $server): stdClass
{
    $url = "https://" . $server . "/npm/@fawazahmed0/currency-api@latest/v1/currencies.min.json";
    $currencyList = fetchUrl($url);
    return json_decode($currencyList);
}
function getCurrencyMultiplier(string $server, string $currency): stdClass
{
    $url = "https://" .
        $server . "/npm/@fawazahmed0/currency-api@latest/v1/currencies/" .
        $currency . ".min.json";
    $currencyList = fetchUrl($url);
    return json_decode($currencyList);
}
function listCurrencies(array $keys, array $currencies): void {
    $params = readline("More specific [a-z] or full [ENTER]: ");
    if($params != "" && strlen($params) == 1) {
        for ($i = 0; $i < count($keys); $i++) {
            if ($params === $keys[$i][0]) {
                echo "Key: " . $keys[$i] . str_repeat(" ", 15 - strlen($keys[$i])+1) ."Name: " . $currencies[$keys[$i]] . "\n";
            }
        }
    } else {
        for ($i = 0; $i < count($keys); $i++) {
            echo "Key: " . $keys[$i] . str_repeat(" ", 15 - strlen($keys[$i])+1) ."Name: " . $currencies[$keys[$i]] . "\n";
        }
    }
}

$server = "cdn.jsdelivr.net";
if(checkConnection($server) !== 200) {
    $server= "currency-api.pages.dev";
}

//retrieve currency object
$currencies = getCurrencyList($server);
//convert currency object to array and retrieve keys.
$currenciesArray = (array)$currencies;
$currenciesKeys = array_keys($currenciesArray);

//user input and validation loop
while(true) {
    $inputRaw = readline("Init sum and currency ('list' for currency list): ");
    if(strtolower($inputRaw) == 'list') {
        cls();
        listCurrencies($currenciesKeys, $currenciesArray);
        continue;
    }

    $toCur = readline("Exchange To: ");
    $inputExploded = explode(" ", $inputRaw);

    if(count($inputExploded) != 2) {
        echo "There was something with your input. '100 EUR' -> 'USD' should display how much 100 eur is in USD.\n";
        continue;
    }
    if(is_numeric($inputExploded[0]) && $inputExploded[0] > 0) {
        $amount = $inputExploded[0];
    } else {
        echo "There was something with your input. '100 EUR' -> 'USD' should display how much 100 eur is in USD.\n";
        continue;
    }
    if(in_array($inputExploded[1], $currenciesKeys)) {
        $initCur = $inputExploded[1];
    } else {
        echo "No such initial currency in database";
        continue;
    }
    if( ! in_array($toCur, $currenciesKeys)) {
        echo "No such exchange currency in database";
        continue;
    }
    break;
}

//retrieve currency multiplier object
$multipliers = getCurrencyMultiplier($server, $initCur);
//convert
$conversion = $amount * $multipliers->$initCur->$toCur;
//print results
echo "\n" . $amount . " " . strtoupper($initCur) .
    " -> " . strtoupper($toCur) . " = " .
    number_format($conversion, 2, '.', '') .
    "\n";













