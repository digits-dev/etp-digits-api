<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

	class AdminOrderStatusesController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "order_status";
			$this->limit = "20";
			$this->orderby = "order_status,desc";
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
			$this->table = "order_statuses";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Order Status","name"=>"order_status"];
			$this->col[] = ["label"=>"Style","name"=>"style"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
			$this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Order Status','name'=>'order_status','type'=>'text','validation'=>'required|min:1|max:50','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Style','name'=>'style','type'=>'wysiwyg','validation'=>'required','width'=>'col-sm-5'];
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
	        $status = '';
            switch ($button_name) {
                case 'set_status_inactive':
                    $status = 'INACTIVE';
                    break;
                default:
                    $status = 'ACTIVE';
                    break;
            }

            OrderStatus::whereIn('id', $id_selected)->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => CRUDBooster::myId()
            ]);

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
