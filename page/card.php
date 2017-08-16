<?php

namespace xepan\commerce;

class page_card extends \xepan\base\Page{
	public $title = "Popup Card";

	function init(){
		parent::init();

		$card_m = $this->add('xepan\commerce\Model_Card');
		$crud = $this->add('xepan\hr\CRUD',null,null,['page\card']);
		$crud->setModel($card_m,['name','link','image_id'],['name','link','status','image']);
		$crud->grid->removeColumn('attachment_icon');
	}
}