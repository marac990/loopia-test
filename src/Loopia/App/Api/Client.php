<?php

/*
 * © Loopia. All rights reserved.
 */

namespace Loopia\App\Api;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

class Client
{

    /**
     *
     * @var Credentials
     */
    protected $credentials;
    protected $endpoint;

    /**
     *
     * @var GuzzleClient
     */
    protected $client;

    public function __construct(CredentialsInterface $credentials, $endpoint)
    {
        $this->credentials = $credentials;
        $this->endpoint = $endpoint;
        $this->client = new GuzzleClient([
            'base_uri' => $this->endpoint,
            'timeout' => 0,
            'allow_redirects' => false,
        ]);
    }


    public function getRequest(string $uri): Request
    {
        $token = $this->credentials->getToken();
        return new Request('GET', $uri, [
            'X-Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache'
        ]);
    }

    public function send(Request $request)
    {
        return $this->client->send($request);
    }

}
