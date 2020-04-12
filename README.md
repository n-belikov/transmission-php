# PHP Transmission API

[![Build Status](https://travis-ci.com/mostertb/transmission-php.svg?branch=master)](https://travis-ci.com/mostertb/transmission-php)

This repo contains a fork of the un-maintained [kleiram/transmission-php](https://github.com/kleiram/transmission-php)
PHP Transmission RPC client library. This fork provides support for more recent versions of PHP and functionality in newer
versions of Transmission as well as fixes to upstream issues. See the [CHANGLOG](CHANGELOG) for a full list of changes 

The library provides an interface to the [Transmission](http://transmissionbt.com) bit-torrent downloader. It provides 
means to get and remove torrents from the downloader as well as adding new torrents to the download queue.

## Installation

Installation is easy using [Composer](https://getcomposer.org):

```bash
composer require mostertb/transmission-php
```

## Usage

Using the library is as easy as installing it:

```php
<?php
use Transmission\Transmission;

$transmission = new Transmission();

// Getting all the torrents currently in the download queue
$torrents = $transmission->all();

// Getting a specific torrent from the download queue
$torrent = $transmission->get(1);

// (you can also get a torrent by the hash of the torrent)
$torrent = $transmission->get(/* torrent hash */);

// Adding a torrent to the download queue
$torrent = $transmission->add(/* path to torrent */);

// Removing a torrent from the download queue
$torrent = $transmission->get(1);
$transmission->remove($torrent);

// Or if you want to delete all local data too
$transmission->remove($torrent, true);

// You can also get the Trackers that the torrent currently uses
// These are instances of the Transmission\Model\Tracker class
$trackers = $torrent->getTrackers();

// You can also get the Trackers statistics and info that the torrent currently has
// These are instances of the Transmission\Model\trackerStats class
$trackerStats = $torrent->getTrackerStats();

// To get the start date/time of the torrent in UNIX Timestamp format
$startTime = $torrent -> getStartDate();

// To get the number of peers connected
$connectedPeers = $torrent -> getPeersConnected();

// Getting the files downloaded by the torrent are available too
// These are instances of Transmission\Model\File
$files = $torrent->getFiles();

// You can start, stop, verify the torrent and ask the tracker for
// more peers to connect to
$transmission->stop($torrent);
$transmission->start($torrent);
$transmission->start($torrent, true); // Pass true if you want to start the torrent immediatly
$transmission->verify($torrent);
$transmission->reannounce($torrent);
```

To find out which information is contained by the torrent, check
[`Transmission\Model\Torrent`](https://github.com/kleiram/transmission-php/tree/master/lib/Transmission/Model/Torrent.php).

By default, the library will try to connect to `localhost:9091`. If you want to
connect to another host or post you can pass those to the constructor of the
`Transmission` class. 

```php
<?php
use Transmission\Transmission;

$transmission = new Transmission('example.com', 33);

$torrents = $transmission->all();
$torrent  = $transmission->get(1);
$torrent  = $transmission->add(/* path to torrent */);

// When you already have a torrent, you don't have to pass the client again
$torrent->delete();
```
The constructor will also allow you to configure a non-standard RPC URL with its third parameter and
a request timeout (in seconds) with its fourth  parameter. All parameters are optional:
```php
<?php
use Transmission\Transmission;

$transmission = new Transmission(null, null, '/transmission/some-custom-url/rpc', 10);
```

It is also possible to pass the torrent data directly instead of using a file
but the metadata must be base64-encoded:

```php
<?php
$torrent = $transmission->add(/* base64-encoded metainfo */, true);
```

If the Transmission server is secured with a username and password you can
authenticate using the `Client` class:

```php
<?php
use Transmission\Client;
use Transmission\Transmission;

$client = new Client(); // Can take the same optional parameters for host, port, url and timeout as the Transmission class
$client->authenticate('username', 'password');
$transmission = new Transmission();
$transmission->setClient($client);
```

Additionally, you can control the actual Transmission setting. This means
you can modify the global download limit or change the download directory:

```php
<?php
use Transmission\Transmission;

$transmission = new Transmission();
$session = $transmission->getSession();

$session->setDownloadDir('/home/foo/downloads/complete');
$session->setIncompleteDir('/home/foo/downloads/incomplete');
$session->setIncompleteDirEnabled(true);
$session->save();
```

## Testing

Testing is done using [PHPUnit](https://github.com/sebastianbergmann/phpunit). Assuming your `composer install` was done
including development dependencies, you can run the version of PHPUnit supported by this project straight from the `vendor`
directory:

```bash
php vendor/bin/phpunit
```

## License

This library is licensed under the BSD 2-clause license. Refer to the [LICENSE](LICENSE) file
