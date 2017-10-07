<?php

namespace xepan\commerce;

class page_redirection extends \xepan\base\Page{
	public $title = "Redirection";

	function init(){
		parent::init();

		$this->add('CRUD')->setModel('xepan\commerce\Model_Redirection');
	}
}