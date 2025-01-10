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
    $formats = InputSanitizer::getCliArgument("f", "formats", $options, "", InputType::FORMAT);
    $targetPath = InputSanitizer::getCliArgument("t", "targetPath", $options, "", InputType::PATH, false);

    $key = null;
    $keyPass = null;

    if (!empty(array_intersect(Converter::FORMAT_PKCS12, $formats))) {
        $key = InputSanitizer::getCliArgument("k", "keyPath", $options, "", InputType::FILE, false);
        $keyPass = InputSanitizer::getCliArgument("p", "keyPass", $options, "", InputType::STRING, false);
    }

    // Read the input certificate
   // Validate and read the input certificate

   $inputFile = InputSanitizer::getCliArgument("i", "input", $options, "", InputType::FILE);
   $inputFile = InputSanitizer::getCliArgument("i", "input", $options, "", InputType::STRING);

   echo "Input file path: " . $inputFile . PHP_EOL;
 
   if (!empty($inputFile) && file_exists($inputFile)) {
    // Read certificate content from the file
    $certificateContent = file_get_contents($inputFile);
} else {
    // If the input is not a file, treat it as certificate content directly
    $certificateContent = $inputFile;
}

    // Ensure the input file exists
    if (!file_exists($inputFile)) {
        throw new ConfigurationException("Input file not found: " . $inputFile);
    }
    
   // Read the file content
   $certificateContent = file_get_contents($inputFile);
   if ($certificateContent === false) {
       throw new ConfigurationException("Failed to read input file: " . $inputFile);
   }
  
   echo "Certificate Content:\n" . $certificateContent . PHP_EOL;
   
   $inputCert = file_get_contents($inputFile);
   if ($inputCert === false) {
       throw new ConfigurationException("Unable to read input certificate file: " . $inputFile);
   }
    // Convert and save in specified formats
    foreach ($formats as $format) {
        $outputFile = $targetPath . DIRECTORY_SEPARATOR . "converted_certificate" . "." . $format;
        $convertedCert = Converter::fromCrt($inputCert, $format, $key, $keyPass);
        
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






