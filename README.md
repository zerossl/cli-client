# ZeroSSL CLI client (beta)

Get signed and valid SSL certificates, create CSRs and the corresponding private keys directly from the command line
in usually less than a minute. Easy, secure, fast. 

## Beta notice

This software is in an early beta stage, feedback is being gathered. Although most things should work there might be unexpected errors or problems.
Please report any bugs you may encounter or even better create an MR to fix them.

## Use cases

 - Automate the creation of a huge amount of certificates with the ZeroSSL API (can be used as a library / standalone toolkit)
 - Everything except for certificate creation and domain validation will happen in your local system
 - Can be used as a free tool for CSR creation
 - Can be used as a free tool for self-signed certificate creation

## How to get an API key for creating valid SSL certificates signed by ZeroSSL

1. Go to: https://zerossl.com/
2. Create an free account
3. Within your dashboard navigate to developer and get your API key
4. Now you can create valid SSL certificates with ZeroSSL (if you exceed your free limit you need a paid account)

## Requirements

 - PHP 8.1 or higher
 - PHP OpenSSL extension (https://www.php.net/manual/en/book.openssl.php)
 - PHP CURL extension (https://www.php.net/manual/de/book.curl.php)
 - Optional: Composer (PHP dependency manager, only needed for development, used for autoloading, https://getcomposer.org/)

## Sample Usage

Self-signed certificates:

```
php ./src/generator.php --targetPath="/home/blackbam/work/test/" --domains="example.com,www.example.com" --csrData="countryName=AT&stateOrProvinceName=Vienna&localityName=Vienna&organizationName=CLI%20Operations&emailAddress=certmaster@mailinator.com"
```

Official certificates:

```
php ./src/generator.php --apiKey=KEY --targetPath="/home/blackbam/work/test/" --domains="example.com,www.example.com" --csrData="countryName=AT&stateOrProvinceName=Vienna&localityName=Vienna&organizationName=CLI%20Operations&emailAddress=certmaster@mailinator.com"
```

## Flags

The flags should provide all configuration that you may ever need. Take a little time
to fully understand what they are doing:

<table>
<thead>
<tr>
<th>Flag</th>
<th>Description</th>
<th>Type</th>
<th>Examples</th>
<th>Required</th>
</tr>
</thead>
<tbody>

<tr>
<td><code>-d,--domains</code></td>
<td>Comma seperated list of domains for the certificate. Use wildcards like *.example.com. The
first domain name will be the common name of the certificate. </td>
<td>String</td>
<td>example.com,www.example.com<br/><br/>
*.example.com<br/><br/>
*.foo.bar.com,*.abc,bar.com,*.xyz.bar.com</td>
<td>✓</td>
</tr>

<tr>
<td><code>-c,--csrData</code></td>
<td>
<strong>Important:</strong>The organization information for your CSR and your certificate.

Required parts:
    <table>
    <tr>
    <td>countryName</td>
    <td>Country code (e.g. AT,DE,...). Find your country code: <a href="https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes">https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes</a></td>
    </tr>
    <tr>
    <td>stateOrProvinceName</td>
    <td>Your state or province</td>
    </tr>
    <tr>
    <td>localityName</td>
    <td>Your city</td>
    </tr>
    <tr>
    <td>organizationName</td>
    <td>Organization to issue the certificate for.</td>
    </tr>
    <tr>
    <td>emailAddress</td>
    <td>Contact email for certificate.</td>
    </tr>
    </table>
</td>
<td>QUERY_STRING</td>
<td>countryName=AT&stateOrProvinceName=Vienna&localityName=Vienna&organizationName=CLI%20Operations&emailAddress=certmaster@mailinator.com</td>
<td>✓</td>
</tr>


<tr>
<td><code>-p,--privateKeyPassword</code></td>
<td>The password which is used to encrypt the private key.</td>
<td>String</td>
<td></td>
<td></td>
</tr>

<tr>
<td><code>-n,--noOut</code></td>
<td>If this is set, no output is printed to the screen. Only needed if you embed the application somewhere.</td>
<td>Boolean</td>
<td></td>
<td></td>
</tr>

<tr>
<td><code>-t,--targetPath</code></td>
<td>The path in your local system where all output is saved (Certificate, CSR, private key,
files for validation,...). Not mandatory, because you also could copy all output from the terminal and save it to files on your own.</td>
<td>String</td>
<td><code>/etc/ssl/</code></td>
<td></td>
</tr>

<tr>
<td><code>-s,--suffix</code></td>
<td>Output file suffix. This is useful if you generate multiple certificates and you do not overwrite the existing output. Suffix is appended to any output.</td>
<td>String</td>
<td>-2<br/><br/>-ecc<br/><br/>project</td>
<td></td>
</tr>

<tr>
<td><code>-k,--apiKey</code></td>
<td>Required if you want to sign your certificate with ZeroSSL (recommended). You need to register at 
<a href="https://app.zerossl.com/signup">https://app.zerossl.com/signup</a> to get an API key.

<strong>If no API key is defined, you will create self-signed certificates. You might want to do this on purpose,
but common webbrowsers will show a warning that the certificate is not trusted.</strong></td>
<td>String</td>
<td>663f5da7524344266195a785279e72d1</td>
<td></td>
</tr>

<tr>
<td><code>-m,--validationMethod</code></td>
<td>
The validation method (only required if certificate is signed with ZeroSSL).
<code>EMAIL</code>: For email validation<br/>
<code>CNAME</code>: CNAME validation<br/>
<code>HTTP_CSR_HASH</code>: HTTP file upload validation<br/>
<code>HTTPS_CSR_HASH</code>: HTTPS file upload validation<br/>

More information here: <a href="https://zerossl.com/documentation/api/verify-domains/">https://zerossl.com/documentation/api/verify-domains/</a>
</td>
<td>Enum</td>
<td></td>
<td></td>
</tr>

<tr>
<td><code>-d,--useEccDefaults</code></td>
<td>By default RSA encrypted certificates are generated. If this is set to true, the defaults for ECC 
certificates are used. More information: <a href="https://en.wikipedia.org/wiki/Elliptic-curve_cryptography">https://en.wikipedia.org/wiki/Elliptic-curve_cryptography</a>.</td>
<td>Boolean</td>
<td></td>
<td></td>
</tr>

<tr>
<td><code>-y,--privateKeyOptions</code></td>
<td>You can configure certain options for the private key, like the encryption algorithm. This is an advanced feature.

All options are explained in the PHP documentation. <a href="https://www.php.net/manual/en/function.openssl-csr-new.php">https://www.php.net/manual/en/function.openssl-csr-new.php</a>
</td>
<td>QUERY_STRING (URL encoded string)</td>
<td>
<code>digest_alg=sha512</code>
<code>curve_name=sect571r1</code>
</td>
<td></td>
</tr>

<tr>
<td><code>-o,--csrOnly</code></td>
<td>You can use the application for pure CSR generation, without certificate or signing. If you specify this option
the script simply generates your CSR and stops afterwards.</td>
<td>Boolean</td>
<td></td>
<td></td>
</tr>


<tr>
<td><code>-s,--csrOptions</code></td>
<td>You can configure certain options for the private key, like the encryption algorithm. This is an advanced feature.

All options are explained in the PHP documentation. <a href="https://www.php.net/manual/en/function.openssl-csr-new.php">https://www.php.net/manual/en/function.openssl-csr-new.php
</td>
<td>QUERY_STRING (URL encoded string)</td>
<td>
<code>digest_alg=sha512</code>
<code>curve_name=sect571r1</code>
</td>
<td></td>
</tr>

<tr>
<td><code>-v,--validityDays</code></td>
<td>Default: 90. Days of certificate validity.

While for self-signed certificates you can choose any amount, for ZeroSSL signed certificates only 90 and 365 days are supported currently.
For 365 days (1-Year) you need a ZeroSSL premium account.
</td>
<td>INTEGER</td>
<td>90

365
</td>
<td></td>
</tr>

<tr>
<td><code>-z,--validationEmail</code></td>
<td>Only required for E-Mail certificate validation with ZeroSSL. Comma-seperated string of validation E-Mails which must be equivalent to your
domains string. More information here: <a href="https://zerossl.com/documentation/api/verify-domains/">https://zerossl.com/documentation/api/verify-domains/</a>.</td>
<td>STRING</td>
<td>admin@example.com

admin@example.com,admin@foo.com,admin@bar.com</td>
<td></td>
</tr>

<tr>
<td><code>-i,--includeCrossSigned</code></td>
<td>Do you want to include the cross-signed certificate into your CA Bundle delivered by ZeroSSL after signing?</td>
<td>BOOLEAN</td>
<td></td>
<td></td>
</tr>

<tr>
<td><code>-q,--debug</code></td>
<td>ONLY USED FOR DEBUGGING THIS SCRIPT - INSECURE. You can set a test API URL here, insecure HTTP requests are allowed.</td>
<td>STRING</td>
<td>https://mylocal.cert.api</td>
<td></td>
</tr>

</tbody>
</table>


## Errors

The script will display native PHP errors with the stack trace in case something goes wrong. In order
to understand what is not working check the first line of the error output. Most likely
you will have a `ConfigurationException`, which means you have to adjust your input.
