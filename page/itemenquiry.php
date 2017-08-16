<?php

namespace xepan\commerce;

class page_itemenquiry extends\xepan\base\Page{
	public $title = "Item Enquiry";
	
	function init(){
		parent::init();

		$enquiry_model = $this->add('xepan\commerce\Model_ItemEnquiry');
		$grid = $this->add('xepan\base\Grid');
		$grid->setModel($enquiry_model);
	}
}