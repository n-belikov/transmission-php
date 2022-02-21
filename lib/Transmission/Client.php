<?php

namespace Transmission;

use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Client\Curl;
use Buzz\Client\ClientInterface;

/**
 * The Client class is used to make API calls to the Transmission server
 *
 * @author Ramon Kleiss <ramon@cubilon.nl>
 */
class Client
{
    /**
     * @var string
     */
    const DEFAULT_SCHEME = 'http';

    /**
     * @var string
     */
    const DEFAULT_HOST = 'localhost';

    /**
     * @var integer
     */
    const DEFAULT_PORT = 9091;

    /**
     * @var string
     */
    const DEFAULT_PATH = '/transmission/rpc';

    /**
     * @var string
     */
    const TOKEN_HEADER = 'X-Transmission-Session-Id';

    /**
     * @var string
     */
    protected $scheme = self::DEFAULT_SCHEME;

    /**
     * @var string
     */
    protected $host = self::DEFAULT_HOST;

    /**
     * @var integer
     */
    protected $port = self::DEFAULT_PORT;

    /**
     * @var string
     */
    protected $path = self::DEFAULT_PATH;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $auth;

    /**
     * Constructor
     *
     * @param string $host The hostname or IP of the Transmission server, or a full URL
     * @param integer $port The port the Transmission server is listening on
     * @param string $path The path to Transmission server rpc api
     * @param integer $timeout Number of seconds after which to fail requests
     */
    public function __construct($host = null, $port = null, $path = null, $timeout = null)
    {
        $this->token  = null;
        $this->client = new Curl();

        if (!is_null($timeout)) {
            $this->client->setTimeout($timeout);
        }

        if ($host) {
            // Check if the host is actually a URL
            if (strpos($host, ':') !== FALSE) {
                $urlscheme = parse_url($host, PHP_URL_SCHEME);
                $urlhost   = parse_url($host, PHP_URL_HOST);
                $urlport   = parse_url($host, PHP_URL_PORT);
                $urlpath   = parse_url($host, PHP_URL_PATH);

                if ($urlscheme) {
                    $this->setScheme($urlscheme);
                }
                if ($urlhost) {
                    $this->setHost($urlhost);
                }
                if ($urlport) {
                    $this->setPort($urlport);
                }
                if ($urlpath) {
                    $this->setPath($urlpath);
                }
            } else {
                $this->setHost($host);
            }
        }

        if ($port) {
            $this->setPort($port);
        }
        if ($path) {
            $this->setPath($path);
        }
    }

    /**
     * Authenticate against the Transmission server
     *
     * @param string $username
     * @param string $password
     */
    public function authenticate($username, $password)
    {
        $this->auth = base64_encode($username.':'.$password);
    }

    /**
     * Make an API call
     *
     * @param string $method
     * @param array $arguments
     * @return \stdClass
     * @throws \RuntimeException
     */
    public function call($method, array $arguments)
    {
        [$request, $response] = $this->compose($method, $arguments);

        try {
            $this->getClient()->send($request, $response);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Could not connect to Transmission',
                0,
                $e
            );
        }

        return $this->validateResponse($response, $method, $arguments);
    }

    /**
     * Get the URL used to connect to Transmission
     *
     * @return string
     */
    public function getUrl()
    {
        return sprintf(
            '%s://%s:%d',
            $this->getScheme(),
            $this->getHost(),
            $this->getPort()
        );
    }

    /**
     * Set the scheme of the Transmission server
     *
     * @param string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = (string)$scheme;
    }

    /**
     * Get the scheme of the Transmission server
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Set the hostname of the Transmission server
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = (string)$host;
    }

    /**
     * Get the hostname of the Transmission server
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the port the Transmission server is listening on
     *
     * @param integer $port
     */
    public function setPort($port)
    {
        $this->port = (integer)$port;
    }

    /**
     * Get the port the Transmission server is listening on
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the path to Transmission server rpc api
     *
     * @param string $path
     */
    public function setPath($path)
    {
        return $this->path = (string)$path;
    }

    /**
     * Get the path to Transmission server rpc api
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the CSRF-token of the Transmission client
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = (string)$token;
    }

    /**
     * Get the CSRF-token for the Transmission client
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the Buzz client used to connect to Transmission
     *
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Get the Buzz client used to connect to Transmission
     *
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return array|Request[]|Response[]
     */
    protected function compose($method, $arguments)
    {
        $request = new Request('POST', $this->getPath(), $this->getUrl());
        $request->addHeader(sprintf('%s: %s', self::TOKEN_HEADER, $this->getToken()));
        $request->setContent(json_encode(array (
            'method'    => $method,
            'arguments' => $arguments
        )));

        if (is_string($this->auth)) {
            $request->addHeader(sprintf('Authorization: Basic %s', $this->auth));
        }

        return array ($request, new Response());
    }

    /**
     * @param Response $response
     * @param string $method
     * @param array $arguments
     * @return \stdClass
     * @throws \RuntimeException
     */
    protected function validateResponse($response, $method, $arguments)
    {
        if (!in_array($response->getStatusCode(), array (200, 401, 409))) {
            throw new \RuntimeException('Unexpected response received from Transmission');
        }

        if ($response->getStatusCode() == 401) {
            throw new \RuntimeException('Access to Transmission requires authentication');
        }

        if ($response->getStatusCode() == 409) {
            $this->setToken($response->getHeader(self::TOKEN_HEADER));

            return $this->call($method, $arguments);
        }

        return json_decode($response->getContent());
    }
}
