<?php

namespace xepan\commerce;

class Tool_Wishlist extends \xepan\cms\View_Tool{
	public $reload_object;
	public $options = [];

	function init(){
		parent::init();
		
		$form = $this->add('Form');
		$form->addSubmit('Add To Wishlist')->setHTML('<span style ="font-size:16px;"><i class="glyphicon glyphicon-heart"></i></span> <br>Add To Wishlist')->addClass('wishlist-button');

		if($form->isSubmitted()){
			if(!$this->app->auth->isLoggedIn()){				
				$this->js()->redirect($this->app->url('login'))->execute();
			}

			if($this->app->auth->model->loaded() AND $_GET['commerce_item_id']){
				$contact_m = $this->add('xepan\base\Model_Contact');
				$contact_m->loadBy('user_id',$this->app->auth->model->id);
				
				$wishlist_m = $this->add('xepan\commerce\Model_Wishlist');
				$wishlist_m->addCondition('customer_id',$contact_m->id);
				$wishlist_m->addCondition('item_id',$_GET['commerce_item_id']);
				$wishlist_m->tryLoadAny();

				if(!$wishlist_m->loaded())
					$wishlist_m->save();
				else
					return $form->js()->univ()->errorMessage('Already In Wishlist')->execute();

				return $form->js()->univ()->successMessage('Added To Wishlist')->execute();
			}							
		}
	}
}