<?php

namespace xepan\commerce;


class page_import extends \xepan\base\Page{
	
	function init(){
		parent::init();
		
		ini_set('max_execution_time', 600);

		$form = $this->add('Form');
		$form->addSubmit('Export Current Stock');
		
		if($_GET['download_sample_csv_file']){
			$output = ['sku','size','stock','category'];

			$output = implode(",", $output);
	    	header("Content-type: text/csv");
	        header("Content-disposition: attachment; filename=\"sample_xepan_stock_import.csv\"");
			header('Pragma: no-cache');
			header('Expires: 0');
	        
			$file = fopen('php://output', 'w');
	        
			fputcsv($file, array('sku', 'size', 'stock','category'));
	        
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
					$stock_m = $this->add('xepan\commerce\Model_ItemStock');
					$stock_m->addCondition('item_id',$item->id);
					$stock_m->addCondition('size',$cf_val['name']);
					$stock_m->tryLoadAny();

					$current_stock = '';
					$category = '';

					if($stock_m->loaded()){
						$current_stock = $stock_m['current_stock'];
						
						$category_m = $this->add('xepan\commerce\Model_Category');
						$category_m->addCondition('id',$stock_m['category']);						
						$category_m->tryLoadAny();
						
						if($category_m->loaded())
							$category = $category_m['name'];
					}

	        		$data [] = [$item['sku'],$cf_val['name'],$current_stock,$category];		
				}
	        }
	        
			foreach ($data as $row)
			    fputcsv($file, $row);
			 
			exit();	        
		}

		if($form->isSubmitted()){
			$form->js()->univ()->newWindow($form->app->url('xepan_commerce_import',['download_sample_csv_file'=>true]))->execute();
		}

		$this->add('View')->setElement('iframe')->setAttr('src',$this->api->url('xepan_commerce_importexecute',array('cut_page'=>1)))->setAttr('width','100%');
	}
}
