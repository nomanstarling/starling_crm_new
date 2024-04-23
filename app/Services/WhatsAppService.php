<?php

namespace App\Services;
use App\Models\Leads;
use App\Models\User;

class WhatsAppService
{
    function whatsapp_notify($number, $template = 'new_crm_lead_v9', $name = null, $email = null, $debug = false, $leadid = null)
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
            echo 'First Name : ' . $name . '<br>';
            echo 'Email : ' . $email . '<br>';
            exit;
        }

        // first of all, add the contact to respond.io
        $client = new \GuzzleHttp\Client();

        $contact_payload = [
            'phone' => $number,
            "language" => "en"
        ];

        if ($name){
            $contact_payload['firstName'] = $name;
        }

        if ($name){
            $contact_payload['lastName'] = $name;
        }

        if ($email)
        {
            $contact_payload['email'] = $email;
        }

        if ($leadid){
            $contact_payload['leadid'] = $leadid;
        }

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

        // $response = $client->request('POST', 'https://api.respond.io/v2/contact/' . $respond_io_phone_number . '/message', [
        //     'body' => json_encode([
        //         "channelId" => $respond_io_channel_id,
        //         "message" => [
        //             "type" => "whatsapp_template",
        //             "template" => [
        //                 "name" => $respond_io_whatsapp_template,
        //                 "languageCode" => "en",
        //                 "components" => [
        //                     $contact_payload
        //                 ]
        //             ]
        //         ]
        //     ]),
        //     'headers' => [
        //         'Accept' => 'application/json',
        //         'Authorization' => 'Bearer ' . $respond_io_api_key,
        //         'Content-Type' => 'application/json',
        //     ],
        // ]);

        return $response->getBody();
    }

    function notify($agent_id, $template = 'new_crm_lead_v9')
    {
        if (is_numeric($agent_id))
        {
            $agent = User::select('name', 'email', 'phone')->where('id', $agent_id)->first();
            $this->whatsapp_notify($agent->phone, $template, $agent->name, $agent->email);
        }
    }

    public function sendMessage()
    {
        // Your Respond.io API key
        //$apiKey = 'YOUR_API_KEY';

        $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MzE2Mywic3BhY2VJZCI6MTI3NjYxLCJvcmdJZCI6MTMwMjc0LCJ0eXBlIjoiYXBpIiwiaWF0IjoxNjkwOTE3MDIzfQ.cOUDP18yzoSkZGUBRHigfXZAxTWAq9jG4B4NkRXcugs';
        $respond_io_channel_id = '152478';

        // WhatsApp number to send the message to
        $phoneNumber = '971554178772';

        // Message content
        $message = 'Hi please check your CRM, a new lead has landed there.';

        // Prepare the request body
        $payload = [
            'phone_number' => $phoneNumber,
            'message' => [
                'type' => 'text',
                'text' => $message
            ]
        ];

        // Create a Guzzle HTTP client
        //$client = new Client();
        $client = new \GuzzleHttp\Client();

        try {
            // Send the POST request to the Respond.io API
            $response = $client->request('POST', 'https://api.respond.io/v2/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            // Get the response body
            $body = $response->getBody()->getContents();

            // Handle the response as needed
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'response' => $body
            ]);

        } catch (\Exception $e) {
            // Handle any exceptions or errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
