<?php
namespace xepan\commerce;

class Tool_CategoryHeading extends \xepan\cms\View_Tool{
	public $options = [
	];

	function init(){
		parent::init();
		
		if(!$_GET['xsnb_category_id'])
			return;
		
		$view = $this->add('xepan\commerce\View_CategoryHeading',['options'=>$this->options]);
	}
}