<?php

namespace xepan\commerce;

class Model_ItemEnquiry extends \Model_Table{
	public $table = "itemenquiry";

	function init(){
		parent::init();

		$this->hasOne('xepan\commerce\Item','item_id');
		$this->hasOne('xepan\commerce\Customer','customer_id');

		$this->addField('name');
		$this->addField('organization');
		$this->addField('email');
		$this->addField('contact_no');
		$this->addField('address');
		$this->addField('city');
		$this->addField('state');
		$this->addField('country');
		$this->addField('requirements');
		$this->addField('created_at')->defaultValue($this->app->now);

		$this->addHook('beforeSave',[$this,'validations']);
	}

	function validations(){
		if($this['name'] == '')
			throw $this->exception('Name Field is required','ValidityCheck')->setField('name');
	}
}