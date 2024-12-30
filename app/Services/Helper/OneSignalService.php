<?php

namespace App\Services\Helper;

use GuzzleHttp\Client;

class OneSignalService
{
    protected $appId;
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.api_key');
        $this->client = new Client([
            'base_uri' => 'https://onesignal.com/api/v1/',
            'headers' => [
                'Authorization' => "Basic {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    // إرسال إشعار بناءً على التوكن
    public function sendNotificationToToken($token, $title, $message, $data = [])
    {
        $payload = [
            'app_id' => $this->appId,
            'include_player_ids' => [$token],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data,
        ];

        return $this->sendRequest('notifications', $payload);
    }

    // إرسال إشعار بناءً على القناة
    public function sendNotificationToChannel($channel, $title, $message, $data = [])
    {
        $payload = [
            'app_id' => $this->appId,
            'included_segments' => [$channel],
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'data' => $data,
        ];

        return $this->sendRequest('notifications', $payload);
    }

    // استقبال التوكن من الموبايل أو الويب
    public function registerToken($token, $deviceType)
    {
        $payload = [
            'app_id' => $this->appId,
            'identifier' => $token,
            'device_type' => $deviceType, // 1 = iOS, 2 = Android
        ];

        return $this->sendRequest('players', $payload);
    }

    // إنشاء قناة جديدة
    public function createChannel($channel)
    {
        // OneSignal لا يدعم إنشاء قنوات ديناميكيًا
        return response()->json(['message' => 'Channels are managed via segments in OneSignal Dashboard.']);
    }

    // حذف قناة (غير مدعومة في OneSignal)
    public function deleteChannel($channel)
    {
        // OneSignal لا يدعم حذف القنوات ديناميكيًا
        return response()->json(['message' => 'Channels are managed via segments in OneSignal Dashboard.']);
    }

    // دالة مساعدة لإرسال الطلبات
    protected function sendRequest($endpoint, $payload)
    {
        try {
            $response = $this->client->post($endpoint, [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
