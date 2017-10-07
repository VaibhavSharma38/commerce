<?php

namespace xepan\commerce;

class Model_Redirection extends \xepan\base\Model_Table{
	public $table = "redirection";
	public $acl = false;
	
	function init(){
		parent::init();
		
		$this->addField('request');
		$this->addField('target');
	}
}