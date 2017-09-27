<?php

namespace xepan\commerce;

class page_itemimport extends \xepan\base\Page{
	function init(){
		parent::init();

		ini_set('max_execution_time', 600);
		$form = $this->add('Form');
		$form->addSubmit('Export Current Item');
		
		if($_GET['download_sample_csv_file']){
			$output = ['sku','description','hide_in_product','hide_in_shop','category','style','construction','design','color','size','shape','material','features','hsn_code'];

			$output = implode(",", $output);
	    	header("Content-type: text/csv");
	        header("Content-disposition: attachment; filename=\"sample_xepan_item_import.csv\"");
			header('Pragma: no-cache');
			header('Expires: 0');
	        
			$file = fopen('php://output', 'w');
	        
			fputcsv($file, array('sku','description','hide_in_product','hide_in_shop','category','collection','style','construction','design','color','color family','standard size','shape','material','pile height','features','hsn_code'));
	        
	        $item_m = $this->add('xepan\commerce\Model_Item');

	        $data = [];
	        foreach ($item_m as $item) {	
        		// find construction
				$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Construction');

				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);

				$model_cf_value->tryLoadAny();
				$construction = $model_cf_value['name'];					   

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();

        		// design
				$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Design');

				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);

				$model_cf_value->tryLoadAny();
				$design = $model_cf_value['name'];

        		// color
				$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Color');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);


				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();

				$model_cf_value->tryLoadAny();
				$color = $model_cf_value['name'];

				// COLOR FAMILY
				$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
        		$spec_m->loadBy('name',' Color');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
  
				$model_cf_value->tryLoadAny();
				$color_family = $model_cf_value['name'];	

        		// material
	        	$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
        		$spec_m->loadBy('name','Material');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
  
				$model_cf_value->tryLoadAny();
				$material = $model_cf_value['name'];	
        		
        		// features
	        	$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
        		$spec_m->loadBy('name','Features');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
  
				$model_cf_value->tryLoadAny();
				$features = $model_cf_value['name'];	

				// size
	        	$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Standard Size');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
  
				$model_cf_value->tryLoadAny();
				$size = $model_cf_value['name'];	

        		// find shape
	        	$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Shape');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);
				$model_cf_value->tryLoadAny();
				$shape = $model_cf_value['name'];					   

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
	        	
	        	// find style
	        	$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Style');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);
				$model_cf_value->tryLoadAny();
				$style = $model_cf_value['name'];					   

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
	        	
	        	// find material
	        	$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Material');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);
				$model_cf_value->tryLoadAny();
				$material = $model_cf_value['name'];					   

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();
    			
				// PILE HEIGHT
				$spec_m = $this->add('xepan\commerce\Model_Item_Specification');
				$spec_m->loadBy('name','Pile Height');
				
				$model_cf_asso = $this->add('xepan\commerce\Model_Item_CustomField_Association');
				$model_cf_asso->addCondition('customfield_generic_id',$spec_m->id);
				$model_cf_asso->addCondition('item_id',$item_m->id);
				$model_cf_asso->tryLoadAny();

				$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value')
									   ->addCondition('customfield_association_id', $model_cf_asso->id);
				$model_cf_value->tryLoadAny();
				$pile_height = $model_cf_value['name'];					   

				$spec_m->unload();
				$model_cf_asso->unload();
				$model_cf_value->unload();


	        	// CATEGORY
	        	$assoc_m = $this->add('xepan\commerce\Model_CategoryItemAssociation');
	        	$assoc_m->addCondition('item_id',$item->id);

	        	$category_name_array = [];
	        	$collection_name_array = [];
	        	foreach ($assoc_m as $assoc) {
	        		$cat_m = $this->add('xepan\commerce\Model_Category');
	        		$cat_m->load($assoc['category_id']);
	        		
	        		if($cat_m['is_collection'])
	        			$collection_name_array [] = $cat_m['name'];
	        		else
	        			$category_name_array [] = $cat_m['name'];
	        	}

	        	$category = implode(',',$category_name_array);
	        	$collection = implode(',',$collection_name_array);
        		$data [] = [$item['sku'],$item['description'],$item['hide_in_product'],$item['hide_in_shop'],$category,$collection,$style,$construction,$design,$color,$color_family,$size,$shape,$material,$pile_height,$features,$item['hsn_no']];		        		
	        }
	        
			foreach ($data as $row)
			    fputcsv($file, $row);
			 
			exit();	     
		}

		if($form->isSubmitted()){
			$form->js()->univ()->newWindow($form->app->url('xepan_commerce_itemimport',['download_sample_csv_file'=>true]))->execute();
		}

		$this->add('View')->setElement('iframe')->setAttr('src',$this->api->url('xepan_commerce_itemimportexecute',array('cut_page'=>1)))->setAttr('width','100%');
	}
}