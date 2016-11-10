<?php
use yab\Loader;
require_once 'yab/Loader.php';
// Регистрируем автозагрузчик для папки vendor
(new Loader())->registerDirs([
    __DIR__
])->register();
