<?php

namespace xepan\commerce;

class Tool_ChatBot extends \xepan\cms\View_Tool{
	
	function init(){
		parent::init();
		
		return;
		$icon_view = $this->add('view',null,'icon',['view\tool\icon']);

		$icon_view->on('click','.fa-commenting',function($js,$data)use($icon_view){		
			$js_array = [
				$icon_view->js(true,$this->js()->_selector("div.toshow")->show())->hide()
				];
			return $js_array;
		});

		$this->on('click','.show_icon',function($js,$data)use($icon_view){		
			$js_array = [
				$icon_view->js(true,$this->js()->_selector("div.toshow")->hide())->show()
				];
			return $js_array;
		});

		$form = $this->add('form',null,'form');
		$form->setLayout('view\tool\form\chat');
		$form->addField('message');
		$form->addSubmit('Send');


		$message_grid = $this->add('xepan\commerce\View_Messages',null,'message_lister');

		if($form->isSubmitted()){
			return $form->js(null,$message_grid->js()
											->reload(
												[
													'message' => $form['message'],
													'flag' => 1
												]))->univ()->reload()->execute();
		}
	}

	function defaultTemplate(){
		return ['view\tool\chatbot'];
	}
}