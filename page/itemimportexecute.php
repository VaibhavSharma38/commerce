<?php

namespace xepan\commerce;

class page_itemimportexecute extends \xepan\base\Page{
	function init(){
		parent::init();

		ini_set('max_execution_time', 1000);

		$form= $this->add('Form');
		$form->template->loadTemplateFromString("<form method='POST' action='".$this->api->url(null,array('cut_page'=>1))."' enctype='multipart/form-data'>
			<input type='file' name='csv_stock_file'/>
			<input type='submit' value='Upload'/>
			</form>"
			);

		if($_FILES['csv_stock_file']){
									
			if ( $_FILES["csv_stock_file"]["error"] > 0 ) {
				$this->add( 'View_Error' )->set( "Error: " . $_FILES["csv_stock_file"]["error"] );
			}else{
				$mimes = ['text/comma-separated-values', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel', 'text/anytext'];
				if(!in_array($_FILES['csv_stock_file']['type'],$mimes)){
					$this->add('View_Error')->set('Only CSV Files allowed');
					return;
				}

				$importer = new \xepan\base\CSVImporter($_FILES['csv_stock_file']['tmp_name'],true,',');
				$data = $importer->get();

				$item_m = $this->add('xepan\commerce\Model_Item');
				$item_m->importItem($data);
			}
		}
	}	
}