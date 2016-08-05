<?php

namespace FireTruck\API;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;
use FireTruck\Exception\FireTruckAPIException;
use FireTruck\Exception\ResponseException;
use FireTruck\API\Response;

/**
 * 
 * FireTruck API Client
 *
 * FireTruck API v1 wrapper for PHP
 *
 * @author Leo Flapper <leo.flapper@slicklabs.nl>
 * @version 1.0.0
 */
class Client
{

    /**
     * The FireTruck API key.
     * @var string
     */
    private $apiKey;

    /**
     * The FireTruck API url without the version number.
     * @var string
     */
    private $apiUrl = 'https://api.firetruck.io';

    /**
     * The API version
     * @var string
     */
    private $apiVersion = 'v1';

    /**
     * The request headers.
     * @var array
     */
    private $headers = [];

    /**
     * Verify SSL peer.
     * @var boolean
     */
    private $verify = true;

    /**
     * The request client.
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Valid request methods.
     * @var string[]
     */
    protected $validMethods = [
        'DELETE'    => true,
        'GET'       => true,
        'PATCH'     => true,
        'POST'      => true,
        'PUT'       => true
    ];

    /**
     * Methods which allow a request body.
     * @var string[]
     */
    protected $bodyMethods = [
        'PATCH' => true,
        'POST'  => true,
        'PUT'   => true
    ];

    /**
     * Sets the FireTruck API key and request headers.
     * @param string $apiKey the FireTruck API key
     */
    public function __construct($apiKey)
    {
        $this->setApiKey($apiKey);
        $this->setHeaders($this->getDefaultHeaders());
    }

