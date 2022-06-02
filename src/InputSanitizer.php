<?php

namespace ZeroSSL\CliClient;


use ZeroSSL\CliClient\Converter\Converter;
use ZeroSSL\CliClient\Enum\InputType;
use ZeroSSL\CliClient\Enum\InternetAddressType;
use ZeroSSL\CliClient\Exception\ConfigurationException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InputSanitizer
{

    public const DOMAIN_ALLOWED_REGEX = '/^(?:[a-z\d](?:[a-z\d-]{0,61}[a-z\d])?\.)+[a-z\d][a-z\d-]{0,61}[a-z]$/i';
    public const DOMAIN_MAXLENGTH = 253;

    /**
     * @param string $short
     * @param string $long
     * @param array $input
     * @param mixed $default
     * @param InputType $inputType
     * @param bool $allowEmpty
     * @return mixed
     * @throws ConfigurationException
     */
    public static function getCliArgument(string $short,string $long,array $input, mixed $default,InputType $inputType = InputType::DYNAMIC, bool $allowEmpty = true): mixed
    {
        $value = $input[$short] ?? $input[$long] ?? $default;
        return match ($inputType) {
            InputType::DYNAMIC => $value,
            InputType::BOOL => (bool) $value,
            InputType::INT => (int) $value,
            InputType::FLOAT => (float) $value,
            InputType::STRING => (string) $value,
            InputType::QUERY_STRING => self::sanitizeQueryString($value, $long, $allowEmpty),
            InputType::DOMAINS => self::sanitizeDomains($value),
            InputType::VALIDATION_EMAIL => self::initializeCertificateValidationEmails($value),
            InputType::FORMAT => self::initializeFormats($value,$allowEmpty),
            InputType::PATH => self::initializePath($value,$allowEmpty),
            InputType::FILE => self::initializeFile($value,$allowEmpty)
        };
    }

    /**
     * @param $input
     * @param $errorLabel
     * @param bool $allowEmpty
     * @return array
     * @throws ConfigurationException
     */
    private static function sanitizeQueryString($input,$errorLabel, bool $allowEmpty = true): array
    {
        try {
            parse_str($input,$parsed);
        } catch(Throwable) {
            if(!$allowEmpty) {
                throw new ConfigurationException("Unable to parse required input query string: " . $errorLabel);
            }
            return [];
        }
        return $parsed;
    }

    /**
     * @param string $domains
     * @return array
     * @throws ConfigurationException
     */
    private static function sanitizeDomains(string $domains): array
    {
        $domains = array_unique(array_map([__CLASS__,'sanitizeDomainName'],explode(",",$domains)));
        if(empty($domains)) {
            throw new ConfigurationException("After sanitation no valid domains or IPs were left. Comma-seperated string required. Example: --domains=example.com,www.example.com");
        }
        foreach($domains as $domain) {
            try {
                self::getAddressType($domain);
            } catch(ConfigurationException) {
                throw new ConfigurationException("Invalid IP address or domain for certificate: ". $domain);
            }
        }
        usort($domains,static function($a,$b) {
            return (self::getAddressType($a) === InternetAddressType::DOMAIN ? -1 : 1) <=> (self::getAddressType($b) === InternetAddressType::DOMAIN ? -1 : 1);
        });
        return $domains;
    }

    /**
     * @param string $domain
     * @return string
     */
    private static function sanitizeDomainName(string $domain): string
    {
        return strtolower(rtrim(trim($domain), "."));
    }

    /**
     * Check the type of an internet address.
     *
     * @param string $address
     * @param bool $allowWildcardDomains
     * @return InternetAddressType
     * @throws ConfigurationException
     */
    public static function getAddressType(string $address,bool $allowWildcardDomains = true): InternetAddressType
    {

        if(filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return InternetAddressType::IPv4;
        }

        if(filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return InternetAddressType::IPv6;
        }

        if($allowWildcardDomains) {
            $address = self::removeWildcardPrefix($address);
        }

        if(preg_match(self::DOMAIN_ALLOWED_REGEX, $address) && strlen($address) <= self::DOMAIN_MAXLENGTH) {
            return InternetAddressType::DOMAIN;
        }
        throw new ConfigurationException("A given domain or IP address is invalid or not allowed.");
    }

    /**
     * @param string $validationEmailParameter
     * @return array
     * @throws ConfigurationException
     */
    public static function initializeCertificateValidationEmails(string $validationEmailParameter): array
    {
        $prepared = array_filter(array_map('trim', explode(',', trim($validationEmailParameter))));
        foreach($prepared as $email) {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ConfigurationException("Invalid validation E-Mail address detected: " . $email);
            }
        }
        return $prepared;
    }

    /**
     * In case a wildcard domain is given, it is returned without the wildcard prefix. Otherwise the domain is returned without change.
     *
     * @param string $domain
     * @return string
     */
    #[Pure] public static function removeWildcardPrefix(string $domain): string
    {
        return (self::isWildcard($domain)) ? substr($domain, 2) : $domain;
    }

    /**
     * Check if a domain is a wildcard domain (in the context of certificates).
     *
     * @param string $domain
     * @return bool
     */
    public static function isWildcard(string $domain): bool
    {
        return str_starts_with($domain, '*.');
    }

    /**
     * Initialize the file endings / formats
     *
     * @param string $formats
     * @return array
     * @throws ConfigurationException
     */
    public static function initializeFormats(string $formats, bool $allowEmpty): array
    {
        $prepared = array_filter(array_map('trim', explode(',', trim($formats))));
        foreach($prepared as $format) {
            if(!in_array($format, Converter::SUPPORTED_FORMATS, true)) {
                throw new ConfigurationException("Format not supported currently: " . $format);
            }
        }
        if(!$allowEmpty && empty($prepared)) {
            throw new ConfigurationException("You have to pass a valid formats. Use a comma seperated string with at least one of: " . implode(",",Converter::SUPPORTED_FORMATS));
        }
        return $prepared;
    }

    /**
     * @param string $path
     * @param bool $allowEmpty
     * @return string
     * @throws ConfigurationException
     */
    public static function initializePath(string $path, bool $allowEmpty): string
    {
        if(empty($path) && !$allowEmpty) {
            throw new ConfigurationException("You have to pass a valid and already existing output path. Your input was empty.");
        }
        $path = realpath($path);
        if(!is_dir(realpath($path))) {
            throw new ConfigurationException("You have to pass a valid and already existing output path. You passed: " . $path);
        }
        return $path;
    }

    /**
     * @param string $filepath
     * @param bool $allowEmpty
     * @return string
     * @throws ConfigurationException
     */
    public static function initializeFile(string $filepath, bool $allowEmpty): string
    {
        if(empty($filepath)) {
            if($allowEmpty) {
                return "";
            }
            throw new ConfigurationException("You have to pass a valid and already existing output path. Your input was empty.");
        }
        $filepath = realpath($filepath);
        if(file_exists($filepath)) {
            $fileStr = file_get_contents($filepath);
            if($fileStr) {
                return $fileStr;
            }
        }
        throw new ConfigurationException("Unable to read input file. Please check your file path.");
    }
}
