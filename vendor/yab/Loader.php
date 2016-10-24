<?php
namespace yab;

/**
 * Class Loader
 *
 * За основу взят автозагрузчик фреймворка Phalcon
 * Авторы:  Andres Gutierrez <andres@phalconphp.com>
 *          Eduar Carvajal <eduar@phalconphp.com>
 *
 * @author gakusei <gakusei@spaces.ru>
 * @package yab
 */
class Loader
{
    //
    protected $_extensions = [];
    protected $_registered = false;
    protected $_classes = [];
    protected $_namespaces = ['prefix' => 'directory'];
    protected $_prefixes = [];
    protected $_directories = [];

    // просто конструктор
    public function __construct()
    {
        $this->_extensions = ['php'];
    }

    // позволяет установить список поддерживаемых автозагрузчиком расширений
    public function setExtensions(array $extensions) : Loader
    {
        $this->_extensions = $extensions;
        return $this;
    }

    // регистрирует список классов с файлами
    public function registerClasses(array $classes, bool $merge = false) : Loader
    {
        if ($merge) {
            $this->_classes = array_merge($this->_classes, $classes);
        } else {
            $this->_classes = $classes;
        }
        return $this;
    }

    // регистрирует директорию
    public function registerDirs(array $dirs, bool $merge = false) : Loader
    {
        if ($merge) {
            $this->_directories = array_merge($this->_directories, $dirs);
        } else {
            $this->_directories = $dirs;
        }
        return $this;
    }

    // регистрирует имя пространства для директории
    public function registerNamespaces(array $namespaces, bool $merge = false) : Loader
    {
        if ($merge) {
            $this->_namespaces = array_merge($this->_namespaces, $namespaces);
        } else {
            $this->_namespaces = $namespaces;
        }
        return $this;
    }

    // регистрирует префикс для имени пространства
    public function registerPrefixes(array $prefixes, bool $merge = false) : Loader
    {
        if ($merge) {
            $this->_prefixes = array_merge($this->_prefixes, $prefixes);
        } else {
            $this->_prefixes = $prefixes;
        }
        return $this;
    }

    // Регистрирует автозагрузочную функцию
    public function register() : Loader
    {
        if (!$this->_registered) {
            spl_autoload_register([$this, 'autoload']);
            $this->_registered = true;
        }
        return $this;
    }

    // Удяляет автозагрузочную функцию
    public function unregister() : Loader
    {
        if ($this->_registered) {
            spl_autoload_unregister([$this, 'autoload']);
            $this->_registered = false;
        }
        return $this;
    }

    // Загрузчик
    public function autoload(string $className) : bool
    {
        //
        if (isset($this->_classes[$className]) && is_file($this->_classes[$className])) {
            require_once $this->_classes[$className];
            return true;
        }
        //
        static $ds = DIRECTORY_SEPARATOR;
        static $namespaceSeparator = '\\';
        // перебираем имена пространств
        foreach ($this->_namespaces as $namespace => $directory) {
            // проверка на вхождение по имени пространства
            if (preg_match(sprintf("#^%s#is", $namespace), $className)) {
                $fileName = substr($className, strlen($namespace . $namespaceSeparator));
                $fileName = str_replace('_', $ds, $fileName);
                $fixedDirectory = rtrim($directory, $ds) . $ds;
                foreach ($this->_extensions as $extension) {
                    $fileName = sprintf("%s%s.%s", $fixedDirectory, $fileName, $extension);
                    if (is_file($fileName)) {
                        require_once $fileName;
                        return true;
                    }
                }
            }
        }
        // перебираем префиксы
        foreach ($this->_prefixes as $prefix => $directory) {
            if (preg_match(sprintf("#^%s#", $prefix), $className)) {
                $filePath = str_replace($prefix . $namespaceSeparator, '', $className);
                $filePath = str_replace($prefix . '_', '', $filePath);
                $filePath = str_replace('_', $ds, $filePath);
                // Проверка, не упостошили ли мы строку с именем класса
                if ($filePath) {
                    $fixedDirectory = rtrim($directory, $ds) . $ds;
                    foreach ($this->_extensions as $extension) {
                        $fileName = sprintf("%s%s.%s", $fixedDirectory, $filePath, $extension);
                        if (is_file($fileName)) {
                            require_once $fileName;
                            return true;
                        }
                    }
                }
            }
        }
        //
        $nsClassName = str_replace($namespaceSeparator, $ds, $className);
        $dsClassName = str_replace('_', $ds, $nsClassName);
        //
        foreach ($this->_directories as $directory) {
            $fixedDirectory = rtrim($directory, $ds) . $ds;
            //
            foreach ($this->_extensions as $extension) {
                $fileName = sprintf("%s%s.%s", $fixedDirectory, $dsClassName, $extension);
                if (is_file($fileName)) {
                    require_once $fileName;
                    return true;
                }
            }
        }
        return false;
    }
}
