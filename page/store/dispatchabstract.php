<?php

namespace xepan\commerce;

class page_store_dispatchabstract extends \xepan\base\Page{
	public $title="Dispatch Request Management";

	function init(){
		parent::init();

		$count_m = $this->add('xepan\commerce\Model_Store_TransactionRow');
		$counts = $count_m->_dsql()->del('fields')->field('status')->field('count(*) counts')->group('Status')->get();
		$counts_redefined =[];
		$total=0;
		foreach ($counts as $cnt) {
			$counts_redefined[$cnt['status']] = $cnt['counts'];
			$total += $cnt['counts'];
		}


		$order_dispatch_m = $this->add('xepan\commerce\Model_Store_OrderItemDispatch');
		$order_dispatch_m->addCondition('due_quantity','>',0);
		$order_dispatch_m->_dsql()->group('qsp_master_id');
		$total_order_to_dispatch = $order_dispatch_m->count()->getOne()?:0;

		$this->app->side_menu->addItem(['To Received','badge'=>[$counts_redefined['ToReceived']?:0,'swatch'=>' label label-primary label-circle pull-right']],$this->api->url("xepan_commerce_store_dispatchrequest"));
		$this->app->side_menu->addItem(['Dispatch','badge'=>[$total_order_to_dispatch,'swatch'=>' label label-primary label-circle pull-right']],$this->api->url("xepan_commerce_store_dispatch"));
	}
}