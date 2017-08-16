<?php

namespace xepan\commerce;

class page_feeds extends \xepan\base\Page{
	public $title = "Latest Feed";

	function init(){
		parent::init();
	
		$feed_m = $this->add('xepan\commerce\Model_Feeds');

		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($feed_m);
	}
}