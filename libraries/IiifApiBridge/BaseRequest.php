<?php

/**
 * Template for request helpers.
 * Use this to implement requests to the IIIF API.
 *
 * @package IiifApiBridge
 */
class IiifApiBridge_BaseRequest {
    /**
     * The internal HTTP client.
     * @var Zend_Http_Client
     */
    private $__httpClient;

    /**
     * The authentication key attached to requests.
     * @var string
     */
    private $__authenticationKey;

    /**
     * The URL root to the IIIF API instance.
     * @var string
     */
    private $__apiRoot;

    /**
     * The latest verb used in a request for this helper.
     * @var string
     */
    private $__lastVerb;

    /**
     * Build a new request helper.
     */
    function __construct() {
        $this->__httpClient = new Zend_Http_Client;
        $this->__authenticationKey = 'JWT ' . get_option('iiifapibridge_api_key');
        $this->__apiRoot = get_option('iiifapibridge_api_root');
        $this->__lastVerb = 'GET';
    }

    /**
     * Make an authenticated JSON request to the given URL relative to the root.
     * @param string $verb
     * @param string $url
     * @param array $json
     * @return array
     */
    protected function authJsonRequest($verb, $url, $json) {
        return $this->__recordedJsonRequest($verb, $this->__authenticationKey, $url, $json);
    }

    /**
     * Make an unauthenticated JSON request to the given URL relative to the root.
     * @param string $verb
     * @param string $url
     * @param array $json
     * @return array
     */
    protected function rawJsonRequest($verb, $url, $json) {
        return $this->__recordedJsonRequest($verb, NULL, $url, $json);
    }

    /**
     * Repeat the last request made using this helper.
     * @return array
     */
    public function repeatJsonRequest() {
        return $this->__httpClient->request($this->__lastVerb);
    }

    /**
     * Record the verb and make a JSON request.
     * @param string $verb
     * @param string|null $auth
     * @param string $url
     * @param array $json
     * @return array
     * @throws Zend_Http_Client_Exception
     * @throws IiifApiBridge_Exception_FailedJsonRequestException
     */
    private function __recordedJsonRequest($verb, $auth, $url, $json) {
        $this->__lastVerb = strtoupper($verb);
        $response = $this->__httpClient
             ->setUri($this->__apiRoot . $url)
             ->setHeaders('Authorization', $auth)
             ->setHeaders('Content-Type', 'application/json')
             ->setRawData(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
             ->request($this->__lastVerb);
        $status = $response->getStatus();
        $body = json_decode($response->getBody(), true);
        debug("Outgoing request: " . json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $statusType = floor($status/100);
        if ($statusType != 2 && $statusType != 3) {
            debug("IIIF API {$status}: " . $response->getBody());
        } else {
            return $body;
        }
        throw new IiifApiBridge_Exception_FailedJsonRequestException($status, $body);
    }
}
