<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TextLkService
{
    protected $apiKey;
    protected $senderId;
    protected $apiUrl = 'https://app.text.lk/api/v3/sms/send';

    public function __construct()
    {
        $this->apiKey = env('TEXTLK_API_KEY');
        $this->senderId = env('TEXTLK_SENDER_ID');
    }

    public function sendSms($recipient, $message, $scheduleTime = null)
    {
        $data = [
            'recipient' => $recipient,
            'sender_id' => $this->senderId,
            'type' => 'plain',
            'message' => $message,
        ];

        if ($scheduleTime) {
            $data['schedule_time'] = $scheduleTime;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->apiUrl, $data);

        return $response->json();
    }
}
