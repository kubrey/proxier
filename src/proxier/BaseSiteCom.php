<?php

namespace proxier;


class BaseSiteCom
{

    protected $errors = array();
    protected $parsedProxies = array();
    protected $config = array();
    protected $lastPage = 1;

    protected $curlObject = null;
    protected $curlError = null;
    protected $curlResult = null;
    protected $curlOptions = array();
    protected $curlInfo = array();
    protected $options = array();

    public function __construct($options = array()) {
        $this->options = $options;
    }

    public function parse() {

    }

    /**
     * @param $error
     * @return $this
     */
    public function setError($error) {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getLastError() {
        if ($this->errors) {
            return end($this->errors);
        }
        return null;
    }

    /**
     * @return $this
     */
    protected function setCurlTor() {
        $this->curlOptions[CURLOPT_AUTOREFERER] = 1;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = 1;
        $this->curlOptions[CURLOPT_PROXY] = '127.0.0.1:' . ($this->config['curlTorPort'] ? (int)$this->config['curlTorPort'] : 9050);
        $this->curlOptions[CURLOPT_PROXYTYPE] = 7;
        $this->curlOptions[CURLOPT_TIMEOUT] = 120;
        $this->curlOptions[CURLOPT_VERBOSE] = 0;
        $this->curlOptions[CURLOPT_HEADER] = 0;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * @param $url
     * @return $this
     */
    protected function curlInit($url) {
        $this->resetCurl();
        $this->setCurlDefaultOptions();
        $this->curlOptions[CURLOPT_URL] = $url;
        if (strpos($url, 'https:') === 0) {
            //ssl
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, 0);
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);
        }
        if (isset($this->options['tor']) && $this->options['tor']) {
            $this->setCurlTor();
        }
        if (!$this->curlObject) {
            $this->curlObject = curl_init($url);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function runCurl() {
        try {
            curl_setopt_array($this->curlObject, $this->curlOptions);
            $this->curlResult = curl_exec($this->curlObject);
            $this->curlError = curl_error($this->curlObject);
            $this->curlInfo = curl_getinfo($this->curlObject);
            curl_close($this->curlObject);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            $this->curlError = $e->getMessage();
        }

        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    protected function setCurlOption($key, $val) {
        $this->curlOptions[$key] = $val;
        return $this;
    }

    /**
     * @return $this
     */
    protected function setCurlDefaultOptions() {
        $this->curlOptions[CURLOPT_USERAGENT] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36";
        $this->curlOptions[CURLOPT_TIMEOUT] = 60;
        $cookie = "proxy_cookie.txt";
        $this->curlOptions[CURLOPT_COOKIEJAR] = $cookie;
        $this->curlOptions[CURLOPT_COOKIE] = $cookie;
        $this->curlOptions[CURLOPT_FOLLOWLOCATION] = 1;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = 1;

        return $this;
    }

    /**
     * @return $this
     */
    protected function resetCurl() {
        $this->curlObject = null;
        $this->curlOptions = array();
        $this->curlInfo = array();
        $this->curlError = null;
        $this->curlResult = null;
        return $this;
    }

    /**
     * @return array
     */
    public function getParsedProxies() {
        return $this->parsedProxies;
    }

}