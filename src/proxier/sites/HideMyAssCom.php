<?php


namespace proxier\sites;

use proxier\BaseSiteCom;

/**
 * Class HideMyAssCom
 * @package proxier\sites
 */
class HideMyAssCom extends BaseSiteCom
{


    protected $config = array('baseUrl' => 'http://proxylist.hidemyass.com/');

    protected $inlineCss = array();

    public function parse() {
        $this->lastPage = 7;
        $this->parsePaging();
        $cycle = range(1, $this->lastPage, 1);
        foreach ($cycle as $pageNum) {
            $url = $this->config['baseUrl'] . $pageNum;
            $this->curlInit($url)->runCurl();
            if ($this->curlError) {
                continue;
            }
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->validateOnParse = true;
            @$dom->loadHTML($this->curlResult);
            $table = $dom->getElementById('listable');
            if ($table) {
                $tbody = $table->getElementsByTagName('tbody');
                if (!$tbody->length) {
                    continue;
                }
                $body = $tbody->item(0);
                /**
                 * @var \DOMElement $body
                 */

                $rows = $body->getElementsByTagName('tr');
                if (!$rows->length) {
                    continue;
                }
                foreach ($rows as $row) {
                    /**
                     * @var \DOMElement $row
                     */
                    $tds = $row->getElementsByTagName('td');
                    if (!$tds->length) {
                        continue;
                    }
                    /**
                     * @var \DOMElement $td
                     */
                    $td = $tds->item(1);

                    //third column is port
                    $port = $tds->item(2)->textContent;

                    $spans = $td->getElementsByTagName('span');
                    if (!$spans->length) {
                        continue;
                    }
                    $span = $spans->item(0);
                    /**
                     * @var \DOMElement $span
                     */

                    $style = $span->getElementsByTagName('style')->item(0);


                    if (!$style) {
                        continue;
                    }
                    $this->parseStyleTag($style->textContent);
                    /**
                     * @var \DOMElement $style
                     */

//                    $style->parentNode->removeChild($style);
                    //style example: .kOr8{display:none}.cOWu{display:inline}.kGKg{display:none}.fUI4{display:inline}

                    //each VISIBLE! span contains ip byte
                    $ipSpans = $span->getElementsByTagName('*');

                    if (!$ipSpans->length) {
                        continue;
                    }

                    $ip = '';
                    $allowed = array('span', 'div', '#text');

                    while ($span->hasChildNodes()) {
                        $child = $span->firstChild;
                        $tag = $child->nodeName;
                        if (!in_array($tag, $allowed)) {
                            $span->removeChild($span->firstChild);
                            continue;
                        }
                        if ($tag == '#text') {
                            $ip .= $child->nodeValue;
                            $span->removeChild($span->firstChild);
                            continue;
                        }
                        $blockStyle = $child->getAttribute('style');
                        $blockClass = $child->getAttribute('class');
                        if (strpos($blockStyle, 'display:none') !== false) {
                            //block is invisible
                            $span->removeChild($span->firstChild);
                            continue;
                        }
                        if (!$this->isVisible($blockClass)) {
                            //css class with display none
                            $span->removeChild($span->firstChild);
                            continue;
                        }

                        $ip .= $child->textContent;

                        $span->removeChild($span->firstChild);

                    }

                    $this->parsedProxies[] = trim($ip) . ":" . trim(strip_tags($port));

                }

            } else {
                continue;
            }
            unset($dom);
        }
    }

    /** style eg: .kOr8{display:none}.cOWu{display:inline}.kGKg{display:none}.fUI4{display:inline}
     * @param $style
     * @return string
     */
    protected function parseStyleTag($style) {
        $parts = explode('.', $style);
        foreach ($parts as $prt) {
            $css = explode('{', $prt);
            if (!$css) {
                continue;
            }
            $className = current($css);
            $classCss = rtrim(end($css), "}");
            $this->inlineCss[$className] = $classCss;
        }
    }

    /**
     * @param $class
     * @return bool
     */
    protected function isVisible($class) {
        if (isset($this->inlineCss[$class])) {
            if (strpos($this->inlineCss[$class], 'display:none') !== false) {
                return false;
            }
        }
        return true;
    }

    protected function parseRow($row) {

    }

    protected function parsePaging() {

    }

}