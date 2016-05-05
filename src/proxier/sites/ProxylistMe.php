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

    protected $config = array('baseUrl' => 'http://proxylist.me/proxys/list/');

    public function parse() {
        //table class="table table-bordered table-hover" > tbody > tr > td первая и вторая
    }

    protected function parsePaging() {
//ul class="pagination"
    }
}