@extends('crudbooster::admin_template')
@section('content')

    @push('head')
    @endpush

    @if ($errors->any())
        <div class="alert alert-danger">
            <p>Error !</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class='panel panel-default noselect' id='sts_form'>
        <div class='panel-heading'>
        <h3 class="box-title text-center"><b>Stock Transfer Form</b></h3>
        </div>

        <div class='panel-body'>

            <div class="col-md-12">
                <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS CODE**</b></p>
            </div>

            <form action="" method="POST" id="sts_create" autocomplete="off" role="form" enctype="multipart/form-data">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}" >

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Transfer From: <span class="required">*</span></label>
                    <select class="form-control select2" style="width: 100%;" name="transfer_from" id="transfer_from" required>
                        <option value="">Please select a store</option>
                        @foreach ($transfer_from as $data)
                            <option value="{{$data->id}}">{{$data->store_name}}</option>

                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Transfer To: <span class="required">*</span></label>
                    <select class="form-control select2" style="width: 100%;" name="transfer_to" id="transfer_to" required>
                        <option value="">Please select a store</option>
                        {{-- @foreach ($transfer_to as $data)
                            <option value="{{$data->warehouse_id}}" data-id="{{$data->warehouse_id}}">{{$data->warehouse_name}}</option>
                        @endforeach --}}
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Reason: <span class="required">*</span></label>
                    <select class="form-control select2" style="width: 100%;" name="reason" id="reason" required>
                        <option value="">Please select a reason</option>
                        {{-- @foreach ($reasons as $data)
                            <option value="{{$data->id}}">{{$data->pullout_reason}}</option>
                        @endforeach --}}
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Transport By: <span class="required">*</span></label>
                    <select class="form-control select2" style="width: 100%;" name="transport_type" id="transport_type" required>
                        <option value="">Please select a transport type</option>
                        {{-- @foreach ($transport_types as $data)
                            <option value="{{$data->id}}">{{$data->transport_type}}</option>
                        @endforeach --}}
                    </select>
                </div>
            </div>

            <div class="col-md-3 col-md-offset-9" id="hand_carriers">
                <div class="form-group">
                    <label class="control-label">Hand Carrier:</label>
                    <input class="form-control" type="text" name="hand_carrier" id="hand_carrier" placeholder="First name Last name"/>
                </div>

            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Scan Digits Code</label>
                    <input class="form-control" type="text" name="item_search" id="item_search"/>
                </div>

            </div>

            <div class="col-md-9">
                <div class="form-group">
                    <label class="control-label">Memo:</label>
                    <input class="form-control" type="text" name="memo" id="memo" maxlength="120"/>
                </div>
            </div>

            <br>

            <div class="col-md-12">
                <h4 style="color: red;"><b>Note:</b> Maximum lines <b><span id="sku_count">0</span></b>/100 skus/serials. </h4>
            </div>

            <div class="col-md-12">
                <div class="box-header text-center">
                    <h3 class="box-title"><b>Stock Transfer Items</b></h3>
                </div>

                <div class="box-body no-padding noselect">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="st_items">
                            <thead>
                                <tr style="background: #0047ab; color: white">
                                    <th width="15%" class="text-center">Digits Code</th>
                                    <th width="15%" class="text-center">UPC Code</th>
                                    <th width="25%" class="text-center">Item Description</th>
                                    <th width="5%" class="text-center">Qty</th>
                                    <th width="25%" class="text-center">Serial #</th>
                                    <th width="10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="dynamicRows"></tr>
                                <tr class="tableInfo">
                                    <td colspan="3" class="td-right"><strong>Total Qty</strong></td>
                                    <td class="td-left" colspan="1" class="noselect"><span id="totalQuantity">0</span></td>
                                    <td colspan="2"></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS CODE**</b></p>
            </div>

        </div>

        <div class='panel-footer'>
            <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary pull-right" type="submit" id="btnSubmit"> <i class="fa fa-save" ></i> Create</button>
        </div>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="scan_serial" role="dialog">

    	<div class="modal-dialog">

    		<!-- Modal content-->
    		<div class="modal-content">

                <div class="modal-header alert-info">
                    <h4 class="modal-title"><b>Item Code: </b><span id="scanned_item_code" style="color:black;"></span></h4>
                </div>

                <input type="hidden" name="serial_field" id="serial_field">

                <div class="modal-body">

                    <div class="container-fluid">

                        <div class="row">
                            <div class="form-group">
                                <label for="scanned_serial">Serial #:</label>
                                <input class='form-control' type='text' name='scanned_serial' tabindex="1" id="scanned_serial" autofocus required>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="cancelSerial()" class="btn btn-default" style="margin-right:15px;" data-dismiss="modal">Cancel</button>
                </div>

    		</div><!-- End modal-content -->

    	</div><!-- End modal-dialog -->

    </div><!-- End dialog -->

    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="item_scan_error" role="dialog">
    	<div class="modal-dialog">

    		<!-- Modal content-->
    		<div class="modal-content">

                <div class="modal-header alert-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><b>Error!</b></h4>
                </div>

                <div class="modal-body">
                    <p>Item not found! Please try again.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="changeFocus()" id="close_error" class="btn btn-info pull-right" data-dismiss="modal"><i class="fa fa-times" ></i> Close</button>
                </div>

    		</div>
    	</div>
    </div>

    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="item_scan_limit" role="dialog">
    	<div class="modal-dialog">

    		<!-- Modal content-->
    		<div class="modal-content">

                <div class="modal-header alert-danger">
                    <h4 class="modal-title"><b>Warning!</b></h4>
                </div>

                <div class="modal-body">
                    <p>Limit of 100 skus/serials reach!</p>
                </div>

                <div class="modal-footer">
                    <button type="button" id="close_limit" class="btn btn-info pull-right" data-dismiss="modal"><i class="fa fa-times" ></i> Close</button>
                </div>

    		</div>
    	</div>
    </div>

    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="serial_scan_error" role="dialog">
    	<div class="modal-dialog">

    		<!-- Modal content-->
    		<div class="modal-content">

                <div class="modal-header alert-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><b>Error!</b></h4>
                </div>

                <div class="modal-body">
                    <p>Serial not found! Please try again.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="changeFocus()" id="close_error" class="btn btn-info pull-right" data-dismiss="modal"><i class="fa fa-times" ></i> Close</button>
                </div>

    		</div>
    	</div>
    </div>

@endsection
