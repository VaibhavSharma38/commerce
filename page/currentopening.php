<?php

namespace xepan\commerce;

class page_currentopening extends \xepan\base\Page{
	public $title = "Current Opening";

	function init(){
		parent::init();

		$current_opening_m = $this->add('xepan\commerce\Model_CurrentOpening');

		$crud = $this->add('xepan\hr\CRUD',null,null,['page\currentopening']);
		$crud->setModel($current_opening_m);
	}
}