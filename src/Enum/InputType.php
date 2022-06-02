<?php

namespace ZeroSSL\CliClient\Enum;

enum InputType
{
    case DYNAMIC;
    case STRING;
    case INT;
    case BOOL;
    case FLOAT;
    case QUERY_STRING;
    case DOMAINS;
    case VALIDATION_EMAIL;
    case FORMAT;
    case PATH;
    case FILE;
}
