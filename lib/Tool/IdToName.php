<?php

namespace xepan\commerce;

class Tool_IdToName extends \xepan\cms\View_Tool{
	public $options = [
					    'page_name'=>'',
					    'type'=>'',
					    'show_parent'=>''
					  ];

	function init(){
		parent::init();
			
		if($parent_category = $_GET['parent_category_id']){			
			$parent_category_m = $this->add('xepan\commerce\Model_Category');
			$parent_category_m->load($parent_category);

			$this->template->trySet('parent_category',strtoupper($parent_category_m['name']));
			$this->template->trySet('pc_class','inactive-breadcrumb');
			$this->template->trySet('bslash','/');
			
			if($this->options['type'] == 'shop'){
				if($parent_category_m['name'] == 'Shop By Collection')
					$page_name = "shop-by-collection";
				else
					$page_name = strtolower($parent_category_m['name']);
				
				$this->template->trySet('pc_url',$this->app->url($page_name.'&xsnb_category_id='.$parent_category));
			}
			else{
				$this->template->trySet('pc_url',$this->app->url('collection'.'&xsnb_category_id='.$parent_category));
			}
		}

		if($xsnb_category_id = $_GET['xsnb_category_id']){
			$category_m = $this->add('xepan\commerce\Model_Category');
			$category_m->load($xsnb_category_id);
			
			$this->template->trySet('category',strtoupper($category_m['name']));
			$this->template->trySet('c_url',$this->app->url($this->options['page_name'].'&xsnb_category_id='.$category_m['id'].'&parent_category_id='.$parent_category));
			$this->template->trySet('c_class','active-breadcrumb');
		}elseif($category_code = $_GET['category_code']){
			$category_m = $this->add('xepan\commerce\Model_Category');
			$category_m->loadBy('slug_url',$category_code);
			
			$this->template->trySet('category',strtoupper($category_m['name']));
			$this->template->trySet('c_url',$this->app->url($this->options['page_name'].'&xsnb_category_id='.$category_m['id'].'&parent_category_id='.$parent_category));
			$this->template->trySet('c_class','active-breadcrumb');
		}else{
			$category_m = $this->add('xepan\commerce\Model_Category');
			$category_m->load(-1);
		}
			
		if($commerce_item_id = $_GET['commerce_item_id']){
			$item_m = $this->add('xepan\commerce\Model_Item');
			$item_m->load($commerce_item_id);
			
			$this->template->trySet('item',$item_m['name']);
			$this->template->trySet('backslash',' / ');
			$this->template->trySet('i_class','active-breadcrumb');
			$this->template->trySet('c_class','inactive-breadcrumb');
		}
	}

	function defaultTemplate(){
		return ['view\tool\idtoname'];
	}
}