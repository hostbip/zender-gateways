<?php
/**
 * SendPulse SMS Gateway
 * @autor RenatoAscencio
 */

define("SENDPULSE_GATEWAY", [
    "apiUrl" => "https://api.sendpulse.com",
    "apiId" => "YOUR_API_ID", // Replace with your SendPulse API ID
    "apiSecret" => "YOUR_API_SECRET", // Replace with your SendPulse API Secret
    "senderName" => "YOUR_SENDER_NAME" // Replace with your configured sender name
]);

function getAccessToken() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SENDPULSE_GATEWAY["apiUrl"] . "/oauth/access_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "grant_type" => "client_credentials",
        "client_id" => SENDPULSE_GATEWAY["apiId"],
        "client_secret" => SENDPULSE_GATEWAY["apiSecret"]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        return null;
    }
    curl_close($ch);

    $response = json_decode($result, true);
    return $response['access_token'] ?? null;
}

return [
    "send" => function ($phone, $message, &$system) {
        /**
         * Implement sending here
         * @return bool:true
         * @return bool:false
         */

        $accessToken = getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $send = $system->guzzle->post(SENDPULSE_GATEWAY["apiUrl"] . "/sms/send", [
            "headers" => [
                "Authorization" => "Bearer " . $accessToken,
                "Content-Type" => "application/json"
            ],
            "json" => [
                "sender" => SENDPULSE_GATEWAY["senderName"],
                "phones" => [$phone],
                "body" => $message
            ],
            "allow_redirects" => true,
            "http_errors" => false
        ]);

        if ($send->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    },
    "callback" => function ($request, &$system) {
        /**
         * Implement status callback here if gateway supports it
         * @return array:MessageID
         * @return array:Empty
         */
    }
];
?>
