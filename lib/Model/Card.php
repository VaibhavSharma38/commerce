<?php

namespace xepan\commerce;

class Model_Card extends \xepan\base\Model_Table{
	public $table = "card";
	// public $acl = false;

	public $status=[
		'Active',
		'InActive'
	];

	public $actions=[
		'Active'=>['view','edit','delete','deactivate'],
		'InActive'=>['view','edit','delete','activate']
	];

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id);

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('status')->enum($this->status)->defaultValue('InActive');
		$this->addField('type')->defaultValue('PopupCard');		
		$this->addField('name');		
		$this->addField('link');		
		
		$this->add('xepan\filestore\Field_Image','image_id');
		
		$this->addHook('beforeSave',[$this,'changeStatus']);
	}

	function changeStatus($m){
		if($m['status'] != 'Active')
			return;
					
		$card_m = $this->add('xepan\commerce\Model_Card');
		$card_m->addCondition('id','<>',$m->id);

		foreach ($card_m as $card) {
			$card['status'] = 'InActive';
			$card->save();
		}
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("PopCard : '".$this['name']."' has been deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("PopCard : '".$this['name']."' is now active", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}
}