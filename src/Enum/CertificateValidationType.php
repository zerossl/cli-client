<?php

namespace ZeroSSL\CliClient\Enum;

enum CertificateValidationType: string
{
    case EMAIL = 'EMAIL';
    case CNAME_CSR_HASH = 'CNAME_CSR_HASH';
    case HTTP_CSR_HASH = 'HTTP_CSR_HASH';
    case HTTPS_CSR_HASH = 'HTTPS_CSR_HASH';

    public const FILE_VALIDATION_TYPE = [self::HTTP_CSR_HASH, self::HTTPS_CSR_HASH];
}
