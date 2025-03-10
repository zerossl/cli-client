# CSR Tool

A command-line tool for generating Certificate Signing Requests (CSRs) and private keys for SSL certificates. Supports RSA and ECC key generation.

# Requirements
PHP version 7.4 or higher.

# Usage

The script csrtool.php can be executed via the command line to generate a CSR and private key. The main command structure is:

php csrtool.php --domains="<comma_separated_domains>" \
                --csrData="<csr_details_in_query_string_format>" \
                --output="<output_directory>" \
                --useEcc


This will generate:

mycsr.csr → The CSR file
mycsr.key → The private key
Generating a CSR with an ECC Private Key
To use ECC (Elliptic Curve Cryptography) instead of RSA, add the --useEcc flag:


# Arguments
Argument	Description
--domains	  Comma-separated list of domains (e.g., example.com,www.example.com)
--csrData	  CSR details in query string format (e.g., countryName=US&stateOrProvinceName=CA...)
--useEcc	  (Optional) Use ECC instead of RSA for the private key
--output      The filename prefix for the CSR and private key (default: csr)


# Example of the command

php csrtool.php --domains="example.com,www.example.com" \
                --csrData="countryName=US&stateOrProvinceName=California&localityName=Los Angeles&organizationName=MyCompany&emailAddress=admin@example.com" \
                --useEcc \
                --output="/path/to/output"


# Output
Private key and CSR have been generated and saved to mycsr.key and mycsr.csr
You can then use mycsr.csr to request an SSL certificate.

# Error Handling

# Invalid Input
Ensure that the --domains parameter contains valid domain names.
Example of valid domains: example.com,www.example.com
Invalid: http://example.com (Do not include http:// or https://).

# Invalid CSR Data
Ensure that the --csrData parameter includes all required fields:
-countryName
-stateOrProvinceName
-localityName
-organizationName
-emailAddress

# Invalid Output Path
Ensure that the --output directory exists and is writable.
If the path is incorrect, create the directory manually before running the command.