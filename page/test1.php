<?php

namespace xepan\commerce;

Class page_test1 extends \xepan\base\Page{

	function init(){
		parent::init();		
			
		$item_m = $this->add('xepan\commerce\Model_Item');
		
		foreach ($item_m as $item) {
			$item->save();	
		}
	}
}