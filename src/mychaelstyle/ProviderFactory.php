<?php
/**
 * mychaelstyle\ProviderFactory
 * @package mychaelstyle
 * @auther Masanori Nakashima
 */
namespace mychaelstyle;
require_once dirname(__FILE__).'/Provider.php';
require_once dirname(__FILE__).'/Exception.php';
/**
 * Service provider factory abstract class
 * @package mychaelstyle 
 * @auther Masanori Nakashima
 */
abstract class ProviderFactory {
  /**
   * @var string name
   */
  protected $name;
  /**
   * @var string $uri
   */
  protected $uri;
  /**
   * get providers' package name.
   * @return string package name;
   */
  abstract protected function getPackage();
  /**
   * get provider files dir path
   * @return string dir path
   */
  abstract protected function getPath();
  /**
   * get instance
   * @param string $dsn
   * @param array $options
   */
  public function getProvider($dsn,$options=array()){
    $object = $this->getClass($dsn,$this->getPackage(),$this->getPath());
    $object->connect($this->uri,$options);
    return $object;
  }
  /**
   * get provider class name
   * @param string $dsn
   * @return string driver name
   */
  protected function getClass($dsn,$package,$path){
    $this->perse($dsn);
    $baseName = $this->name;
    $baseName = str_replace('_','',$baseName);
    $filePath = $path.'/'.$baseName.'.php';
    $className = $package.'\\'.$baseName;
    if(file_exists($filePath)){
      require_once $filePath;
    }
    if(class_exists($className)){
      return new $className;
    }
    throw new \mychaelstyle\Exception('Provider class '.$className.' is not found.',\mychaelstyle\Exception::ERROR_PROVIDER_CONNECTION);
  }
  /**
   * set DSN
   * @param string $dsn
   */
  protected function perse($dsn){
    if(is_null($dsn) || strlen($dsn)==0){
      throw new \mychaelstyle\Exception('The dsn is invalid format!',
        \mychaelstyle\Exception::ERROR_PROVIDER_CONNECTION);
    } else if(false===strpos($dsn,'://')){
      throw new \mychaelstyle\Exception('The dsn is invalid format!',
        \mychaelstyle\Exception::ERROR_PROVIDER_CONNECTION);
    }
    list($this->name, $this->uri) = explode('://',$dsn);
    if(strlen(trim($this->name))==0){
      throw new \mychaelstyle\Exception('The dsn provider name is null!',
        \mychaelstyle\Exception::ERROR_PROVIDER_CONNECTION);
    } else if(strlen(trim($this->uri))==0){
      throw new \mychaelstyle\Exception('The provider root is null!',
        \mychaelstyle\Exception::ERROR_PROVIDER_CONNECTION);
    }
  }
}
