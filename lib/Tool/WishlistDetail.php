<?php

namespace xepan\commerce;

class Tool_WishlistDetail extends \xepan\cms\View_tool{
	public $options = [];

	function init(){
		parent::init();
		

		if(!$this->app->auth->isLoggedIn()){				
			return $this->js(true)->redirect($this->app->url('login'));
		}

		if($this->app->auth->model->loaded()){
			$contact_m = $this->add('xepan\base\Model_Contact');
			$contact_m->loadBy('user_id',$this->app->auth->model->id);
			
			$wishlist_m = $this->add('xepan\commerce\Model_Wishlist');
			$wishlist_m->addCondition('customer_id',$contact_m->id);

			$item_wish_j = $wishlist_m->join('item.document_id','item_id');
			$item_wish_j->addField('name');
			

			$wishlist_m->addExpression('item_image')->set(function($m,$q){
				$image_m = $this->add('xepan\commerce\Model_Item_Image');
				$image_m->addCondition('item_id',$m->getElement('item_id'));
				$image_m->setLimit(1);
				return $image_m->fieldQuery('thumb_url');
			});

			$grid = $this->add('xepan\base\Grid',null,null,['view\tool\wishlistdetail']);
			$grid->setModel($wishlist_m);
		}

		$grid->on('click','.remove-from-wishlist',function($js,$data)use($grid){
			$wishlist_m = $this->add('xepan\commerce\Model_Wishlist');
			$wishlist_m->load($data['id']);
			$wishlist_m->delete();

			$js_array = [
				// z$grid->js()->reload(),
				$js->univ()->successMessage('Successfully Removed')
				];
			return $js_array;
		});
	}
}