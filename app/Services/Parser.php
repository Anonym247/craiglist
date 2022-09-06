<?php

namespace App\Services;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;

class Parser
{
    /**
     * @var string[]
     */
    private array $headers;
    private array $filter;
    /**
     * @var Repository|Application|mixed
     */
    private $url;

    public function __construct()
    {
        $this->headers = [
            'Content-Type: application/json',
//            'x-api-key: ' . config('parser.key'),
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br'
        ];

//        $this->filter = [
//            'page' => config('parser.page'),
//            'pageSize' => config('parser.page_size'),
//            'listPriceMax' => config('parser.price_max'),
//            'listPriceMin' => config('parser.price_min'),
//            'yearMax' => config('parser.year_max'),
//            'yearMin' => config('parser.year_min'),
//        ];

        $this->url = config('parser.url');

        $this->threads = config('parser.threads', 1);

//        $this->proxies = json_decode(file_get_contents(public_path('proxies.json')), true);
    }

    public function getAreas(): void
    {
        if (DB::table('areas')->count()) {
            return;
        }

        $areas = file_get_contents(config('parser.urls.areas'));
        $insertArray = [];

        if (!$areas) {
            throw new \Exception('Areas not found');
        }

        $areas = json_decode($areas, true);

        foreach ($areas as $area) {
            $insertArray[] = [
                'area_id' => $area['AreaID'],
                'country' => $area['Country'],
                'region' => $area['Region'],
                'host' => $area['Hostname'],
                'is_completed' => 0
            ];
        }

        DB::table('areas')->insertOrIgnore($insertArray);
    }

    public function getCarLinks()
    {

    }

    public function singleCurl()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
//        curl_setopt($ch, CURLINFO_CONTENT_TYPE, 'application/json');
//        curl_setopt($ch, CURLOPT_PROXY, $this->setProxy());
        $result = curl_exec($ch);
        $response = json_decode(curl_exec($ch), true);
//        curl_close($ch);
        dd($response, $ch);

        return $response;
    }

    /**
     * @return Repository|Application|mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param Repository|Application|mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }
}
