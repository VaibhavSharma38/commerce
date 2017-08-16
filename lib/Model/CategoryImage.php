<?php

namespace xepan\commerce;

class Model_CategoryImage extends \Model_Table{
	public $table = "category_image";
	
	function init(){
		parent::init();
	
		$this->hasOne('xepan\commerce\Category','category_id');
		$this->add('xepan\filestore\Field_Image','image_id');
		
		$this->addHook('beforeSave',$this);	
	}

	function beforeSave($m){
		$categoryimage_m = $this->add('xepan\commerce\Model_CategoryImage');
		$categoryimage_m->addCondition('category_id',$m['category_id']);

		if($categoryimage_m->count()->getOne())
			throw new \Exception("Category entry already exist, first delete the entry and then re-enter");	
	}
}