    public function getApiUrl()
    {
        return $this->apiUrl . '/' . $this->getApiVersion();
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion)
    {
        if (!is_string($apiVersion)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($apiVersion) ? get_class($apiVersion) : gettype($apiVersion))
            ));
        }

        $this->apiVersion = $apiVersion;
    }

    /**
     * Returns the FireTruck API key.
     * @return string the FireTruck API key.
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Sets the FireTruck API key.
     * @param string $apiKey the FireTruck API key.
     */
    public function setApiKey($apiKey)
    {
        if (!is_string($apiKey)) {
            throw new InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($apiKey) ? get_class($apiKey) : gettype($apiKey))
            ));
        }

        $this->apiKey = $apiKey;
    }

    /**
     * Returns a single or all headers.
     * @param  string $key optional header key.
     * @return mixed the header values, or a single header value.
     */
    public function getHeaders($key = '')
    {

        $result = $this->headers;
        
        if($key){
            $result = '';
            if (!is_string($key)) {
                throw new InvalidArgumentException(sprintf(
                    '%s: expects a string argument; received "%s"',
                    __METHOD__,
                    (is_object($key) ? get_class($key) : gettype($key))
                ));
            }

            if(isset($this->headers[$key])){
                $result = $this->headers[$key];
            }
        }

        return $result;
    }

    /**
     * Sets headers by the array provided.
     * @param array $headers the headers.
     */
    public function setHeaders(array $headers)
    {
        foreach($headers as $key => $value){
            $this->setHeader($key, $value);
        }
    }

    /**
     * Sets a single header.
     * @param string    $key   the header key.
     * @param mixed     $value the header value.
     */
    public function setHeader($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($key) ? get_class($key) : gettype($key))
            ));
        }

        $this->headers[$key] = $value;
    }

    /**
     * Sets the verify SSL peer boolean.
     * @param bool $verify true to verify, false if not.
     */
    public function setVerify($verify)
    {
        if (!is_bool($verify)) {
            throw new \InvalidArgumentException(sprintf(
                '%s: expects a boolean argument; received "%s"',
                __METHOD__,
                (is_object() ? get_class() : gettype())
            ));
        }
        
        return $this->verify = $verify;
    }

    /**
     * Returns the verify SSL peer boolean.
     * @return bool $verify true to verify, false if not.
     */
    public function verify()
    {
        return $this->verify;
    }

    /**
     * Returns the default headers.
     * @return array the default headers.
     */
    public function getDefaultHeaders()
    {
        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            'User-Agent' => 'Firetruck/API/Client'
        ];
    }

    /**
     * Returns the request client.
     * @return GuzzleHttp\Client the request client.
     */
    private function getClient()
    {
        if(!$this->client){
            $this->client = new GuzzleHttpClient();
        }
        return $this->client;
    }

    /**
     * Returns the valid request methods.
     * @return array the valid request methods.
     */
    private function getMethods()
    {
        return array_keys($this->validMethods);
    }

    /**
     * Checks the method provided is valid.
     * @param  string $method the request method.
     * @return boolean true if valid, false if not.
     */
    private function isValidMethod($method)
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }

        return (isset($this->validMethods[$method]));
    }

    /**
     * Checks if the a request body is allowed for the 
     * desired method.
     * @param  string $method the request method.
     * @return boolean true if body allowed, false if not.       
     */
    private function bodyAllowed($method)
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                '%s: expects a string argument; received "%s"',
                __METHOD__,
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }

        return (isset($this->bodyMethods[$method]));
    }

    /**
     * Performs a GET request.
     * @param  string $uri the FireTruck API uri.
     * @param  array  $args   request values.
     * @throws ResponseException if the response status code is not 200.
     * @return FireTruck\API\ResponseInterface the FireTruck API response.
     */
    public function get($uri, $args = [])
    {
        return $this->doRequest('GET', $uri, $args);
    }

    /**
     * Performs a PATCH request.
     * @param  string $uri the FireTruck API uri.
     * @param  array  $args   request values.
     * @throws ResponseException if the response status code is not 200.
     * @return FireTruck\API\ResponseInterface the FireTruck API response.
     */
    public function patch($uri, $args = [])
    {
        return $this->doRequest('PATCH', $uri, $args);
    }

    /**
     * Performs a POST request.
     * @param  string $uri the FireTruck API uri.
     * @param  array  $args   request values.
     * @throws ResponseException if the response status code is not 200.
     * @return FireTruck\API\ResponseInterface the FireTruck API response.
     */
    public function post($uri, $args = [])
    {
        return $this->doRequest('POST', $uri, $args);
    }

    /**
     * Performs a PUT request.
     * @param  string $uri the FireTruck API uri.
     * @param  array  $args   request values.
     * @throws ResponseException if the response status code is not 200.
     * @return FireTruck\API\ResponseInterface the FireTruck API response.
     */
    public function put($uri, $args = [])
    {
        return $this->doRequest('PUT', $uri, $args);
    }  

    /**
     * Performs an DELETE request.
     * @param  string $uri the FireTruck API uri.
     * @param  array  $args   request values.
     * @throws ResponseException if the response status code is not 200.
     * @return FireTruck\API\ResponseInterface the FireTruck API response.
     */
    public function delete($uri, $args = [])
    {
        return $this->doRequest('DELETE', $uri, $args);
    }

    /**
     * Performs a HTTP request.
     * @param  string $method                           the desired HTTP request method.
     * @param  string $uri                              the FireTruck API uri.
     * @param  array  $args                             request values.
     * @return FireTruck\API\ResponseInterface          the FireTruck API response.
     */
    protected function doRequest($method, $uri, $args = [])
    {

        $response = false;

        if(!$this->isValidMethod($method)){
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid HTTP method: available methods are %s.', $method, implode(', ', $this->getMethods()))
            );
        }        

        $url = $this->getApiUrl() . '/' . $uri;
        $defaultArgs = [
            'query' => ['apikey' =>  $this->getApiKey()],
            'headers' => $this->getHeaders(),
            'timeout' => 10,
            'verify' => $this->verify()     
        ];

        $args = array_replace_recursive($defaultArgs, $args);

        if($this->bodyAllowed($method)){
            if(!isset($args['body'])){
                $args['body'] = '';
            }
            $args['json'] = $args['body']; 
        }
        unset($args['body']);

        try {
            $response = $this->formatResponse($this->getClient()->request($method, $url, $args));
        } catch (ClientException $e) {
            $response = $this->formatResponse($e->getResponse());
        }

        return $response;

    }

    /**
     * Sets the FireTruck API response.
     * @param  ResponseInterface $response              PSR-7 response interface.
     * @throws ResponseException if the response status code is not 200.
     * @return FireTruck\API\ResponseInterface    the FireTruck API response.
     */
    protected function formatResponse(ResponseInterface $response)
    {
        $FiretruckResponse = new Response($response);

        $body = $FiretruckResponse->getBody();
        if ($FiretruckResponse->getStatusCode() !== 200) {
            throw new ResponseException($FiretruckResponse);
        }

        return $FiretruckResponse;
    }

}