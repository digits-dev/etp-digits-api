<?php

namespace App\Http\Controllers;

use App\Models\Reason;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

	class AdminReasonsController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "pullout_reason";
			$this->limit = "20";
			$this->orderby = "pullout_reason,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "reasons";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Transaction Type","name"=>"transaction_types_id","join"=>"transaction_types,transaction_type"];
			$this->col[] = ["label"=>"SO Reason","name"=>"bea_so_reason"];
			$this->col[] = ["label"=>"MO Reason","name"=>"bea_mo_reason"];
			$this->col[] = ["label"=>"Pullout Reason","name"=>"pullout_reason"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Transaction Type','name'=>'transaction_types_id','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-5','datatable'=>'transaction_types,transaction_type'];
			$this->form[] = ['label'=>'SO Reason','name'=>'bea_so_reason','type'=>'text','validation'=>'required|min:1|max:100','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'MO Reason','name'=>'bea_mo_reason','type'=>'text','validation'=>'required|min:1|max:100','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Pullout Reason','name'=>'pullout_reason','type'=>'text','validation'=>'required|min:1|max:100','width'=>'col-sm-5'];
			if(in_array(CRUDBooster::getCurrentMethod(),['getEdit','postEditSave','getDetail'])) {
				$this->form[] = ['label'=>'Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-5','dataenum'=>'ACTIVE;INACTIVE'];
			}
            # END FORM DO NOT REMOVE THIS LINE

	        $this->button_selected = array();
            if(CRUDBooster::isUpdate() && CRUDBooster::isSuperadmin()) {
                $this->button_selected[] = ['label'=>'Set Status ACTIVE','icon'=>'fa fa-check-circle','name'=>'set_status_active'];
				$this->button_selected[] = ['label'=>'Set Status INACTIVE','icon'=>'fa fa-times-circle','name'=>'set_status_inactive'];
            }
	    }

	    public function actionButtonSelected($id_selected,$button_name) {

	        $value = [
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => CRUDBooster::myId()
            ];

            switch ($button_name) {
                case 'set_status_inactive':
                    $value['status'] = 'INACTIVE';
                    break;
                case 'set_status_active':
                    $value['status'] = 'ACTIVE';
                    break;
                default:
                    break;
            }
            Reason::whereIn('id', $id_selected)->update($value);
	    }

	    public function hook_before_add(&$postdata) {
	        //Your code here
            $postdata['created_at'] = date('Y-m-d H:i:s');
			$postdata['created_by'] = CRUDBooster::myId();
	    }

	    public function hook_before_edit(&$postdata,$id) {
	        //Your code here
            $postdata['updated_at'] = date('Y-m-d H:i:s');
			$postdata['updated_by'] = CRUDBooster::myId();
	    }

	}
