<?php

namespace ZeroSSL\CliClient\Signers;

use ZeroSSL\CliClient\Exception\OpenSSLSigningException;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use OpenSSLCertificateSigningRequest;

class SelfSigner
{

    /**
     * @param OpenSSLCertificateSigningRequest $csr
     * @param OpenSSLAsymmetricKey $privateKey
     * @param int $days
     * @param array|null $options
     * @param int $serial
     * @return void
     * @throws OpenSSLSigningException
     */
    public static function sign(OpenSSLCertificateSigningRequest $csr, OpenSSLAsymmetricKey $privateKey, int $days, ?array $options, int $serial = 0): OpenSSLCertificate
    {
        $result = openssl_csr_sign($csr, null, $privateKey, $days, $options, $serial);
        if(!$result) {
            throw new OpenSSLSigningException("Something went wrong with self-singing your certificate.");
        }
        return $result;
    }

}
