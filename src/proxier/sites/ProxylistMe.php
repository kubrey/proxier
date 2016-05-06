<?php

namespace proxier\sites;


use proxier\BaseSiteCom;

/**
 * Пейджинг
 * Class ProxylistMe
 * @package proxier\sites
 */
class ProxylistMe extends BaseSiteCom
{

    protected $config = array('baseUrl' => 'http://proxylist.me/proxys/index/');
    protected $perPage = 20;

    /**
     *  table class="table table-bordered table-hover" > tbody > tr > td первая и вторая
     *
     */
    public function parse() {
        $this->parsePaging();

        $offsets = range(0, $this->lastPage, $this->perPage);
        foreach ($offsets as $offset) {
            $this->curlInit($this->config['baseUrl'] . $offset);
            $this->runCurl();
            if ($this->curlError) {
                continue;
            }
            $this->parseTable();
        }
    }

    protected function parseTable() {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);
        $tables = $dom->getElementsByTagName("table");
        if (!$tables || !$tables->length) {
            return false;
        }
        foreach ($tables as $table) {
            /**
             * @var \DOMElement $table
             */
            $class = $table->getAttribute('class');
            if (!$class) {
                continue;
            }
            if ($class != "table table-bordered table-hover") {
                continue;
            }
            $tbody = $table->getElementsByTagName('tbody');
            if (!$tbody || !$tbody->length) {
                return false;
            }
            /**
             * @var \DOMElement $body
             */
            $body = $tbody->item(0);
            $rows = $body->getElementsByTagName('tr');
            if (!$rows || !$rows->length) {
                return false;
            }
            $cellList = array();
            foreach ($rows as $row) {
                /**
                 * @var \DOMElement $row
                 */
                $cells = $row->getElementsByTagName('td');
                if (!$cells || !$cells->length) {
                    continue;
                }
                $rowList = array();
                foreach ($cells as $ind => $td) {
                    /**
                     * @var \DOMElement $td
                     */
                    $rowList[] = $td->textContent;
                }
                $cellList[] = $rowList;
            }

            foreach ($cellList as $cl) {
                if (!is_numeric($cl[1])) {
                    continue;
                }
                $this->parsedProxies[] = $cl[0] . ":" . $cl[1];
            }
        }
        unset($dom);
    }

    /**
     * ul class="pagination" - 2 equal paging blocks
     * @return $this|bool
     */
    protected function parsePaging() {
        $this->lastPage = 0;
        $this->curlInit($this->config['baseUrl']);
        $this->runCurl();
        if ($this->curlError) {
            return false;
        }
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);
        $lists = $dom->getElementsByTagName("ul");
        if (!$lists || !$lists->length) {
            return false;
        }
        foreach ($lists as $list) {
            /**
             * @var \DOMElement $list
             *
             */
            $class = $list->getAttribute("class");
            if ($class == "pagination") {
                $paging = $list->getElementsByTagName('li');
                if (!$paging || !$paging->length) {
                    break;
                }
                $lastPage = $paging->item($paging->length - 1);
                $lastPageLink = $lastPage->getElementsByTagName('a');
                if (!$lastPageLink || !$lastPageLink->length) {
                    break;
                }
                $lastUrl = $lastPageLink->item(0)->getAttribute('href');
                if ($lastUrl) {
                    $exploded = explode(DIRECTORY_SEPARATOR, $lastUrl);
                }
                /**
                 * it is not page number but items offset
                 */
                $this->lastPage = end($exploded);
                unset($paging);
                break;
            }
        }

        unset($dom);
        return $this;
    }
}