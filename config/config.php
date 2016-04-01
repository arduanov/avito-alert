<?php

$root_path = realpath(__DIR__ . "/..");

return [
    'debug' => true,
    'root.path' => $root_path,
    'cache.path' => $root_path . '/var/cache',
    'crawler.ttl' => 7200,


    'monolog.level' => Monolog\Logger::DEBUG,

    'monolog.config' => [
        'monolog.logfile' => $root_path . '/var/logs/' . date('Y-m-d') . '.log',
        'monolog.level' => Monolog\Logger::NOTICE,
        'monolog.name' => 'application',
        'monolog.slack.key' => 'xoxp-31291429250-31278728224-31231827475-370c28caa9',
    ],

];
