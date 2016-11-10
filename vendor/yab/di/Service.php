<?php
/**
 * Created by PhpStorm.
 * User: gakusei
 * Date: 10.11.2016
 * Time: 21:28
 */

namespace yab\di;

use yab\Di;
use yab\util\InstanceUtil;


class Service
{
    protected $_name = '';
    protected $_definition = null;
    protected $_shared = false;
    protected $_sharedInstance = null;
    protected $_resolved = false;
    public function __construct(string $name, $definition, bool $shared = false)
    {
        $this->_name = $name;
        $this->_definition = $definition;
        $this->_shared = $shared;
    }
    //
    use InstanceUtil;
    // даёт содержимое $definition
    public function resolve(array $parameters = [], Di $dependencyInjector = null)
    {
        if ($this->_shared) {
            if (!is_null($this->_sharedInstance)) {
                return $this->_sharedInstance;
            }
        }
        //
        $found = true;
        if (is_string($this->_definition)) {
            // строка является именем класса
            if (class_exists($this->_definition)) {
                if (!empty($parameters)) {
                    $instance = $this->createInstanceParams($this->_definition, $parameters);
                } else {
                    $instance = $this->createInstance($this->_definition);
                }
            } else {
                $found = false;
            }
        } elseif (is_object($this->_definition)) {
            // в качестве объекта может выступать только анонимная функция
            if ($this->_definition instanceof \Closure) {
                if (!empty($parameters)) {
                    $instance = call_user_func_array($this->_definition, $parameters);
                } else {
                    $instance = call_user_func($this->_definition);
                }
            } else {
                $found = false;
            }
        } elseif (is_array($this->_definition)) {
            // todo реализовать класс для сборки инстанса из массива
        } else {
            $found = false;
        }
        //
        if (!$found) {
            throw new \Exception(sprintf("Service '%s' cannot be resolved!", $this->_name));
        }
        //
        if ($this->_shared) {
            $this->_sharedInstance = $instance;
        }
        // пытаемся "засунуть" контейнер
        if ($instance instanceof InjectionAwareInterface) {
            if (!is_null($dependencyInjector)) {
                $instance->setDi($dependencyInjector);
            }
        }
        $this->_resolved = true;
        return $instance;
    }
}