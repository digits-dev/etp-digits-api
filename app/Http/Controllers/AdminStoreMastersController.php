<?php namespace App\Http\Controllers;

use App\Models\StoreMaster;
use App\Services\SubmasterService;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

	class AdminStoreMastersController extends \crocodicstudio\crudbooster\controllers\CBController {

        protected $activeChannel;
        protected $activeTransferGroup;

        public function __construct(SubmasterService $masterService) {
            $this->activeChannel = $masterService->getChannels();
            $this->activeTransferGroup = $masterService->getTransferGroups();
        }

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "store_name";
			$this->limit = "20";
			$this->orderby = "store_name,asc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = true;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "store_masters";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Warehouse Code","name"=>"warehouse_code"];
			$this->col[] = ["label"=>"Warehouse Type","name"=>"warehouse_type"];
			$this->col[] = ["label"=>"Store Name","name"=>"store_name"];
            $this->col[] = ["label"=>"Channel","name"=>"channels_id","join"=>"channels,channel_description"];
			$this->col[] = ["label"=>"SO Store Name","name"=>"bea_so_store_name"];
			$this->col[] = ["label"=>"MO Store Name","name"=>"bea_mo_store_name"];
			$this->col[] = ["label"=>"DOO Subinventory","name"=>"doo_subinventory"];
			$this->col[] = ["label"=>"SIT Subinventory","name"=>"sit_subinventory"];
            $this->col[] = ["label"=>"ORG Subinventory","name"=>"org_subinventory"];
            $this->col[] = ["label"=>"Transfer Groups","name"=>"transfer_groups_id","join"=>"transfer_groups,group"];
			$this->col[] = ["label"=>"Status","name"=>"status"];
            $this->col[] = ["label"=>"Created By","name"=>"created_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated By","name"=>"updated_by","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Warehouse Code','name'=>'warehouse_code','type'=>'text','validation'=>'required','width'=>'col-sm-5','readonly'=>true];
			$this->form[] = ['label'=>'Warehouse Type','name'=>'warehouse_type','type'=>'select','validation'=>'required','width'=>'col-sm-5','dataenum'=>'0|Store;1|Warehouse'];
			$this->form[] = ['label'=>'Store Name','name'=>'store_name','type'=>'text','validation'=>'required|min:1|max:150','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'SO Store Name','name'=>'bea_so_store_name','type'=>'text','validation'=>'required|min:1|max:250','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'MO Store Name','name'=>'bea_mo_store_name','type'=>'text','validation'=>'required|min:1|max:250','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'DOO Subinventory','name'=>'doo_subinventory','type'=>'text','validation'=>'max:50','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'SIT Subinventory','name'=>'sit_subinventory','type'=>'text','validation'=>'max:50','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'ORG Subinventory','name'=>'org_subinventory','type'=>'text','validation'=>'max:50','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Transfer Groups','name'=>'transfer_groups_id','type'=>'select','validation'=>'min:0','width'=>'col-sm-5','datatable'=>'transfer_groups,group'];
            if(in_array(CRUDBooster::getCurrentMethod(),['getEdit','postEditSave','getDetail'])) {
				$this->form[] = ['label'=>'Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-5','dataenum'=>'ACTIVE;INACTIVE'];
			}

            $this->index_button = array();
            if(CRUDBooster::isSuperAdmin() && CRUDBooster::getCurrentMethod() == 'getIndex'){
                $this->index_button[] = ["label"=>"Pull New Stores","url"=>"javascript:pullNewStores()","icon"=>"fa fa-download","color"=>"warning"];
                $this->index_button[] = ["label"=>"Pull Updated Stores","url"=>"javascript:pullUpdatedStores()","icon"=>"fa fa-refresh","color"=>"info"];
            }

	        $this->button_selected = array();
            if(CRUDBooster::isUpdate() && CRUDBooster::isSuperadmin()) {
                $this->button_selected[] = ['label'=>'Set Status ACTIVE','icon'=>'fa fa-check-circle','name'=>'set_status_active'];
				$this->button_selected[] = ['label'=>'Set Status INACTIVE','icon'=>'fa fa-times-circle','name'=>'set_status_inactive'];
                foreach ($this->activeChannel as $keyChannel => $valueChannel) {
                    $this->button_selected[] = ["label"=>"Set Channel as {$valueChannel->channel_code}","icon"=>"fa fa-check-circle","name"=>"set_channel_{$valueChannel->channel_code}"];
                }
                foreach ($this->activeTransferGroup as $keyTransferGroup => $valueTransferGroup) {
                    $this->button_selected[] = ["label"=>"Set Transfer Group as {$valueTransferGroup->group}","icon"=>"fa fa-check-circle","name"=>"set_group_{$valueTransferGroup->group}"];
                }
            }

	        $this->script_js = "
                function pullNewStores() {
					$('#modal-pull-new-stores').modal('show');
				}
                function pullUpdatedStores() {
					$('#modal-pull-updated-stores').modal('show');
				}
                $(document).ready(function() {
                    $('.dateInput').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true,
                        todayHighlight: true
                    }).on('changeDate', function(e) {
                        const date = e.format('yyyy-mm-dd');
                        console.log(date);
                    });
                });
            ";

	        $this->post_index_html = "
			<div class='modal fade' tabindex='-1' role='dialog' id='modal-pull-new-stores'>
				<div class='modal-dialog'>
					<div class='modal-content'>
						<div class='modal-header'>
							<button class='close' aria-label='Close' type='button' data-dismiss='modal'>
								<span aria-hidden='true'>×</span></button>
							<h4 class='modal-title'><i class='fa fa-download'></i> Pull New Stores</h4>
						</div>

						<form method='get' target='_blank' action=".route('stores.pull-new-store').">
                        <input type='hidden' name='_token' value=".csrf_token().">
                        ".CRUDBooster::getUrlParameters()."
                        <div class='modal-body'>
                            <div class='form-group'>
                                <label>Date From</label>
                                <input type='text' name='datefrom' class='form-control dateInput' required />
                            </div>
                            <div class='form-group'>
                                <label>Date To</label>
                                <input type='text' name='dateto' class='form-control dateInput' required />
                            </div>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <div class='form-group'>
                                    <label>Page</label>
                                    <input type='number' name='page' class='form-control' required value='1'/>
                                    </div>
                                </div>
                                <div class='col-md-6'>
                                    <div class='form-group'>
                                    <label>Limit</label>
                                    <input type='number' name='limit' class='form-control' required value='500'/>
                                    </div>
                                </div>
                            </div>
						</div>
						<div class='modal-footer' align='right'>
                            <button class='btn btn-default' type='button' data-dismiss='modal'>Close</button>
                            <button class='btn btn-primary btn-submit' type='submit'>Submit</button>
                        </div>
                    </form>
					</div>
				</div>
			</div>

            <div class='modal fade' tabindex='-1' role='dialog' id='modal-pull-updated-stores'>
				<div class='modal-dialog'>
					<div class='modal-content'>
						<div class='modal-header'>
							<button class='close' aria-label='Close' type='button' data-dismiss='modal'>
								<span aria-hidden='true'>×</span></button>
							<h4 class='modal-title'><i class='fa fa-download'></i> Pull Updated Stores</h4>
						</div>

						<form method='get' target='_blank' action=".route('stores.pull-updated-store').">
                        <input type='hidden' name='_token' value=".csrf_token().">
                        ".CRUDBooster::getUrlParameters()."
                        <div class='modal-body'>
                            <div class='form-group'>
                                <label>Date From</label>
                                <input type='text' name='datefrom' class='form-control dateInput' required />
                            </div>
                            <div class='form-group'>
                                <label>Date To</label>
                                <input type='text' name='dateto' class='form-control dateInput' required />
                            </div>
						</div>
						<div class='modal-footer' align='right'>
                            <button class='btn btn-default' type='button' data-dismiss='modal'>Close</button>
                            <button class='btn btn-primary btn-submit' type='submit'>Submit</button>
                        </div>
                    </form>
					</div>
				</div>
			</div>
			";

	    }

	    public function actionButtonSelected($id_selected,$button_name) {
	        $value = [
                'updated_by' => CRUDBooster::myId(),
                'updated_at' => date("Y-m-d H:i:s")
            ];

            switch ($button_name) {
                case 'set_status_active':
                    $value['status'] = 'ACTIVE';
                    break;
                case 'set_status_inactive':
                    $value['status'] = 'INACTIVE';
                    break;
                default:
                    {
                        foreach ($this->activeChannel as $keyChannel => $valueChannel) {
                            if($button_name == "set_channel_{$valueChannel->channel_code}"){
                                $value['channels_id'] = $valueChannel->id;
                            }
                        }
                        foreach ($this->activeTransferGroup as $keyTransferGroup => $valueTransferGroup) {
                            if($button_name == "set_group_{$valueTransferGroup->group}"){
                                $value['transfer_groups_id'] = $valueTransferGroup->id;
                            }
                        }
                    }
                    break;
            }

            StoreMaster::whereIn('id', $id_selected)->update($value);
	    }

	    public function hook_before_edit(&$postdata,$id) {
	        //Your code here
            $postdata['updated_by'] = CRUDBooster::myId();
            $postdata['updated_at'] = date("Y-m-d H:i:s");
	    }

	}
