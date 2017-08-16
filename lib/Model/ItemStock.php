<?php

namespace xepan\commerce;

class Model_ItemStock extends \xepan\base\Model_Table{
	public $table = "item_stock";

	function init(){
		parent::init();
		
		$this->hasOne('xepan\commerce\Item','item_id');

		$this->addField('created_at')->type('datetime');
		$this->addField('current_stock');
		$this->addField('size');
		$this->addField('category');
	}

	function importStock($data){

		$this->add('xepan\commerce\Model_ItemStock')->deleteAll();

		foreach ($data as $key => $record) {
			try{
				$this->api->db->beginTransaction();

				$item_stock_m = $this->add('xepan\commerce\Model_ItemStock');
				$not_found_array = [];
				foreach ($record as $field => $value) {
					$field = trim($field);
					$value = trim($value);

					if($field == "sku" && $value){
						$item_m = $this->add('xepan\commerce\Model_Item');
						$item_m->addCondition('sku',$value);
						$item_m->tryLoadAny();

						if(!$item_m->loaded())
							continue;

						$item_id = $item_m->id;
						$item_stock_m['item_id'] = $item_m->id;			


					}

					if($field == "stock" && $value){
						$current_stock = $value;
						$item_stock_m['current_stock'] = $value;
					}	

					if($field == "category" && $value){						
						$cat_m = $this->add('xepan\commerce\Model_category');
						$cat_m->tryLoadBy('name',$value);

						if(!$cat_m->loaded()){
							continue;
						}
						else{
							$item_stock_m['category'] = $cat_m->id;
							
							$cat_asso = $this->add('xepan\commerce\Model_CategoryItemAssociation');
							$cat_asso->addCondition('item_id',$item_id);
							$cat_asso->addCondition('category_id',$cat_m->id);
							$cat_asso->tryLoadAny();

							if(!$cat_asso->loaded())
								$cat_asso->save();
							
							$collection_id = $cat_m->findCollection($item_id);

							$parent_asso_m = $this->add('xepan\commerce\Model_CategoryParentAssociation');
							$parent_asso_m->addCondition('parent_category_id',$cat_m->id);	
							$parent_asso_m->addCondition('category_id',$collection_id);
							$parent_asso_m->tryLoadAny();

							if(!$parent_asso_m->loaded())	
								$parent_asso_m->save();

							$category_m = $this->add('xepan\commerce\Model_Category');
							$category_m->tryLoadBy('id',$collection_id);

							if($category_m->loaded()){
								$parent_array = explode(',', $category_m['parent_category']);

								if(!in_array($cat_m->id, $parent_array)){
									array_push($parent_array, $cat_m->id);
									$category_m['parent_category'] = implode(',', $parent_array);
									$category_m->save();
								}	
							}
						}
					}

					if($field == "size" && $value){						
						$size = $value;						
						
						$asso = $this->add('xepan\commerce\Model_Item_CustomField_Association')
									 ->addCondition('item_id',$item_id);

						$asso->addCondition('CustomFieldType','CustomField');
						$asso->addCondition('name','Size');
						$asso->tryLoadAny();

						$cf_value_m = $this->add('xepan\commerce\Model_Item_CustomField_Value');				
						$cf_value_m->addCondition('customfield_association_id',$asso->id);
						$cf_value_m->addCondition('name',$size);
						$cf_value_m->tryLoadAny();

						if(!$cf_value_m->loaded()){
							$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value');
							$model_cf_value->addCondition('customfield_association_id',$asso->id);
							$model_cf_value->addCondition('name',$size);
							$model_cf_value->tryLoadAny();
							$model_cf_value['status'] = "Active";
							$model_cf_value->save();
						}					

						$item_stock_m['size'] = $value;
					}

					$item_stock_m['created_at'] = $this->app->now;
					
					// try{
						if(!$item_stock_m['current_stock'] == '' && !$item_stock_m['size'] == '' && !$item_stock_m['sku'] == '')
							$item_stock_m->save();
					// }catch(\Exception $e){
					// 	continue;
					// }
				}

				if($item_stock_m->loaded())
					$item_stock_m->unload();

				$this->api->db->commit();
			}catch(\Exception $e){
				// echo $e->getMessage()."<br/>";
				// continue;
				throw $e;
				$this->api->db->rollback();
			}
		}
	}

	function consumeStock($item_id, $size, $item_qty){
		$item_stock_m = $this->add('xepan\commerce\Model_ItemStock');
		$item_stock_m->addCondition('item_id',$item_id);
		$item_stock_m->addCondition('size',$size);

		$item_stock_m->tryLoadAny();

		if($item_stock_m->loaded()){
			$item_stock_m['current_stock'] = $item_stock_m['current_stock'] - $item_qty;
			$item_stock_m->save();			
		}		
	}

	function getStock($item_id, $size){		
		$item_stock_m = $this->add('xepan\commerce\Model_ItemStock');
		$item_stock_m->addCondition('item_id',$item_id);
		$item_stock_m->addCondition('size',$size);

		$item_stock_m->tryLoadAny();

		if($item_stock_m->loaded()){
			return $item_stock_m['current_stock'];
		}else{
			return 0;
		}
	}
}