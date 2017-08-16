<?php

namespace xepan\commerce;

class Model_CategoryParentAssociation extends \Model_Table{
	public $table = "category_parent_association";
	public $acl = false;

	function init(){
		parent::init();
	
		$this->hasOne('xepan\commerce\Category','category_id');
		$this->hasOne('xepan\commerce\Category','parent_category_id');
	}
}