<?php
namespace snb\file;
require_once 'file/Storage.php';
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-10 at 18:46:21.
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var Storage
   */
  private $object;
  /**
   * dsn map
   */
  private $dsnMap = array();
  /**
   * test file uri
   */
  private $uri = 'test.txt';

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  public function setUp()
  {
    $this->dsnMap = array(
      'Local' => array(
        'dsn' => 'local:///Users/masanori/work/snb-php-utils/tests/work',
        'options' => array('permission'=>0644),
      ),
      'Local2' => array(
        'dsn' => 'local:///Users/masanori/work/snb-php-utils/tests/tmp',
        'options' => array('permission'=>0644),
      ),
      'AmazonS3' => array(
        'dsn' => 'amazon_s3://REGION_'.$_SERVER['SNB_AWS_S3_REGION_NAME'].'/'.$_SERVER['SNB_AWS_S3_BUCKET'],
        'options' => array(
          'key' => $_SERVER['SNB_AWS_KEY'],
          'secret' => $_SERVER['SNB_AWS_SECRET'],
          'default_cache_config' => '',
          'certificate_autority' => false
        )
      )
    );
    $def = $this->dsnMap['Local'];
    $this->object = new Storage($def['dsn'],$def['options'],false);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  public function tearDown()
  {
  }

  /**
   * assert local providers writing
   */
  protected function assertLocalWritten($dsn,$expected,$uri){
    $dir = str_replace('local://','',$dsn);
    $path = $dir."/".$uri;
    $result = file_get_contents($path);
    $this->assertEquals($expected,$result);
  }

  protected function assertLocalDeleted($dsn,$uri){
    $dir = str_replace('local://','',$dsn);
    $path = $dir."/".$uri;
    $this->assertFalse(file_exists($path));
  }

  protected function fileWrite($file,$strings){
    $file->open('w');
    $file->write($strings);
    $file->close();
    $file->commit();
  }

  /**
   * @covers snb\file\Storage::createFile
   */
  public function testCreateFile()
  {
    // renew storage
    $def = $this->dsnMap['Local'];
    $this->object = new Storage($def['dsn'],$def['options'],false);

    // create file
    $file = $this->object->createFile($this->uri);
    $this->assertNotNull($file);
    $this->assertEquals('snb\file\File',get_class($file));

    // test writing
    $expected = 'This is test!';
    $this->fileWrite($file,$expected);
    $this->assertLocalWritten($def['dsn'],$expected,$this->uri);

    // remove file
    $this->object->remove($this->uri);
  }

  /**
   * @covers snb\file\Storage::addProvider
   */
  public function testAddProvider()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);

    $file = $this->object->createFile($this->uri);

    $expected = 'This is test 2 providers!';
    $this->fileWrite($file,$expected);

    $this->assertLocalWritten($def1['dsn'],$expected,$this->uri);
    $this->assertLocalWritten($def2['dsn'],$expected,$this->uri);

    // remove file
    $this->object->remove($this->uri);

    // assertLocalDeleted
    $this->assertLocalDeleted($def1['dsn'],$this->uri);
    $this->assertLocalDeleted($def2['dsn'],$this->uri);
  }

  /**
   * @covers snb\file\Storage::removeProvider
   * @depends testAddProvider
   */
  public function testRemoveProvider()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);
    $this->object->removeProvider($def2['dsn'],$def2['options']);

    $file = $this->object->createFile($this->uri);

    $expected = 'This is test to remove provider!';
    $this->fileWrite($file,$expected);

    $this->assertLocalWritten($def1['dsn'],$expected,$this->uri);
    $this->assertLocalDeleted($def2['dsn'],$this->uri);

    // remove file
    $this->object->remove($this->uri);
    // assertLocalDeleted
    $this->assertLocalDeleted($def1['dsn'],$this->uri);
  }

  /**
   * @covers snb\file\Storage::put
   */
  public function testPut()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);

    $path = DIR_TEST.'/fixtures/example.txt';
    $expected = file_get_contents($path);

    $this->object->put($path,$this->uri,array('permittion'=>0644));
    
    $this->assertLocalWritten($def1['dsn'],$expected,$this->uri);
    $this->assertLocalWritten($def2['dsn'],$expected,$this->uri);

  }

  /**
   * @covers snb\file\Storage::get
   * @depends testPut
   */
  public function testGet()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);

    $path = DIR_TEST.'/fixtures/example.txt';
    $expected = file_get_contents($path);

    $result = $this->object->get($this->uri);
    $this->assertEquals($expected,$result);
  }

  /**
   * @covers snb\file\Storage::remove
   * @depends testGet
   */
  public function testRemove()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);
    // remove file
    $this->object->remove($this->uri);
    // assertLocalDeleted
    $this->assertLocalDeleted($def1['dsn'],$this->uri);
    $this->assertLocalDeleted($def2['dsn'],$this->uri);
  }

  /**
   * @covers snb\file\Storage::putContents
   */
  public function testPutContents()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);

    $expected = 'This is put contents test';

    $this->object->putContents($this->uri,$expected,array('permission'=>0644));
    $result = $this->object->get($this->uri);
    $this->assertEquals($expected,$result);
  }

  /**
   * @covers snb\file\Storage::getContents
   * @depends testPutContents
   */
  public function testGetContents()
  {
    $def1 = $this->dsnMap['Local'];
    $def2 = $this->dsnMap['Local2'];
    $this->object->addProvider($def2['dsn'],$def2['options']);

    $expected = 'This is put contents test';

    $result = $this->object->getContents($this->uri);
    $this->assertEquals($expected,$result);

    $this->testRemove();
  }

  /**
   * @covers snb\file\Storage::commit
   * @todo   Implement testCommit().
   */
  public function testCommit()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers snb\file\Storage::rolback
   * @todo   Implement testRolback().
   */
  public function testRolback()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }
}
