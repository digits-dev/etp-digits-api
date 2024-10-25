@extends('crudbooster::admin_template')
@section('content')

@push('head')
<link rel='stylesheet' href='<?php echo asset("vendor/crudbooster/assets/select2/dist/css/select2.min.css")?>'/>
<style type="text/css">
.select2-container--default .select2-selection--single {border-radius: 0px !important}
.select2-container .select2-selection--single {height: 35px}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #3c8dbc !important;
    border-color: #367fa9 !important;
    color: #fff !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff !important;
}

table.table.table-bordered td {
  border: 1px solid black;
}

table.table.table-bordered tr {
  border: 1px solid black;
}

table.table.table-bordered th {
  border: 1px solid black;
}

.noselect {
  -webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
     -khtml-user-select: none; /* Konqueror HTML */
       -moz-user-select: none; /* Old versions of Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently supported by Chrome, Edge, Opera and Firefox */
}

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}

</style>
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

<div class="panel panel-default" id="pullout_form">
    <div class="panel-heading">  
    <h3 class="box-title text-center"><b>Pullout Form</b></h3>
    </div>

    <div class="panel-body">
        
        <div class="col-md-12">
            <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS CODE**</b></p>
        </div>

        <form action="" method="POST" id="stw_create" autocomplete="off" role="form" enctype="multipart/form-data">
        <input type="hidden" name="_token" id="token" value="{{csrf_token()}}" >
        <input type="hidden" name="transfer_transit" id="transfer_transit" value="" >
        <input type="hidden" name="transfer_branch" id="transfer_branch" value="" >
        <input type="hidden" name="transfer_org" id="transfer_org" value="{{ $transfer_org }}" >

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Pullout From: <span class="required">*</span></label>
                <select class="form-control select2" style="width: 100%;" required name="transfer_from" id="transfer_from">
                    <option value="">Please select a store</option>
                    @foreach ($transfer_from as $data)
                        <option value="{{$data->id}}">{{$data->store_name}}</option>
                        
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Pullout To: <span class="required">*</span></label>
                <select class="form-control select2" style="width: 100%;" required name="transfer_to" id="transfer_to">
                    <option value="">Please select a store</option>
                    @foreach ($transfer_to as $data)
                        <option value="{{$data->id}}">{{$data->store_name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Pullout Reason: <span class="required">*</span></label>
                <select class="form-control select2" style="width: 100%;" required name="reason" id="reason">
                    <option value="">Please select a reason</option>
                    @foreach ($reasons as $data)
                        <option value="{{$data->bea_reason}}">{{$data->pullout_reason}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Transport By: <span class="required">*</span></label>
                <select class="form-control select2" style="width: 100%;" required name="transport_type" id="transport_type">
                    <option value="">Please select a transport type</option>
                    <option value="1">Logistics</option>
                    <option value="2">Hand Carry</option>
                    {{-- @foreach ($transport_types as $data)
                        <option value="{{$data->id}}">{{$data->transport_type}}</option>
                    @endforeach --}}
                </select>
            </div>
        </div>

        <div class="col-md-3 col-md-offset-9" id="hand_carriers" style="display: none;">
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

        <div class="col-md-1">
            <div class="form-group">
                <label class="control-label" style="padding-top: 20px;"></label>
                <button type="button" class="btn btn-default" id="scan_digits_code" style="color: limegreen">
                    <i class="fa fa-barcode" id="scanIcon"></i>
                    <i class="fa fa-spinner fa-pulse fa-fw" id="scanningSpinner" style="display: none;"></i>
                    Scan
                </button>
            </div>
        </div>

        <div class="col-md-5">
            <div class="form-group">
                <label class="control-label">Memo:</label>
                <input class="form-control" type="text" name="memo" id="memo" maxlength="120"/>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Pullout Date: <span class="required">*</span></label>
                    <input type='date' name='pullout_date' id="pullout_date" onkeydown="return false" autocomplete="off" class='form-control' required/>
            </div>
        </div>
        
        <br>
        <div class="col-md-12">
            <h4 style="color: red;"><b>Note:</b> Maximum lines <b><span id="sku_count">0</span></b>/100 skus/serials. </h4>
        </div>

        <div class="col-md-12">
            <div class="box-header text-center">
                <h3 class="box-title"><b>Pullout Items</b></h3>
            </div>
            
            <div class="box-body no-padding">
                <div class="table-responsive">
                    <table class="table table-bordered noselect" id="st_items">
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
                            
                        </tbody>
                        <tfoot>
                            <tr class="tableInfo">
                                <td colspan="3" align="right"><strong>Total Qty</strong></td>
                                <td align="left" colspan="1">
                                    <input type='text' name="total_quantity" class="form-control text-center" id="totalQuantity" value="0" readonly></td>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-12">
            <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS CODE**</b></p>
        </div>

    </div>

    <div class='panel-footer'>
        <a href="#" id="cancelBtn" class="btn btn-default">Cancel</a>
        <button class="btn btn-primary pull-right" type="submit" id="btnSubmit"> <i class="fa fa-save" ></i> Create</button>
    </div>
    </form>
</div>


<!-- The Modal -->
<div class="modal fade" id="SerialModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalCenterTitle"> <i class="fa fa-barcode"></i> Create Serial Number</h4>
          {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button> --}}
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="">Serial Number</label>
            <input type="text" name="createSerial" id="createSerial" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          {{-- <button type="button" class="btn btn-success">Create</button> --}}
        </div>
      </div>
    </div>
  </div>

@endsection

@push('bottom')
<script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
<script src='https://cdn.jsdelivr.net/gh/admsev/jquery-play-sound@master/jquery.playSound.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        $('#transfer_to').select2();
        $('#transfer_from').select2();
        $('#reason').select2();
        $('#transport_type').select2();

        $('#transport_type').change(function(){
            let transport_type = $('#transport_type').val();
            if (transport_type == 2){
                $('#hand_carriers').show();
            }
            else{
                $('#hand_carriers').hide();
            }
 
        });
    })

    $('#item_search').on('copy paste cut', function(e) {
            e.preventDefault();
        });

        $('#item_search').on('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        function play(){
            // $.playSound('https://assets.mixkit.co/active_storage/sfx/931/931-preview.mp3');
        }

        $('#scan_digits_code').click(function() {
            const digits_code = $('#item_search').val();
            $('#scanningSpinner').show();
            $('#scanIcon').hide();
            play();
            $.ajax({
                url: '{{ route('scan-digits-code') }}',
                method: 'POST',
                data: {
                    digits_code: digits_code,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const tbody = $('#st_items tbody');
                        tbody.empty();
                        const row = response.data;
                        const qty = 1;
                        const tr = `
                            <tr>
                                <td class="text-center">${row.digits_code || ''}</td>
                                <td class="text-center">${row.upc_code || ''}</td>
                                <td class="text-center">${row.item_description || ''}</td>
                                <td class="text-center">${qty}</td>
                                <td class="text-center">${row.has_serial || ''}</td>
                                <td class="text-center">-</td>
                            </tr>
                        `;
                        tbody.append(tr);
                        $('#totalQuantity').val(qty);

                        if(row.has_serial == 1){
                            $('#SerialModal').modal('show');
                        } else if(row.has_serial == 0) {
                            // alert('No need serial');
                        }
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            html: "<h5><strong>Invalid digits code:</strong> <br> No matching data found, please try again!</h5>",
                            confirmButtonText: '<i class="fa fa-thumbs-up"></i> Okay'
                        });
                    }
                    $('#scanningSpinner').hide();
                    $('#scanIcon').show();
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                    $('#scanningSpinner').hide();
                    $('#scanIcon').show();
                }
            });
        });

</script>

@endpush
