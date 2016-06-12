<?php
use App\Services\AvitoService;
use Mockery as m;

class AvitoParserTest extends TestCase
{
    public function testCommandExecuted()
    {
        $avito = m::mock(AvitoService::class);
        $avito->shouldReceive('start')->once();

        $this->app->instance(AvitoService::class, $avito);
        $this->artisan('parser:avito');
    }
}
