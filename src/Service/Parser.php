<?php

namespace App\Service;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    protected $client;
    protected $ttl;
    private $cachePath;
    private $useCache = false;
    private $secondRequest = false;
    private $delay = 30;
    private $timeout = 15;

    public function getClient()
    {
        return $this->client;
    }

    /**
     * задержка в секундах
     */
    public function setDelay($seconds)
    {
        $this->delay = $seconds;
        return $this;
    }

    public function setCacheTtl($seconds)
    {
        $this->ttl = $seconds;
        return $this;
    }

    public function useCache($val)
    {
        $this->useCache = $val;
        return $this;
    }

    protected function sleep()
    {
        if ($this->delay) {
            $min = (int)($this->delay / 2 * 1000);
            $max = $this->delay * 1000;
            usleep(mt_rand($min, $max) * 1000);
        }
    }


    public function __construct($cachePath, $ttl = 3600)
    {
        $this->setCacheTtl($ttl);
        $this->cachePath = $cachePath;

        $this->client = new Client();

        $this->client->setClient(new \GuzzleHttp\Client(
            [
//                'stream' => true,
                'defaults' => [
                    'timeout' => $this->timeout,
                    'connect_timeout' => $this->timeout,
                ],
                'timeout' => $this->timeout,
                'connect_timeout' => $this->timeout,
            ]
        ));
    }

    public function get($url, $useCache = true)
    {

        if (!$url) {
            throw  new \Exception('Url is empty');
        }
        if ($this->useCache && $useCache && $cache = $this->cacheGet($url)) {
            return new Crawler($cache);
        }

        if ($this->secondRequest) {
            $this->sleep();
        }
//        var_dump($url);
        $crawler = $this->client->request('GET', $url, ['connect_timeout' => $this->timeout, 'timeout' => $this->timeout]);

        $html = null;
        if ($crawler->count()) {
            $html = $crawler->html();
        }

        if ($this->useCache) {
            $this->cacheSet($url, $html);
        }

        $this->secondRequest = true;
        return $crawler;
    }

    public function getHtml($url)
    {
        if (!$url) {
            throw  new \Exception('Url is empty');
        }

        if ($this->useCache && $cache = $this->cacheGet($url)) {
            return $cache;
        }
        if ($this->secondRequest) {
            $this->sleep();
        }
        var_dump($url);
        $response = $this->client->getClient()->get($url);
        $html = $response->getBody();

        if ($this->useCache) {
            $this->cacheSet($url, $response->getBody());
        }

        $this->secondRequest = true;
        return $html;
    }

    public function getJson($url)
    {
        if (!$url) {
            throw  new \Exception('Url is empty');
        }

        if ($this->useCache && $cache = $this->cacheGet($url)) {
            return json_decode($cache, true);
        }
        if ($this->secondRequest) {
            $this->sleep();
        }
        //var_dump($url);
        $response = $this->client->getClient()->get($url);
        $json = json_decode($response->getBody(), true);

        if ($this->useCache) {
            $this->cacheSet($url, $response->getBody());
        }

        $this->secondRequest = true;
        return $json;
    }

    protected function cacheGet($url)
    {
        $key = $this->cachePath . '/' . md5($url);

        if (is_file($key) && filemtime($key) > time() - $this->ttl) {
            return file_get_contents($key);
        }
        return false;
    }

    protected function cacheSet($url, $data)
    {
        $key = $this->cachePath . '/' . md5($url);
        return file_put_contents($key, $data);
    }
}