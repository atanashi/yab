<?php
namespace yab;
use yab\di\Service;

class Di
{
    protected $_services = [];
    //
    public function get(string $name)
    {
        /** @var Service $service */
        $service = $this->_services[$name];
        return $service->resolve();
    }
    public function set(string $name, $definition)
    {
    }
    public function setShared(string $name, $definition)
    {
    }
}