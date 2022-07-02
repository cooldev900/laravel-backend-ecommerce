<?php
/*
* Purpose : passing Authentication config object to the configuration
*/
namespace CyberSource;
require_once __DIR__. DIRECTORY_SEPARATOR .'../vendor/autoload.php';

class ExternalConfiguration
{
    private $merchantConfig;

    //initialize variable on constructor
    function __construct()
    {
        $this->authType = "http_signature";//http_signature/jwt
        $this->merchantID = "cbq_alfardan_qar";
        $this->apiKeyID = "6923d223-3278-4e81-a4c1-7ad1d630d3d3";
        $this->secretKey = "0Dq8HrDVTB6mum5Z9/P5u7gHsNvlMkUTs2B8ZYCh6TM=";

        // MetaKey configuration [Start]
        $this->useMetaKey = false;
        $this->portfolioID = "";
        // MetaKey configuration [End]

        $this->keyAlias = "testrest";
        $this->keyPass = "testrest";
        $this->keyFilename = "testrest";
        $this->keyDirectory = "Resources/";
        $this->runEnv = "apitest.cybersource.com";

        //OAuth related config
        $this->enableClientCert = false;
        $this->clientCertDirectory = "Resources/";
        $this->clientCertFile = "";
        $this->clientCertPassword = "";
        $this->clientId = "";
        $this->clientSecret = "";

        // New Logging
        $this->enableLogging = false;
        $this->debugLogFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Log" . DIRECTORY_SEPARATOR . "debugTest.log";
        $this->errorLogFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Log" . DIRECTORY_SEPARATOR . "errorTest.log";
        $this->logDateFormat = "Y-m-d\TH:i:s";
        $this->logFormat = "[%datetime%] [%level_name%] [%channel%] : %message%\n";
        $this->logMaxFiles = 3;
        $this->logLevel = "debug";
        $this->enableMasking = false;

        $this->merchantConfigObject();
    }

    //creating merchant config object
    public function merchantConfigObject()
    {
        if (!isset($this->merchantConfig)) {
            $config = new \CyberSource\Authentication\Core\MerchantConfiguration();
            $config->setauthenticationType(strtoupper(trim($this->authType)));
            $config->setMerchantID(trim($this->merchantID));
            $config->setApiKeyID($this->apiKeyID);
            $config->setSecretKey($this->secretKey);
            $config->setKeyFileName(trim($this->keyFilename));
            $config->setKeyAlias($this->keyAlias);
            $config->setKeyPassword($this->keyPass);
            $config->setUseMetaKey($this->useMetaKey);
            $config->setPortfolioID($this->portfolioID);
            $config->setKeysDirectory(__DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $this->keyDirectory);
            $config->setRunEnvironment($this->runEnv);

            // New Logging
            $logConfiguration = new \CyberSource\Logging\LogConfiguration();
            $logConfiguration->enableLogging($this->enableLogging);
            $logConfiguration->setDebugLogFile($this->debugLogFile);
            $logConfiguration->setErrorLogFile($this->errorLogFile);
            $logConfiguration->setLogDateFormat($this->logDateFormat);
            $logConfiguration->setLogFormat($this->logFormat);
            $logConfiguration->setLogMaxFiles($this->logMaxFiles);
            $logConfiguration->setLogLevel($this->logLevel);
            $logConfiguration->enableMasking($this->enableMasking);
            $config->setLogConfiguration($logConfiguration);

            $config->validateMerchantData();
            $this->merchantConfig = $config;
        } else {
            return $this->merchantConfig;
        }
    }

    public function ConnectionHost()
    {
        $merchantConf = $this->merchantConfigObject();
        $config = new Configuration();
        $config->setHost($merchantConf->getHost());
        $config->setLogConfiguration($merchantConf->getLogConfiguration());
        return $config;
    }

    function FutureDate($format){
        if($format){
            $rdate = date("Y-m-d",strtotime("+7 days"));
            $retDate = date($format,strtotime($rdate));
        }
        else{
            $retDate = date("Y-m",strtotime("+7 days"));
        }
        echo $retDate;
        return $retDate;
    }

    function CallTestLogging($testId, $apiName, $responseMessage){
        $runtime = date('d-m-Y H:i:s');
        $file = fopen("./CSV_Files/TestReport/TestResults.csv", "a+");
        fputcsv($file, array($testId, $runtime, $apiName, $responseMessage));
        fclose($file);
    }

    function downloadReport($downloadData, $fileName){
        $filePathName = __DIR__. DIRECTORY_SEPARATOR .$fileName;
        $file = fopen($filePathName, "w");
        fwrite($file, $downloadData);
        fclose($file);
        return __DIR__.'\\'.$fileName;
    }
}
?>