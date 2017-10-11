<?php

namespace xepan\commerce;

Class page_test1 extends \xepan\base\Page{

	function init(){
		parent::init();		

		$category_m = $this->add('xepan\commerce\Model_Category');
		$category_m->addCondition('slug_url',['null','']);
		
		foreach ($category_m as $category) {
			$category['slug_url'] = strtolower($category['name']);
			$category->saveAndUnload();
		}
	}
}