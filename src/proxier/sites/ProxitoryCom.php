<?php

namespace proxier\sites;

use proxier\BaseSiteCom;

class ProxitoryCom extends BaseSiteCom
{

    protected $config = array('baseUrl' => 'http://www.proxitory.com/free-proxy-list/');

    public function parse() {
        $this->parsePaging();
        $cycle = range(1, $this->lastPage, 1);
        foreach ($cycle as $pageNum) {
            $url = $this->config['baseUrl'] . $pageNum . "/";
            $this->curlInit($url)->runCurl();
            if ($this->curlError) {
                continue;
            }
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->validateOnParse = true;
            @$dom->loadHTML($this->curlResult);
            $tables = $dom->getElementsByTagName('table');
            if ($tables->length) {
                $table = $tables->item(0);
                $tbody = $table->getElementsByTagName('tbody');
                if (!$tbody->length) {
                    continue;
                }
                $body = $tbody->item(0);

                $rows = $body->getElementsByTagName('tr');
                if (!$rows->length) {
                    continue;
                }
                foreach ($rows as $row) {
                   $tds = $row->getElementsByTagName('td');
                    if(!$tds->length){
                        continue;
                    }
                    foreach($tds as $td){
//                        var_dump($td->textContent);
                    }
//                    var_dump($pageNum);
//                    break 2;

                }

            } else {
                continue;
            }
//

            unset($dom);
            break;
        }
    }

    protected function parseRow($row){

    }

    protected function parsePaging() {
        $pagingId = "paging"; $this->curlInit($this->config['baseUrl']);
        $this->runCurl();
        if ($this->curlError) {
            return false;
        }
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);
        $paging = $dom->getElementById($pagingId);
        if ($paging) {
            $pagingString = $paging->nodeValue;//Page X of Y pages
            $count = explode(' ', $pagingString);
            if (isset($count[5])) {
                $this->lastPage = (int)$count[5];
            }
        }

        unset($dom);
        unset($paging);
        return $this;

    }

}