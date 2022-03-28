<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],
        'blog' => [
            'driver' => 'local',
            'root' => storage_path('app/public/blog'),
            'url' => env('APP_URL').'/storage/blog',
            'visibility' => 'public'
        ],
        'activity' => [
            'driver' => 'local',
            'root' =>  storage_path('app/public/activity'),
            'url' => env('APP_URL').'/storage/activity',
            'visibility' => 'public'
        ],
        'channel' => [
            'driver' => 'local',
            'root' => storage_path('app/public/channel'),
            'url' => env('APP_URL').'/storage/channel',
            'visibility' => 'public'
        ],
        'videos' => [
            'driver' => 'local',
            'root' => storage_path('app/public/videos'),
            'url' => env('APP_URL').'/storage/videos',
            'visibility' => 'public'
        ],
        'games' => [
            'driver' => 'local',
            'root' => storage_path('app/public/games'),
            'url' => env('APP_URL').'/storage/games',
            'visibility' => 'public'
        ],
        'school' => [
            'driver' => 'local',
            'root' => storage_path('app/public/school'),
            'url' => env('APP_URL').'/storage/school',
            'visibility' => 'public'
        ],
        'event' => [
            'driver' => 'local',
            'root' =>  storage_path('app/public/event'),
            'url' => env('APP_URL').'/storage/event',
            'visibility' => 'public'
        ],
        'shop' => [
            'driver' => 'local',
            'root' => storage_path('app/public/shop'),
            'url' => env('APP_URL').'/storage/shop',
            'visibility' => 'public'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
