<?php

namespace ZeroSSL\CliClient\Signers;

use ZeroSSL\CliClient\Exception\OpenSSLExportException;
use InvalidArgumentException;
use OpenSSLCertificateSigningRequest;

class ZeroSSLSigner
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param OpenSSLCertificateSigningRequest $csr
     * @param string $certificateDomains
     * @return string
     * @throws OpenSSLExportException
     */
    public function createDraft(OpenSSLCertificateSigningRequest $csr, string $certificateDomains): string
    {
        $domains = self::sanitizeDomains($certificateDomains);

        if (empty($domains)) {
            throw new InvalidArgumentException("A certificate needs at least one domain.");
        }

        $csrAsText = "";
        if(!openssl_csr_export($csr,$csrAsText)) {
            throw new OpenSSLExportException("Unable to use your CSR as text for certificate signing.");
        }

        $certData = $this->apiEndpointRequester->requestJson($this->apiEndpointCreate, [], [
            'certificate_domains' => $certificateDomains,
            'certificate_validity_days' => null,
            'certificate_csr' => $csrAsText,
            'strict_domains' => 1
        ]);
    }

    public function verifyAndSign()
    {

    }

}
