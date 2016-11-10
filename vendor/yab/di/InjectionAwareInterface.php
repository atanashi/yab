<?php
namespace yab\di;

// интерфейс для классов, в которые может инъецироваться контейнер зависимостей
use yab\Di;

interface InjectionAwareInterface
{
    // @todo поменять использование класса на интерфейс
    public function getDi() : Di;
    public function setDi(Di $dependencyInjector);
}