<?php

namespace proxier\sites;


use proxier\BaseSiteCom;

/**
 * Все прокси выдаются сразу, без пейджинга
 * Затем через js они преобрауются на странице, но в курле имеют отличный от браузерного вид
 * Class UsProxyOrg
 * @package proxier\sites
 */
class UsProxyOrg extends BaseSiteCom
{

    protected $config = array('baseUrl' => 'https://www.us-proxy.org/');

    public function parse() {
        $idTable = "proxylisttable";

        $this->curlInit($this->config['baseUrl']);
        $this->runCurl();
        if ($this->curlError) {
            return false;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);

        $table = $dom->getElementById($idTable);
        if (!$table) {
            return false;
        }
        $tbody = $table->getElementsByTagName('tbody');
        if (!$tbody || !$tbody->length) {
            return false;
        }
        $body = $tbody->item(0);
        $rows = $body->getElementsByTagName('tr');
        if (!$rows || !$rows->length) {
            return false;
        }
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            if (!$cells || !$cells->length) {
                continue;
            }
            $rowData = array();
            foreach ($cells as $i => $td) {
                $rowData[(string)$i] = $td->textContent;
            }

            $this->parsedProxies[] = $rowData[0] . ":" . $rowData[1];
        }

    }
}