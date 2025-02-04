<?php

namespace Transmission;

use Transmission\Model\File;
use Transmission\Model\ModelInterface;
use Transmission\Model\Torrent;
use Transmission\Model\Session;
use Transmission\Model\FreeSpace;
use Transmission\Model\Stats\Session as SessionStats;
use Transmission\Util\PropertyMapper;
use Transmission\Util\ResponseValidator;

/**
 * @author Ramon Kleiss <ramon@cubilon.nl>
 */
class Transmission
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ResponseValidator
     */
    protected $validator;

    /**
     * @var PropertyMapper
     */
    protected $mapper;

    /**
     * Constructor
     *
     * @param string $host The hostname or IP of the Transmission server
     * @param integer $port The port the Transmission server is listening on
     * @param string $path The path to Transmission server rpc api
     * @param integer $timeout Number of seconds after which to fail requests
     */
    public function __construct($host = null, $port = null, $path = null, $timeout = null)
    {
        $this->setClient(new Client($host, $port, $path, $timeout));
        $this->setMapper(new PropertyMapper());
        $this->setValidator(new ResponseValidator());
    }

    /**
     * Get all the torrents in the download queue
     *
     * @return Torrent[]
     */
    public function all()
    {
        $client   = $this->getClient();
        $mapper   = $this->getMapper();
        $response = $this->getClient()->call(
            'torrent-get',
            array ('fields' => array_keys(Torrent::getMapping()))
        );

        return array_map(function ($data) use ($mapper, $client) {
            return $mapper->map(
                new Torrent($client),
                $data
            );
        }, $this->getValidator()->validate('torrent-get', $response));
    }

    /**
     * Get a specific torrent from the download queue
     *
     * @param integer $id
     * @return Torrent
     * @throws \RuntimeException
     */
    public function get($id)
    {
        $client   = $this->getClient();
        $mapper   = $this->getMapper();
        $response = $this->getClient()->call('torrent-get', array (
            'fields' => array_keys(Torrent::getMapping()),
            'ids'    => array ($id)
        ));

        $torrent = array_reduce(
            $this->getValidator()->validate('torrent-get', $response),
            function ($torrent, $data) use ($mapper, $client) {
                return $torrent ? $torrent : $mapper->map(new Torrent($client), $data);
            });

        if (!$torrent instanceof Torrent) {
            throw new \RuntimeException(sprintf("Torrent with ID %s not found", $id));
        }

        return $torrent;
    }

    /**
     * Get the Transmission session
     *
     * @return Session
     */
    public function getSession()
    {
        $response = $this->getClient()->call(
            'session-get',
            array ()
        );

        return $this->getMapper()->map(
            new Session($this->getClient()),
            $this->getValidator()->validate('session-get', $response)
        );
    }

    /**
     * @return SessionStats
     */
    public function getSessionStats()
    {
        $response = $this->getClient()->call(
            'session-stats',
            array ()
        );

        return $this->getMapper()->map(
            new SessionStats(),
            $this->getValidator()->validate('session-stats', $response)
        );
    }

    /**
     * Get Free space
     * @param string $path
     * @return FreeSpace
     */
    public function getFreeSpace($path = null)
    {
        if (!$path) {
            $path = $this->getSession()->getDownloadDir();
        }
        $response = $this->getClient()->call(
            'free-space',
            array ('path' => $path)
        );

        return $this->getMapper()->map(
            new FreeSpace(),
            $this->getValidator()->validate('free-space', $response)
        );
    }

    /**
     * Add a torrent to the download queue
     *
     * @param string $torrent
     * @param boolean $metainfo
     * @param string $savepath
     * @return Torrent|ModelInterface
     */
    public function add($torrent, $metainfo = false, $savepath = null)
    {
        $parameters = array ($metainfo ? 'metainfo' : 'filename' => $torrent);

        if ($savepath !== null) {
            $parameters['download-dir'] = (string)$savepath;
        }

        $response = $this->getClient()->call(
            'torrent-add',
            $parameters
        );

        return $this->getMapper()->map(
            new Torrent($this->getClient()),
            $this->getValidator()->validate('torrent-add', $response)
        );
    }

    /**
     * Start the download of a torrent
     *
     * @param Torrent $torrent
     * @param bool $now
     */
    public function start(Torrent $torrent, bool $now = false): void
    {
        $this->getClient()->call(
            $now ? 'torrent-start-now' : 'torrent-start',
            array ('ids' => array ($torrent->getId()))
        );
    }

    /**
     * Stop the download of a torrent
     *
     * @param Torrent $torrent
     */
    public function stop(Torrent $torrent)
    {
        $this->getClient()->call(
            'torrent-stop',
            array ('ids' => array ($torrent->getId()))
        );
    }

    /**
     * Stop the download of a torrent
     *
     * @param Torrent $torrent
     */
    public function stopFile(Torrent $torrent, File $file): void
    {
        $this->getClient()->call(
            'torrent-set',
            [
                'ids'            => [$torrent->getId()],
                'files-unwanted' => [$file->getId()]
            ]
        );
    }

    /**
     * Stop the download of a torrent
     *
     * @param Torrent $torrent
     */
    public function startFile(Torrent $torrent, File $file): void
    {
        $this->getClient()->call(
            'torrent-set',
            [
                'ids'          => [$torrent->getId()],
                'files-wanted' => [$file->getId()]
            ]
        );
    }

    /**
     * Verify the download of a torrent
     *
     * @param Torrent $torrent
     */
    public function verify(Torrent $torrent): void
    {
        $this->getClient()->call(
            'torrent-verify',
            array ('ids' => array ($torrent->getId()))
        );
    }

    /**
     * Request a reannounce of a torrent
     *
     * @param Torrent $torrent
     */
    public function reannounce(Torrent $torrent): void
    {
        $this->getClient()->call(
            'torrent-reannounce',
            array ('ids' => array ($torrent->getId()))
        );
    }

    /**
     * Remove a torrent from the download queue
     *
     * @param Torrent $torrent
     */
    public function remove(Torrent $torrent, bool $localData = false): void
    {
        $arguments = array ('ids' => array ($torrent->getId()));

        if ($localData) {
            $arguments['delete-local-data'] = true;
        }

        $this->getClient()->call('torrent-remove', $arguments);
    }

    /**
     * Set the client used to connect to Transmission
     *
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Get the client used to connect to Transmission
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Set the hostname of the Transmission server
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->getClient()->setHost($host);
    }

    /**
     * Get the hostname of the Transmission server
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->getClient()->getHost();
    }

    /**
     * Set the port the Transmission server is listening on
     *
     * @param integer $port
     */
    public function setPort(int $port): void
    {
        $this->getClient()->setPort($port);
    }

    /**
     * Get the port the Transmission server is listening on
     *
     * @return integer
     */
    public function getPort(): int
    {
        return $this->getClient()->getPort();
    }

    /**
     * Set the mapper used to map responses from Transmission to models
     *
     * @param PropertyMapper $mapper
     */
    public function setMapper(PropertyMapper $mapper): void
    {
        $this->mapper = $mapper;
    }

    /**
     * Get the mapper used to map responses from Transmission to models
     *
     * @return PropertyMapper
     */
    public function getMapper(): PropertyMapper
    {
        return $this->mapper;
    }

    /**
     * Set the validator used to validate Transmission responses
     *
     * @param ResponseValidator $validator
     */
    public function setValidator(ResponseValidator $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * Get the validator used to validate Transmission responses
     *
     * @return ResponseValidator
     */
    public function getValidator(): ResponseValidator
    {
        return $this->validator;
    }
}
