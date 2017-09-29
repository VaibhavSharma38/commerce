<?php

namespace xepan\commerce;

Class page_test1 extends \xepan\base\Page{

	function init(){
		parent::init();		
		
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
	}
}