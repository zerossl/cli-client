<?php

namespace ZeroSSL\CliClient\Signers\ZeroSSL;

use ZeroSSL\CliClient\Enum\HTTPMethod;
use ZeroSSL\CliClient\Http\ApiEndpoint;

class ApiEndpointInfo
{
    public array $endpoints = [];

    public function __construct()
    {
        $this->endpoints['create_certificate'] = new ApiEndpoint( '{{API_URL}}/certificates?access_key={{ACCESS_KEY}}', [], HTTPMethod::POST);
        $this->endpoints['verify_domains'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/challenges?access_key={{ACCESS_KEY}}', [], HTTPMethod::POST);
        $this->endpoints['download_certificate_zip'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/download?access_key={{ACCESS_KEY}}&include_cross_signed={{INCLUDE_CROSS_SIGNED}}', [], HTTPMethod::GET);
        $this->endpoints['download_certificate_json'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/download/return?access_key={{ACCESS_KEY}}&include_cross_signed={{INCLUDE_CROSS_SIGNED}}', [], HTTPMethod::GET);
        $this->endpoints['get_certificate'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}?access_key={{ACCESS_KEY}}', [], HTTPMethod::GET);
        $this->endpoints['list_certificates'] = new ApiEndpoint('{{API_URL}}/certificates?access_key={{ACCESS_KEY}}', [], HTTPMethod::GET);
        $this->endpoints['certificate_validation_status'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/status?access_key={{ACCESS_KEY}}', [], HTTPMethod::GET);
        $this->endpoints['resend_certificate_verification_email'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/challenges/email?access_key={{ACCESS_KEY}}', [], HTTPMethod::POST);
        $this->endpoints['revoke_certificate'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/revoke?access_key={{ACCESS_KEY}}', [], HTTPMethod::POST);
        $this->endpoints['cancel_certificate'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}/cancel?access_key={{ACCESS_KEY}}', [], HTTPMethod::POST);
        $this->endpoints['delete_certificate'] = new ApiEndpoint('{{API_URL}}/certificates/{{CERT_HASH}}?access_key={{ACCESS_KEY}}', [], HTTPMethod::DELETE);
        $this->endpoints['validate_csr'] = new ApiEndpoint('{{API_URL}}/validation/csr?access_key={{ACCESS_KEY}}', [], HTTPMethod::POST);
    }

    /**
     * @param string $key
     * @return array
     */
    public function getEndpointByKey(string $key): array
    {
        return $this->endpoints[$key];
    }

    /**
     * @param ApiEndpoint $endpoint
     * @param array $params
     * @return string
     */
    public function getPopulatedEndpointUrl(ApiEndpoint $endpoint,array $params): string
    {
        $out = $endpoint->getUrl();
        foreach($params as $key => $value) {
            $out = str_replace('{{'.$key.'}}',$value,$out);
        }
        return $out;
    }
}
