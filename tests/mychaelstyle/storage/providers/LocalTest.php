<?php
namespace mychaelstyle\storage\providers;
require_once 'storage/providers/Local.php';

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-07-11 at 08:11:45.
 */
class LocalTest extends \mychaelstyle\TestBase
{
  /**
   * @var Local
   */
  protected $object;
  /**
   * Valid DSN
   * 正常接続できるDSN
   */
  protected $dsn;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    parent::setUp();
    $this->dsn = DIR_WORK;
    // new
    $this->object = new Local;
    clearstatcache();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
    $fpath = DIR_WORK.'/'.$this->uri_example;
    if(file_exists($fpath)){
      unlink($fpath);
    }
    $this->object->disconnect();
    parent::tearDown();
  }

  /**
   * connect
   */
  protected function connect(){
    // none options
    $this->object->connect($this->dsn);
    // null options
    $this->object->connect($this->dsn,null);
    // use options
    $options = array('permission' => 0644);
    $this->object->connect($this->dsn,$options);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\Provider::perseDsn
   * @expectedException mychaelstyle\Exception
   */
  public function testConnectInvalid1()
  {
    // invalid name
    $dsn = DIR_TEST.'/hogehoge';
    $options = array('permission' => 0644);
    $this->object->connect($dsn,$options);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\Provider::perseDsn
   * @expectedException mychaelstyle\Exception
   */
  public function testConnectInvalid2()
  {
    // no path
    $dsn = '';
    $options = array('permission' => 0644);
    $this->object->connect($dsn,$options);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\Provider::perseDsn
   * @expectedException mychaelstyle\Exception
   */
  public function testConnectInvalid3()
  {
    // no path dir
    $dsn = '/foo/foo/foo';
    $options = array('permission' => 0644);
    $this->object->connect($dsn,$options);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\Provider::perseDsn
   * @covers mychaelstyle\storage\providers\Local::__construct
   */
  public function testConnect()
  {
    $this->object = new Local;
    // valid
    $options = array('permission' => 0644);
    $this->object->connect($this->dsn,$options);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   */
  public function testDisconnect()
  {
    $this->object = new Local;
    $expected = new Local;
    // valid
    $this->connect();
    $this->assertNotEquals($expected,$this->object);
    $this->object->disconnect();
    $this->assertEquals($expected,$this->object);
    // reconnect
    $this->connect();
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::get
   * @covers mychaelstyle\storage\providers\Local::getRealPath
   */
  public function testGet()
  {
    $this->connect();
    // 存在しないファイルの読み込みテスト
    $result = $this->object->get('noexist.txt');
    $this->assertNull($result);
    // 正常読み込みテスト
    $expect = file_get_contents($this->org_example);
    $result = $this->object->get($this->uri_example);
    $this->assertEquals($expect,$result,'Get fail.');
    // 保存
    $copyPath = DIR_WORK.'/copy.txt';
    $expect = file_get_contents($this->org_example);
    $result = $this->object->get($this->uri_example,$copyPath);
    $this->assertTrue($result);
    $this->assertEquals($expect,file_get_contents($copyPath));
    unlink($copyPath);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::put
   * @covers mychaelstyle\storage\providers\Local::getRealPath
   */
  public function testPut()
  {
    clearstatcache();
    $putUri = 'put.txt';
    $expectedPath = DIR_WORK.'/put.txt';
    $this->connect();
    // 転送テスト
    $this->object->put($this->org_example,$putUri);
    $this->assertTrue(file_exists($expectedPath));
    $this->assertEquals(file_get_contents($this->org_example),file_get_contents($expectedPath));
    $perms = fileperms($expectedPath);
    $this->assertEquals('0644',substr(sprintf('%o',$perms),-4));
    unlink($expectedPath);
    // パーミッションテスト
    $this->object->put($this->org_example,$putUri,array('permission'=>0666));
    $perms = fileperms($expectedPath);
    $this->assertEquals('0666',substr(sprintf('%o',$perms),-4));
    unlink($expectedPath);
    $this->object->put($this->org_example,$putUri,array('permission'=>0600));
    $perms = fileperms($expectedPath);
    $this->assertEquals('0600',substr(sprintf('%o',$perms),-4));
    unlink($expectedPath);
    // 存在しないディレクトリが作成されるかテスト
    $putUri = '/tdir/child/gchild/put.txt';
    $expectedPath = DIR_WORK.'/tdir/child/gchild/put.txt';
    $this->object->put($this->org_example,$putUri,array('permission'=>0600,'folder_permission'=>0755));
    $this->assertTrue(file_exists($expectedPath));
    $this->assertEquals(file_get_contents($this->org_example),file_get_contents($expectedPath));
    $perms = fileperms($expectedPath);
    $this->assertEquals('0600',substr(sprintf('%o',$perms),-4));
    unlink($expectedPath);
    $this->removeDirectory(DIR_WORK.'/tdir');
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::put
   * @expectedException mychaelstyle\Exception
   */
  public function testPutExceptionCopy(){
    clearstatcache();
    $this->connect();
    $putUri = '/tdir/child/gchild/put.txt';
    $expectedPath = DIR_WORK.'/tdir/child/gchild/put.txt';
    $this->object->put($this->org_example,$putUri,array('permission'=>0644,'folder_permission'=>0755));
    // exception
    chmod($expectedPath,0111);
    $this->object->put($this->org_example,$putUri,array('permission'=>0644,'folder_permission'=>0755));
    chmod($expectedPath,0666);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::put
   * @expectedException mychaelstyle\Exception
   */
  public function testPutExceptionDir(){
    clearstatcache();
    $this->connect();
    $putUri = '/tdir/child/gchild/put.txt';
    $expectedPath = DIR_WORK.'/tdir/child/gchild/put.txt';
    $gpDir = dirname(dirname($expectedPath));
    if(!is_dir($gpDir)){
      mkdir($gpDir,0755,true);
    }
    chmod($gpDir,0000);
    $this->object->put($this->org_example,$putUri,array('permission'=>0644,'folder_permission'=>0755));
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::remove
   * @covers mychaelstyle\storage\providers\Local::removeDir
   * @covers mychaelstyle\storage\providers\Local::getRealPath
   */
  public function testRemove()
  {
    $putUri = '/tdir/child/put.txt';
    $expectedPath = DIR_WORK.'/tdir/child/put.txt';
    $this->connect();
    $this->object->put($this->org_example,$putUri);
    // 最初はある
    $result = $this->object->get($putUri);
    $this->assertNotNull($result);
    // 削除後はない
    $this->object->remove($putUri);
    $result = $this->object->get($putUri);
    $this->assertNull($result);
    $this->assertFalse(file_exists($expectedPath));
    // ディレクトリごと削除
    $this->object->put($this->org_example,$putUri);
    $this->object->remove(dirname(dirname($putUri)),true);
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::remove
   * @covers mychaelstyle\storage\providers\Local::removeDir
   * @covers mychaelstyle\storage\providers\Local::getRealPath
   * @expectedException mychaelstyle\Exception
   */
  public function testRemoveExceptionContains()
  {
    $putUri = '/tdir/child/put.txt';
    $expectedPath = DIR_WORK.'/tdir/child/put.txt';
    $this->connect();
    $this->object->put($this->org_example,$putUri);
    $this->object->remove(dirname($putUri));
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::remove
   * @covers mychaelstyle\storage\providers\Local::removeDir
   * @covers mychaelstyle\storage\providers\Local::getRealPath
   * @expectedException mychaelstyle\Exception
   */
  public function testRemoveExceptionPermission()
  {
    $putUri = '/tdir/child/put.txt';
    $expectedPath = DIR_WORK.'/tdir/child/put.txt';
    $this->connect();
    $this->object->put($this->org_example,$putUri);
    $this->object->remove($putUri);
    chmod(dirname($expectedPath),0000);
    $this->object->remove(dirname($putUri));
  }

  /**
   * @covers mychaelstyle\storage\providers\Local::__construct
   * @covers mychaelstyle\storage\providers\Local::connect
   * @covers mychaelstyle\storage\providers\Local::disconnect
   * @covers mychaelstyle\storage\providers\Local::remove
   * @covers mychaelstyle\storage\providers\Local::removeDir
   * @covers mychaelstyle\storage\providers\Local::getRealPath
   * @expectedException mychaelstyle\Exception
   */
  public function testRemoveExceptionPermissionRecursive()
  {
    clearstatcache();
    $putUri = '/t2dir/child/put.txt';
    $expectedPath = DIR_WORK.$putUri;
    $this->connect();
    $this->object->put($this->org_example,$putUri);
    $this->assertTrue(file_exists($expectedPath));
    $tcd = dirname($expectedPath);
    $td = dirname($tcd);
    $tcd2 = $td.'/test';
    mkdir($tcd2,0000);
    chmod($tcd2,0000);
    $this->object->remove('/t2dir',true);
    $this->assertFalse(is_dir($td));
  }

}
