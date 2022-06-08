<?php

require getcwd() . '/vendor/autoload.php';

// just in case remote calls take a bit longer
use ZeroSSL\CliClient\Dto\Options;
use ZeroSSL\CliClient\Enum\CertificateValidationType;
use ZeroSSL\CliClient\Enum\InputType;
use ZeroSSL\CliClient\InputSanitizer;
use ZeroSSL\CliClient\RequestProcessor;

ini_set('max_execution_time', 1200);
set_time_limit(1200);

// set CLI options
$short_options = "d:p::nt::a::s::k::m::ey::orc::s::v::ziq::";
$long_options = [
    "domains:",
    "privateKeyPassword::",
    "noOut",
    "targetPath::",
    "targetSubfolder::",
    "suffix::",
    "apiKey::",
    "validationMethod::",
    "useEccDefaults",
    "privateKeyOptions::",
    "csrOnly",
    "createOnly",
    "csrData::",
    "csrOptions::",
    "validityDays::",
    "validationEmail::",
    "includeCrossSigned",
    "debug::"
];
$options = getopt($short_options, $long_options);

$preparedOptions = new Options();
$preparedOptions->domains = InputSanitizer::getCliArgument("d", "domains", $options, [], InputType::DOMAINS);
$preparedOptions->privateKeyPassword = InputSanitizer::getCliArgument("p", "privateKeyPassword", $options, "",InputType::STRING);
$preparedOptions->noOut = InputSanitizer::getCliArgument("n", "noOut", $options, false,InputType::BOOL);
$preparedOptions->targetPath = InputSanitizer::getCliArgument("t", "targetPath", $options, "",InputType::STRING);
$preparedOptions->targetSubfolder = InputSanitizer::getCliArgument("a", "targetSubfolder", $options, "",InputType::STRING);
$preparedOptions->suffix = InputSanitizer::getCliArgument("s", "suffix", $options, "",InputType::STRING);
$preparedOptions->apiKey = InputSanitizer::getCliArgument("k", "apiKey", $options, "",InputType::STRING);
$preparedOptions->validationType = CertificateValidationType::tryFrom(InputSanitizer::getCliArgument("m", "validationMethod", $options, null,InputType::STRING));
$preparedOptions->useEccDefaults = InputSanitizer::getCliArgument("d", "useEccDefaults", $options, false,InputType::BOOL);
$preparedOptions->privateKeyOptions = InputSanitizer::getCliArgument("y", "privateKeyOptions", $options, [],InputType::QUERY_STRING, true);
$preparedOptions->csrOnly = InputSanitizer::getCliArgument("o", "csrOnly", $options, false,InputType::BOOL);
$preparedOptions->createOnly = InputSanitizer::getCliArgument("r", "createOnly", $options, false,InputType::BOOL);
$preparedOptions->csrData = InputSanitizer::getCliArgument("c", "csrData", $options, [],InputType::QUERY_STRING, false);
$preparedOptions->csrOptions = InputSanitizer::getCliArgument("s", "csrOptions", $options, [],InputType::QUERY_STRING, true);
$preparedOptions->validityDays = InputSanitizer::getCliArgument("v", "validityDays", $options, 90,InputType::INT);
$preparedOptions->validationEmail = InputSanitizer::getCliArgument("z", "validationEmail", $options, "",InputType::VALIDATION_EMAIL);
$preparedOptions->includeCrossSigned = InputSanitizer::getCliArgument("i", "includeCrossSigned", $options, false,InputType::BOOL);
$preparedOptions->debug = InputSanitizer::getCliArgument("q", "debug", $options, false,InputType::STRING);

RequestProcessor::process($preparedOptions);
