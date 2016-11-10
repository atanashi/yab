<?php
namespace yab\util;
//
trait InstanceUtil
{
    // костыль-заглушка
    public function createInstance(string $className)
    {
        return new $className();
    }
    public function createInstanceParams(string $className, array $params)
    {
        $reflection = new \ReflectionClass($className);
        return $reflection->newInstanceArgs($params);
    }
}
