<?php

use App\Service;
use Silex\Application;
use Silex\Provider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Monolog\Logger;

class AppKernel extends Application
{
    public function __construct()
    {
        parent::__construct();
        $this->loadConfig($this);
        $this->loadProviders($this);
        $this->loadMonolog($this);
        $this->loadErrorHandler($this);
        $this->loadServices($this);
//        $this->loadRoutes($this);
//        $this->loadEventListeners($this);
//        $this->loadJson();
//        return $this;
    }

    public function loadConfig(Application $app)
    {
        $config = require __DIR__ . '/../config/config.php';

        foreach ($config as $key => $value) {
            $app[$key] = $value;
        }
    }

    public function loadMonolog(Application $app)
    {
        $app['monolog.factory'] = $app->protect(function ($name) use ($app) {
            $log = new $app['monolog.logger.class']($name);

            $handlers = isset($app["monolog.{$name}.handlers"])
                ? $app["monolog.{$name}.handlers"]
                : [$app['monolog.handler']];

            foreach ($handlers as $handler) {
                $log->pushHandler($handler);
            }

            return $log;
        });

        $app['logger'] = $app->share(
            $app->extend(
                'logger',
                function (Logger $logger, \Pimple $app) {
                    $logger->pushHandler($app['monolog.handler.slack']);
                    return $logger;
                }
            )
        );

        $app['monolog.slack.alert'] = $app->share(function () use ($app) {
            return new \Monolog\Handler\SlackHandler(
                $app['monolog.config']['monolog.slack.key'],
                '#general',
                'Avito',
                true,
                null,
                \Monolog\Logger::INFO,
                true,
                true,
                true
            );
        });

        $app['monolog.file.alert'] = $app->share(function () use ($app) {
            return new \Monolog\Handler\StreamHandler(
                $app['root.path'] . '/var/logs/slack.alert.log'
            );
        });

        $app['monolog.alert.handlers'] = $app->share(function ($app) {
            return [
                $app['monolog.slack.alert'],
                $app['monolog.file.alert']
            ];
        });


        foreach (['alert'] as $channel) {
            $app['monolog.' . $channel] = $app->share(function ($app) use ($channel) {
                return $app['monolog.factory']($channel);
            });
        }

    }

    public function loadProviders(Application $app)
    {
        $app->register(new \Sorien\Provider\PimpleDumpProvider(), ['dump.path' => $app['root.path']]);
        $app->register(new Provider\MonologServiceProvider(), $app['monolog.config']);
    }

    public function loadServices(Application $app)
    {
        $app['avito'] = $app->share(function () use ($app) {
            return new Service\Avito($app);
        });
        $app['parser'] = $app->share(function () use ($app) {
            return new Service\Parser($app['cache.path'] . '/crawler', $app['crawler.ttl']);
        });
    }

    public function loadErrorHandler(Application $app)
    {
        $errorHandler = ErrorHandler::register();

//        if (php_sapi_name() !== 'cli') {
        $app->error(function (HttpException $e) use ($app) {
            $errCode = $e->getStatusCode();
            $data = [
                'code' => $errCode,
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => $e->getHeaders(),
            ];

            $headers['Content-Type'] = 'application/json; charset=UTF-8';
            return new JsonResponse($data, $errCode);
        }, 1000);

        $app->error(function (\Exception $e) use ($app) {
            throw $e;
        });

        $errorHandler->setExceptionHandler(function (\Exception $e) use ($app) {
            $data = [
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString(),
            ];
            $app['logger']->critical(implode("\n", $data));
        });
//        }

    }


    public function json($data, $status = 200, array $headers = [])
    {
        if (is_array($data)) {
            $data = json_encode($data, 15);
        }

        $response = new Response($data, $status, $headers);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
