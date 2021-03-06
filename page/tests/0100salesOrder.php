<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\commerce;


class page_tests_0100salesOrder extends \xepan\base\Page_Tester {
	
	public $title='Sales Order Importer';

	public $proper_responses=[
        'test_testEmptyRows'=>['master'=>0,'detail'=>0]
	];

	function init(){
        set_time_limit(0);
        // $this->add('xepan\commerce\page_tests_init')->resetDB();
        $this->pdb = $this->add('DB')->connect($this->app->getConfig('dsn2'));
        
        try{
            $this->app->db->dsql()->expr('SET FOREIGN_KEY_CHECKS = 0;')->execute();
            $this->app->db->dsql()->expr('SET unique_checks=0;')->execute();
            $this->app->db->dsql()->expr('SET autocommit=0;')->execute();

            $this->api->db->beginTransaction();
                parent::init();
            $this->app->db->dsql()->expr('SET FOREIGN_KEY_CHECKS = 1;')->execute();
            $this->app->db->dsql()->expr('SET unique_checks=1;')->execute();
            $this->api->db->commit();
        }catch(\Exception_StopInit $e){

        }catch(\Exception $e){
            $this->app->db->dsql()->expr('SET FOREIGN_KEY_CHECKS = 1;')->execute();
            $this->app->db->dsql()->expr('SET unique_checks=1;')->execute();
            $this->api->db->rollback();
            throw $e;
        }
        
    }

    function test_testEmptyRows(){
        return [
            'master'=>$this->api->db->dsql()->table('qsp_master')->del('fields')->field('count(*)')->getOne(),
            'detail'=>$this->api->db->dsql()->table('qsp_detail')->del('fields')->field('count(*)')->getOne()
        ];
    }

    private function getNewStatus($status){
        $mapping =[
        'draft'=>'Draft',
        'submitted'=>'Submitted',
        'approved'=>'Approved',
        'processing'=>'InProgress',
        'processed'=>'InProgress',
        'dispatched'=>'Dispatched',
        'complete'=>'Completed',
        'cancel'=>'Canceled',
        'return'=>'Dispatched',
        'redesign'=>'Redesign',
        'onlineunpaid'=>'OnlineUnpaid'];

        return $mapping[$status];
    }

    function prepare_importSalesOrder(){
        $old_m = $this->pdb->dsql()->table('xshop_orders')
                    ->get();

        $init_obj = $this->add('xepan\commerce\page_tests_init');
        $customer_mapping = $init_obj->getMapping('customer');
        $payg_mapping = $init_obj->getMapping('paymentgateway');
        $item_mapping = $init_obj->getMapping('item');
        $tax_mapping = $init_obj->getMapping('tax');
        $tnc_mapping = $init_obj->getMapping('tnc');

        $new_m = $this->add('xepan\commerce\Model_SalesOrder');
        $new_d_m = $this->add('xepan\commerce\Model_QSP_Detail');
        $new_d_m->removeHook('afterInsert');

        $details_count =0;
        $file_data=[];
        foreach ($old_m as $om) {
            $new_m['contact_id'] = $customer_mapping[$om['member_id']]['new_id'];
            $new_m['document_no'] = $om['name'];
            $new_m['billing_address'] = $customer_mapping[$om['member_id']]['address']?:'__';
            $new_m['billing_city'] = $customer_mapping[$om['member_id']]['city']?:'__';
            $new_m['billing_state'] = $customer_mapping[$om['member_id']]['state']?:'__';
            $new_m['billing_country'] = $customer_mapping[$om['member_id']]['country']?:'__';
            $new_m['billing_pincode'] = $customer_mapping[$om['member_id']]['pincode']?:'__';
            $new_m['shipping_address'] = $customer_mapping[$om['member_id']]['address']?:'__';
            $new_m['shipping_city'] = $customer_mapping[$om['member_id']]['city']?:'__';
            $new_m['shipping_state'] = $customer_mapping[$om['member_id']]['state']?:'__';
            $new_m['shipping_country'] = $customer_mapping[$om['member_id']]['country']?:'__';
            $new_m['shipping_pincode'] = $customer_mapping[$om['member_id']]['pincode']?:'__';
            $new_m['currency_id'] = $this->app->epan->default_currency->id;
            $new_m['discount_amount'] = 0;
            $new_m['search_string'] = $om['search_string'];
            $new_m['narration'] = '';
            $new_m['from'] = $om['order_from'];
            $new_m['priority_id'] = '';
            $new_m['due_date'] = date('Y-m-d',strtotime('+1 month',strtotime($om['created_at'])));
            $new_m['exchange_rate'] = '1';
            $new_m['tnc_id'] = $tnc_mapping[$om['termsandcondition_id']]['new_id']?:0;
            $new_m['tnc_text'] = $tnc_mapping[$om['termsandcondition_id']]['content'];
            $new_m['paymentgateway_id'] = $payg_mapping[$om['paymentgateway_id']]['new_id'];
            $new_m['status'] = $this->getNewStatus($om['status']);
            $new_m['created_at'] = $om['created_at'];
            $new_m['updated_at'] = $om['updated_at'];
            $new_m->save();

            // Order Items
            $old_m_2 = $this->pdb->dsql()->table('xshop_orderdetails')
                            ->where('order_id',$om['id'])
                            ->get();
            $details_count += count($old_m_2);
            foreach ($old_m_2 as $od) {
                $new_d_m['qsp_master_id']=$new_m->id;
                $new_d_m['item_id'] = $item_mapping[$od['item_id']]['new_id'];
                $new_d_m['taxation_id'] = $tax_mapping[$od['tax_id']]['new_id'];
                $new_d_m['price'] = $od['rate'];
                $new_d_m['quantity'] = $od['qty'];
                $new_d_m['tax_percentage'] = $tax_mapping[$od['tax_id']]['tax_percentage'];
                $new_d_m['shipping_charge'] = $od['shipping_charge'];
                $new_d_m['narration'] = $od['narration'];
                try{
                    $new_d_m['extra_info'] = $init_obj->parseCustomFieldsJSON($od['custom_fields']);
                }catch(\Exception $e){
                    $new_m['narration']='BAD JSON';
                    $new_m->save();
                }
                $new_d_m->saveAndUnload();
            }

            $file_data[$om['id']] = ['new_id'=>$new_m->id];
            $new_m->unload();
        }

        $this->proper_responses['test_importSalesOrder']=['master'=>count($old_m),'detail'=>$details_count];

        file_put_contents(__DIR__.'/salesorder_mapping.json', json_encode($file_data));
    }

    function test_importSalesOrder(){
        return [
            'master'=>$this->api->db->dsql()->table('qsp_master')->del('fields')->field('count(*)')->getOne(),
            'detail'=>$this->api->db->dsql()->table('qsp_detail')->del('fields')->field('count(*)')->getOne()
        ];
    }

}
