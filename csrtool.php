<?php

require_once 'src/CSRTool.php';
require_once 'src/InputSanitizer.php';

use ZeroSSL\CliClient\CSRTool;
use ZeroSSL\CliClient\InputSanitizer;

// Parse command line arguments
$options = getopt("", ["domains:", "csrData:", "useEcc::", "output:"]);

// Validate and sanitize input
try {
    $domains = InputSanitizer::processDomainsInput($options['domains'] ?? '');
    $csrData = InputSanitizer::sanitizeCSRData($options['csrData'] ?? '');
    $useEcc = isset($options['useEcc']);
    $output = $options['output'] ?? 'csr_output';

    // Generate private key
    $privateKey = CSRTool::generatePrivateKey($useEcc);

    // Generate CSR
    $csr = CSRTool::getCSR($privateKey, $csrData, $domains, $useEcc);

    // Export private key to file
    openssl_pkey_export_to_file($privateKey, $output . '.key');

    // Export CSR to file
    openssl_csr_export_to_file($csr, $output . '.csr');

    echo "Private key and CSR have been generated and saved to {$output}.key and {$output}.csr\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
