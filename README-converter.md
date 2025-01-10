# Certificate converter

# Requirements
PHP version 7.4 or higher.
OpenSSL installed on your system for handling certificate conversions.

# Usage
The script converter.php can be executed via the command line to convert certificates into various formats. The main command structure is as follows:

php converter.php --input="<input_certificate_path>" --formats="<comma_separated_formats>" --targetPath="<output_directory>" --keyPath="<private_key_path>"

# Arguments

--input (-i):
The path to the input certificate file (e.g., .crt, .cer, .pem).

--formats (-f):
A comma-separated list of formats you want the certificate to be converted into. Supported formats include:

pem (PEM-encoded certificate)
der (DER-encoded certificate)
p12 (PKCS#12 format for certificate and private key)

--targetPath (-t):
The directory where the converted certificates will be saved. If omitted, the script will save the files in the current working directory.

--keyPath (-k):
Path to the private key file (required when converting to p12 format).

# Supported formats

The following formats are supported for certificate conversion:

pem: PEM-encoded certificate (Base64 encoded with delimiters).
Example file extensions: .pem, .crt, .cer.

der: DER-encoded certificate (binary format).
Example file extensions: .der.

p12 (PKCS#12): A format that bundles the certificate and private key into one file, typically with a .p12 or .pfx extension.
This format is commonly used for importing certificates into systems that require a private key along with the certificate.

# How to handle Errors

Invalid input file path: Ensure that the path to the input certificate file is correct.

Unsupported format: Ensure that the formats specified in --formats are valid and supported (e.g., pem, der, p12).

Missing private key for p12 conversion: When converting to p12 format, you must provide the private key using the --keyPath argument.


