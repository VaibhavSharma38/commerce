<?php

namespace xepan\commerce;

class Tool_CategoryImage extends \xepan\cms\View_Tool{
	public $options = [
					'show_image_of'=>'parent_category'
				];

	function init(){
		parent::init();

		$categoryimage_m = $this->add('xepan\commerce\Model_CategoryImage');
		
		if($this->options['show_image_of'] === "parent_category"){
			$categoryimage_m->tryLoadBy('category_id',$_GET['parent_category_id']);
		}else{
			$categoryimage_m->tryLoadBy('category_id',$_GET['xsnb_category_id']);
		}

		if($categoryimage_m->loaded())
			$this->template->trySet('url',$categoryimage_m['image']);
		else
			$this->template->tryDel('superWrapper');
	}

	function defaultTemplate(){
		return ['view\tool\categoryimage'];
	}
}