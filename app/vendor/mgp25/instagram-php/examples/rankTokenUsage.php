<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__ . '/../../../../vendor/autoload.php';

/////// CONFIG ///////
$username = 'alberto_dreyes';
$password = 'Alredi19';
// $username = 'marcos.socialhub';
// $password = 'Marcos*01+123';
// $username = 'alberto_test';
// $password = 'alberto3';
// $username = 'socialhub.pro';
// $password = 'Marcos*01+123000';
$proxy = "http://albertreye9917:3r4rcz0b1v@207.188.155.18:21316";
$debug = false;
$debug = true;
$truncatedDebug = false;
//////////////////////

\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

if ($proxy && isValidProxy($proxy)) {
    $ig->setProxy($proxy);
}

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
    exit(0);
}

try {

    // Let's list media for the "nature" hashtag.
    // $tag = 'nature';
    $tag = '213369';

    // Generate a random rank token.
    $rankToken = \InstagramAPI\Signatures::generateUUID();

    // $ig->session_id = "";
    var_dump($tag);
    $resp = $ig->people->follow("5439579511");
    ddd($resp);

    // Starting at "null" means starting at the first page.
    $maxId = null;
    // do {
    // Request the page corresponding to maxId.
    // Note that we are using the same rank token for all pages.
    // $response = $ig->hashtag->getFeed($tag, $rankToken, $maxId);
    $response = $ig->location->getFeed($tag, $rankToken);

    $collectRecentItems = array();
    foreach ($response->getSections() as $items) {
        foreach ($items->getLayoutContent()->getMedias() as $item) {
            array_push($collectRecentItems, $item->getMedia());
            $mediaCounter += 1;
        }
    }

    // ddd($response);
    dd($collectRecentItems);

    // In this example we're simply printing the IDs of this page's items.
    // foreach ($items as $item) {
    //     printf("[%s] https://instagram.com/p/%s/\n\r\n\r", $item->getId(), $item->getCode());
    // }

    // Now we must update the maxId variable to the "next page".
    // This will be a null value again when we've reached the last page!
    // And we will stop looping through pages as soon as maxId becomes null.
    $maxId = $response->getNextMaxId();

    // Sleep for 5 seconds before requesting the next page. This is just an
    // example of an okay sleep time. It is very important that your scripts
    // always pause between requests that may run very rapidly, otherwise
    // Instagram will throttle you temporarily for abusing their API!
    echo "Sleeping for 5s...\n";
    //     sleep(5);
    // } while ($maxId !== null); // Must use "!==" for comparison instead of "!=".
} catch (\Exception $e) {
    echo 'Something went wrong: ' . $e->getMessage() . "\n";
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
        $res = $client->request('GET', 'http://www.instagram.com',
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
