<?php

namespace ZeroSSL\CliClient\Dto;

use ZeroSSL\CliClient\Enum\CertificateValidationType;

class Options
{
    public array $domains;
    public string $privateKeyPassword;
    public bool $noOut;
    public string $targetPath;
    public string $suffix;
    public ?string $apiKey;
    public ?CertificateValidationType $validationType;
    public array $validationEmail;
    public bool $csrOnly;
    public bool $useEccDefaults;
    public array $privateKeyOptions;
    public array $csrData;
    public array $csrOptions;
    public int $validityDays;
    public bool $includeCrossSigned;
    public string $debug = "";
}
