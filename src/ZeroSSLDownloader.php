<?php

namespace ZeroSSL\CliClient;

use ZeroSSL\CliClient\Signers\ZeroSSL\ApiEndpointRequester;

class ZeroSSLDownloader
{

    /**
     * @param string $apiKey
     * @param string $hash
     * @return array
     * @throws Exception\RemoteRequestException
     * @throws \JsonException
     */
    public static function download(string $apiKey, string $hash, bool $includeCrossSigned = false): array
    {
        $requester = new ApiEndpointRequester();

        return $requester->requestJson($requester->apiEndpointInfo->endpoints['download_certificate_json'],[
            "API_URL" => $requester->apiUrl, // API url dynamisch
            "ACCESS_KEY" => $apiKey,
            "CERT_HASH" => $hash,
            "INCLUDE_CROSS_SIGNED" => (int) $includeCrossSigned
        ]);
    }

}