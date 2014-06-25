<?php
/**
 * Simple easy to use SFTP class
 *
 * @author  Chris Kacerguis <public@cold.io>
 * @link    https://github.com/chriskacerguis/sftp
 * @license Propritary
 */

namespace chriskacerguis\SFTP;

class SFTP
{

    /**
     * the host of the server you are connecing to
     * @var string
     */
    protected $host         = '';

    /**
     * SSH username that you are connecting as
     * @var string
     */
    protected $user         = '';

    /**
     * SSH password of the user you are connecting as
     * @var string
     */
    protected $pass         = '';

    /**
     * path to your public key file (defauls to ~/.ssh/id_rsa.pub)
     * @var string
     */
    protected $publicKeyFile    = '';

    /**
     * path to your private key file (defauls to ~/.ssh/id_rsa)
     * @var string
     */
    protected $privateKeyFile   = '';

    /**
     * the passphrase of your key (leave blank if you don't have one)
     * @var string
     */
    protected $passphrse        = '';

    /**
     * [$port description]
     * @var integer
     */
    protected $port             = 22;

    /**
     * ssh connection for internal use
     * @var boolean
     */
    private $conn               = false;

    /**
     * sftp resource connection
     * @var boolean
     */
    private $sftp               = false;

    /**
     * [__construct description]
     * @author Chris Kacerguis <public@cold.io>
     */
    public function __construct()
    {

    }

    /**
     * [__destruct description]
     * @author Chris Kacerguis <public@cold.io>
     */
    public function __destruct() 
    { 
        $this->disconnect(); 
    } 

    /**
     * [connect description]
     * @author Chris Kacerguis <public@cold.io>
     * @param  [type]  $host [description]
     * @param  [type]  $user [description]
     * @param  [type]  $pass [description]
     * @param  string  $pub  [description]
     * @param  string  $pri  [description]
     * @param  integer $port [description]
     * @return [type]        [description]
     */
    public function connect($host, $user, $pass = null, $pub = '', $pri = '', $port = 22)
    {
        $this->host             = preg_replace('|.+?://|', '', $host);
        $this->user             = $user;
        $this->pass             = $pass;
        $this->publicKeyFile    = $pub;
        $this->privateKeyFile   = $pri;
        $this->port             = $port;

        if (!$this->conn = @ssh2_connect($this->host, $this->port)) {
            throw new Exception("connection to {$this->host} failed");
        }

        if (!$this->login()) {
            throw new Exception('login failed');
        }

        if (!$this->sftp = @ssh2_sftp($this->conn)) {
            throw new Exception("unable to establish sftp connection with {$host}");
        }

        return $this->sftp;
    }

    /**
     * [disconnect description]
     * @return [type] [description]
     */
    public function disconnect() 
    { 
        @ssh2_exec('echo "EXITING" && exit;'); 
        $this->conn = null; 
    } 

    /**
     * [upload description]
     * @param  [type] $localFile   [description]
     * @param  [type] $remoteFile  [description]
     * @param  [type] $permissions [description]
     * @return [type]              [description]
     */
    public function upload($localFile, $remoteFile, $permissions = 0644)
    {
        if (!$this->validConn()) {
            throw new Exception('invalid connection');
        }

        if (!file_exists($localFile)) {
            throw new Exception("{$localFile} does not exist");
        }

        return @ssh2_scp_send($this->conn, $localFile, $remoteFile, $permissions);

    }

    /**
     * [download description]
     * @author  Chris Kacerguis <ckacerguis@apple.com>
     * @param   [type] $rempath [description]
     * @param   [type] $locpath [description]
     * @param   string $mode    [description]
     * @return  [type]          [description]
     */
    public function download($remoteFile, $localFile)
    {
        if (false === ($this->validConn())) {
            throw new Exception('invalid connection');
        }

        return @ssh2_scp_recv($this->conn, $remoteFile, $localFile);
    }

