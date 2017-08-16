<?php

namespace xepan\commerce;

class page_test2 extends \xepan\base\Page{
	function init(){
		parent::init();
		
		$item_m = $this->add('xepan\commerce\Model_Item');
		$item_m->addCondition('hide_in_shop',false);

		$grid = $this->add('Grid');
		$grid->setModel($item_m,['sku']);
	}
}