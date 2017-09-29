<?php

namespace xepan\commerce;

Class page_test1 extends \xepan\base\Page{

	function init(){
		parent::init();		
			
		$item_m = $this->add('xepan\commerce\Model_Item');
		$item_m->setLimit(1);			
		
		foreach ($item_m as $item) {
			$item->save();	
		}
	}
}