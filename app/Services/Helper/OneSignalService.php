<?php


namespace App\Services\Helper;

use Illuminate\Support\Facades\Http;


class OneSignalService
{

    private string $baseUrl;
    private string $oneSignalAppId;
    private string $oneSignalAuthKey;
    private string $oneSignalAuthorize;

    private array $query;
    private $http;


    private string $aliasLabel;
    private string $sendMode;

    const ALIAS_MODE = 'alias';
    const PLAYER_ID_MODE = 'player_id';

    public function __construct()
    {
        //  تحميل ملف الاعداد الخاص ب onsignal
        $config = config('onesignal');

        $this->baseUrl = $config['base_url'];

        $this->oneSignalAppId = $config['one_signal_app_id'];

        $this->oneSignalAuthKey = $config['one_signal_auth_key'];

        // REST API key in onesignal.com
        $this->oneSignalAuthorize = $config['one_signal_authorize'];

        $this->aliasLabel = $config['alias_label'];

        $this->sendMode = self::ALIAS_MODE;

        $this->query['app_id'] = $this->oneSignalAppId;

        // Add to header (Authorization: Bearer one-signal-authorize-token)
        $this->http = Http::withToken($this->oneSignalAuthorize);
    }



    public function setMode($mode = self::ALIAS_MODE)
    {
        $this->sendMode = $mode;
    }

    public function getUserIdentity(string $identifier)
    {
        //https://onesignal.com/api/v1/apps/{{app_id}}/users/by/alias_label/$identifier/identity
        $url = implode('/', [
            $this->baseUrl,
            'apps',
            $this->oneSignalAppId,
            'users/by',
            $this->aliasLabel,
            $identifier,
            'identity'
        ]);

        $response = $this->http->get($url);


        return [
            'status_code' => $response->status(),
            'data' => $response->json()
        ];
    }


    public function getNotification(string $identifier)
    {
        //https://onesignal.com/api/v1/notifications/{notification_id}?app_id={app_id}

        $url = implode('/', [
            $this->baseUrl,
            'notifications',
            $identifier

        ]);

        $response = $this->http->get($url, $this->query);

        return [
            'status_code' => $response->status(),
            'data' => $response->json()
        ];
    }


    public function sendNotification(array $playersId, array $data): array
    {
        //https://onesignal.com/api/v1/notifications

        $url = implode('/', [
            $this->baseUrl,
            'notifications'
        ]);

        $postData = [
            'app_id' => $this->oneSignalAppId,
            'target_channel' => 'push',
            'contents' => ['en' => $data['contents'] ?? throw new \Exception('no contents provided !')],
        ];

        switch ($this->sendMode) {
            case self::ALIAS_MODE:
                $postData['include_aliases'] = [$this->aliasLabel => $playersId];
                break;
            case self::PLAYER_ID_MODE:
                $postData['include_player_ids'] =  $playersId;
                break;

            default:
                $postData['include_aliases'] = [$this->aliasLabel => $playersId];
                break;
        }

        if ($data['headings'] ?? false) {
            $postData['headings'] = ['en' => $data['headings']];
        }

        if ($data['subtitle'] ?? false) {
            $postData['subtitle'] = ['en' => $data['subtitle']];
        }

        if ($data['picture'] ?? false) {
            $postData['big_picture'] =  $data['picture'];
        }

        if ($data['url'] ?? false) {
            $postData['url'] = $data['url'];
        }

        $response = $this->http->post($url, $postData);

        return [
            'status' => $response->status(),
            'data' => $response->json()
        ];
    }
}


// $onesignal = new OneSignal();

// // return $onesignal->getUserIdentity(188);
// // return $onesignal->getNotification('edadb48e-3c23-4aa7-b7d1-f937aadfd51a');

// $n = [
//     'contents' => 'hello from laravel',
//     // 'picture' => 'https://cloudinary-marketing-res.cloudinary.com/images/w_1000,c_scale/v1679921049/Image_URL_header/Image_URL_header-png?_i=AA',
// ];

// return $onesignal->sendNotification(['188'],  $n);