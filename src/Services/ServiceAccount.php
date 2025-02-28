<?php

namespace Railroad\Railnotifications\Services;

use FCM;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class ServiceAccount
{
    protected $client;

    public function __construct()
    {
        $serviceAccountCredentials = config('railnotifications.google_service_account_credentials');

        $this->client = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $serviceAccountCredentials
        );
    }

    public function getAccessToken()
    {
        $token = $this->client->fetchAuthToken(HttpHandlerFactory::build());

        return $token['access_token'];
    }
}
