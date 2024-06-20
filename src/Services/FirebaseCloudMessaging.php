<?php

namespace Railroad\Railnotifications\Services;

use FCM;
use GuzzleHttp\Client;

class FirebaseCloudMessaging
{
    protected $client;
    protected $projectId;
    protected $accessToken;

    public function __construct(ServiceAccount $serviceAccount)
    {
        $this->client = new Client();
        $this->projectId = 'drumeo-app';
        $this->accessToken = $serviceAccount->getAccessToken();
    }

    public function sendMessage($notifications)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $headers = array(
            'Authorization: Bearer '.$this->accessToken,
            'Content-Type: application/json'
        );

        $multiCurl = array();
        $mh = curl_multi_init();
        curl_multi_setopt($mh, CURLMOPT_PIPELINING,CURLPIPE_MULTIPLEX);

        foreach ($notifications as $i => $notification) {
            $multiCurl[$i] = curl_init();
            curl_setopt($multiCurl[$i], CURLOPT_URL,$url);
            curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, $headers);
            curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,true);
            curl_setopt($multiCurl[$i], CURLOPT_POST, true);
            curl_setopt($multiCurl[$i], CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($multiCurl[$i], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($multiCurl[$i], CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_2_0);
            curl_setopt($multiCurl[$i], CURLOPT_POSTFIELDS, json_encode($notification,JSON_UNESCAPED_UNICODE));
            curl_multi_add_handle($mh, $multiCurl[$i]);
        }

        $index=null;
        do {
            curl_multi_exec($mh,$index);
            curl_multi_select($mh);
        } while($index > 0);
        foreach($multiCurl as $k => $ch) {
            $result[$k] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        return response()->json($result);
    }
}
