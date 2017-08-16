<?php

namespace xepan\commerce;

class Tool_ItemEnquiry extends \xepan\cms\View_Tool{
	function init(){
		parent::init();

		$vp = $this->add('VirtualPage');
		$vp->set(function($p){
			$item_id = $this->app->stickyGET('item_id');
			
			$form = $p->add('Form');
			$form->setLayout(['view\tool\form\itemenquiry']);
			$form->addField('name');
			$form->addField('organization');
			$form->addField('email');
			$form->addField('contact_no');
			$form->addField('address');
			$form->addField('city');
			$form->addField('state');
			$form->addField('country');
			$form->addField('text','requirements');
			$form->addSubmit('Submit Enquiry');

			$item_m = $this->add('xepan\commerce\Model_Item');
			$item_m->load($item_id);
			
			$custom_fields = $item_m->activeAssociateCustomField();
			foreach ($custom_fields as $custom_field) {
				if($custom_field['name'] === 'Size'){
					$cf_field = $form->addField('xepan\commerce\DropDown','item_size', 'Rug Size');
					
					$cf_value_m = $this->add('xepan\commerce\Model_Item_CustomField_Value',['id_field'=>'name','title_field'=>'name']);				
					$cf_value_m->addCondition('customfield_association_id',$custom_field->id);

					// $cf_value_m->addExpression('stock_exist')->set(function($m,$q){
					// 	$item_stock_m = $this->add('xepan\commerce\Model_ItemStock');
					// 	$item_stock_m->addCondition('item_id',$m->getElement('item_id'));
					// 	$item_stock_m->addCondition('size',$m->getElement('name'));
					// 	return $item_stock_m->sum('current_stock');
					// });

					// $cf_value_m->addCondition('stock_exist','>',0);
					$cf_field->setEmptyText('Select any available size');
					$cf_field->setModel($cf_value_m);
				}
			}
		
			if($form->isSubmitted()){
				if($form['name'] == ''){
					$form->displayError('name','Name field is mandatory');
				}

				if (filter_var($form['email'], FILTER_VALIDATE_EMAIL) === false) {
					$form->displayError('email','Please type a valid email address');
				}

				if($form['contact_no'] == ''){
					$form->displayError('contact_no','Contact field is mandatory');
				}				

				$enquiry_m = $p->add('xepan\commerce\Model_ItemEnquiry');
				$enquiry_m['name'] = $form['name'];
				$enquiry_m['organization'] = $form['organization'];
				$enquiry_m['email'] = $form['email'];
				$enquiry_m['contact_no'] = $form['contact_no'];
				$enquiry_m['address'] = $form['address'];
				$enquiry_m['city'] = $form['city'];
				$enquiry_m['state'] = $form['state'];
				$enquiry_m['country'] = $form['country'];
				$enquiry_m['requirements'] = $form['requirements'];
				$enquiry_m['item_id'] = $_GET['item_id'];
				$enquiry_m['item_size'] = $form['item_size'];

				if($this->app->auth->model->id){
					$contact_m = $this->add('xepan\base\Model_Contact');
					$contact_m->loadBy('user_id',$this->app->auth->model->id);
					$enquiry_m['customer_id'] = $contact_m->id;
				}

				$enquiry_m->save();

				$form->js()->univ()->successMessage('Enquiry Send')->execute();
			}

		});

		$button = $this->add('Button')->set('Submit Enquiry')->setHTML('<span style ="font-size:16px;"><i class="glyphicon glyphicon-envelope"></i></span> <br>Submit Enquiry')->addClass('enquiry-button');
		
		$button->js('click',$this->js()->univ()->frameURL("Send Enquiry",$this->api->url($vp->getURL(),['item_id'=>$_GET['commerce_item_id']])))->_selector('.enquiry-button');
	}
}