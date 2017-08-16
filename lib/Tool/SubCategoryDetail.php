<?php
namespace xepan\commerce;

class Tool_SubCategoryDetail extends \xepan\cms\View_Tool{
	public $options = [
		'url_page' =>'index',
		"custom_template"=>'',
		'show_name'=>true,
		'show_price'=>false,
		'show_image'=>true,
		'show_product_image'=>false,
		'show_item_count'=>false,
		'show_only_new_product' =>false
	];

	function init(){
		parent::init();
		if($this->options['custom_template']){
			$path = getcwd()."/websites/".$this->app->current_website_name."/www/view/tool/".$this->options['custom_template'].".html";
			if(!file_exists($path)){
				$this->add('View_Warning')->set('template not found');
				return;	
			}	
		}else
			$this->options['custom_template'] = "subcategorydetaillister";

		$lister = $this->add('xepan\commerce\View_SubCategoryDetailLister',['options'=>$this->options]);
	
		$paginator = $lister->add('Paginator',['ipp'=>'15']);
		$paginator->setRowsPerPage(15);
	}
}