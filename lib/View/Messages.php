<?php

namespace xepan\commerce;

class View_Messages extends \xepan\base\Grid{
	public $message;
	public $count = 0;

	function init(){
		parent::init();

		$message_array = ['<strong>Hey there,</strong><br> <br><span><strong>Just type the letter to let us know what you want.</strong><br></span><br> <strong>a)</strong> I want to visit shop page <br> <strong>b)</strong> Tell me about saraswati global <br> <strong>c)</strong> I have a complaint/request <br> <strong>d)</strong> Connect me to support staff'];
		$side_array = ['left'];

		if($_COOKIE['customer_support_message']){
			$message_array = json_decode($_COOKIE['customer_support_message'],true);
		}

		if($_COOKIE['message_side']){
			$side_array = json_decode($_COOKIE['message_side'],true);
		}


		if($message = $_GET['message']){
			array_push($side_array, 'right');			
			array_push($message_array, $message);

			switch (trim(strtolower($message))) {
				case 'a':
					array_push($side_array, 'left');			
					array_push($message_array, '<a href="shop">Click to visit shop page</a>');
					
					break;
				case 'b':
					array_push($side_array, 'left');			
					array_push($message_array, 'As a vertically-integrated company, Saraswatii Global directly owns and controls the pillar facilities in rug production. Raw material sourcing and spinning (Bikaner), designing, dyeing, and finishing (Jaipur), are all regulated by Saraswatii’s expert team. As a result, Saraswatii is a brand synonymous with beauty and quality. Whether it be knotted, tufted, or woven, and whatever the fiber system, a buyer can rest easy in the fact that a premier floor covering has been purchased.<br><br> <strong><a href="ourstory">Read our story </a></strong>');
					
					break;
				case 'c':
					array_push($side_array, 'left');			
					array_push($message_array, 'Please mail your complaint or request at support@saraswatiglobal.com and one of our representative will soon get in touch with you');
					
					break;
				case 'd':
					array_push($side_array, 'left');			
					array_push($message_array, 'Sorry there is no support staff online right now.');
					
					break;
				
				default:
					array_push($side_array, 'left');			
					array_push($message_array, 'Opps ! there is no such option. Please try something else.');

					break;
			}

			$side_json  = json_encode($side_array);
			$message_json = json_encode($message_array);
			
			setcookie('message_side',$side_json);
			setcookie('customer_support_message',$message_json);
		}

		
		$this->setSource($message_array);
		

		$this->addHook('formatRow',function($g)use($side_array){			
			$g->current_row_html['message'] = $g->model['name'];			
			$g->current_row_html['side'] = $side_array[$this->count];
			$this->count++;
		});
	}

	function defaultTemplate(){
		return ['view\tool\messagelister'];
	}
}