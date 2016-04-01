<?php

namespace App\Service;

use Silex\Application;
use Symfony\Component\DomCrawler\Crawler;

class Avito
{
    public $parseUrl = 'https://m.avito.ru/perm/audio_i_video/televizory_i_proektory';

    public $priceMin = 8000;
    public $priceMax = 20000;

    public function __construct(Application $app)
    {
        $this->app = $app;
//        $this->app['parser']->useCache(true);

    }

    public function start()
    {

        $previousId = $this->getPreviousId();

        $data = $this->getData();
        $new = $this->getNewItems($data, $previousId);

//        print_r($new);

        $this->sendAlerts($new);

        if ($new) {
            $this->setPreviousId($new[0]['id']);
        }

    }

    public function sendAlerts($data)
    {
        foreach ($data as $item) {

            if (!($this->priceMin <= $item['price'] && $item['price'] <= $this->priceMax)) {
                continue;
            }
            $message = [];

            $description = $this->getItemDescription($item['link']);
            
            $message[] = '*' . $item['title'] . '*';
            $message[] = 'Цена: ' . number_format($item['price']);
            $message[] = $description['seller'];
            $message[] = $description['text'];
            $message[] = $item['link'];

            $message = implode("\n", $message);

            $this->app['monolog.alert']->info($message);

        }
    }

    public function getItemDescription($url)
    {
        $crawler = $this->app['parser']->get($url);
        $text = trim($crawler->filterXPath('//div[@id="description"]/div/div')->html());
        $text = str_replace(['<br>', '<p>', '</p>'], "\n", $text);
        $text = preg_replace("#\n+#", "\n", $text);

        $seller = trim($crawler->filterXPath('//div[@class="person-name"]')->text());
        $seller = str_replace("\n",' ',$seller);
        
        return ['seller' => $seller, 'text' => $text];
    }

    public function getNewItems($data, $previousId)
    {
        $new = [];
        foreach ($data as $item) {
            if ($item['id'] == $previousId) {
                return $new;
            } else {
                $new[] = $item;
            }
        }

        return $new;
    }

    public function getPreviousId()
    {
        $file = $this->app['root.path'] . '/var/previousId';
        $previousId = '';

        if (is_file($file)) {
            $previousId = file_get_contents($file);
        }

        return $previousId;
    }

    public function setPreviousId($previousId)
    {
        $file = $this->app['root.path'] . '/var/previousId';

        file_put_contents($file, $previousId);
    }


    public function getData()
    {
        $crawler = $this->app['parser']->get($this->parseUrl);

        $data = [];
        $crawler->filterXPath('//article[@data-item-premium="0"]')->each(function ($crawler) use (&$data) {
            $data[] = $this->processArticle($crawler);
        });

        return $data;
    }

    public function processArticle(Crawler $crawler)
    {
        return [
            'id' => $crawler->attr('data-item-id'),
            'title' => $crawler->filterXPath('//span[@class="header-text"]')->text(),
            'price' => preg_replace('#[\D]*#', '', $crawler->filterXPath('//div[@class="item-price "]')->text()),
            'link' => 'https://m.avito.ru' . $crawler->filterXPath('//a[@class="item-link"]')->attr('href'),
        ];
    }

}