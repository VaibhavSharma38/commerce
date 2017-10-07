<?php
namespace xepan\commerce;
class View_SubCategoryDetailLister extends \CompleteLister{
		public $options = [
			'url_page' =>'index',
			"custom_template"=>'',
			'show_name'=>true,
			'show_price'=>false,
			'show_image'=>false,
			'show_product_image'=>false,
			'show_item_count'=>false,
			'show_only_new_product'=>false,
			'show_new'=>false
		];

	function init(){
		parent::init();

		$xsnb_category_id = $_GET['xsnb_category_id'];
		$category_code = $_GET['category_code'];

		$model = $this->add('xepan\commerce\Model_Category');
		$model->addCondition('parent_category','<>',null);
		$model->addCondition('parent_category','<>',0);
		$model->addCondition('is_for_product',true);

		$model->addExpression('has_item')->set(function($m,$q) use($xsnb_category_id){
			$asso_m = $this->add('xepan\commerce\Model_CategoryItemAssociation');
			$asso_m->addCondition('category_id',$m->getElement('id'));
			return $asso_m->count();
		});

		$model->addCondition('has_item','>',0);
		
		if($this->options['show_only_new_product']){
			$ass_j = $model->leftJoin('category_item_association.category_id','id');
			$ass_j->addField('item_id');
			$ass_i_j = $ass_j->join('item.document_id','item_id');
			$ass_i_j->addField('is_new');

			$model->addCondition('is_new',true);
			$model->_dsql()->group('name');
			// $this->owner->add('Grid')->setModel($model);
		}

		if($xsnb_category_id){
			$cat_m = $this->add('xepan\commerce\Model_Category');
			$cat_m->load($xsnb_category_id);

			if(!$cat_m['parent_category']){				
				
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
		}

		if($category_code){
			$cat_m = $this->add('xepan\commerce\Model_Category');
			$cat_m->loadBy('slug_url',$category_code);

			if(!$cat_m['parent_category']){				
				
				$m = $this->add('xepan\commerce\Model_CategoryParentAssociation');
				$m->addCondition('parent_category_id',$cat_m->id);
						
				$temp = [];
				foreach ($m as $value) {
					$temp [] = $value['category_id'];
				}

				if(!empty($temp))
					$model->addCondition('id',$temp);
				else
					$model->addCondition('id',0);	
			}
		}
		
		if($this->options['show_new']){
			$model->addCondition('is_new',true);
		}
		
		$this->setModel($model);

		$this->add('xepan\cms\Controller_Tool_Optionhelper',['options'=>$this->options,'model'=>$model]);
	}
	
	function formatRow(){

		$parent_category_id = $_GET['xsnb_category_id'] ?:$_GET['parent_category_id'];
		//calculating url
		if($this->model['custom_link']){
			// if custom link contains http or https then redirect to that website
			$has_https = strpos($this->model['custom_link'], "https");
			$has_http = strpos($this->model['custom_link'], "http");
			if($has_http === false or $has_https === false )
				$url = $this->app->url($this->model['custom_link'],['xsnb_category_id'=>$this->model->id,'parent_category_id'=>$parent_category_id]);
			else
				$url = $this->model['custom_link'];
			$this->current_row_html['url'] = $url;
		}else{
			$url = $this->app->url($this->options['url_page'],['xsnb_category_id'=>$this->model->id,'parent_category_id'=>$parent_category_id]);
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
		elseif ($this->options['show_product_image']) {			
			$l->current_row_html['category_image_url'] = $l->model['product_image'];
		}else
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