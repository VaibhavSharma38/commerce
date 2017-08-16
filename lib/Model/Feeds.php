<?php

namespace xepan\commerce;

class Model_Feeds extends \xepan\base\Model_Table{
	public $table = "feeds";

	function init(){
		parent::init();

		$this->add('xepan\filestore\Field_Image','image_id');
		$this->addField('title');
		$this->addField('description')->type('text');
		$this->addField('url');
		
		$this->addHook('beforeSave',$this);
	}

	function beforeSave($m){
		$feed_m = $this->add('xepan\commerce\Model_Feeds');
		
		if($feed_m->count()->getOne())
			throw new \Exception("At present only single news feed feature is working");
	}
}