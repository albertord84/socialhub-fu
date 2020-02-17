<?php

set_time_limit(0);
date_default_timezone_set('UTC');

var_dump(date('Y-m-d H:i:s', time()));

require __DIR__ . '/../../../../vendor/autoload.php';

/////// CONFIG ///////
// $username = 'thiagobrunodias';
// $password = 'luna2307';
// $username = 'alberto_dreyes';
// $password = 'albertord2';
// $username = 'marcos.socialhub';
// $password = 'Marcos*01+123';
$username = 'alberto_test';
$password = 'alberto3';
// $username = 'socialhub.pro';
// $password = 'Marcos*01+123000';

// $username = 'josergm86';
// $password = 'josergm2';

// $username = 'draflavia.drniltoncamposjr';
// $password = 'conexao1';

// $proxy = "http://albertosocialhub:albertosocialhubproxy@br.smartproxy.com:16390";
// $proxy = "https://EdHgVA:wbydcf@168.235.109.152:29548";
// $proxy = "https://mTNPvr:GZcaDW@168.235.93.240:24272";
$proxy = "https://mTNPvr:GZcaDW@168.235.93.240:24272";

$debug = true;
$truncatedDebug = false;
\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
$verification_method = 0; //0 = SMS, 1 = Email

//////////////////////

$ig = new \InstagramAPI\ExtendedInstagram($debug, $truncatedDebug);

if ($proxy && isValidProxy($proxy)) {
    $ig->setProxy($proxy);
}

try {
    $loginResponse = $ig->login($username, $password);
    echo "Logged in!\n\r";

    echo "Trying to follow: \n\r";
    $resp = $ig->people->follow("5439579511");
    ddd($resp);
} catch (Exception $exception) {

    $response = $exception->getResponse();
    // var_dump($response);

    if ($exception instanceof InstagramAPI\Exception\ChallengeRequiredException
        && $response->getErrorType() === 'checkpoint_challenge_required') {

        sleep(3);

        $checkApiPath = substr($response->getChallenge()->getApiPath(), 1);
        $arrayChallenge = explode('/', $checkApiPath);
        $ig->account_id = $ig->account_id ?? $arrayChallenge[1];
        $customResponse = $ig->request($checkApiPath)
            ->setNeedsAuth(false)
            ->addPost('choice', $verification_method)
            ->addPost('_uuid', $ig->uuid)
            ->addPost('guid', $ig->uuid)
            ->addPost('device_id', $ig->device_id)
            ->addPost('_uid', $ig->account_id)
            ->addPost('_csrftoken', $ig->client->getToken())
            ->getDecodedResponse(false);
        if (is_object($customResponse)) {
            if ($customResponse->status == 'fail' && strpos('file is required', $customResponse->status) > 0) {
                $customResponse->status == 'ok';
            }
            $user_id = is_object($customResponse->logged_in_user) ? $customResponse->logged_in_user->pk : 0;
            $challenge_id = $customResponse->nonce_code;
        } else {
            echo "Weird response from challenge request...\n";
            var_dump($customResponse);
            exit;
        }
    } else {
        echo "Not a challenge required exception...\n";
        exit;
    }

    try {

        if ($customResponse->status === 'ok' && isset($customResponse->action)
            && $customResponse->action === 'close') {
            echo 'Checkpoint bypassed';
            exit();
        }

        /// TODO Alberto
        $code = readln('Code that you received via ' . ($verification_method ? 'email' : 'sms') . ':');
        // $code = "490673";
        $ig->changeUser($username, $password);
        $customResponse = $ig->request($checkApiPath)
            ->setNeedsAuth(false)
            ->addPost('security_code', $code)
            ->addPost('_uuid', $ig->uuid)
            ->addPost('guid', $ig->uuid)
            ->addPost('device_id', $ig->device_id)
            ->addPost('_uid', $ig->account_id)
            ->addPost('_csrftoken', $ig->client->getToken())
            ->getDecodedResponse(false);

        if ($customResponse->status === 'ok' && (int) $customResponse->logged_in_user->pk === (int) $user_id) {
            // if ($customResponse->status === 'ok' && (int) $customResponse->logged_in_user['pk'] === (int) $user_id) {
            echo 'Finished, logged in successfully! Run this file again to validate that it works.';
        } else {
            echo "Probably finished...\n";
            var_dump($customResponse);
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
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

function readln($prompt)
{
    return trim((string) readline("$prompt "));
}
