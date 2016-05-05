<?php

namespace proxier;

use proxier\BaseSiteCom;
use proxier\sites\ProxitoryCom;
use proxier\sites\UsProxyOrg;
use proxier\sites\PublicProxyServersCom;


class ProxySearcher
{
    protected $options = array();
    protected $proxies = array();
    protected $errors = array();

    public function __construct($options = array()) {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function run() {
        $sites = array('ProxitoryCom' => true, 'UsProxyOrg' => true, 'PublicProxyServersCom' => true);
        foreach ($this->options as $key => $optVal) {
            if (in_array($key, $sites) && !$optVal) {
                $sites[$key] = false;
            }
        }

        foreach ($sites as $site => $available) {
            if (!$available) {
                continue;
            }

            $ns = __NAMESPACE__ . "\\sites\\";

            $siteClass = $ns . $site;
            /**
             * @var BaseSiteCom $class
             */
            $class = new $siteClass();
            $class->parse();
            foreach ($class->getErrors() as $err) {
                $this->setError($err);
            }
            $this->proxies[$site] = $class->getParsedProxies();
        }

        return $this->proxies;
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
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
}