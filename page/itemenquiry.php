<?php

namespace xepan\commerce;

class page_itemenquiry extends\xepan\base\Page{
	public $title = "Item Enquiry";
	
	function init(){
		parent::init();

		$enquiry_model = $this->add('xepan\commerce\Model_ItemEnquiry');
		
		$crud = $this->add('xepan\hr\CRUD',['allow_add'=>false]);
		$crud->setModel($enquiry_model);
	}
}