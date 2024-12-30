<?php

namespace ZeroSSL\CliClient;

use ZeroSSL\CliClient\Enum\InternetAddressType;
use ZeroSSL\CliClient\Exception\ConfigurationException;
use ZeroSSL\CliClient\Exception\OpenSSLGenerationException;
use JetBrains\PhpStorm\ArrayShape;
use OpenSSLAsymmetricKey;
use OpenSSLCertificateSigningRequest;

class CSRTool
{

    public const PRIVATE_KEY_DEFAULT_RSA = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA
    ];

    public const PRIVATE_KEY_DEFAULT_ECC = [
        "private_key_type" => OPENSSL_KEYTYPE_EC,
        "curve_name" => 'prime256v1'
    ];

    public const DIGEST_ALG_DEFAULT_RSA = 'sha256';
    public const DIGEST_ALG_DEFAULT_ECC = 'sha384';

    /**
     * @param bool $useEccDefault
     * @param array $options
     * @return OpenSSLAsymmetricKey
     * @throws OpenSSLGenerationException
     */
    public static function generatePrivateKey(bool $useEccDefault, array $options = []): OpenSSLAsymmetricKey
    {
        $key = openssl_pkey_new(array_merge(($useEccDefault) ? self::PRIVATE_KEY_DEFAULT_ECC : self::PRIVATE_KEY_DEFAULT_RSA, $options));
        if (!$key) {
            throw new OpenSSLGenerationException("Unable to generate private key with the given options. Please check: https://www.php.net/manual/en/function.openssl-pkey-new.php for more information.");
        }
        return $key;
    }


    /**
     * @description for SSL server certificates the commonName is the domain name to be secured
     * for S/MIME email certificates the commonName is the owner of the email address
     * location and identification fields refer to the owner of domain or email subject to be secured
     * @param OpenSSLAsymmetricKey $privateKey
     * @param $csrData
     * @param bool $useEccDefault
     * @param array $options
     * @return OpenSSLCertificateSigningRequest
     * @throws OpenSSLGenerationException
     * @throws ConfigurationException
     */
    public static function getCSR(
        OpenSSLAsymmetricKey $privateKey,
        #[ArrayShape([
            "countryName" => "string",
            "stateOrProvinceName" => "string",
            "localityName" => "string",
            "organizationName" => "string",
            "organizationalUnitName" => "string",
            "emailAddress" => "string"
        ])] $csrData,
        array $internetAddresses,
        bool $useEccDefault = false,
        array $options = []
    ): OpenSSLCertificateSigningRequest {
        if (!array_key_exists(key: "countryName", array: $csrData) || !array_key_exists(key: "organizationName", array: $csrData) || !array_key_exists(key: "emailAddress", array: $csrData)) {
            throw new ConfigurationException(message: "Please provide at least country code, organization name and email address for your CSR as a query string parameter. This is required.\nExample: --csrData countryName={_}&stateOrProvinceName={_}&localityName={_}&organizationName={_}&organizationalUnitName={_}&emailAddress{_}");
        }
        $csrData['commonName'] = reset($internetAddresses);

        if (empty($csrData['commonName'])) {
            throw new ConfigurationException("Missing common name for CSR. Please check your domains parameter.");
        }
      

        // check if sans included: https://www.pixelite.co.nz/article/how-to-generate-a-csr-with-sans-in-php/
        // Check the operating system
        $isWindows = strtoupper(string: substr(string: PHP_OS, offset: 0, length: 3)) === 'WIN';
        $isMacOS = strtoupper(string: substr(string: PHP_OS, offset: 0, length: 6)) === 'DARWIN';


        // Define the directory path based on the operating system
        if ($isWindows) {
            $configPath = 'C:\OpenSSL\config';
        } elseif ($isMacOS) {
            // Common paths for OpenSSL config on macOS
            $configPath = '/usr/local/etc/openssl'; // Homebrew installation
            if (!is_dir(filename: $configPath)) {
                $configPath = '/System/Library/OpenSSL'; // Default macOS OpenSSL location
            }
        } else {
            $configPath = '/tmp/openssl';
        }

        // Create the directory if it doesn't exist
        if (!is_dir(filename: $configPath)) {
            mkdir(directory: $configPath, permissions: 0755, recursive: true);
        }

        $options['digest_alg'] = $useEccDefault ? self::DIGEST_ALG_DEFAULT_ECC : self::DIGEST_ALG_DEFAULT_RSA;
        // Generate the full path for the openssl.cnf file
        $configFile = $configPath . ($isWindows ? '\openssl.cnf' : '/openssl.cnf');

        // Write the configuration file
        file_put_contents(filename: $configFile, data: self::generateSANTemplate(internetAddresses: $internetAddresses));
        $options['config'] = $configFile;

        // Generate a certificate signing request
        $csr = openssl_csr_new(distinguished_names: $csrData, private_key: $privateKey, options: array_merge(["digest_alg" => ($useEccDefault) ? self::DIGEST_ALG_DEFAULT_ECC : self::DIGEST_ALG_DEFAULT_RSA], $options));

        if (!$csr) {
            throw new OpenSSLGenerationException("Unable to generate CSR with the given options. More information: https://www.php.net/manual/en/function.openssl-csr-new.php");
        }
        return $csr;
    }

    /**
     * @param array $internetAddresses : Should already be ordered by InputSanitizer
     * @return string
     * @throws ConfigurationException
     */
    private static function generateSANTemplate(array $internetAddresses): string
    {
        $ctIp = 0;
        $ctDomain = 0;
        $sans = "";

        foreach ($internetAddresses as $address) {
            if (InputSanitizer::getAddressType($address) === InternetAddressType::DOMAIN) {
                $cur = $ctDomain++;
                $prefix = "DNS";
            } else {
                $cur = $ctIp++;
                $prefix = "IP";
            }
            $sans .= $prefix . "." . $cur . " = " . $address . "\n";
        }

        return <<<SANTEMPLATE
[ req ]
distinguished_name = req_distinguished_name
req_extensions = v3_req

[ req_distinguished_name ]

[ v3_req ]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @san

[ san ]
$sans
SANTEMPLATE;
    }

}
