<?php

set_time_limit(0);
date_default_timezone_set('UTC');

var_dump(date('Y-m-d H:i:s', time()));

require __DIR__ . '/app/vendor/autoload.php';

//////////////////////

$proxy = "http://Socialhub.pro:C_LAjPwZxzD-an@185.128.148.49:30508";

try {
    if ($proxy && isValidProxy($proxy)) {
        die("Conexao feita com proxy HTTPS! :) \n");
    }
    die("Erro conectando atravez do proxy atravez do PHP com HTTPS! :( \n");
} catch (Exception $exception) {
    die("Erro conectando atravez do proxy atravez do PHP com HTTPS! :( \n");
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

    // http://Socialhub.pro:C_LAjPwZxzD-an@185.128.148.49:30508
    // $ch = curl_init(); 
    // curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/"); 
    // curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    // curl_setopt($ch, CURLOPT_PROXY, '185.128.148.49');
    // curl_setopt($ch, CURLOPT_PROXYPORT, 30508);
    // curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'Socialhub.pro:C_LAjPwZxzD-an');
    // curl_setopt($ch, CURLOPT_HEADER, false);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // var_dump(curl_exec($ch)); 
    // var_dump(curl_error($ch));
    // exit();

    try {
        $client = new \GuzzleHttp\Client();
        // $res = $client->request('GET', 'http://www.google.com',
        $res = $client->request('GET', 'http://www.instagram.com',
            [
                "verify" => false,
                "timeout" => 10,
                "proxy" => $proxy,
                "debug" => true,
                "CURLOPT_SSLVERSION" => 'CURL_SSLVERSION_TLSv1_2',
            ]);
        $code = $res->getStatusCode();
    } catch (\Exception $e) {
        ddd($e);
    }

    return $code == 200;
}