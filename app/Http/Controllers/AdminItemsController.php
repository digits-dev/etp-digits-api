<?php namespace App\Http\Controllers;

use App\Exports\ItemExport;
use App\Helpers\Helper;
use App\Models\Item;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

	class AdminItemsController extends CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "digits_code";
			$this->limit = "20";
			$this->orderby = "digits_code,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = false;
			$this->button_edit = false;
			$this->button_delete = false;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = false;
			$this->table = "items";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Beach Item ID","name"=>"beach_item_id"];
			$this->col[] = ["label"=>"Digits Code","name"=>"digits_code"];
			$this->col[] = ["label"=>"UPC Code","name"=>"upc_code"];
			$this->col[] = ["label"=>"Item Description","name"=>"item_description"];
			$this->col[] = ["label"=>"Brand","name"=>"brand"];
			$this->col[] = ["label"=>"Current SRP","name"=>"current_srp"];
			$this->col[] = ["label"=>"Serial","name"=>"has_serial"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Beach Item ID','name'=>'beach_item_id','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Digits Code','name'=>'digits_code','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'UPC Code1','name'=>'upc_code','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'UPC Code2','name'=>'upc_code2','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'UPC Code3','name'=>'upc_code3','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'UPC Code4','name'=>'upc_code4','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'UPC Code5','name'=>'upc_code5','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Item Description','name'=>'item_description','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Brand','name'=>'brand','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Current SRP','name'=>'current_srp','type'=>'text','width'=>'col-sm-5'];
			$this->form[] = ['label'=>'Has Serial','name'=>'has_serial','type'=>'number','width'=>'col-sm-5'];
			# END FORM DO NOT REMOVE THIS LINE

	        $this->button_selected = array();
            if(CRUDBooster::isSuperAdmin()){
                $this->button_selected[] = ['label'=>'Set Item Serialize', 'icon'=>'fa fa-check-circle', 'name'=>'set_item_serialize'];
				$this->button_selected[] = ['label'=>'Set Item General', 'icon'=>'fa fa-check-circle-o', 'name'=>'set_item_general'];
            }

	        $this->index_button = array();
            if(CRUDBooster::isSuperAdmin()){
                $this->index_button[] = ["title" => "Export Items", "label" => "Export Items", 'color' => 'info', "icon" => "fa fa-download", "url" => route('export-items') . '?' . urldecode(http_build_query(@$_GET))];
                $this->index_button[] = ["label"=>"Pull New Items","url"=>"javascript:pullNewItems()","icon"=>"fa fa-download","color"=>"warning"];
                $this->index_button[] = ["label"=>"Pull Updated Items","url"=>"javascript:pullUpdatedItems()","icon"=>"fa fa-refresh","color"=>"info"];
            }

            $this->script_js = "
				function pullNewItems() {
					$('#modal-pull-new-items').modal('show');
				}
                function pullUpdatedItems() {
					$('#modal-pull-updated-items').modal('show');
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
			<div class='modal fade' tabindex='-1' role='dialog' id='modal-pull-new-items'>
				<div class='modal-dialog'>
					<div class='modal-content'>
						<div class='modal-header'>
							<button class='close' aria-label='Close' type='button' data-dismiss='modal'>
								<span aria-hidden='true'>×</span></button>
							<h4 class='modal-title'><i class='fa fa-download'></i> Pull New Items</h4>
						</div>

						<form method='get' target='_blank' action=".route('items.pull-new-item').">
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

            <div class='modal fade' tabindex='-1' role='dialog' id='modal-pull-updated-items'>
				<div class='modal-dialog'>
					<div class='modal-content'>
						<div class='modal-header'>
							<button class='close' aria-label='Close' type='button' data-dismiss='modal'>
								<span aria-hidden='true'>×</span></button>
							<h4 class='modal-title'><i class='fa fa-download'></i> Pull Updated Items</h4>
						</div>

						<form method='get' target='_blank' action=".route('items.pull-updated-item').">
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
                case 'set_item_serialize':
                    $value['has_serial'] = 1;
                    break;
                case 'set_item_general':
                    $value['has_serial'] = 0;
                    break;
                default:
                    # code...
                    break;
            }

            Item::whereIn('id', $id_selected)->update($value);

	    }

        public function exportItems(Request $request) {
            $fileName = 'Export Items - ' . now()->format('Ymdhis') . '.xlsx';
            $filter_column['filter_column'] = $request->get('filter_column');
            return Excel::download(new ItemExport($filter_column), $fileName);
        }

	}
