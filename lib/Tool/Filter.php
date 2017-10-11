<?php

namespace xepan\commerce;

class Tool_Filter extends \xepan\cms\View_Tool{
	public $options = [
			"show_price_filter"=>true,
			"min_price"=>0,
			"max_price"=>10,
			"left_label" => "min",
			"right_label" => "max",
			"filter_type" => "",
			"custom_template"=>''
	];
	public $header_view;

	function init(){
		parent::init();

		$parent_category_id = $this->app->stickyGET('parent_category_code');

		$form_layout = 'view/tool/filter/formsection';

		if($this->options['custom_template']){
			$path = getcwd()."/websites/".$this->app->current_website_name."/www/view/tool/filter".$this->options['custom_template'].".html";
			if(file_exists($path)){
				$form_layout = 'view/tool/filter/'.$this->options['custom_template'];
			}else{
				$this->add('View_Error')->set('Custom template not found.');
				return; 
			}
		}
		
		$this->app->stickyGET('category_code');

		$previous_selected_filter = json_decode($this->app->recall('filter'),true)?:[];
		
		$model_filter = $this->add('xepan\commerce\Model_Filter');

		$spec_array = [];
		$avil_size_array = [];

		$category_code = explode('/', $_GET['category_code']);
		$category_m = $this->add('xepan\commerce\Model_Category');
		$category_m->tryLoadBy('slug_url',$category_code[1]);

		$parent_category_m = $this->add('xepan\commerce\Model_Category');
		$parent_category_m->loadBy('slug_url',$_GET['parent_category_code']);

		if($xsnb_category_id = $category_m->id){			
			$assoc_m = $this->add('xepan\commerce\Model_CategoryItemAssociation');
			$assoc_m->addCondition('category_id',$xsnb_category_id);

			$item_id = [];
			foreach ($assoc_m as $assoc) {
				$item_id [] = $assoc['item_id'];
			}

			foreach ($item_id as $item){				
				$item_m = $this->add('xepan\commerce\Model_Item');	
				$item_m->tryLoad($item);

				if(!$item_m->loaded())
					continue;

				if($this->options['filter_type'] == 'Product'){
					if($item_m['hide_in_product'])
						continue;
				}

				if($this->options['filter_type'] == 'Shop'){
					if($item_m['hide_in_shop'])
						continue;

					$item_stock_m = $this->add('xepan\commerce\Model_ItemStock');
					$item_stock_m->addCondition('item_id',$item_m->id);

					
					if($item_stock_m->count()->getOne() <1)
						continue;
					else
						foreach ($item_stock_m as $is) {																					
							if($is['category'] == $parent_category_m->id)
								$avil_size_array[] = $is['size'];
						}
				}

				$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name',' Color');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);
			    $model_cf_value->tryLoadAny();

				if($this->options['filter_type'] == 'shop'){
					$stock_m = $this->add('xepan\commerce\Model_ItemStock');
					$stock_m->addCondition('item_id',$item_m->id);
					$stock_m->addCondition('category',$category_m->id);

					if($stock_m['current_stock'] > 0)
			    		$spec_array [] = $model_cf_value['name'];
				}			    		
			    else{
			    	$spec_array [] = $model_cf_value['name'];
			    }

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();

				$custom_field = $this->add('xepan\commerce\Model_Item_CustomField');
				$custom_field->loadBy('name','Size');

				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$custom_field->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value');
				$model_cf_value->addCondition('customfield_association_id',$model_cf_asso->id);

				foreach ($model_cf_value as $val) {
					if(($this->options['filter_type'] == 'Shop')){
						if(in_array($val['name'], array_unique($avil_size_array)))
							$spec_array[] = $val['name'];
					}
					// else{
					// 	$spec_array [] = $val['name']; 	
					// }						
				}

				$custom_field->unload();					   
				$model_cf_asso->unload();	
				$model_cf_value->unload();
			}
		}

		if(!$model_filter->count()->getOne()){
			$this->add('View_Error')->set('no filter found');
			return;
		}

		//Filter Form
		$form = $this->add('Form',null,null,['form/empty']);

		//price slider
		if($this->options['show_price_filter']){
			$this->heading = $form->add('View',null,null,[$form_layout]);

			$price = $this->heading->addField('xepan\commerce\RangeSlider','price');
			$price->min = $this->options['min_price']?:0;
			$price->max = $this->options['max_price']?:10;
			$price->step = $this->options['step']?:1;
			$price->left = $this->options['left_label']?:'min';
			$price->right = $this->options['right_label']?:'max';

			if($price_range = $this->app->recall('price_range')){
				$range_array = explode(",", $price_range);
				$price->selected_min = $range_array[0];
				$price->selected_max = $range_array[1];
				$price->set($price_range);
				// $this->app->forget('price_range');
			}
			$this->heading->template->trySet('name','Price Range '.$price->selected_min." - ".$price->selected_max);			
		}

		$q = $model_filter->dsql();
		/**
		get all unique value
		Filterable specification has many Association
		Association has many values
		*/
		//join with association
		$asso_join = $model_filter->Join('customfield_association.customfield_generic_id','id');

		//association join with values
		$value_join = $asso_join->join('customfield_value.customfield_association_id','id');
		$value_join->addField('value_name','name');
		$value_join->addField('value_id','id');

		//group by with value name
		$cf_name_group_element = $q->expr('[0]',[$model_filter->getElement('id')]);
		//group by with specification name
		$value_group_element = $q->expr('[0]',[$model_filter->getElement('value_name')]);
		$model_filter->_dsql()->group($value_group_element);

		$model_filter->addCondition('value_name','<>',"");
		$model_filter->setOrder('value_name','asc');
		$model_filter->setOrder('name','asc');
		
		if(!empty($spec_array))
			$model_filter->addCondition('value_name',$spec_array);
		
		$unique_specification_array = [];
		$count = 1;
	
		foreach ($model_filter as $specification) {

			if(!isset($unique_specification_array[$specification['name']])){
				$this->heading = $form->add('View',null,null,['view/tool/filter/formsection']);
				$this->heading->template->trySet('name',$specification['name']);
				// $this->heading->add('H2')->set($specification['name']);
				$unique_specification_array[$specification['name']] = [];
			}
			$field = $this->heading->addField('checkbox',$specification['value_id'],$specification['value_name']);
			
			if(count($previous_selected_filter)){

				// echo "<pre>";
					// print_r($previous_selected_filter[$specification['id']]['values']);
				if(isset($previous_selected_filter[$specification['id']]))
					if(in_array($specification['value_name'],$previous_selected_filter[$specification['id']]))
						$field->set(1);
			}
			// $count++;
		}
		// $form->on('click','input',$form->js()->submit());
		$form->on('change','input',$form->js()->submit());


		// $url = "product/".$url;
		//specification_id_1:value1,value2|specification_id_2:value_1,value_2
		if($form->isSubmitted()){						
			$selected_options = [];				
			$str = "";
			$specification_array=[];
			// $count = 1;

			foreach ($model_filter as $specification) {
				//if filter checked or not
				if($form[$specification['value_id']]){
						
					if(!isset($specification_array[$specification['id']]))
						$specification_array[$specification['id']] = [];

					$specification_array[$specification['id']][] = $specification['value_name'];
					// echo '<pre>';
					// var_dump($specification_array);
					// exit;
					
				}
				// $count++;
			}

			$this->app->memorize('filter',json_encode($specification_array,true));
			$this->app->memorize('price_range',$form['price']);
			
			if($this->options['filter_type'] == 'Product'){
				$url = 'product/'.$_GET['category_code'];
				$this->app->stickyForget('category_code');
				$form->app->redirect($this->app->url($url));
			}			
			elseif($this->options['filter_type'] == 'Shop'){
				$url = 'shop/'.$_GET['category_code'];
				$this->app->stickyForget('category_code');
				$this->app->stickyForget('parent_category_code');
				$form->app->redirect($this->app->url($url));
			}
			else	{
				$form->app->redirect($this->app->url());
			}
		}

	}

	function render(){
		$this->js(true)->_css("jquery-ui");
		parent::render();
	}
}