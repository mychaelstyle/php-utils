<?php
namespace mychaelstyle\storage\providers;
require_once 'storage/providers/AmazonS3.php';
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-11 at 18:37:34.
 *
 * You must set env before testing this case.
 * add the following env variables to your environment.
 *
 * <pre>
 * AWS_KEY=[your aws key]
 * AWS_SECRET=[your aws secret]
 * AWS_S3_BUCKET="your bucket name"
 * AWS_REGION_NAME="TOKYO"
 * AWS_REGION_HOST="s3-ap-northeast-1.amazonaws.com"
 * export AWS_KEY AWS_SECRET AWS_S3_BUCKET SNB_AWS_REGION_NAME SNB_AWS_S3_REGION_HOST
 * </pre>
 */
class AmazonS3Test extends \mychaelstyle\TestBase
{
  /**
   * @var AmazonS3
   */
  private $object;
  /**
   * DSN
   */
  private $dsn = null;
  /**
   * options
   */
  private $options = array();
  /**
   * test file path
   */
  protected $path_example;
  /**
   * test file uri
   */
  protected $uri = 'example.txt';
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp()
  {
    parent::setUp();
    if($this->markIncompleteIfNoNetwork()){
      return;
    }
    $this->path_example = DIR_FIXTURES.'/example.txt';
    $this->uri = 'example.txt';
    $this->url = 'https://'.$_SERVER['AWS_REGION_HOST'].'/'.$_SERVER['AWS_S3_BUCKET'].'/'.$this->uri;
    // dsn
    $this->dsn = $_SERVER['AWS_REGION_NAME'].'/'.$_SERVER['AWS_S3_BUCKET'];
    // set your aws key and secret to your env
    $this->options = array(
      'key' => $_SERVER['AWS_KEY'],
      'secret' => $_SERVER['AWS_SECRET'],
      'default_cache_config' => '',
      'certificate_autority' => false
    );
    // connect
    $this->object = new AmazonS3;
    $this->object->connect($this->dsn,$this->options);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  public function tearDown()
  {
    parent::tearDown();
    $this->object->disconnect();
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::getServiceName
   * @covers mychaelstyle\storage\providers\AmazonS3::getPath
   * @covers mychaelstyle\storage\providers\AmazonS3::connect
   * @covers mychaelstyle\storage\providers\AmazonS3::disconnect
   * @covers mychaelstyle\storage\providers\AmazonS3::getServiceName
   * @covers mychaelstyle\ProviderAws::connect
   * @expectedException mychaelstyle\Exception
   */
  public function testConnectFail1(){
    $this->object = new AmazonS3;
    $dsn = $_SERVER['AWS_REGION_NAME'];
    $this->object->connect($dsn,$this->options);
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::connect
   * @covers mychaelstyle\ProviderAws::connect
   * @expectedException mychaelstyle\Exception
   */
  public function testConnectFail2(){
    $this->object = new AmazonS3;
    $dsn = '';
    $this->object->connect($dsn,$this->options);
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::connect
   * @covers mychaelstyle\ProviderAws::connect
   * @expectedException mychaelstyle\Exception
   */
  public function testConnectFail3(){
    $this->object = new AmazonS3;
    $dsn = $_SERVER['AWS_S3_BUCKET'];
    $this->object->connect($dsn,$this->options);
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::connect
   * @covers mychaelstyle\ProviderAws::connect
   * @covers mychaelstyle\storage\Factory::getInstance
   */
  public function testConnect()
  {
    $this->object = new AmazonS3;
    $dsn = $_SERVER['AWS_REGION_NAME'].'/'.$_SERVER['AWS_S3_BUCKET'];
    $this->object->connect($this->dsn,$this->options);
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::disconnect
   * @covers mychaelstyle\ProviderAws::disconnect
   */
  public function testDisconnect()
  {
    $this->object->disconnect();
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::put
   * @covers mychaelstyle\storage\providers\AmazonS3::remove
   * @covers mychaelstyle\storage\providers\AmazonS3::__mergePutOptions
   * @covers mychaelstyle\storage\providers\AmazonS3::__formatUri
   */
  public function testPut()
  {
    if($this->markIncompleteIfNoNetwork()){
      return true;
    }
    $expected = file_get_contents($this->path_example);
    // put a example file
    $options = array('contentType'=>'text/plain;charset=UTF8');
    $this->object->put($this->path_example,$this->uri,$options);
    $options = array('contentType'=>'text/plain');
    $options['contentType'] = null;
    $options['acl'] = 'private';
    //$options['curl.options'] = array(CURLOPT_SSL_VERIFYPEER => false);
    $this->object->put($this->path_example,$this->uri,$options);
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::get
   * @covers mychaelstyle\storage\providers\AmazonS3::__mergePutOptions
   * @covers mychaelstyle\storage\providers\AmazonS3::__formatUri
   * @depends testPut
   */
  public function testGet()
  {
    if($this->markIncompleteIfNoNetwork()){
      return true;
    }
    $expected = file_get_contents($this->path_example);
    // get as a file
    $tmp = tempnam(sys_get_temp_dir(),'mychaelstyle_file_test_');
    $this->object->get($this->uri,$tmp);
    $this->assertEquals($expected,file_get_contents($tmp));
    unlink($tmp);
    // get as strings
    $result = $this->object->get($this->uri);
    $this->assertEquals($expected,$result);
    // get as strings
    $result = $this->object->get('/'.$this->uri);
    $this->assertEquals($expected,$result);
  }
  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::get
   * @covers mychaelstyle\storage\providers\AmazonS3::__mergePutOptions
   * @covers mychaelstyle\storage\providers\AmazonS3::__formatUri
   * @expectedException mychaelstyle\Exception
   */
  public function testGetFail()
  {
    if($this->markIncompleteIfNoNetwork()){
      return true;
    }
    // not exists
    $result = $this->object->get('notexist.txt');
    $this->assertNull($result);
  }

  /**
   * @covers mychaelstyle\storage\providers\AmazonS3::remove
   * @covers mychaelstyle\storage\providers\AmazonS3::__mergePutOptions
   * @covers mychaelstyle\storage\providers\AmazonS3::__formatUri
   * @depends testPut
   */
  public function testRemove()
  {
    if($this->markIncompleteIfNoNetwork()){
      return true;
    }
    // remove
    $this->object->remove($this->uri);
  }

}
