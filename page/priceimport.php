<?php

namespace xepan\commerce;


class page_priceimport extends \xepan\base\Page{
	
	function init(){
		parent::init();
		
		ini_set('max_execution_time', 600);

		$form = $this->add('Form');
		$form->addSubmit('Export Current Price');
		
		if($_GET['download_sample_csv_file']){
			$output = ['sku','size','price'];

			$output = implode(",", $output);
	    	header("Content-type: text/csv");
	        header("Content-disposition: attachment; filename=\"sample_xepan_stock_priceimport.csv\"");
			header('Pragma: no-cache');
			header('Expires: 0');
	        
			$file = fopen('php://output', 'w');
	        
			fputcsv($file, array('sku','size','price'));
	        
	        $item_m = $this->add('xepan\commerce\Model_Item');

	        $data = [];
	        foreach ($item_m as $item) {
	        	$custom_field = $this->add('xepan\commerce\Model_Item_CustomField');
				$custom_field->loadBy('name','Size');

				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$custom_field->id);
				$model_cf_asso->addCondition('item_id',$item->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value');
				$model_cf_value->addCondition('customfield_association_id',$model_cf_asso->id);
				$model_cf_value['name'];

				foreach ($model_cf_value as $cf_val) {
					$price_m = $this->add('xepan\commerce\Model_Item_Quantity_Set');
					$price_m->addCondition('item_id',$item->id);
					$price_m->addCondition('name',$cf_val['name']);
					$price_m->tryLoadAny();

					$price = '';

					if($price_m->loaded()){
						$price = $price_m['price'];						
					}

	        		$data [] = [$item['sku'],$cf_val['name'],$price];		
				}
	        }
	        
			foreach ($data as $row)
			    fputcsv($file, $row);
			 
			exit();	        
		}

		if($form->isSubmitted()){
			$form->js()->univ()->newWindow($form->app->url('xepan_commerce_priceimport',['download_sample_csv_file'=>true]))->execute();
		}

		$this->add('View')->setElement('iframe')->setAttr('src',$this->api->url('xepan_commerce_priceimportexecute',array('cut_page'=>1)))->setAttr('width','100%');
	}
}
