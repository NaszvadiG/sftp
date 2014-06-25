SFTP
==========

A simple library for interacting with SFTP servers.

### Requirments

* PHP 5.4+
* ssh2 PECL library

### Directions

````
$SFTP = new \chriskacerguis\SFTP();

// the FQDN you want to connect to
$hostname = 'some.server.com'; 

// the username of the server you are connecting to
$username = 'myuser'; 

// the password of the username (or if you are using public/private keys it is the passphrase)
$password = 'MyP4ssW0rd'; 

// public key location
$publicKey = '~/.ssh/id_rsa.pub';

// private key location
$publicKey = '~/.ssh/id_rsa';

// the port (optional, defaults to 22)
$port = 22;

// connect with username/password
$sftp = $SFTP->connect($hostname, $username, $password);

// alternative connection with public/private keys
$sftp = $SFTP->connect($hostname, $username, null, $publicKey, $privateKey);

// Send a file 
$sftp->upload($localFile, $remoteFile, $permissions = 0644);

// download a file
$sftp->download($remoteFile, $localFile);

// get a directory listing
$dirList = $sftp->ls($remotePath);

// create a directory on the remote server (will recursivly create the remote dir)
$sftp->mkdir($remotePath, $permissions = 0755);

// Renames a file
$sftp->move($oldFile, $newFile);

````