<?php

namespace xepan\commerce;

class page_categoryimage extends \xepan\base\Page{
	public $title = "Category Header Image";

	function init(){
		parent::init();

		$categoryimage_m = $this->add('xepan\commerce\Model_CategoryImage');
		
		$field = $categoryimage_m->getField('category_id');
		

		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($categoryimage_m);
	}
}