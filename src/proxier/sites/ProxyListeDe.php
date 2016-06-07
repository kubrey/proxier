<?php

namespace proxier\sites;


use proxier\BaseSiteCom;

class ProxyListeDe extends BaseSiteCom
{

    protected $config = array('baseUrl' => 'http://www.proxy-listen.de/Proxy/Proxyliste.html');
    protected $perPage = 300;

    /**
     * @return bool
     */
    public function parse() {
        $post = array(
            'filter_port' => '',
            'filter_http_gateway' => '',
            'filter_http_anon' => '',
            'filter_response_time_http' => '',
            'filter_country' => '',
            'filter_timeouts1' => '20',
            'liststyle' => 'leech',
            'proxies' => $this->perPage,
            'type' => 'http',
            'submit' => 'Show'
        );

        $this->curlInit($this->config['baseUrl']);
        $this->runCurl();
        if ($this->curlError) {
            return false;
        }
        $hidden = $this->getHiddenFields();
        if ($hidden) {
            $post = array_merge($post, $hidden);
        }
        $this->curlInit($this->config['baseUrl']);
        $this->setCurlOption(CURLOPT_POST, 1);
        $this->setCurlOption(CURLOPT_POSTFIELDS, http_build_query($post));
        $this->runCurl();
        $this->parseTable();

    }

    /**
     * @return array|bool
     */
    protected function getHiddenFields() {
        if (!$this->curlResult) {
            return false;
        }
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);
        $inputs = $dom->getElementsByTagName("input");
        if (!$inputs || !$inputs->length) {
            return false;
        }
        $fields = array();
        foreach ($inputs as $input) {
            /**
             * @var \DOMElement $input
             */
            $type = $input->getAttribute('type');
            if (!$type) {
                continue;
            }
            if ($type != 'hidden') {
                continue;
            }
            $fields[$input->getAttribute('name')] = $input->getAttribute('value');
        }
        return $fields;
    }

    /**
     * @return bool
     */
    protected function parseTable() {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);
        $links = $dom->getElementsByTagName("a");
        if (!$links || !$links->length) {
            return false;
        }
        foreach ($links as $link) {
            /**
             * @var \DOMElement $link
             */
            $class = $link->getAttribute('class');
            if (!$class) {
                continue;
            }
            if ($class != "proxyList") {
                continue;
            }
            $ip = $link->textContent;
            $this->parsedProxies[] = $ip;
        }
        unset($dom);
    }
}