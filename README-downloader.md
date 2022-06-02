# ZeroSSL Download Converter

Download ZeroSSL certificates and save them directly in the required format.

Currently supported formats:
 - .crt
 - .cer
 - .pem
 - .der
 - .pfx (requires private key and password)
 - .p12 (requires private key and password)
 - .p12PKCS#12 (requires private key and password)

## Use cases

Depending on what software you are using you might require your certificate in a different format than .crt (default).
Using the downloader you can download a certificate directly in the required form.

## Sample Usage

Basic usage:

```
php downloader.php --hash="CERTIFICATE_HASH" --apiKey="API_KEY" --formats=der --targetPath="/var/www"
```

Example usage with all options:

```
php downloader.php --hash="CERTIFICATE_HASH" --apiKey="API_KEY" --formats=crt,pem,cer,der,p12,pfx,p12PKCS#12 --targetPath="/var/www" --keyPath="/home/user/private.key" --keyPass="KEY_PASSWORD" --includeCrossSigned
```

## Flags
"
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
<td><code>-h,-hash</code></td>
<td>ZeroSSL certificate hash (the certificate to be downloaded and converted).</td>
<td>String</td>
<td>263f5da7524344266195a785279e72d6</td>
<td>yes</td>
</tr>

<tr>
<td><code>-a,--apiKey</code></td>
<td>ZeroSSL API Key. You need to register at <a href="https://app.zerossl.com/signup">https://app.zerossl.com/signup</a> to get an API key.</td>
<td>String</td>
<td>663f5da7524344266195a785279e72d1</td>
<td>yes</td>
</tr>

<tr>
<td><code>-f,--formats</code></td>
<td>A comma seperated string of formats you need for your certificate.

Note: If you want to create a PKCS12 format, which contains the private key, you have to pass private key and password from your local system.
If your certificate has been generated with ZeroSSL, you can download the private key from the UI first. Your ZeroSSL password is the password for the private key.</td>
<td>String</td>
<td><code>crt,der,p12</code></td>
<td>yes</td>
</tr>


<tr>
<td><code>-t,--targetPath</code></td>
<td>The path in your local system where the converted certificate file(s) is/are saved.</td>
<td>String</td>
<td><code>/etc/ssl/</code></td>
<td>yes</td>
</tr>


<tr>
<td><code>-k,--keyPath</code></td>
<td>The path including filename for your private key, which is only needed in certain conversion cases.</td>
<td>String</td>
<td><code>/home/user/private.key</code></td>
<td></td>
</tr>

<tr>
<td><code>-p,--keyPass</code></td>
<td>Your private key password. Only use this option in a shell where this is secure (password might get saved e.g. in log files).</td>
<td>String</td>
<td>DoNotUseInsecurePasswords</td>
<td></td>
</tr>


<tr>
<td><code>-i,--includeCrossSigned</code></td>
<td>Download and convert the cross-signed certificate if this is required.</td>
<td>BOOLEAN</td>
<td></td>
<td></td>
</tr>

</tbody>
</table>


## Usage as a library

Just have a look at `downloader.php`, implementation is quite straightforward.