<?php

use App\Models\Service;

return [
    [
        'type' => Service::TYPE_CORE,
        'name' => 'ServD',
        'service_name' => 'servd',
        'version' => '8.1', // PHP Version
        'port' => '22,80,443,9000',
        'has_volume' => false,
        'should_build' => true,
        'available_versions' => [
            '7.4',
            '8.0',
            '8.1',
            '8.2',
            '8.3',
        ],
    ],
    [
        'type' => Service::TYPE_DATABASE,
        'name' => 'MySQL',
        'description' => '(5.7 incompatible with arm64)',
        'service_name' => 'mysql',
        'version' => '5.7',
        'port' => '3306',
        'has_volume' => false,
        'should_build' => true,
        'available_versions' => [
            '5.7',
            '8.0',
        ],
    ],
    [
        'type' => Service::TYPE_DATABASE,
        'name' => 'MariaDB',
        'service_name' => 'mariadb',
        'version' => '10.4',
        'port' => '3306',
        'has_volume' => true,
        'available_versions' => [
            '10.4',
            '10.5',
            '10.6',
            '10.11',
            '11.0',
            '11.1',
            '11.2',
            '11.3',
        ],
    ],
    [
        'type' => Service::TYPE_DATABASE,
        'name' => 'PostgreSQL',
        'service_name' => 'pgsql',
        'version' => '13',
        'port' => '3306',
        'has_volume' => false,
        'available_versions' => [
            '13',
            '14',
            '15',
            '16',
        ],
    ],
    [
        'type' => Service::TYPE_MEMORY_STORE,
        'name' => 'Redis',
        'service_name' => 'redis',
        'version' => 'latest',
        'port' => '6379',
        'has_volume' => true,
    ],
    [
        'type' => Service::TYPE_OTHER,
        'name' => 'Dnsmasq',
        'service_name' => 'dnsmasq',
        'version' => 'latest',
        'port' => '53',
        'has_volume' => false,
    ],
    [
        'type' => Service::TYPE_OTHER,
        'name' => 'Mailhog',
        'service_name' => 'mailhog',
        'version' => 'latest',
        'port' => '8025,1025',
        'has_volume' => false,
    ],
    [
        'type' => Service::TYPE_OTHER,
        'name' => 'Elasticsearch',
        'service_name' => 'elasticsearch',
        'version' => 'latest',
        'port' => '9200',
        'has_volume' => true,
        'single_stub' => true,
        'available_versions' => [
            'latest',
            '8.1',
            '8.0',
            '7.17',
            '7.16',
            '7.15',
            '7.10',
            '7.9',
            '7.8',
            '7.7',
            '7.6',
            '7.5',
            '7.4',
            '7.3',
            '7.2',
            '7.1',
            '7.0',
            '6.8',
            '6.7',
            '6.6',
            '6.5',
            '6.4',
            '6.3',
            '6.2',
            '6.1',
            '6.0',
            '5.6',
            '5.5',
            '5.4',
            '5.3',
            '5.2',
            '5.1',
            '5.0',
            '2.4',
            '2.3',
            '1.7',
        ],
    ],
];
