<?php


namespace App\Services;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AvitoService
{
    private $client;

    public $listUrl = 'https://m.avito.ru/perm/audio_i_video/televizory_i_proektory?user=1';
    public $priceMin = 8000;
    public $priceMax = 22000;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function start()
    {
        $crawler = $this->fetchListUrl();
        $items = $this->parseItemsList($crawler);
        if (!$items) {
            throw new NotFoundHttpException('e');
        }

        $newItems = $this->getNewItems($items);

        $this->sendAlerts($newItems);
    }

    public function sendAlerts($data)
    {
        foreach ($data as $item) {

            $message = [];

            $description = $this->getItemDescription($item['link']);

            $message['text'][] = '*' . $item['title'] . '* Цена: ' . number_format($item['price']);
            $message['text'][] = $description['seller'];
            $message['text'][] = $item['link'];
            $message['text'][] = $description['text'];
            $message['text'] = implode("\n", $message['text']);

            if ($description['image']) {
                $message['image_url'] = $description['image'];
            }


            \Slack::createMessage()
                  ->attach($message)
                  ->send();
        }
    }

    public function getItemDescription($url)
    {
        $crawler = $this->client->request('GET', $url);
        return $this->parseItemContent($crawler);
    }

    public function getNewItems($items)
    {


        $cachedItems = \Cache::get('avito-list', []);
        $newItemsIds = array_diff(array_keys($items), (array)$cachedItems);
        \Cache::forever('avito-list', array_keys($items));

        $newItems = [];
        foreach ($newItemsIds as $itemId) {
            if (!($this->priceMin <= $items[$itemId]['price'] && $items[$itemId]['price'] <= $this->priceMax)) {
                continue;
            }
            $newItems[$itemId] = $items[$itemId];
        }

        return $newItems;
    }

    public function fetchListUrl()
    {
        return $this->client->request('GET', $this->listUrl);
    }

    public function parseItemsList(Crawler $crawler)
    {
        $crawler->filterXPath('//section/article[@data-item-premium="0"]')->each(function ($crawler) use (&$data) {
            $element = $this->parseItemsElement($crawler);
            $data[$element['id']] = $element;
        });

        return $data;
    }

    private function parseItemsElement(Crawler $crawler)
    {
        $price = $crawler->filterXPath('//div[@class="item-price "]');
        if (!$price->count()) {
            $price = $crawler->filterXPath('//div[@class="item-price price-discount"]');
        }

        $id = $crawler->attr('data-item-id');
        $title = $crawler->filterXPath('//span[@class="header-text"]')->text();
        $price = $price->count() ? preg_replace('#[\D]*#', '', $price->text()) : '';
        $link = 'https://m.avito.ru' . $crawler->filterXPath('//a[@class="item-link"]')->attr('href');

        return compact('id', 'title', 'price', 'link');
    }

    public function parseItemContent(Crawler $crawler)
    {
        $text = trim($crawler->filterXPath('//div[@id="description"]/div/div')->html());
        $text = str_replace(['<br>', '<p>', '</p>'], "\n", $text);
        $text = preg_replace("#\n+#", "\n", $text);

        $seller = trim($crawler->filterXPath('//div[contains(@class,"person-name")]')->text());
        $seller = str_replace("\n", ' ', $seller);

        try {
            $image = $crawler->filterXPath('//link[@rel="image_src"]')->attr('href');
        } catch (\Exception $e) {
            $image = null;
        }

        return compact('seller', 'text', 'image');
    }
}