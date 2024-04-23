<?php

namespace App\Services;

use GuzzleHttp\Client;

class OpenAIService
{
    protected $client;
    protected $apiKey = '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';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function generateCompletion($prompt)
    {
        $response = $this->client->post('/v1/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo', // Specify the desired model
                'prompt' => $prompt,
                'temperature' => 0.7, // Adjust as needed
                'max_tokens' => 50,   // Adjust as needed
                'n' => 1,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
