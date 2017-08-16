<?php

namespace xepan\commerce;

class Model_CurrentOpening extends \xepan\base\Model_Table{
	public $table = "current_opening";
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
		$this->addField('status')->enum($this->status)->defaultValue('Active');
		$this->addField('type')->defaultValue('CurrentOpening');		
		$this->addField('post_name');		
		$this->addField('experience_required');		
		$this->addField('location');		
		$this->addField('description')->type('text')->display(['form'=>'xepan\base\RichText']);		
		
		$this->addHook('afterSave',[$this,'populateValue']);
	}

	function populateValue(){
		// Populate post_name in custom form field dropdown

		$current_opening_m = $this->add('xepan\commerce\Model_CurrentOpening');
		$current_opening_m->addCondition('status','Active');

		$post_name_array = [];
		foreach ($current_opening_m as $crr) {
			$post_name_array [] = $crr['post_name'];
		}

		$custom_form_m = $this->add('xepan\cms\Model_Custom_Form');
		$custom_form_m->tryLoadBy('name','Job Application');

		if(!$custom_form_m->loaded())
			return;

		$form_field_m = $this->add('xepan\cms\Model_Custom_FormField');
		$form_field_m->addCondition('custom_form_id',$custom_form_m->id);
		$form_field_m->addCondition('name','Job_Title');
		$form_field_m->tryLoadAny();

		if(!$form_field_m->loaded())
			return;
		
		$form_field_m['value'] = implode(',', $post_name_array);
		$form_field_m->save();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("Job Opening : '".$this['post_name']."' has been deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Job Opening : '".$this['post_name']."' is now active", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}
}