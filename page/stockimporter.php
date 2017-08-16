<?php

namespace xepan\commerce;

class page_stockimporter extends \xepan\base\Page{
	public $title = "Stock | Price | Item [Import/Export]";
	
	function init(){
		parent::init();

		ini_set('max_execution_time', 600);

		$tabs = $this->add('Tabs');
		$stock_tab = $tabs->addTab('Stock');
		$price_tab = $tabs->addTab('Price');
		$item_tab = $tabs->addTab('Item');

		$item_stock_m = $stock_tab->add('xepan\commerce\Model_ItemStock');

		$crud = $stock_tab->add('xepan\base\CRUD');
		$crud->setModel($item_stock_m,['item','size','current_stock']);
		
		$crud->grid->addQuickSearch(['item','current_stock','size']);
		$crud->grid->addPaginator('50');

		$import_btn = $crud->grid->addButton('Import/Export CSV')->addClass('btn btn-primary');
		$import_btn->setIcon('ui-icon-arrowthick-1-n');
		$import_btn->js('click')->univ()->frameURL('Import CSV',$this->app->url('xepan_commerce_import'));		
		
		$price_crud = $price_tab->add('xepan\base\CRUD',['allow_add'=>false]);
		$price_crud->setModel('xepan\commerce\Model_Item_Quantity_Set',['item','name','price']);
		
		$price_crud->grid->addQuickSearch(['item','price','name']);
		$price_crud->grid->addPaginator('50');

		$price_import_btn = $price_crud->grid->addButton('Import/Export CSV')->addClass('btn btn-primary');
		$price_import_btn->setIcon('ui-icon-arrowthick-1-n');
		$price_import_btn->js('click')->univ()->frameURL('Import CSV',$this->app->url('xepan_commerce_priceimport'));				
	
		$item_tab->add('xepan\commerce\page_item');
	}
}