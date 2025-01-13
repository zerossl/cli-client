<?php

require getcwd() . '/vendor/autoload.php';

use ZeroSSL\CliClient\Converter\Converter;
use ZeroSSL\CliClient\Enum\InputType;
use ZeroSSL\CliClient\InputSanitizer;
use ZeroSSL\CliClient\Exception\ConfigurationException;

ini_set('max_execution_time', 1200);
set_time_limit(1200);

// Set CLI options
$short_options = "i:f:t:k::p::";
$long_options = [
    "input:",
    "formats:",
    "targetPath:",
    "keyPath::",
    "keyPass::"
];
$options = getopt($short_options, $long_options);

try {
    $inputFile = InputSanitizer::getCliArgument("i", "input", $options, "", InputType::FILE);
    $inputFile = InputSanitizer::getCliArgument("i", "input", $options, "", InputType::STRING);
    $formats = InputSanitizer::getCliArgument("f", "formats", $options, "", InputType::FORMAT);
    $targetPath = InputSanitizer::getCliArgument("t", "targetPath", $options, "", InputType::PATH, false);

    $key = null;
    $keyPass = null;

    if (!empty(array_intersect(Converter::FORMAT_PKCS12, $formats))) {
        $key = InputSanitizer::getCliArgument("k", "keyPath", $options, "", InputType::FILE, false);
        $keyPass = InputSanitizer::getCliArgument("p", "keyPass", $options, "", InputType::STRING, false);
    }
    
     // Read the input certificate

   echo "Input file path: " . $inputFile . PHP_EOL;

 
   if (file_exists($inputFile)) {
    $certificateContent = file_get_contents($inputFile);
    if ($certificateContent === false) {
        throw new ConfigurationException("Failed to read input file: " . $inputFile);
    }
    } else {
    throw new ConfigurationException("Input file not found: " . $inputFile);
}
  
   echo "Certificate Content:\n" . $certificateContent . PHP_EOL;
   

    // Convert and save in specified formats
    foreach ($formats as $format) {
        $outputFile = $targetPath . DIRECTORY_SEPARATOR . "converted_certificate" . "." . $format;
        $convertedCert = Converter::fromCrt($certificateContent, $format, $key, $keyPass);
        
        if (file_put_contents($outputFile, $convertedCert) === false) {
            throw new ConfigurationException("Failed to write converted certificate to $outputFile");
        }
        echo "Successfully converted and saved certificate to $outputFile\n";
    }

    echo "Conversion process completed. Cheers 🍻.\n";
} catch (ConfigurationException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "An unexpected error occurred: " . $e->getMessage() . "\n";
    exit(1);
}






