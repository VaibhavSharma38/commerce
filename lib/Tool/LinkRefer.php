<?php

namespace xepan\commerce;

class Tool_LinkRefer extends \xepan\cms\View_Tool{
	public $options = [];

	function init(){
		parent::init();

		// $this->template->trySet('subject','Hey ! look, saraswatii global has amazing rugs');
		// $this->template->trySet('body',$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	}

	function defaultTemplate(){
		return['view\tool\linkrefer'];
	}
}