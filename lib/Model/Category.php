<?php

 namespace xepan\commerce;

 class Model_Category extends \xepan\hr\Model_Document{
 	public $status = ['Active','InActive'];
 	public $actions = [
 						'Active'=>['view','edit','delete','deactivate'],
 						'InActive'=>['view','edit','delete','activate']
 					];
	var $table_alias = 'category';

	function init(){
		parent::init();

		$cat_j=$this->join('category.document_id');


		$cat_j->addField('name')->sortable(true);
		$cat_j->addField('alt_text')->hint('set alt_text of image tag');
		$cat_j->addField('description')->display(['form'=>'xepan\base\RichText'])->type('text');

		$cat_j->addField('custom_link');
		$cat_j->addField('meta_title');
		$cat_j->addField('meta_description')->type('text');
		$cat_j->addField('meta_keywords');
		$cat_j->addField('is_website_display')->type('boolean');
		$cat_j->addField('is_for_shop')->type('boolean');
		$cat_j->addField('is_for_product')->type('boolean');
		$cat_j->addField('is_new')->type('boolean');
		$cat_j->addField('slug_url');

		$cat_j->addField('display_sequence')->type('int')->hint('change the sequence of category, sort by decenting order')->defaultValue(0);
		$cat_j->addField('collection_order')->type('int')->defaultValue(0);
		$cat_j->addField('exclusive_order')->type('int')->defaultValue(0);
		$cat_j->addField('clearance_order')->type('int')->defaultValue(0);
		$cat_j->addField('is_collection')->type('boolean');
		
		$cat_j->addField('parent_category')->display(['form'=>'xepan\base\DropDown'])->setModel('xepan\commerce\Model_Category');
				
		$this->add('xepan\filestore\Field_Image','cat_image_id')->display(['form'=>'xepan\base\Upload'])->from($cat_j);
		$this->add('xepan\filestore\Field_Image','exclusive_image_id')->display(['form'=>'xepan\base\Upload'])->from($cat_j);
		$this->add('xepan\filestore\Field_Image','clearance_image_id')->display(['form'=>'xepan\base\Upload'])->from($cat_j);
		
		$this->add('xepan\filestore\Field_Image','product_image_id')->display(['form'=>'xepan\base\Upload'])->from($cat_j);

		$cat_j->hasMany('xepan\commerce\Filter','category_id');
		$cat_j->hasMany('xepan\commerce\CategoryItemAssociation','category_id');		

		$this->addCondition('type','Category');
		// $this->addCondition('epan_id',$this->app->epan->get('id'));
		$this->getElement('status')->defaultValue('Active');
		
		// return count of saleable and and websites display item
		$this->addExpression('website_display_item_count')->set(function($m,$q){
				$cat_item_model = $m->add('xepan\commerce\Model_CategoryItemAssociation');
				$cat_item_j = $cat_item_model->leftJoin('item.document_id','item_id');
				$cat_item_j->addField('is_saleable');
				$cat_item_j->addField('website_display');

				$item_doc_j = $cat_item_j->join('document','document_id');
				$item_doc_j->addField('status');
				
				$cat_item_model->addCondition('status','Published');
				$cat_item_model->addCondition('is_saleable',1);
				$cat_item_model->addCondition('website_display',1);
				$cat_item_model->addCondition('is_template',0);
				$cat_item_model->addCondition('category_id',$m->getElement('id'));

				return $cat_item_model->count();
		})->sortable(true);

		$this->addExpression('min_price')->set(function($m,$q){
			return $q->expr("IFNULL([0],0)",[$m->refSQL('xepan\commerce\CategoryItemAssociation')->setOrder('sale_price','asc')->setLimit(1)->fieldQuery('sale_price')]);
		});

		$this->addExpression('max_price')->set(function($m,$q){
			return $q->expr("IFNULL([0],0)",[$m->refSQL('xepan\commerce\CategoryItemAssociation')->setOrder('sale_price','desc')->setLimit(1)->fieldQuery('sale_price')]);
		});

		$this->addExpression('effective_name',function($m,$q){
			// return "'ToDo'";
			return $q->expr("CONCAT([0],'   ',IFNULL([1],'None'),' ')",
					[
						$m->getElement('name'),
						"' '" 
					]);
		
		});

		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',[$this,'updateSearchString']);		
		$this->addHook('beforeSave',[$this,'associateParent']);		

		$this->is([
				'name|to_trim|required',
				'display_sequence|int'
			]);
	}
	
	function associateParent($m){		
		if($m['parent_category']){
			$this->removeOldAssociation($m->id);
			
			foreach (explode(',', $m['parent_category']) as $value) {
				$model = $this->add('xepan\commerce\Model_CategoryParentAssociation');
				$model['category_id'] = $m->id;		
				$model['parent_category_id'] = $value;		
				$model->save();
			}
		}
	}

	function removeOldAssociation($id){			
		$model = $this->add('xepan\commerce\Model_CategoryParentAssociation');
		$model->addCondition('category_id',$id);

		if($model->count()->getOne()){
			foreach ($model as $m) {
				$m->delete();
			}
		}
	}

	function nameExistInParent(){ //Check Duplicasy on Name Exist in Parent Category

		// $cat = $this->add('xepan\commerce\Model_Category');
		// $cat->addCondition('parent_category_id',$this['parent_category_id']?:null);
		// $cat->addCondition('name',$this['name']);
		// $cat->addCondition('id','<>',$this->id);
		// $cat->tryLoadAny();

		// return $cat->loaded();

		// return $this->ref('parent_category_id')->loaded()? 
		// $this->ref('parent_category_id')->ref('SubCategories')
		// 		->addCondition('name',$this['name'])
		// 		->addCondition('id','<>',$this->id)
				// ->tryLoadAny()->loaded(): false;
	}

	function updateSearchString($m){
		$search_string = ' ';
		$search_string .=" ". $this['name'];
		$search_string .=" ". $this['type'];
		$search_string .=" ". $this['status'];
		$search_string .=" ". $this['alt_text'];
		$search_string .=" ". $this['meta_title'];
		$search_string .=" ". $this['meta_description'];
		$search_string .=" ". $this['meta_keywords'];

		$this['search_string'] = $search_string;

		if($this->nameExistInParent())
			throw $this->Exception('Name Already Exist','ValidityCheck')->setField('name');
	}

	function quickSearch($app,$search_string,&$result_array,$relevency_mode){
		
		$this->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$this->addCondition('Relevance','>',0);
 		$this->setOrder('Relevance','Desc');
 		
 		if($this->count()->getOne()){
 			foreach ($this->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_category',['status'=>$data['status']])->getURL(),
 				];
 			}
		}

 		$item = $this->add('xepan\commerce\Model_Item');
 		$item->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$item->addCondition('Relevance','>',0);
 		$item->setOrder('Relevance','Desc');
 		
 		if($item->count()->getOne()){
 			foreach ($item->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_itemdetail',['status'=>$data['status'],'document_id'=>$data['id']])->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 					'quick_info'=>'Sale Price: '.$data['sale_price'].' Total Orders: '.$data['total_orders'],
 				];
 			}
		}

 		$customer = $this->add('xepan\commerce\Model_Customer');
 		$customer->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$customer->addCondition('Relevance','>',0);
 		$customer->setOrder('Relevance','Desc');
 		
 		if($customer->count()->getOne()){
 			foreach ($customer->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_customerdetail',['status'=>$data['status'],'contact_id'=>$data['id']])->getURL(),
 				];
 			}
		}

 		$supplier = $this->add('xepan\commerce\Model_Supplier');
 		$supplier->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$supplier->addCondition('Relevance','>',0);
 		$supplier->setOrder('Relevance','Desc');
 		
 		if($supplier->count()->getOne()){
 			foreach ($supplier->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_supplierdetail',['status'=>$data['status'],'contact_id'=>$data['id']])->getURL(),
 				];
 			}
		}

 		$master = $this->add('xepan\commerce\Model_QSP_Master');
 		$master->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" IN BOOLEAN MODE)');
		$master->addCondition('Relevance','>',0);
 		$master->setOrder('Relevance','Desc');

 		$details_j = $master->join('qsp_detail.qsp_master_id');
 		$details_j->hasOne('xepan\commerce\Item','item_id');
 		$details_j->addField('qsp_master_id');


 		$master->addExpression('item_list')->set(function($m,$q){
			return $q->expr('group_concat([0])',[$m->getElement('item')]);
		});

		$master->_dsql()->group('qsp_master_id');
 		
 		if($master->count()->getOne()){
 			foreach ($master->getRows() as $data) {	

 				if($data['type'] == 'Quotation')
    				$url = $this->app->url('xepan_commerce_quotationdetail',['action'=>'view','status'=>$data['status'],'document_id'=>$data['id']])->getURL();	
    			if($data['type'] == 'SalesOrder')
    				$url = $this->app->url('xepan_commerce_salesorderdetail',['action'=>'view','status'=>$data['status'],'document_id'=>$data['id']])->getURL();	
    			if($data['type'] == 'SalesInvoice')
    				$url = $this->app->url('xepan_commerce_salesinvoicedetail',['action'=>'view','status'=>$data['status'],'document_id'=>$data['id']])->getURL();	
    			if($data['type'] == 'PurchaseOrder')
    				$url = $this->app->url('xepan_commerce_purchaseorderdetail',['action'=>'view','status'=>$data['status'],'document_id'=>$data['id']])->getURL();	
    			if($data['type'] == 'PurchaseInvoice')
    				$url = $this->app->url('xepan_commerce_purchaseinvoicedetail',['action'=>'view','status'=>$data['status'],'document_id'=>$data['id']])->getURL();	 				 				

 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['document_no'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$url,
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 					'quick_info'=>'Total Amount: '.$data['total_amount'].' Item List: '.$data['item_list'],
 				];
 			}
		}


 		$tax = $this->add('xepan\commerce\Model_Taxation');
 		$tax->addExpression('Relevance')->set('MATCH(name, type) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$tax->addCondition('Relevance','>',0);
 		$tax->setOrder('Relevance','Desc');
 		
 		if($tax->count()->getOne()){
 			foreach ($tax->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_tax')->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 				];
 			}
		}

 		$tnc = $this->add('xepan\commerce\Model_TNC');
 		$tnc->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$tnc->addCondition('Relevance','>',0);
 		$tnc->setOrder('Relevance','Desc');
 		
 		if($tnc->count()->getOne()){
 			foreach ($tnc->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_tnc')->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 				];
 			}
		}

 		$warehouse = $this->add('xepan\commerce\Model_Store_Warehouse');
 		$warehouse->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$warehouse->addCondition('Relevance','>',0);
 		$warehouse->setOrder('Relevance','Desc');
 		
 		if($warehouse->count()->getOne()){
 			foreach ($warehouse->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_commerce_store_warehouse')->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 				];
 			}
		}
	}

	function activate(){
		$this['status'] = "Active";
		$this->app->employee
            ->addActivity("Item's Category : '".$this['name']."' Activated", $this->id/* Related Document ID*/, null /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}

	function deactivate(){
		$this['status'] = "InActive";
		$this->app->employee
            ->addActivity("Item's Category'". $this['name'] ."' Deactivated", $this->id /*Related Document ID*/, null /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}

	function beforeDelete($m){
		$this->ref('xepan\commerce\CategoryItemAssociation')->deleteAll();
	}

	function findCollection($item_id){
		$assoc_m = $this->add('xepan\commerce\Model_CategoryItemAssociation');
		$assoc_m->addCondition('item_id',$item_id);

		foreach ($assoc_m as $ass) {
			$cat_m = $this->add('xepan\commerce\Model_Category');
			$cat_m->load($ass['category_id']);

			if($cat_m['is_collection'])
				return $cat_m->id;
		}
	}
}