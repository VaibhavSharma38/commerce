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
	}
}