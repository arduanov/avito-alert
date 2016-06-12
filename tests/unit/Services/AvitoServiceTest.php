<?php
use App\Services\AvitoService;
use Mockery as m;
use Symfony\Component\DomCrawler\Crawler;

class AvitoServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $client \Mockery\MockInterface
     */
    public $client;

    public function tearDown()
    {
        m::close();
    }

    public function setUp()
    {
        $this->client = m::mock(Goutte\Client::class);

    }

    public function testStart()
    {
//        $queue = $this->getMock(AvitoService::class, ['getTime'], [$this->client]);
//        $queue->expects($this->any())->method('getTime')->will($this->returnValue('time'));
//        $queue->
//$this->client->makePartial()
//        $avito = new AvitoService($this->client);
//        $avito->start();

    }

    public function test_fetchListUrl()
    {
        $this->client->shouldReceive('request')->once();

        $avito = new AvitoService($this->client);
        $avito->fetchListUrl();
    }

    public function test_parseItemsList()
    {
        $html = file_get_contents(ROOT . '/tests/data/avito-list.html');
        $crawler = new Crawler($html);

        $avito = new AvitoService($this->client);
        $result = $avito->parseItemsList($crawler);

        $this->assertTrue(is_array($result) && $result, 'Result not empty');
        $this->assertSame(['id', 'title', 'price', 'link'], array_keys(array_shift($result)));
    }

    public function test_parseItemContent()
    {
        $html = file_get_contents(ROOT . '/tests/data/avito-item.html');
        $crawler = new Crawler($html);

        $avito = new AvitoService($this->client);
        $result = $avito->parseItemContent($crawler);

        $this->assertTrue(is_array($result) && $result, 'Result not empty');
        $this->assertSame(['seller', 'text'], array_keys($result));
    }
}