    /**
     * [fileList description]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function ls($path = '.')
    {
        if (false === ($this->validConn())) {
            throw new Exception('invalid connection');
        }
        if (!$stream = @ssh2_exec($this->conn, "ls {$path}")) {
            throw new Exception('unable to ');
        }
        stream_set_blocking($stream, true); 
        $cmd    = fread($stream, 4096); 
        return explode("\n", $cmd); 
    }

    /**
     * [mkdir description]
     * @author  Chris Kacerguis <ckacerguis@apple.com>
     * @param   string $path        [description]
     * @param   string $permissions [description]
     * @return  [type]              [description]
     */
    public function mkdir($path = '', $permissions = 0755)
    {
        if ($path == '') {
            throw new Exception('invalid path');
        }

        if (!$this->validConn()) {
            throw new Exception('invalid connection');
        }

        if (!$stream = @ssh2_exec($this->conn, "mkdir {$path}")) {
            throw new Exception("unable to create directory {$path}");
        }

        return $stream;
    }
    
    /**
     * [rename description]
     * @param  [type] $oldFile [description]
     * @param  [type] $newFile [description]
     * @return [type]          [description]
     */
    public function move($oldFile, $newFile)
    {
        if (false === ($this->validConn())) {
            throw new Exception('invalid connection');
        }

        if (!$this->sftp = @ssh2_sftp($this->conn)) {
            throw new Exception("unable to establish sftp connection with {$host}");
        }

        return $this->sftp;

        if (false === ($result = @ssh2_sftp_rename($this->sftp, $oldFile, $newFile))) {
            throw new Exception("unable to rename file");
        }

        return $result;
    }

    public function exists($remote)
    {
        
    }


    /**
     * [deleteFile description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    public function deleteFile($file)
    {
        if (false === ($this->validConn())) {
            throw new Exception('invalid connection');
        }
        if (!$this->sftp = @ssh2_sftp($this->conn)) {
            throw new Exception("unable to establish sftp connection with {$host}");
        }

        return $this->sftp;
        if (!$result = @ssh2_sftp_unlink($this->sftp, $file)) {
            throw new Exception("unable to delete file");
        }
    }

    /**
     * [deleteDir description]
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function deleteDir($path)
    {
        if (false === ($this->validConn())) {
            throw new Exception('invalid connection');
        }
        if (!$this->sftp = @ssh2_sftp($this->conn)) {
            throw new Exception("unable to establish sftp connection with {$host}");
        }

        return $this->sftp;
        // Add a trailing slash to the file path if needed
        $filepath   = preg_replace("/(.+?)\/*$/", "\\1/",  $path);
        $list       = $this->fileList($path);

        if (count($list) > 0) {
            foreach ($list as $item) {
                if (!@ssh2_sftp_rmdir($this->sftp, $item)) {
                    $this->deleteDir($item);
                }
            }
        }

        return true;
    }

    /**
     * [chmod description]
     * @param  [type] $path [description]
     * @param  [type] $perm [description]
     * @return [type]       [description]
     */
    public function chmod($path, $permissions)
    {
        if (false === ($this->validConn())) {
            throw new Exception('invalid connection');
        }
        if (!$this->sftp = @ssh2_sftp($this->conn)) {
            throw new Exception("unable to establish sftp connection with {$host}");
        }

        return $this->sftp;
        if (false === ($result = @ssh2_sftp_chmod($this->sftp, $path, $permissions))) {
            throw new Exception("unable to chmod {$permissions} of {$path}");
        }

        return $result;
    }

    /**
     * [login description]
     * @author  Chris Kacerguis <ckacerguis@apple.com>
     * @return [type] [description]
     */
    private function login()
    {
        // Login via username/password
        if ($this->publicKeyFile == '') {
            return @ssh2_auth_password($this->conn, $this->user, $this->pass);
        }

        if (!file_exists($this->publicKeyFile)) {
            throw new Exception("public key '{$this->publicKeyFile}' file does not exist");
        }

        if (!file_exists($this->privateKeyFile)) {
            throw new Exception("private key '{$this->publicKeyFile}' file does not exist");
        }

        // login with an SSH key with a passphrase
        if ($this->pass != null) {
            return @ssh2_auth_pubkey_file($this->conn, $this->user, $this->publicKeyFile, $this->privateKeyFile, $this->pass);
        }

        // login WITHOUT an SSH key with a passphrase
        return @ssh2_auth_pubkey_file($this->conn, $this->user, $this->publicKeyFile, $this->privateKeyFile);

    }

    /**
     * [validConn description]
     * @author  Chris Kacerguis <ckacerguis@apple.com>
     * @return [type] [description]
     */
    private function validConn()
    {
        if (!is_resource($this->conn) && !is_resource($this->sftp)) {
            return false;
        }
        return true;
    }



}
