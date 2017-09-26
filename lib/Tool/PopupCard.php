<?php

namespace xepan\commerce;

class Tool_PopupCard extends \xepan\cms\View_Tool{
	public $options = ['show_popup'=>true,
					   'show_image'=>true,
					   'show_form'=>true
					];

	function init(){
		parent::init();

		$card_m = $this->add('xepan\commerce\Model_Card');
		$card_m->addCondition('status','Active');
		$card_m->tryLoadAny();

		if(!$card_m->loaded()){
			$this->options['show_popup'] = false;
		}

		// $value = isset($_COOKIE['xepan_popupcard']);

		// if($value)
		// 	$this->options['show_popup'] = false;

		if($this->options['show_form']){			
			$form = $this->add('Form',null,'form');
			$form->setLayout('view/tool/form/popupcard');
			$form->addField('name')->addClass('form-control');
			$form->addField('email')->addClass('form-control');
			$form->addSubmit('Submit');

			if($form->isSubmitted()){
				if($form['name'] == '')
					return $this->js()->univ()->alert('Fill your name')->execute();

				if(!filter_var(trim($form['email']), FILTER_VALIDATE_EMAIL))
					return $this->js()->univ()->alert('Enter a valid email id')->execute();

				$ei = $this->add('xepan\base\Model_Contact_Email');
				$ei->tryLoadBy('value',$form['email']);

				if($ei->loaded()){
					$l_id = $ei['contact_id'];
					$l_model = $this->add('xepan\marketing\Model_Lead')->load($l_id);
					$cat_arr = $l_model->getAssociatedCategories();
					if(in_array('5595', $cat_arr))
						return $this->js(true,$form->js()->univ()->errorMessage('Already Subscribed'))->_selector('#'.$this->name."_card_model")->modal('hide')->execute();
						// return $form->js()->univ()->errorMessage('Already Subscribed')->execute();
					

					$category_m = $this->add('xepan\marketing\Model_MarketingCategory');
					$category_m->loadBy('name','Online Subscriptions');

					$association = $this->add('xepan\marketing\Model_Lead_Category_Association');
					$association->addCondition('lead_id',$l_model->id);
					$association->addCondition('marketing_category_id',$category_m->id);  
					$association->tryLoadAny();

					if(!$association->loaded()){
						$association['created_at'] = $this->app->now;  
						$association->save();
					}

					return $this->js(true,$form->js()->univ()->successMessage(' Subscribed'))->_selector('#'.$this->name."_card_model")->modal('hide')->execute();
				}				

				$lead = $this->add('xepan\marketing\Model_Lead');				
				try{
					
					$this->api->db->beginTransaction();
					$lead['source'] = 'Subscription';
					
					$lead['first_name'] = $form['name'];
					
					$lead->save();

					$category_m = $this->add('xepan\marketing\Model_MarketingCategory');
					$category_m->loadBy('name','Online Subscriptions');

					$assoc = $this->add('xepan\marketing\Model_Lead_Category_Association');
					$assoc['lead_id'] = $lead->id;
					$assoc['marketing_category_id'] = $category_m->id;  
					$assoc['created_at'] = $this->app->now;  
					$assoc->save();

					$email_info = $this->add('xepan\base\Model_Contact_Email');
					$email_info['contact_id'] = $lead->id;
					$email_info['head'] = 'Official';
					$email_info['value'] = $form['email'];
					$email_info->save();
					$this->api->db->commit();
				}catch(\Exception $e){
					throw $e;
					$this->api->db->rollback();	    			
	    			return $form->error('email','Please try again');
	    		}
	    			    													    												
				return $this->js(true,$form->js()->univ()->successMessage(' Subscribed'))->_selector('#'.$this->name."_card_model")->modal('hide')->execute();	
			}
		}

		if($this->options['show_image']){
			$this->template->trySet('image',$card_m['image']);
			$this->template->trySet('url',$card_m['link']);
		}
		
		setcookie('xepan_popupcard',true);
	}

	function render(){
		if($this->options['show_popup'])
			$this->js(true)->_selector('#'.$this->name."_card_model")->modal('show');
		
		parent::render();
	}

	function defaultTemplate(){
		return ['view\tool\card'];
	}
}