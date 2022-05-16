<?php

namespace ZeroSSL\CliClient\Signers\ZeroSSL;

use ZeroSSL\CliClient\Enum\HTTPMethod;
use ZeroSSL\CliClient\Exception\RemoteRequestException;
use ZeroSSL\CliClient\Http\ApiEndpoint;
use JsonException;

class ApiEndpointRequester
{

    public ApiEndpointInfo $apiEndpointInfo;

    public function __construct(public string $apiUrl = "")
    {
        if(!filter_var($this->apiUrl,FILTER_VALIDATE_URL)) {
            $this->apiUrl = "https://api.zerossl.com";
        }
        $this->apiEndpointInfo = new ApiEndpointInfo();
    }

    /**
     * Requests JSON from API. Also adds default parameters API_URL and ACCESS_KEY.
     *
     * @param ApiEndpoint $endpoint
     * @param array $urlParams
     * @param array|string $params
     * @param bool $insecureDebug
     * @return array
     * @throws JsonException
     * @throws RemoteRequestException
     */
    public function requestJson(ApiEndpoint $endpoint, array $urlParams = [], array|string $params = [], bool $insecureDebug = false): array
    {
        $response = $this->request($endpoint,$urlParams,$params,$insecureDebug);
        return json_decode($response, JSON_THROW_ON_ERROR, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param ApiEndpoint $endpoint
     * @param array $urlParams
     * @param array|string $params
     * @param bool $insecureDebug
     * @return string
     * @throws RemoteRequestException
     */
    public function request(ApiEndpoint $endpoint, array $urlParams = [], array|string $params = [],$insecureDebug = false): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpointInfo->getPopulatedEndpointUrl($endpoint, $urlParams));

        if($insecureDebug) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        switch ($endpoint->getHTTPMethod()):
            case HTTPMethod::GET:
            {
                break;
            }
            case HTTPMethod::POST:
            {
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            }
            default:
            {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$endpoint->getHTTPMethod()->name);
            }
        endswitch;

        // execute!
        $response = curl_exec($ch);
        if(!$response) {
            throw new RemoteRequestException("Communication with the ZeroSSL API is currently not possible. This should be a temporary problem, please try again a little bit later. Details: " . curl_errno($ch) . curl_error($ch));
        }

        // close the connection, release resources used
        curl_close($ch);

        return $response;
    }
}
