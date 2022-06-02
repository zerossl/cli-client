<?php

namespace ZeroSSL\CliClient\Exception;

use Throwable;

class CertificateConversionException extends \Exception
{

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Certificate can not be converted: " . $message, $code, $previous);
    }

}