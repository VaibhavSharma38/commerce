<?php 
 namespace xepan\commerce;
 class page_customfield extends \xepan\commerce\page_configurationsidebar{

	public $title='Custom Fields';

	function init(){
		parent::init();
		$cf_model = $this->add('xepan\commerce\Model_Item_CustomField');
		$cf_model->setOrder('id','desc');
		$crud=$this->add('xepan\hr\CRUD','null',null,['view/item/customfield']);

		if($crud->isEditing()){
			$crud->form->setLayout('view\form\customfield');
		}

		$crud->setModel($cf_model,['name','display_type','sequence_order','is_filterable','value']);
		$crud->grid->addPaginator(25);
		$crud->add('xepan\base\Controller_MultiDelete');
		$frm=$crud->grid->addQuickSearch(['name']);
		$frm_drop=$frm->addField('DropDown','display_type')->setValueList(['Line'=>'Line','DropDown'=>'DropDown','Color'=>'Color'])->setEmptyText('Display Type');
		$frm_drop->js('change',$frm->js()->submit());

		$frm->addHook('applyFilter',function($frm,$m){
			if($frm['customfield_id'])
				$m->addCondition('customfield_id',$frm['customfield_id']);
		});
	}
} 