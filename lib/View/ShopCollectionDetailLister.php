<?php
namespace xepan\commerce;
class  View_ShopCollectionDetailLister extends \CompleteLister{
		public $options = [
			'url_page' =>'index',
			"custom_template"=>'',
			'show_name'=>true,
			'show_price'=>false,
			'show_image'=>false,
			'show_item_count'=>false,
			'show_new'=>false,
			// 'order_by'=>''
			'image_type'=>''
		];

	function init(){
		parent::init();
    	
		$xsnb_category_id = $this->app->stickyGET('xsnb_category_id');

		$model = $this->add('xepan\commerce\Model_Category');
		$model->addExpression('has_item')->set(function($m,$q) use($xsnb_category_id){
			$asso_m = $this->add('xepan\commerce\Model_CategoryItemAssociation');
			$asso_m->addCondition('category_id',$m->getElement('id'));
			$asso_stock_j = $asso_m->join('item_stock.item_id','item_id');
			$asso_stock_j->addField('commerce_category_id','category');
			$asso_stock_j->addField('item_stock','current_stock');
			$asso_m->addCondition('commerce_category_id',$xsnb_category_id);
			$asso_m->addCondition('item_stock','>',0);

			return $asso_m->count();
			// return "'1'";
		});
		
		$model->addCondition('has_item','>',0);

		if($xsnb_category_id){
			$cat_m = $this->add('xepan\commerce\Model_Category');
			$cat_m->load($xsnb_category_id);

			$m = $this->add('xepan\commerce\Model_CategoryParentAssociation');
			$m->addCondition('parent_category_id',$xsnb_category_id);
						
			$temp = [];
			foreach ($m as $value) {
				$temp [] = $value['category_id'];
			}

			if(!empty($temp))
				$model->addCondition('id',$temp);
			else
				$model->addCondition('id',0);	
		

		}

		if($this->options['show_new']){
			$model->addCondition('is_new',true);
		}

		if($this->options['order_by']){
			$order_by = $this->options['order_by'];
			$model->setOrder($order_by,'asc');			
		}

		$model->addCondition('parent_category','<>',null);
		$model->addCondition('is_for_shop',true);

		$this->setModel($model);

		$this->add('xepan\cms\Controller_Tool_Optionhelper',['options'=>$this->options,'model'=>$model]);
	}
	
	function formatRow(){

		//calculating url
		if($this->model['custom_link']){
			// if custom link contains http or https then redirect to that website
			$has_https = strpos($this->model['custom_link'], "https");
			$has_http = strpos($this->model['custom_link'], "http");
			if($has_http === false or $has_https === false )
				$url = $this->app->url($this->model['custom_link'],['xsnb_category_id'=>$this->model->id,'parent_category_id'=>$_GET['xsnb_category_id']]);
			else
				$url = $this->model['custom_link'];
			$this->current_row_html['url'] = $url;
		}else{
			$url = $this->app->url($this->options['url_page'],['xsnb_category_id'=>$this->model->id,'parent_category_id'=>$_GET['xsnb_category_id']]);
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
		elseif($this->options['image_type'] == 'Clearance')
			$l->current_row_html['category_image_url'] = $l->model['clearance_image'];
		elseif($this->options['image_type'] == 'Exclusive')
			$l->current_row_html['category_image_url'] = $l->model['exclusive_image'];
		elseif($this->options['image_type'] == 'Product')			
			$l->current_row_html['category_image_url'] = $l->model['product_image'];
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