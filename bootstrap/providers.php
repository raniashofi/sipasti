<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    ...(class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class) ? [\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class] : []),
];
