<?php

set_time_limit(0);
date_default_timezone_set('UTC');

var_dump(date('Y-m-d H:i:s', time()));

require __DIR__ . '/app/vendor/autoload.php';

//////////////////////

$proxy = "https://mTNPvr:GZcaDW@168.235.93.240:24272";;

try {
    if ($proxy && isValidProxy($proxy)) {
        echo "Conexao feita com proxy HTTPS! :)";
    }
} catch (Exception $exception) {
    echo "Erro conectando atravez do proxy atravez do PHP com HTTPS! :(";    
}

/**
 * Validates proxy address
 * @param  string  $proxy [description]
 * @return boolean        [description]
 */
function isValidProxy($proxy)
{
    if (!is_string($proxy) && !is_array($proxy)) {
        return false;
    }

    try {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'https://www.instagram.com',
            [
                "verify" => false,
                "timeout" => 10,
                "proxy" => $proxy,
            ]);
        $code = $res->getStatusCode();
    } catch (\Exception $e) {
        return false;
    }

    return $code == 200;
}