<?php

namespace xepan\commerce;

class Tool_CurrentOpening extends \xepan\cms\View_Tool{
	public $options = [];

	function init(){
		parent::init();
		
		$vp = $this->add('VirtualPage');
		$vp->set(function($p){			
			$current_opening_m = $this->add('xepan\commerce\Model_CurrentOpening');
			$current_opening_m->load($_GET['job_id']);

			$p->add('View')->setHTML($current_opening_m['description']);
		});

		$current_opening_model = $this->add('xepan\commerce\Model_CurrentOpening');
		$current_opening_model->addCondition('status','Active');

		$grid = $this->add('xepan\base\Grid',null,null,['view\tool\currentopening']);	
		$grid->setModel($current_opening_model,['post_name','experience_required','location']);
		
		$grid->js('click')->_selector('.current-opening-detail')->univ()->frameURL('Job Description',[$vp->getUrl(),'job_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);	
		$grid->js('click')->_selector('.apply-for-job')->univ()->redirect('apply-for-job');
	}
}