<?php

namespace ZeroSSL\CliClient;

use ZeroSSL\CliClient\Dto\Options;
use ZeroSSL\CliClient\Enum\CertificateStatus;
use ZeroSSL\CliClient\Enum\CertificateValidationType;
use ZeroSSL\CliClient\Exception\ConfigurationException;
use ZeroSSL\CliClient\Exception\NotYetSupportedException;
use ZeroSSL\CliClient\Signers\SelfSigner;
use ZeroSSL\CliClient\Signers\ZeroSSL\ApiEndpointRequester;
use JetBrains\PhpStorm\ArrayShape;

class RequestProcessor
{

    public const ISSUANCE_SLEEP_SECONDS = 3;
    public const ISSUANCE_CHECK_SECONDS = 10;
    public static bool $out = true;

    /**
     * @param Options $options
     * @return array
     * @throws ConfigurationException
     * @throws Exception\OpenSSLGenerationException
     * @throws Exception\OpenSSLSigningException
     * @throws NotYetSupportedException
     */
    #[ArrayShape([
        "private_key" => "string",
        "csr" => "string",
        "certificate" => "string",
    ])]
    public static function generate(Options $options, bool $nonInteractive = false, bool $noVerification = false): array
    {
        $result = [];
        $pkey = CSRTool::generatePrivateKey($options->useEccDefaults, $options->privateKeyOptions);
        openssl_pkey_export($pkey, $pKeyOut, $options->privateKeyPassword);
        self::dumpGeneratedContent("PRIVATE KEY", $pKeyOut, !$options->noOut);

        if(!empty($options->targetPath)) {
            openssl_pkey_export_to_file($pkey,$options->targetPath . DIRECTORY_SEPARATOR . "private" . $options->suffix . ".key",$options->privateKeyPassword);
        }

        $csr = CSRTool::getCSR($pkey,$options->csrData,$options->domains,$options->useEccDefaults,$options->csrOptions);
        openssl_csr_export($csr, $csrOut);
        self::dumpGeneratedContent("CSR", $csrOut, !$options->noOut);

        if(!empty($options->targetPath)) {
            openssl_csr_export_to_file($csr,$options->targetPath . DIRECTORY_SEPARATOR . "csr" . $options->suffix . ".pem",$options->privateKeyPassword);
        }

        $certificateOut = null;

        if(!$options->csrOnly) {

            // sign with ZeroSSL
            if($options->apiKey) {
                echo "\nInitiating contact with ZeroSSL CA, relax a little bit ... 😌\n";

                if(is_null($options->validationType)) {
                    throw new ConfigurationException("You need to configure a validation method for a ZeroSSL certificate.");
                }

                // call create certificate
                $requester = new ApiEndpointRequester($options->debug);
                $endpoint = $requester->apiEndpointInfo->endpoints['create_certificate'];
                $draft = $requester->requestJson($endpoint,[
                    "API_URL" => $requester->apiUrl,
                    "ACCESS_KEY" => $options->apiKey
                ],[
                    "certificate_domains" => implode(",",$options->domains),
                    "certificate_csr" => $csrOut,
                    "certificate_validity_days" => $options->validityDays,
                    "strict_domains" => 1
                ],!empty($options->debug));

                if(!empty($draft["id"])) {

                    $hash = $draft["id"];

                    if(!$options->noOut) {
                        echo "\nFirst step successfully proceeded 🙂\n";
                        self::dumpGeneratedContent("CERTIFICATE HASH",$hash,true);
                        echo "\nFirst step successfully initiated. Now lets verify your ownership and sign it 🙂\n";
                    }

                    switch($options->validationType):
                        case CertificateValidationType::EMAIL: {
                            echo "\nFor each of this domains provide exactly one e-mail of the following in the validationEmail parameter:\n";
                            $emailInfo = "";
                            foreach($draft["validation"]["email_validation"] as $domain => $mails) {
                                $emailInfo .= "\n$domain: " . implode(",",$mails) . "\n";
                            }
                            self::dumpGeneratedContent("VALIDATION EMAILS",$emailInfo, !$options->noOut);
                            break;
                        }
                        case CertificateValidationType::CNAME_CSR_HASH: {
                            echo "\nPlease make sure the following CNAME entries exist:\n";
                            $cnameInfo = "";
                            foreach($draft["validation"]["other_methods"] as $domain => $info) {
                                if(!array_key_exists("cname_validation_p1",$info)) {
                                    throw new ConfigurationException("CNAME validation is not possible for this certificate.");
                                }
                                $cnameInfo .= "\nName: " . $info["cname_validation_p1"] . "\nValue: " . $info["cname_validation_p2"]."\n";
                            }
                            self::dumpGeneratedContent("CNAME ENTRIES",$cnameInfo, !$options->noOut);
                            break;
                        }
                        case CertificateValidationType::HTTP_CSR_HASH: {
                            $remoteFileInfo = "";
                            foreach($draft["validation"]["other_methods"] as $domain => $info) {
                                if(!array_key_exists("file_validation_url_http",$info)) {
                                    throw new ConfigurationException("HTTP file upload validation is not possible for this certificate.");
                                }
                                $tmp = explode("/",$info["file_validation_url_http"]);
                                $fileName = end($tmp);
                                $currentInfo = "URL: " . $info["file_validation_url_http"] . "\n" . implode("\n",$info["file_validation_content"])."\n\n";
                                file_put_contents($options->targetPath . DIRECTORY_SEPARATOR . $domain . "-validate-" . $options->suffix . "-" . $fileName  . ".txt", $currentInfo);
                                $remoteFileInfo .= $currentInfo;
                            }
                            self::dumpGeneratedContent("FILES",$remoteFileInfo, !$options->noOut);
                            break;
                        }
                        case CertificateValidationType::HTTPS_CSR_HASH: {
                            $remoteFileInfo = "";
                            foreach($draft["validation"]["other_methods"] as $domain => $info) {
                                if(!array_key_exists("file_validation_url_https",$info)) {
                                    throw new ConfigurationException("HTTPS file upload validation is not possible for this certificate.");
                                }
                                $currentInfo = $info["file_validation_url_https"] . "\n\n". implode("\n",$info["file_validation_content"])."\n\n";
                                $tmp = explode("/",$info["file_validation_url_https"]);
                                $fileName = end($tmp);
                                file_put_contents($options->targetPath . DIRECTORY_SEPARATOR . $domain . "-validate-" . $options->suffix . "-" . $fileName  . ".txt", $currentInfo);
                                $remoteFileInfo .= $currentInfo;
                            }
                            self::dumpGeneratedContent("FILES",$remoteFileInfo, !$options->noOut);
                            break;
                        }
                        default: {
                            throw new NotYetSupportedException("Unsupported validation method.");
                        }
                    endswitch;

                    if($nonInteractive && !$noVerification) {
                        $verificationResult = self::zeroSign($hash,$options,true);
                        if(!is_null($verificationResult)) {
                            $result = array_merge($result,$verificationResult);
                        }
                    } else {
                        echo "\nEnter VERIFY and press enter for issuing the certificate (any other input will finish script execution): ";
                        $handle = fopen ("php://stdin", 'rb');
                        $line = fgets($handle);
                        if(trim($line) === 'VERIFY'){
                            fclose($handle);
                            $verificationResult = self::zeroSign($hash,$options,true);
                            if(!is_null($verificationResult)) {
                                $result = array_merge($result,$verificationResult);
                            }
                        } else {
                            echo "\nCertificate will not be signed for now. Continue with verification in the CLI seperatly or continue in your ZeroSSL dashboard: https://app.zerossl.com/certificate/verify/".$hash;
                            fclose($handle);
                        }
                    }
                    // choose validation type
                } elseif(!$options->noOut) {
                    echo "\nThe CA does unfortunately not accept your certificate request. Check what is wrong and maybe try with a different input 🧐 Here is the answer:\n";
                    self::dumpGeneratedContent("ANSWER FROM ZEROSSL",print_r($draft,true),true);
                }
            } else {
                $certificate = SelfSigner::sign($csr,$pkey,$options->validityDays,$options->csrOptions); // could add serial argument here
                openssl_x509_export($certificate, $certificateOut);
                self::dumpGeneratedContent("CERTIFICATE", $certificateOut, !$options->noOut);

                if(!empty($options->targetPath)) {
                    openssl_x509_export_to_file($certificate,$options->targetPath . DIRECTORY_SEPARATOR . "certificate" . $options->suffix . ".pem",$options->privateKeyPassword);
                }
                echo "\nGenerator finished successfully. Cheers 🍻.\n";
            }
        }

        $result['private_key'] = $pKeyOut;
        $result['csr'] = $csrOut;
        $result['certificate'] = $certificateOut;

        return $result;
    }

    public static function zeroSign(string $hash,Options $options, bool $repeat = false): ?array
    {
        $requester = new ApiEndpointRequester($options->debug);
        // lets the certificate get signed
        $pending = $requester->requestJson($requester->apiEndpointInfo->endpoints['verify_domains'],[
            "API_URL" => $requester->apiUrl, // API url dynamisch
            "ACCESS_KEY" => $options->apiKey,
            "CERT_HASH" => $hash
        ],[
            "validation_method" => $options->validationType->value,
            "validation_email" => ($options->validationType === CertificateValidationType::EMAIL) ? implode(",",$options->validationEmail) : null
        ],!empty($options->debug));

        if(!empty($pending["status"]) && $pending["status"] === "pending_validation") {
            echo "\nThe CA is currently trying to issue your certificate ...\n";
            if($options->validationType === CertificateValidationType::EMAIL) {
                echo "The script is waiting for you to confirm the emails ...\n";
            }

            $counter = 0;
            while(true) {
                sleep(self::ISSUANCE_SLEEP_SECONDS);
                $counter++;
                echo "🗎";

                if($counter % self::ISSUANCE_CHECK_SECONDS === 0) {
                    $info = $requester->requestJson($requester->apiEndpointInfo->endpoints['get_certificate'],[
                        "API_URL" => $requester->apiUrl, // API url dynamisch
                        "ACCESS_KEY" => $options->apiKey,
                        "CERT_HASH" => $hash
                    ],[],!empty($options->debug));

                    if(empty($info["id"])) {
                        echo "\nSomething went wrong, the certificate status can currently not be queried. Script will retry anyway ...\n";
                    } else {
                        $currentStatus = CertificateStatus::tryFrom($info["status"]);
                        switch($currentStatus):
                            case CertificateStatus::ISSUED: {
                                $certificateActual = $requester->requestJson($requester->apiEndpointInfo->endpoints['download_certificate_json'],[
                                    "API_URL" => $requester->apiUrl, // API url dynamisch
                                    "ACCESS_KEY" => $options->apiKey,
                                    "CERT_HASH" => $hash,
                                    "INCLUDE_CROSS_SIGNED" => $options->includeCrossSigned
                                ],[],!empty($options->debug));
                                if(!empty($certificateActual["certificate.crt"])) {
                                    self::dumpGeneratedContent("CERTIFICATE", $certificateActual["certificate.crt"], !$options->noOut);
                                    self::dumpGeneratedContent("CA BUNDLE", $certificateActual["ca_bundle.crt"], !$options->noOut);
                                    file_put_contents($options->targetPath . DIRECTORY_SEPARATOR . "certificate" . $options->suffix . ".crt",$certificateActual["certificate.crt"]);
                                    file_put_contents($options->targetPath . DIRECTORY_SEPARATOR . "ca_bundle" . $options->suffix . ".crt",$certificateActual["ca_bundle.crt"]);
                                    echo "\nGenerator finished successfully. Cheers 🍻.\n";
                                    return $certificateActual;
                                }
                                echo "\nUnfortunately there was a problem downloading your issued certificate. The script will retry.\n";
                                break;
                            }
                            case CertificateStatus::PENDING_VALIDATION: {
                                echo "\nBe patient, issuance may take multiple minutes. You have been waiting for " . gmdate("H:i:s", $counter) . " now.\n";
                                break;
                            }
                            case CertificateStatus::CANCELLED:
                            case CertificateStatus::DELETED:
                            case CertificateStatus::REVOKED: {
                                echo "\nYour certificate can not be issued and is in status " . $currentStatus->value . " now. Maybe your domain or TLD are not supported.\n";
                                return null;
                            }
                            default: {
                                echo "\nUnsupported certificate status: ".$info["status"]." Maybe your certificate has been modified while the script was running?\n";
                                return null;
                            }
                        endswitch;
                    }
                }
            }
        } else {
            echo "\nValidation failed or can not be processed at the moment 🧐 Here is the answer:\n";
            self::dumpGeneratedContent("ANSWER FROM ZEROSSL",print_r($pending,true),true);

            if($options->validationType === CertificateValidationType::EMAIL) {
                echo "\nERROR: Your validation emails must match the correct validation email string. Please re-verify with different string.\n";
            } else if($repeat) {
                $count = 0;
                while($count < self::ISSUANCE_CHECK_SECONDS) {
                    echo "🗎";
                    sleep(1);
                    $count++;
                }
                self::zeroSign($hash,$options,$repeat);
            }
        }
        return null;
    }

    /**
     * @param Options $options
     * @return void
     * @throws ConfigurationException
     * @throws Exception\OpenSSLGenerationException|Exception\OpenSSLSigningException|NotYetSupportedException
     */
    public static function process(Options $options): void
    {
        if($options->noOut && !$options->apiKey  && !is_dir(realpath($options->targetPath))) {
            throw new ConfigurationException("If the no output option is specified, you have to pass a valid target path for self-signed certificates.");
        }

        if(!empty($options->targetPath) && !is_dir(realpath($options->targetPath))) {
            throw new ConfigurationException("The target path \"" . $options->targetPath . "\" appears not to be a valid path.");
        }

        if(!$options->noOut) {
            echo "Generator started.\n";
        }
        self::generate($options);

        if(!$options->noOut) {
            echo "\nAll done. Script exiting 🐆\n";
        }
    }

    /**
     * @param string $label
     * @param string $content
     * @param bool $printInfo
     * @return void
     */
    private static function dumpGeneratedContent(string $label, string $content, bool $printInfo): void
    {
        if($printInfo) {
            echo "\n\n### " . $label . " ###\n\n" . $content . "\n\n### END: " . $label . " ###\n\n";
        }
    }
}
