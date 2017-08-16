<?php

namespace xepan\commerce;

class page_dbbackup extends \xepan\base\Page{
	function init(){
		parent::init();

		throw new \Exception(var_dump($this->app->config));
		
	}
}