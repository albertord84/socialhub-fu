<?php

namespace InstagramAPI;

class ExtendedInstagram extends Instagram
{
    public function changeUser($username, $password)
    {
        $this->_setUser($username, $password);
    }

    /**
     * Send the choice to get the verification code in case of checkppoint.
     * @param  string $apiPath Challange api path
     * @param  int $choice     Choice of the user. Possible values: 0, 1
     * @return Array
     */
    public function sendChallangeCode($apiPath, $choice)
    {
        // sleep(2);

        if (!is_string($apiPath) || !$apiPath) {
            throw new \InvalidArgumentException('You must provide a valid apiPath to sendChallangeCode().');
        }

        // $apiPath = ltrim($apiPath, "/");

        // return $this->request($apiPath)
        //     ->setNeedsAuth(false)
        //     ->addPost('choice', $choice)
        //     ->getDecodedResponse(false);

        // $apiPath = ltrim($apiPath, "/");
        $checkApiPath = $apiPath;
        $arrayChallenge = explode('/', $checkApiPath);
        $this->account_id = $this->account_id ?? $arrayChallenge[1];
        $customResponse = $this->request($checkApiPath)
            ->setNeedsAuth(false)
            ->addPost('choice', $choice)
            ->addPost('_uuid', $this->uuid)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getDecodedResponse(false);
        if (is_object($customResponse)) {
            if ($customResponse->status == 'fail' && strpos('file is required', $customResponse->status) > 0) {
                $customResponse->status == 'ok';
            }
            $user_id = $customResponse->user_id ?? $customResponse->user_id;
            $challenge_id = $customResponse->nonce_code;
        } else {
            throw new \InvalidArgumentException('Weird response from challenge request in sendChallengeCode().');
            // var_dump($customResponse);
            // exit;
        }
        return $customResponse;
    }

    /**
     * Re-send the virification code for the checkpoint challenge
     * @param  string $username Instagram username. Used to load user's settings
     *                          from the database.
     * @param  string $apiPath  Api path to send a resend request
     * @return Array
     */
    public function resendChallengeCode($username, $apiPath, $choice)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('You must provide a username resendChallengeCode().');
        }

        if (empty($apiPath)) {
            throw new \InvalidArgumentException('You must provide a api path to resendChallengeCode().');
        }

        $this->_setUserWithoutPassword($username);

        // $apiPath = ltrim($apiPath, "/");

        $customResponse = $this->request($apiPath)
            ->setNeedsAuth(false)
            ->addPost('choice', $choice)
            ->getDecodedResponse(false);

        return $customResponse;
    }

    /**
     * Finish a challenge login
     *
     * This function finishes a checkpoint challenge that was provided by the
     * sendChallangeCode method. If you successfully answer their challenge,
     * you will be logged in after this function call.
     *
     * @param  string  $username           Instagram username.
     * @param  string  $password           Instagram password.
     * @param  string  $apiPath            Relative path to the api endpoint
     *                                     for the challenge.
     * @param  string  $securityCode       Verification code you have received
     *                                     via SMS or Email.
     * @param  integer $appRefreshInterval See `login()` for description of this
     *                                     parameter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function finishChallengeLogin(
        $username,
        $password,
        $apiPath,
        $securityCode,
        $appRefreshInterval = 1800
    ) {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to finishChallengeLogin().');
        }
        if (empty($apiPath) || empty($securityCode)) {
            throw new \InvalidArgumentException('You must provide a api path and security code to finishChallengeLogin().');
        }

        // Remove all whitespace from the verification code.
        $securityCode = preg_replace('/\s+/', '', $securityCode);

        // $this->_setUser($username, $password);
        // $this->_sendPreLoginFlow();

        // $customResponse = $this->request(ltrim($apiPath, "/"))
        //     ->setNeedsAuth(false)
        //     ->addPost('security_code', $securityCode)
        //     ->getResponse(new Response\LoginResponse());

        $this->changeUser($username, $password);
        $customResponse = $this->request($apiPath)
            ->setNeedsAuth(false)
            ->addPost('security_code', $securityCode)
            ->addPost('_uuid', $this->uuid)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_uid', $this->account_id)
			->addPost('_csrftoken', $this->client->getToken())
            // ->getDecodedResponse();
			->getResponse(new Response\LoginResponse());

        // if ($customResponse['status'] === 'ok' && (int) $customResponse['logged_in_user']['pk'] === (int) $this->account_id) {
            // echo 'Finished, logged in successfully! Run this file again to validate that it works.';
        // } else {
            // echo "Probably finished...\n";
            // var_dump($customResponse);
        // }

        // $prettyRespose1 = $customResponse->printJson();
        $prettyRespose2 = $customResponse->asStdClass();
        $this->_updateLoginState($customResponse);
        $this->_sendLoginFlow(true, $appRefreshInterval);

        return $customResponse;
    }
}
