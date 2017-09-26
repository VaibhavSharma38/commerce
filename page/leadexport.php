<?php

namespace xepan\commerce;

class page_leadexport extends \xepan\base\Page{
	function init(){
		parent::init();

		ini_set('max_execution_time', 600);

		if($_GET['download_lead']){			
			$output = ['name','email'];

			$output = implode(",", $output);
	    	header("Content-type: text/csv");
	        header("Content-disposition: attachment; filename=\"lead_export.csv\"");
			header('Pragma: no-cache');
			header('Expires: 0');
	        
			$file = fopen('php://output', 'w');
		        
			fputcsv($file, array('name','email'));
	        
	        $lead_m = $this->add('xepan\marketing\Model_Lead');

	        $data = [];
	        foreach ($lead_m as $lead) {
	    		$data [] = [$lead['name'],$lead['emails_str']];		
	        }
		        
			foreach ($data as $row)
			    fputcsv($file, $row);
			exit();	  
		}
	}
}