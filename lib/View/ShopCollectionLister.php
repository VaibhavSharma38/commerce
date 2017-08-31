<?php

namespace xepan\commerce;

class View_ShopCollectionLister extends \CompleteLister{
		public $options = [
			'url_page' =>'index',
			"custom_template"=>'',
			'show_name'=>true,
			'show_price'=>false,
			'show_image'=>false,
			'show_item_count'=>false,
			'include_sub_category'=>false
		];

	function init(){
		parent::init();
		
		$model = $this->add('xepan\commerce\Model_Category');
		$model->addCondition('name',['Shop By Collection','Exclusive','Clearance']);
		$model->setOrder('display_sequence','asc');
		$this->setModel($model);

		$this->add('xepan\cms\Controller_Tool_Optionhelper',['options'=>$this->options,'model'=>$model]);
	}
	
	function formatRow(){		
		if($this->model['name'] == 'Clearance'){
			$url = $this->app->url('clearance',['xsnb_category_id'=>$this->model->id]);
			$this->current_row_html['url'] = $url;
		}
		elseif ($this->model['name'] == 'Shop By Collection') {
			$url = $this->app->url('shop-by-collection',['xsnb_category_id'=>$this->model->id]);
			$this->current_row_html['url'] = $url;
		}else{
			$url = $this->app->url('exclusive',['xsnb_category_id'=>$this->model->id]);
			$this->current_row_html['url'] = $url;
		}

		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/tool/'.$this->options['custom_template']];
	}

	function addToolCondition_row_show_item_count($value,$l){
		if(!$value)
			$l->current_row_html['item_count_wrapper'] = "";
		else
			$l->current_row_html['item_count'] = $l->model['item_count'];
	}

	function addToolCondition_row_show_image($value,$l){		
		if(!$value)
			$l->current_row_html['image_wrapper'] = "";
		else
			$l->current_row_html['category_image_url'] = $l->model['cat_image'];
	}


	function addToolCondition_row_show_price($value,$l){
		if(!$value)
			$l->current_row_html['price_wrapper'] = "";
		else{
			$l->current_row_html['min_price'] = $l->model['min_price'];	
			$l->current_row_html['max_price'] = $l->model['max_price'];
		}
	}

}