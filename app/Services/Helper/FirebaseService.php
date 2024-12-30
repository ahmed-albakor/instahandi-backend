<?php

namespace App\Services\Helper;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    /**
     * الاشتراك في موضوع باستخدام توكن.
     */
    public static function subscribeToTopic($registrationToken, $topic)
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $topic)) {
            return [
                'success' => false,
                'message' => 'Topic name format is invalid',
            ];
        }

        $messaging = self::getFirebaseMessaging();

        try {
            $response = $messaging->subscribeToTopic($topic, $registrationToken);
            return [
                'success' => true,
                'message' => 'Successfully subscribed to topic',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to subscribe to topic',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * إرسال إشعار إلى موضوع معين.
     */
    public static function sendToTopic($topic, $title, $body, $data = [])
    {
        $messaging = self::getFirebaseMessaging();

        $messageConfig = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        $message = CloudMessage::fromArray($messageConfig);

        try {
            $response = $messaging->send($message);
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * إلغاء الاشتراك من موضوع معين.
     */
    public static function unsubscribeFromTopic($registrationToken, $topic)
    {
        $messaging = self::getFirebaseMessaging();

        try {
            $response = $messaging->unsubscribeFromTopic($topic, $registrationToken);
            return [
                'success' => true,
                'message' => 'Successfully unsubscribed from topic',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to unsubscribe from topic',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * حذف موضوع من توكن معين.
     */
    public static function removeTopicFromToken($registrationToken, $topic)
    {
        // تحقق من تنسيق الموضوع
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $topic)) {
            return [
                'success' => false,
                'message' => 'Topic name format is invalid',
            ];
        }

        $messaging = self::getFirebaseMessaging();

        try {
            $response = $messaging->unsubscribeFromTopic($topic, $registrationToken);
            return [
                'success' => true,
                'message' => 'Successfully removed topic from token',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to remove topic from token',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * إعداد Messaging من Firebase.
     */
    protected static function getFirebaseMessaging()
    {
        $serviceAccount = self::loadServiceAccount();

        return (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createMessaging();
    }

    /**
     * تحميل بيانات حساب الخدمة.
     */
    protected static function loadServiceAccount()
    {
        $serviceAccountPath = storage_path('firebase/instahandi-dc4e1-firebase-adminsdk-8jtnl-ef5d071a0f.json');

        if (!file_exists($serviceAccountPath)) {
            throw new \Exception("Firebase service account file not found.");
        }

        return json_decode(file_get_contents($serviceAccountPath), true);
    }
}
