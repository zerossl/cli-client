<?php

namespace ZeroSSL\CliClient\Enum;

enum CertificateStatus: string
{
    case DRAFT = 'draft';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case PENDING_VALIDATION = 'pending_validation';
    case REVOKED = 'revoked';
    case ISSUED = 'issued';
    case DELETED = 'deleted';
}
