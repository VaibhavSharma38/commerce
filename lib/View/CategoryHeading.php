<?php
namespace xepan\commerce;
class View_CategoryHeading extends \View{		
		public $options = [
		];

	function init(){
		parent::init();
		
		$model = $this->add('xepan\commerce\Model_Category');
		
		if($xsnb_category_id = $_GET['xsnb_category_id']){			
			$model->load($xsnb_category_id);
			
			if($model['parent_category'] == null){				
				$this->template->trySetHTML('heading1',$model['name']);
				$this->template->trySetHTML('description1',$model['description']);
			}
		}elseif($category_code = $_GET['category_code']){			
			$model->loadBy('slug_url',$category_code);
			
			if($model['parent_category'] == null){				
				$this->template->trySetHTML('heading1',$model['name']);
				$this->template->trySetHTML('description1',$model['description']);
			}
		}else{
			$model->load(-1);
		}

		$this->setModel($model);
		$this->add('xepan\cms\Controller_Tool_Optionhelper',['options'=>$this->options,'model'=>$model]);
	}

	function defaultTemplate(){
		return ['view/tool/categoryheading'];
	}
}