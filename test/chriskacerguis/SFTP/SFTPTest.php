<?php

namespace chriskacerguis\SFTP\Test;

class SFTPTest extends \PHPUnit_Framework_TestCase
{
    public $config;

    public function setUp()
    {
        $this->configPass['host']           = '';
        $this->configPass['user']           = '';
        $this->configPass['pass']           = '';
        $this->configPass['testFile']       = '';

        $this->configKey['host']            = '';
        $this->configKey['user']            = '';
        $this->configKey['publicKeyFile']   = '';
        $this->configKey['privateKeyFile']  = '';
        $this->configKey['testFile']        = '';

    }

    public function tearDown()
    {
        // No tear down needed
    }

    public function testConnectWithUsernameAndPassword()
    {
        $sftp       = new \chriskacerguis\SFTP\SFTP();
        $conn       = $sftp->connect($this->configPass['host'], $this->configPass['user'], $this->configPass['pass']);
        $resource   = is_resource($conn);
        $this->assertTrue($resource);

    }

    public function testConnectWithKey()
    {

    }

    public function testUpload()
    {
        $sftp       = new \chriskacerguis\SFTP\SFTP();
        $conn       = $sftp->connect($this->configPass['host'], $this->configPass['user'], $this->configPass['pass']);
        $localFile  = '/Users/chriskacerguis/Development/chriskacerguis-sftp/test/data/frog.jpg';
        $remoteFile = 'frog.jpg';
        $result     = $sftp->upload($localFile, $remoteFile);
        $this->assertTrue($result);
    }

    public function testDownload()
    {
        $sftp       = new \chriskacerguis\SFTP\SFTP();
        $conn       = $sftp->connect($this->configPass['host'], $this->configPass['user'], $this->configPass['pass']);
        $localFile  = '/tmp/frog.jpg';
        $remoteFile = 'frog.jpg';
        $result     = $sftp->download($remoteFile, $localFile);
        $exists     = file_exists($localFile);
        $this->assertTrue($exists);
    }

    public function testMakeDir()
    {
        $sftp       = new \chriskacerguis\SFTP\SFTP();
        $conn       = $sftp->connect($this->configPass['host'], $this->configPass['user'], $this->configPass['pass']);
        $result     = $sftp->mkdir('testDir'.time());
        $this->assertTrue(is_resource($result));
    }

    public function testFileList()
    {
        $sftp       = new \chriskacerguis\SFTP\SFTP();
        $conn       = $sftp->connect($this->configPass['host'], $this->configPass['user'], $this->configPass['pass']);
        $result     = $sftp->ls();
        $this->assertTrue(is_array($result));
    }


    public function testMove()
    {

    }

    public function testDirDelete()
    {

    }

    public function testFileDelete()
    {

    }


}
