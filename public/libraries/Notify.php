<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('whatsapp_notify')) {
    
    function whatsapp_notify($number, $template = 'new_crm_lead_v9', $first_name = null, $last_name = null, $email = null, $debug = false, $leadid = null)
    {
        // remove all non-numeric characters from the number
        $number = preg_replace('/[^0-9]/', '', $number);

        $respond_io_api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MzE2Mywic3BhY2VJZCI6MTI3NjYxLCJvcmdJZCI6MTMwMjc0LCJ0eXBlIjoiYXBpIiwiaWF0IjoxNjkwOTE3MDIzfQ.cOUDP18yzoSkZGUBRHigfXZAxTWAq9jG4B4NkRXcugs';
        $respond_io_channel_id = '152478';
        $respond_io_phone_number = 'phone:' . $number;
        $respond_io_whatsapp_template = $template;

        if ($debug == true) {
            echo 'Number : ' . $number . '<br>';
            echo 'Template : ' . $template . '<br>';
            echo 'First Name : ' . $first_name . '<br>';
            echo 'Last Name : ' . $last_name . '<br>';
            echo 'Email : ' . $email . '<br>';
            exit;
        }

        // first of all, add the contact to respond.io
        $client = new \GuzzleHttp\Client();

        $contact_payload = [
            'phone' => $number,
            "language" => "en"
        ];

        if ($first_name)
            $contact_payload['firstName'] = $first_name;

        if ($last_name)
            $contact_payload['lastName'] = $last_name;

        if ($email)
            $contact_payload['email'] = $email;

        if ($leadid)
            $contact_payload['leadid'] = $leadid;

        try {
            $response = $client->request('POST', 'https://api.respond.io/v2/contact/' . $respond_io_phone_number, [
                'body' => json_encode($contact_payload),
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $respond_io_api_key,
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
        }

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.respond.io/v2/contact/' . $respond_io_phone_number . '/message', [
            'body' => '{
                "channelId": ' . $respond_io_channel_id . ',
                "message": {
                    "type": "whatsapp_template",
                    "template": {
                        "name": "' . $respond_io_whatsapp_template . '",
                        "languageCode": "en",
                        "components": [ 
                            "'.$contact_payload.'"
                        ]
                    }
                }
            }',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $respond_io_api_key,
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response->getBody();
    }

}