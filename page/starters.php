<?php

namespace xepan\commerce;

class page_starters extends \xepan\base\Page{
	public $title = 'Starters';

	function init(){
		parent::init();
	}

	function defaultTemplate(){
		return ['view\starter'];
	}
}





