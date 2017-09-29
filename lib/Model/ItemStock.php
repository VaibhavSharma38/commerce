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

	function importStock($data){
		// deleting old values
		$this->add('xepan\commerce\Model_ItemStock')->deleteAll();
		
		// deleting item size custom field 
		$item_m = $this->add('xepan\commerce\Model_Item');

		foreach ($item_m as $item) {
			$asso = $this->add('xepan\commerce\Model_Item_CustomField_Association')
						 ->addCondition('item_id',$item->id);

			$asso->addCondition('CustomFieldType','CustomField');
			$asso->addCondition('name','Size');
			$asso->tryLoadAny();

			$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value');
			$model_cf_value->addCondition('customfield_association_id',$asso->id);
			
			foreach ($model_cf_value as $cf) {
				$cf->delete();
			}
		}

		// multi record loop
		foreach ($data as $key => $record) {
			try{
				$this->api->db->beginTransaction();

				$item_id = '';
				$item_stock = $this->add('xepan\commerce\Model_ItemStock');
				foreach ($record as $field => $value) {
					$field = strtolower(trim($field));
					$value = trim($value);

					// INSERTING ITEM ID
					if($field == "sku" && $value){
						$item_m = $this->add('xepan\commerce\Model_Item');
						$item_m->loadBy('sku',$value);
						$item_id = $item_m->id;
						$item_stock['item_id'] = $item_m->id;
						continue;
					}

					// FINDING AND ASSOCIATING CATEGORIES
					if($field == "category" && $value){
						// Finding category name from short code
						if($value == 'A')
							$category_name = 'Shop By Collection';
						if($value == 'B')	
							$category_name = 'Exclusive';
						if($value == 'C')
							$category_name = 'Clearance';
						
						// Removing old category
						$category_model = $this->add('xepan\commerce\Model_Category');
						$category_model->addCondition('name',['Shop By Collection','Exclusive','Clearance']);

						$category_array = [];
						foreach ($category_model as $cat){
							$category_array [] = $cat->id;
						}

						$associated_category = $this->add('xepan\commerce\Model_CategoryItemAssociation');		
						$associated_category->addCondition('item_id',$item_id);
						$associated_category->addCondition('category_id',$category_array);

						foreach ($associated_category as $ass_cat) {
							$ass_cat->delete();
						}

						// Adding category 
						$cat_m = $this->add('xepan\commerce\Model_category');
						$cat_m->tryLoadBy('name',$category_name);
						$item_stock['category'] = $cat_m->id;
						
						// Associating item with category if not already associated
						$cat_asso = $this->add('xepan\commerce\Model_CategoryItemAssociation');
						$cat_asso->addCondition('item_id',$item_id);
						$cat_asso->addCondition('category_id',$cat_m->id);
						$cat_asso->tryLoadAny();

						if(!$cat_asso->loaded())
							$cat_asso->save();
						

						$collection_id = $cat_m->findCollection($item_id);

						// Associating collection with category
						$parent_asso_m = $this->add('xepan\commerce\Model_CategoryParentAssociation');
						$parent_asso_m->addCondition('parent_category_id',$cat_m->id);	
						$parent_asso_m->addCondition('category_id',$collection_id);
						$parent_asso_m->tryLoadAny();

						if(!$parent_asso_m->loaded())	
							$parent_asso_m->save();

						// [Storing new values in category model parent_category field]
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
						continue;
					}

					// INSERTING SIZE
					if($field == "size" && $value){
						$asso = $this->add('xepan\commerce\Model_Item_CustomField_Association')
		 							 ->addCondition('item_id',$item_id);

						$asso->addCondition('CustomFieldType','CustomField');
						$asso->addCondition('name','Size');
						$asso->tryLoadAny();

						$cf_value_m = $this->add('xepan\commerce\Model_Item_CustomField_Value');				
						$cf_value_m->addCondition('customfield_association_id',$asso->id);
						$cf_value_m->addCondition('name',$value);
						$cf_value_m->tryLoadAny();

						if(!$cf_value_m->loaded()){
							$model_cf_value = $this->add('xepan\commerce\Model_Item_CustomField_Value');
							$model_cf_value->addCondition('customfield_association_id',$asso->id);
							$model_cf_value->addCondition('name',$value);
							$model_cf_value->tryLoadAny();
							$model_cf_value['status'] = "Active";
							$model_cf_value->save();
						}

						$item_stock['size'] = $value;
						continue;
					}

					if($field == "stock" && $value){
						$item_stock['current_stock'] = $value;
						continue;
					}

					$item_stock[$field] = $value;
				}

				$item_stock['created_at'] = $this->app->now;
				// try{
					$item_stock->save();
				// }catch(\Exception $e){
				// 	continue;
				// }

				$item_stock->unload();

				$this->api->db->commit();
			}catch(\Exception $e){
				// echo $e->getMessage()."<br/>";
				// continue;
				throw $e;
				// $this->api->db->rollback();
			}
		}
	}
}