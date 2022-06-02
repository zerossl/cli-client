<?php

require getcwd() . '/vendor/autoload.php';

// just in case remote calls take a bit longer
use ZeroSSL\CliClient\Converter\Converter;
use ZeroSSL\CliClient\Enum\InputType;
use ZeroSSL\CliClient\Exception\RemoteRequestException;
use ZeroSSL\CliClient\InputSanitizer;
use ZeroSSL\CliClient\RequestProcessor;
use ZeroSSL\CliClient\ZeroSSLDownloader;

ini_set('max_execution_time', 1200);
set_time_limit(1200);

// set CLI options
$short_options = "h:a:f:t:k::p::i";
$long_options = [
    "hash:",
    "apiKey:",
    "formats:",
    "targetPath:",
    "keyPath::",
    "keyPass::",
    "includeCrossSigned"
];
$options = getopt($short_options, $long_options);

$hash = InputSanitizer::getCliArgument("h", "hash", $options, "", InputType::STRING);
$apiKey = InputSanitizer::getCliArgument("a", "apiKey", $options, "", InputType::STRING);
$formats = InputSanitizer::getCliArgument("f", "formats", $options, "", InputType::FORMAT);
$targetPath = InputSanitizer::getCliArgument("t", "targetPath", $options, "", InputType::PATH, false);
$includeCrossSigned = InputSanitizer::getCliArgument("i", "includeCrossSigned", $options, false, InputType::BOOL);

$key = null;
$keyPass = null;

if (!empty(array_intersect(Converter::FORMAT_PKCS12, $formats))) {
    $key = InputSanitizer::getCliArgument("k", "keyPath", $options, "", InputType::FILE, false);
    $keyPass = InputSanitizer::getCliArgument("t", "keyPass", $options, "", InputType::STRING, false);
}

try {
    $certInfo = ZeroSSLDownloader::download($apiKey, $hash, $includeCrossSigned);
} catch (RemoteRequestException|JsonException $e) {
    echo "Unable to do download certificate. Check hash and api key, also a temporary downtime / problem might be the cause 💀️";
    die;
}

if (!array_key_exists("certificate.crt", $certInfo)) {
    RequestProcessor::dumpGeneratedContent("DOWNLOAD ERROR", print_r($certInfo, true), true);
    echo "Unexpected answer from ZeroSSL. Most probably bad input. Exiting unsuccessfully 💀️";
    die;
}

foreach ($formats as $format) {
    file_put_contents(
        $targetPath . DIRECTORY_SEPARATOR . "certificate" . "." . $format,
        Converter::fromCrt($certInfo["certificate.crt"], $format, $key, $keyPass)
    );

    if(!in_array($format,Converter::FORMAT_PKCS12,true)) {
        if ($includeCrossSigned) {
            $certs = explode("----END CERTIFICATE-----\n-----BEGIN CERTIFICATE-----", $certInfo["ca_bundle.crt"]);
            $certs[0] .= "----END CERTIFICATE-----";
            $certs[1] = "-----BEGIN CERTIFICATE-----" . $certs[1];
            file_put_contents($targetPath . DIRECTORY_SEPARATOR . "intermediate" . "." . $format, Converter::fromCrt($certs[0], $format, $key, $keyPass));
            file_put_contents($targetPath . DIRECTORY_SEPARATOR . "cross-sign" . "." . $format, Converter::fromCrt($certs[1], $format, $key, $keyPass));
        } else {
            file_put_contents(
                $targetPath . DIRECTORY_SEPARATOR . "ca_bundle" . "." . $format,
                Converter::fromCrt($certInfo["ca_bundle.crt"], $format, $key, $keyPass)
            );
        }
    }
}
echo "Download successfully finished. Cheers 🍻.\n";