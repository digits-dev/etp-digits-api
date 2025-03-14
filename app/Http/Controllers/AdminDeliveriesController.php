<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Delivery;
use App\Models\EtpDelivery;
use App\Models\EtpReceiving;
use App\Models\OrderStatus;
use Carbon\Carbon;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

	class AdminDeliveriesController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			$this->title_field = "dr_number";
			$this->limit = "20";
			$this->orderby = "transaction_date,desc";
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
			$this->button_export = true;
			$this->table = "deliveries";

			$this->col = [];
			$this->col[] = ["label"=>"Order #","name"=>"order_number"];
			$this->col[] = ["label"=>"DR #","name"=>"dr_number"];
			$this->col[] = ["label"=>"Customer Name","name"=>"customer_name"];
			$this->col[] = ["label"=>"Customer PO","name"=>"customer_po"];
			$this->col[] = ["label"=>"Shipping Instruction","name"=>"shipping_instruction"];
			$this->col[] = ["label"=>"Transaction Type","name"=>"transaction_type"];
			$this->col[] = ["label"=>"Total Qty","name"=>"total_qty"];
			$this->col[] = ["label"=>"Total Amount","name"=>"total_amount","callback"=>function ($row){
                return "P ".number_format($row->total_amount,2,".",",");
            }];
			$this->col[] = ["label"=>"Order Date","name"=>"transaction_date"];
			$this->col[] = ["label"=>"Status","name"=>"status","join"=>"order_statuses,style"];

			$this->form = [];

            if(CRUDBooster::isSuperAdmin()){
                $this->index_button[] = ["label"=>"Get ETP Delivery","url"=>"javascript:pullDeliveries()","icon"=>"fa fa-file-text-o","color"=>"default"];
                $this->index_button[] = ["label"=>"Get ETP Sync","url"=>"javascript:storeSync()","icon"=>"fa fa-refresh","color"=>"default"];

			    $this->button_selected[] = ['label'=>'Update Total Amount', 'icon'=>'fa fa-refresh', 'name'=>'calculate_totals'];
			    $this->button_selected[] = ['label'=>'Update Status PENDING', 'icon'=>'fa fa-file', 'name'=>'update_status_pending'];
			    $this->button_selected[] = ['label'=>'Update Status RECEIVED', 'icon'=>'fa fa-file', 'name'=>'update_status_received'];
            }

            $this->load_js[] = asset("js/delivery.js");
            $this->load_js[] = asset("js/storesync.js");

            $this->post_index_html = "
            <div class='modal fade' id='deliveryModal' tabindex='-1' role='dialog' aria-labelledby='deliveryModalLabel'>
                <div class='modal-dialog modal-lg' role='document'>
                    <div class='modal-content'>
                    <div class='modal-header bg-aqua'>
                        <h4 class='modal-title' id='deliveryModalLabel'><i class='fa fa-file-text-o'> </i> ETP Delivery Information</h4>
                    </div>
                    <div class='modal-body'>
                        <div class='row'>
                            <div class='col-md-4 col-sm-4'>
                                <div class='form-group' >
                                    <label for='searchInput'>Search: </label>
                                    <input type='text' id='searchInput' class='form-control' placeholder='Search...' >
                                </div>
                            </div>
                            <div class='col-md-4 col-sm-4'>
                                <div class='form-group'>
                                    <label for='dateFrom'>Date From: </label>
                                    <input type='date' id='dateFrom' class='form-control' >
                                </div>
                            </div>
                            <div class='col-md-4 col-sm-4'>
                                <div class='form-group'>
                                    <label for='dateTo'>Date To: </label>
                                    <input type='date' id='dateTo' class='form-control' >
                                </div>
                            </div>
                        </div>

                        <div id='spinner' class='text-center' style='display: none;'>
                            <i class='fa fa-spinner fa-spin fa-3x fa-fw'></i>
                            <p>Loading data, please wait...</p>
                        </div>

                        <table class='table table-bordered tbl-bordered' id='deliveryTable'>
                        <thead>
                            <tr>
                            <th>From Warehouse</th>
                            <th>To Warehouse</th>
                            <th>Delivery #</th>
                            <th>Received Date</th>
                            <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id='deliveryTableBody'>
                            <!-- Dynamic content will be populated here -->
                        </tbody>
                        </table>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-danger' data-dismiss='modal'><i class='fa fa-times'> </i> Close</button>
                    </div>
                    </div>
                </div>
            </div>

            <div class='modal fade' id='storeSyncModal' tabindex='-1' role='dialog' aria-labelledby='storeSyncModalLabel'>
                <div class='modal-dialog modal-lg' role='document'>
                    <div class='modal-content'>
                    <div class='modal-header bg-aqua'>
                        <h4 class='modal-title' id='storeSyncModalLabel'><i class='fa fa-refresh'> </i> ETP StoreSync Information</h4>
                    </div>
                    <div class='modal-body'>
                        <div class='row'>
                            <div class='col-md-4 col-sm-4'>
                                <div class='form-group' >
                                    <label for='searchInput'>Search: </label>
                                    <input type='text' id='searchInput' class='form-control' placeholder='Search...' >
                                </div>
                            </div>
                            <div class='col-md-4 col-sm-4'>
                                <div class='form-group'>
                                    <label for='dateFrom'>Date From: </label>
                                    <input type='date' id='dateFrom' class='form-control' >
                                </div>
                            </div>
                            <div class='col-md-4 col-sm-4'>
                                <div class='form-group'>
                                    <label for='dateTo'>Date To: </label>
                                    <input type='date' id='dateTo' class='form-control' >
                                </div>
                            </div>
                        </div>

                        <div id='spinnerSync' class='text-center' style='display: none;'>
                            <i class='fa fa-spinner fa-spin fa-3x fa-fw'></i>
                            <p>Loading data, please wait...</p>
                        </div>

                        <table class='table table-bordered tbl-bordered' id='storeSyncTable'>
                        <thead>
                            <tr>
                            <th>Warehouse</th>
                            <th>Sync DateTime</th>
                            </tr>
                        </thead>
                        <tbody id='storeSyncTableBody'>
                            <!-- Dynamic content will be populated here -->
                        </tbody>
                        </table>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-danger' data-dismiss='modal'><i class='fa fa-times'> </i> Close</button>
                    </div>
                    </div>
                </div>
            </div>
            ";

	    }

	    public function actionButtonSelected($id_selected,$button_name) {

            if($button_name == "calculate_totals"){
                foreach ($id_selected as $id) {
                    try {
                        DB::beginTransaction();
                        $delivery = Delivery::find($id);
                        $delivery->calculateTotals();
                        DB::commit();
                    } catch (Exception $ex) {
                        DB::rollBack();
                        Log::error($ex->getMessage());
                    }
                }
            }
            if($button_name == "update_status_pending"){
                try {
                    DB::beginTransaction();
                    Delivery::whereIn('id', $id_selected)->update([
                        'status' => OrderStatus::PENDING
                    ]);
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
            if($button_name == "update_status_received"){
                try {
                    DB::beginTransaction();
                    Delivery::whereIn('id', $id_selected)->update([
                        'status' => OrderStatus::RECEIVED
                    ]);
                    DB::commit();
                } catch (Exception $ex) {
                    DB::rollBack();
                    Log::error($ex->getMessage());
                }
            }
	    }

        public function hook_query_index(&$query){
            if(!CRUDBooster::isSuperadmin()){
                $query->where('stores_id',Helper::myStore());
            }
        }

        public function getDetail($id){

            if(!CRUDBooster::isRead() && !$this->global_privilege || !$this->button_detail) {
                CRUDBooster::redirect(CRUDBooster::adminPath(),trans("crudbooster.denied_access"));
            }

            $data = [];
            $data['page_title'] = "Delivery Details";
            $data['deliveries'] = Delivery::with(['lines' => function ($query) {
                $query->orderBy('line_number','ASC');
            },'lines.serials'])->find($id);

            return view('deliveries.detail', $data);
        }

        public function updateDeliveryStatus(){
            $dateFrom = now()->subDays(1)->format('Ymd');
            $dateTo = now()->format('Ymd');

            $etpDeliveries = EtpDelivery::getReceivedDelivery()
                ->whereBetween('ReceivingDate',[$dateFrom, $dateTo])
                ->get();

            foreach ($etpDeliveries ?? [] as $drTrx) {
                try {
                    DB::beginTransaction();
                    $drHead = Delivery::where('dr_number', $drTrx->OrderNumber)
                        ->whereNotIn('status', [OrderStatus::PROCESSING_DOTR, OrderStatus::RECEIVED])
                        ->first();

                    $etpRcvHead = EtpReceiving::getReceivedDelivery($drTrx->OrderNumber)->first();

                    if($drHead && $etpRcvHead){
                        $drHead->document_number = $etpRcvHead->DocumentNumber;
                        $drHead->received_date = Carbon::parse($drTrx->ReceivingDate);
                        $drHead->status = ($drHead->transaction_type == 'MO') ? OrderStatus::PROCESSING_DOTR : OrderStatus::RECEIVED;
                        $drHead->save();
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error($e->getMessage());
                }
            }
        }

        public function manualUpdateDeliveryStatus(Request $request){

            try{
                $request->validate([
                    'dateFrom' => ['required', 'date_format:Ymd', 'before:dateto'],
                    'dateTo'   => ['required', 'date_format:Ymd', 'after:datefrom'],
                ], [
                    'dateFrom.before' => 'The datefrom must be before the dateto.',
                    'dateTo.after'    => 'The dateto must be after the datefrom.',
                ]);

                $dateFrom = Carbon::parse($request->dateFrom)->format('Ymd');
                $dateTo = Carbon::parse($request->dateTo)->format('Ymd');

                $etpDeliveries = EtpDelivery::getReceivedDelivery()
                    ->whereBetween('ReceivingDate',[$dateFrom, $dateTo])
                    ->get();

                foreach ($etpDeliveries ?? [] as $drTrx) {
                    try {
                        DB::beginTransaction();
                        $drHead = Delivery::where('dr_number', $drTrx->OrderNumber)
                            ->whereNotIn('status', [OrderStatus::PROCESSING_DOTR, OrderStatus::RECEIVED])
                            ->first();

                        $etpRcvHead = EtpReceiving::getReceivedDelivery($drTrx->OrderNumber)->first();

                        if($drHead && $etpRcvHead){
                            $drHead->document_number = $etpRcvHead->DocumentNumber;
                            $drHead->received_date = Carbon::parse($drTrx->ReceivingDate);
                            $drHead->status = ($drHead->transaction_type == 'MO') ? OrderStatus::PROCESSING_DOTR : OrderStatus::RECEIVED;
                            $drHead->save();
                        }
                        DB::commit();
                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::error($e->getMessage());
                    }
                }

                return response()->json([
                    'api_status' => 1,
                    'api_message' => 'success',
                    'data' => 'Processing dr received from etp to mw!',
                    'http_status' => 200
                ], 200);
            }
            catch (ValidationException $ex){
                return response()->json([
                    'api_status' => 0,
                    'api_message' => 'Validation failed',
                    'errors' => $ex->errors(),
                    'http_status' => 401
                ], 401);
            }


        }

	}
