<?php

namespace ZeroSSL\CliClient\Converter;

use ZeroSSL\CliClient\Exception\CertificateConversionException;

class Converter
{
    public const FORMAT_CRT_PEM = ["crt", "cer", "pem"];
    public const FORMAT_DER = ["der"];
    public const FORMAT_PKCS12 = ["p12", "pfx", "p12PKCS#12"];
    public const SUPPORTED_FORMATS = [...Converter::FORMAT_CRT_PEM, ...Converter::FORMAT_DER, ...Converter::FORMAT_PKCS12];


    /**
     * @param string $certificate
     * @param string $format
     * @param string|null $privateKey
     * @param string|null $privateKeyPassword
     * @return string
     * @throws CertificateConversionException
     */
    public static function fromCrt(string $certificate, string $format, ?string $privateKey, ?string $privateKeyPassword): string
    {
        if (in_array($format, self::FORMAT_CRT_PEM, true)) {
            return $certificate;
        }

        if (in_array($format, self::FORMAT_DER, true)) {
            return self::pem2der($certificate);
        }

        if (in_array($format, self::FORMAT_PKCS12, true)) {
            $out = "";
            openssl_pkcs12_export($certificate, $out, $privateKey, $privateKeyPassword);
            return $out;
        }

        throw new CertificateConversionException("The format " . $format . " is not (yet?) supported.");
    }


    /**
     * Converts a PEM certificate to a DER certificate.
     *
     * @param string $pemData
     * @return string|false
     */
    public static function pem2der(string $pemData): string|false
    {
        $begin = "CERTIFICATE-----";
        $end = "-----END";
        $pemData = substr($pemData, strpos($pemData, $begin) + strlen($begin));
        $pemData = substr($pemData, 0, strpos($pemData, $end));
        return base64_decode($pemData);
    }

    /**
     * Converts a DER certificate to a PEM certificate.
     *
     * @param string $derData
     * @return string
     */
    public static function der2pem(string $derData): string
    {
        $pem = chunk_split(base64_encode($derData), 64, "\n");
        $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
        return $pem;
    }


}