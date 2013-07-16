<?php
namespace snb\file\providers;
require_once 'file/providers/AmazonS3.php';
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-11 at 18:37:34.
 *
 * You must set env before testing this case.
 * add the following env variables to your environment.
 *
 * <pre>
 * SNB_AWS_PHPSDK="${HOME}/Library/aws-sdk-for-php/sdk.class.php"
 * SNB_AWS_KEY=[your aws key]
 * SNB_AWS_SECRET=[your aws secret]
 * SNB_AWS_S3_BUCKET="your bucket name"
 * SNB_AWS_S3_REGION_NAME="TOKYO"
 * SNB_AWS_S3_REGION_HOST="s3-ap-northeast-1.amazonaws.com"
 * export SNB_AWS_PHPSDK SNB_AWS_KEY SNB_AWS_SECRET SNB_AWS_S3_BUCKET SNB_AWS_REGION_NAME SNB_AWS_S3_REGION_HOST
 * </pre>
 */
class AmazonS3Test extends \snb\TestBase
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
    require_once($_SERVER['SNB_AWS_PHPSDK']);
    $this->path_example = DIR_TEST.'/fixtures/example.txt';
    $this->uri = 'example.txt';
    $this->url = 'https://'.$_SERVER['SNB_AWS_S3_REGION_HOST'].'/'.$_SERVER['SNB_AWS_S3_BUCKET'].'/'.$this->uri;
    // dsn
    $this->dsn = 'amazon_s3://REGION_'.$_SERVER['SNB_AWS_S3_REGION_NAME'].'/'.$_SERVER['SNB_AWS_S3_BUCKET'];
    // set your aws key and secret to your env
    $this->options = array(
      'key' => $_SERVER['SNB_AWS_KEY'],
      'secret' => $_SERVER['SNB_AWS_SECRET'],
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
    $this->object->disconnect();
    parent::tearDown();
  }

  /**
   * @covers snb\file\providers\AmazonS3::connect
   * @covers snb\file\Provider::perseDsn
   * @expectedException snb\file\Exception
   */
  public function testConnectFail1(){
    $this->object = new AmazonS3;
    $dsn = 'failname://REGION_'.$_SERVER['SNB_AWS_S3_REGION_NAME'].'/'.$_SERVER['SNB_AWS_S3_BUCKET'];
    $this->object->connect($dsn,$this->options);
  }

  /**
   * @covers snb\file\providers\AmazonS3::connect
   * @covers snb\file\Provider::perseDsn
   * @expectedException snb\file\Exception
   */
  public function testConnectFail2(){
    $this->object = new AmazonS3;
    $dsn = 'amazon_s3://';
    $this->object->connect($dsn,$this->options);
  }

  /**
   * @covers snb\file\providers\AmazonS3::connect
   * @covers snb\file\providers\AmazonS3::__construct
   * @covers snb\file\Provider::getInstance
   * @covers snb\file\Provider::perseDsn
   */
  public function testConnect()
  {
    $this->object = \snb\file\Provider::getInstance($this->dsn,$this->options);
    $this->object = new AmazonS3;
    $this->object->connect($this->dsn,$this->options);
  }

  /**
   * @covers snb\file\providers\AmazonS3::disconnect
   */
  public function testDisconnect()
  {
    $this->object->disconnect();
  }

  /**
   * @covers snb\file\providers\AmazonS3::put
   * @covers snb\file\providers\AmazonS3::remove
   * @covers snb\file\providers\AmazonS3::_mergePutOptions
   * @covers snb\file\providers\AmazonS3::_formatUri
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
    $result = $this->httpRequest($this->url,null);
    $this->assertEquals($expected,$result['body']);
    // put a example file
    $options = array('contentType'=>'text/plain');
    $options['contentType'] = null;
    $options['acl'] = \AmazonS3::ACL_PUBLIC;
    $options['curlopts'] = array(CURLOPT_SSL_VERIFYPEER => false);
    $this->object->put($this->path_example,$this->uri,$options);
    $result = $this->httpRequest($this->url,null);
    $this->assertEquals($expected,$result['body']);
  }

  /**
   * @covers snb\file\providers\AmazonS3::get
   * @covers snb\file\providers\AmazonS3::_mergePutOptions
   * @covers snb\file\providers\AmazonS3::_formatUri
   * @depends testPut
   */
  public function testGet()
  {
    if($this->markIncompleteIfNoNetwork()){
      return true;
    }
    $expected = file_get_contents($this->path_example);
    // get as a file
    $tmp = tempnam(sys_get_temp_dir(),'snb_file_test_');
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
   * @covers snb\file\providers\AmazonS3::get
   * @covers snb\file\providers\AmazonS3::_mergePutOptions
   * @covers snb\file\providers\AmazonS3::_formatUri
   * @expectedException snb\file\Exception
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
   * @covers snb\file\providers\AmazonS3::remove
   * @covers snb\file\providers\AmazonS3::_mergePutOptions
   * @covers snb\file\providers\AmazonS3::_formatUri
   * @depends testPut
   */
  public function testRemove()
  {
    if($this->markIncompleteIfNoNetwork()){
      return true;
    }
    // remove
    $this->object->remove($this->uri);
    $result = $this->httpRequest($this->url,null);
    $this->assertNotEquals('200',$result['code']);
  }

}
