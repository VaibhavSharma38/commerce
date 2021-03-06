<?php

namespace xepan\commerce;

class Model_Store_TransactionAbstract extends \xepan\base\Model_Table{
	public $table="store_transaction";
	public $types = [
						'Opening',
						'Purchase',
						'Purchase_Return',
						'Consumption_Booked',
						'Consumed',
						'ToReceived',
						'Received',//from production
						'Adjustment_Add',
						'Adjustment_Removed',
						'Movement',
						'Issue',
						'Issue_Submitted',
						'Sales_Return',
						'Store_DispatchRequest',
						'Store_Delivered',
						'Store_Transaction',
						'MaterialRequestSend',
						'MaterialRequestDispatch'
					];
	function init(){
		parent::init();

		// $this->hasOne('xepan\base\Epan','epan_id');
		$this->hasOne('xepan\commerce\Store_Warehouse','from_warehouse_id');
		$this->hasOne('xepan\commerce\Store_Warehouse','to_warehouse_id');
		$this->hasOne('xepan\production\Jobcard','jobcard_id');
		$this->hasOne('xepan\hr\Department','department_id');
		$this->addField('type'); //Store_DispatchRequest, Store_Delivered, Store_Transaction, MaterialRequest
		$this->addCondition('type',$this->types);

		$this->addField('related_document_id')->sortable(true); //Sale Ordre/Purchase
		// $this->addField('document_type'); //Purchase/Sale/Dispatch/Deliver
		$this->addField('created_at')->defaultValue(date('Y-m-d H:i:s'))->sortable(true);
		$this->addField('created_by_id')->defaultValue($this->app->employee->id)->sortable(true);
		$this->addField('status')->enum($this->status)->sortable(true);

		//Delivered Option or shipping tracking code
		$this->addField('delivery_via');
		$this->addField('delivery_reference');
		$this->addField('shipping_address')->type('text');
		$this->addField('shipping_charge')->type('money');
		$this->addField('narration')->type('text');
		$this->addField('tracking_code')->type('text');
		$this->addField('related_transaction_id')->type('Number');
		$this->addHook('beforeDelete',[$this,'deleteAllTransactionRow']);

		$this->hasMany('xepan\commerce\Store_TransactionRow','store_transaction_id',null,'StoreTransactionRows');
		$this->hasMany('xepan\commerce\Store_TransactionRowCustomFieldValue','store_transaction_id',null,'StoreTransactionRowsCustomField');
		
		$this->addExpression('item_quantity')->set(function($m,$q){
			$to_received = $m->refSQL('StoreTransactionRows')
							->sum('quantity');
			return $q->expr("IFNULL ([0], 0)",[$to_received]);
		})->sortable(true);

		$this->addExpression('toreceived')->set(function($m,$q){
			$to_received = $m->refSQL('StoreTransactionRows')
							->addCondition('status','ToReceived')
							->sum('quantity');
			return $q->expr("IFNULL ([0], 0)",[$to_received]);
		})->sortable(true);	

		$this->addExpression('received')->set(function($m,$q){
			$to_received = $m->refSQL('StoreTransactionRows')
							->addCondition('status','Received')
							->sum('quantity');
			return $q->expr("IFNULL ([0], 0)",[$to_received]);
		})->sortable(true);

		$this->addExpression('department')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)', [$m->refSQL('jobcard_id')->fieldQuery('department')]);
		});


		$this->addExpression('jobcard_item')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('jobcard_id')->fieldQuery('order_item_name')]);
		});
		
		$this->addExpression('related_contact_id')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->add('xepan\commerce\Model_SalesOrder')
					->addCondition('id',$m->getElement('related_document_id'))
					->fieldQuery('contact_id')]);
		});

		$this->addExpression('contact_name')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$this->add('xepan\base\Model_Contact')
					->addCondition('id',$m->getElement('related_contact_id'))
					->fieldQuery('name')]);

		});
		$this->addExpression('organization')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$this->add('xepan\base\Model_Contact')
					->addCondition('id',$m->getElement('related_contact_id'))
					->fieldQuery('organization')]);

		});

		$this->addExpression('organization_name',function($m,$q){
			return $q->expr('IF(ISNULL([organization]) OR trim([organization])="" ,[contact_name],[organization])',
						[
							'contact_name'=>$m->getElement('contact_name'),
							'organization'=>$m->getElement('organization')
						]
					);
		});

		$this->addExpression('related_document_no')->set(function($m,$q){
			$sales_order =  $m->add('xepan/commerce/Model_QSP_Master',['table_alias'=>'order_no']);
			$sales_order->addCondition('id',$m->getElement('related_document_id'));
			// return $sales_order->fieldQuery('document_no');
			return $q->expr('IFNULL([0],0)',[$sales_order->fieldQuery('document_no')]);
		})->sortable(true);
	}

	function deleteAllTransactionRow(){			
		$this->ref('StoreTransactionRows')->each(function($o){
			$o->delete();
		});
	}
	
	function fromWarehouse($warehouse=false){
		if($warehouse)
			$this['from_warehouse_id'] = $warehouse->id;
		else
			return $this->ref('from_warehouse_id');
	}

	function toWarehouse($warehouse=false){
		if($warehouse)
			$this['to_warehouse_id'] = $warehouse->id;
		else
			return $this->ref('to_warehouse_id');
	}

	function transactionRow(){
		$this->ref('StoreTransactionRows');
	}

	function convertCFKeyToArray($key){
		$return_array = [];
		$temp = explode("||", $key);
		
		foreach ($temp as $cf_v_str) {
			if(!$cf_v_str)
				continue;

			$cf_v_array = explode("<=>", $cf_v_str);
			
			if(!$cf_v_array)
				continue;

			$cf_str = $cf_v_array[0];
			$cf_value_str = $cf_v_array[1];
			
			$cf_array = explode("~", $cf_str);
			$cf_value_array = explode("~", $cf_value_str);

			$return_array[$cf_array[0]] = [
										'custom_field_id'=>$cf_array[0],
										'custom_field_name'=>$cf_array[1],
										'custom_field_value_id'=>$cf_value_array[0],
										'custom_field_value_name'=>$cf_value_array[1]
									];
		}

		return $return_array;				
	}

	function addItem($qsp_detail_id=null,$item_id=null,$qty,$jobcard_detail_id,$custom_field_combination=null,$status="ToReceived",$item_qty_unit_id=null,$qsp_detail_unit_id=null,$check_unit_conversion=true,$serial_no=[]){
		$cf = [];
		if($custom_field_combination)
			$cf = $this->convertCFKeyToArray($custom_field_combination);
		
		if(!$this->loaded()){
			throw new \Exception("Store Transaction Model must loaded");
		}
				
		if($check_unit_conversion){
			// load item model for it's quantity unit if item_qty_unit_id not passed
			if(!$item_qty_unit_id AND $item_id > 0){
				$item_model = $this->add('xepan\commerce\Model_Item')->load($item_id);
				$item_qty_unit_id = $item_model['qty_unit_id'];
			}

			// load qsp_ddetail model for it's quantity unit if qsp_detail_unit_id not passed
			if(!$qsp_detail_unit_id AND $qsp_detail_id > 0){
				$qsp_detail_model = $this->add('xepan\commerce\Model_QSP_Detail')->load($qsp_detail_id);
				$qsp_detail_unit_id = $qsp_detail_model['qty_unit_id'];
			}

			//if item unit and ordered unit not same then unit conversion
	        if($item_qty_unit_id > 0 && $qsp_detail_unit_id > 0){
		        if($item_qty_unit_id != $qsp_detail_unit_id){
		          $qty = $this->app->getConvertedQty($item_qty_unit_id,$qsp_detail_unit_id,$qty);
		        }
	        }
		}
        
		$new_item = $this->add('xepan\commerce\Model_Store_TransactionRow');
		$new_item['store_transaction_id'] = $this->id;
		$new_item['qsp_detail_id'] = $qsp_detail_id;
		$new_item['item_id'] = $item_id;
		$new_item['quantity'] = $qty;
		$new_item['jobcard_detail_id'] = $jobcard_detail_id;
		$new_item['status'] = $status;
		$new_item['extra_info'] = $custom_field_combination;
		$new_item->save();		

		foreach ($cf as $custom_field_id => $cf_array) {
			if(!is_array($cf_array)) continue;
		 	$m = $this->add('xepan\commerce\Model_Store_TransactionRowCustomFieldValue');
			$m['customfield_generic_id'] = $custom_field_id; 
			$m['customfield_value_id']= $cf_array['custom_field_value_id']; 
			$m['custom_name'] = $cf_array['custom_field_name'];
			$m['custom_value'] = $cf_array['custom_field_value_name'];
			$m['store_transaction_row_id'] = $new_item->id;
			$m->save();
		}

		/*Serializable Start*/
		if($serial_no){
			$code = preg_replace('/\n$/','',preg_replace('/^\n/','',preg_replace('/[\r\n]+/',"\n",$serial_no)));
	        $serial_no_array = [];
	        if(strlen($code))
	        	$serial_no_array = explode("\n",$code);

			foreach ($serial_no_array as $key => $value) {
				$serial_model = $this->add('xepan\commerce\Model_Item_Serial')
									->addCondition('item_id',$item_id)
									->addCondition('serial_no',$value)
									->tryLoadAny();
				$serial_model['is_available'] =false;
				$serial_model['sale_order_detail_id'] = $new_item['qsp_detail_id'];
				$serial_model['dispatch_id'] = $this->id;
				$serial_model['dispatch_row_id'] = $new_item->id;
				$serial_model->save();

			}
		}
		/*Serializable Finish*/

		return $this;
	}

	function saleOrder(){
		if(!$this->loaded()){
			throw new \Exception("Transaction Not Loaded");
		}

		$sale_order = $this->add('xepan\commerce\Model_SalesOrder');
		$sale_order->addCondition('id',$this['related_document_id']);
		$sale_order->tryLoadAny();
		if(!$sale_order->loaded())
			return false;

		return $sale_order;
	}
}