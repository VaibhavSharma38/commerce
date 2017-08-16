<?php
 
namespace xepan\commerce;

class page_category extends \xepan\base\Page {
	public $title='Category';

	function init(){
		parent::init();

		$vp = $this->add('VirtualPage');

		$category_model = $this->add('xepan\commerce\Model_Category');
		$category_model->add('xepan\commerce\Controller_SideBarStatusFilter');
		
		$crud = $this->add('xepan\hr\CRUD',
							null,
							null,
							['view/item/category']
						);

		if($crud->isEditing()){
			$crud->form->setLayout('view\form\category');
			
		}

		$crud->setModel($category_model);

		if($crud->isEditing()){
			$parent_field = $crud->form->getElement('parent_category');
			$parent_field->setAttr(['multiple'=>'multiple']);

		   if($crud->model->id){
				$cat = $this->add('xepan\commerce\Model_Category')->load($crud->model->id);
				$temp = [];
				$temp = explode(',', $cat['parent_category']);
				
				$crud->form->getElement('parent_category')->set($temp)->js(true)->trigger('changed');
			}
		}

		$crud->grid->addPaginator(50);
		$crud->add('xepan\base\Controller_Avatar');
		$crud->add('xepan\base\Controller_MultiDelete');
		

		$crud->grid->addHook('formatRow',function($g){			
			$arr = explode(',', $g->model['parent_category']);
			
			$cat_m = $this->add('xepan\commerce\Model_Category');
			$cat_m->addCondition('id',$arr);

			$new_arr = []; 		
			foreach ($cat_m as $m) {
				$new_arr [] = $m['name'];
			}

			$g->current_row_html ['parent_category'] = implode(', ', $new_arr);
		});

		$frm=$crud->grid->addQuickSearch(['name']);
		
	}
}



























// <?php
//  namespace xepan\commerce;
//  class page_customerprofile extends \Page{
//  	public $title='Customer';

// 	function init(){
// 		parent::init();
// 	}

// 	function defaultTemplate(){

// 		return['page/customerprofile'];
// 	}
// }