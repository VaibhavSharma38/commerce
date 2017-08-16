<?php

namespace xepan\commerce;

class Model_Wishlist extends \Model_Table{
	public $table = "wishlist";
	
	function init(){
		parent::init();

		$this->hasOne('xepan\commerce\Customer','customer_id');
		$this->hasOne('xepan\commerce\Item','item_id');
		$this->addField('created_at')->defaultValue($this->app->now);
	}
}