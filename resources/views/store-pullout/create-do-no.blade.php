@extends('crudbooster::admin_template')
@section('content')

@push('head')
<style type="text/css">

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
.swal2-popup, .swal2-modal, .swal2-icon-warning .swal2-show {
    font-size: 1.4rem !important;
}

</style>
@endpush

    <div class='panel panel-default'>
        <div class='panel-heading'>  
        <h3 class="box-title text-center"><b>PULLOUT - CREATE DO</b></h3>
        </div>

        <div class='panel-body'>
            <form action="{{ $action_url }}" method="POST" id="create_do" autocomplete="off" role="form" enctype="multipart/form-data">
            <input type="hidden" name="_token" id="token" value="{{csrf_token()}}" >
            <input type="hidden" name="transport_type" id="transport_type" value="{{$store_pullout->transport_types_id}}" >
            <input type="hidden" name="header_id" id="header_id" value="{{$store_pullout->id}}" >
            
            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>Reference #:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->ref_number }}
                                </td>
                            </tr>
                            <tr>
                                <td width="30%"><b>Approved By:</b></td>
                                <td>{{ $store_pullout->approvedBy->name }} / {{ $store_pullout->approved_at != null ? date('Y-m-d',strtotime($store_pullout->approved_at)) : "" }}</td>
                                
                            </tr>    
                            <tr>
                                <td>
                                    <b>Reason:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->reasons->pullout_reason }} 
                                </td>
                            </tr>
                        
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4">
            </div>

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>Transport By:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->transportTypes->transport_type }} @if(!empty($store_pullout->hand_carrier)) : {{ $store_pullout->hand_carrier }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>From:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->storesFrom->store_name }} 
                                </td>
                            </tr>

                            <tr>
                                <td style="width: 30%">
                                    <b>To:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->storesTo->store_name }} 
                                </td>
                            </tr>         
                        </tbody>
                    </table>
                </div>
            </div>
        
            <br>

            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <p><strong style="color: red">Note: </strong><strong>Please create a Dispatch Order in your ETP Store Operations Module in the POS.</strong></p>
                            <tr>
                                <td style="width: 10%">
                                    <b>Input DO#:</b>
                                </td>
                                <td>
                                    <input type='input' name='do_number' id="do_number" autocomplete="off" class='form-control' placeholder="Input DO#"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
           
            <div class="col-md-12">
                <div class="box-header text-center">
                    <h3 class="box-title"><b>Stock Transfer Items</b></h3>
                </div>
                
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-bordered noselect" id="st-items">
                            <thead>
                                <tr style="background: #0047ab; color: white">
                                    <th width="10%" class="text-center">Digits Code</th>
                                    <th width="15%" class="text-center">UPC Code</th>
                                    <th width="25%" class="text-center">Item Description</th>
                                    <th width="5%" class="text-center">Qty</th>
                                    @if($store_pullout->transaction_type == 2)
                                        <th width="15%" class="text-center">Problem</th>
                                    @endif
                                    <th width="20%" class="text-center">Serial #</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                                @foreach ($store_pullout->lines as $lines)
                                    <tr>
                                        <td class="text-center">{{$lines->item_code }} <input type="hidden" name="digits_code[]" value="{{$lines->item_code}}"></td>
                                        <td class="text-center">{{$lines->item->upc_code}} </td>
                                        <td>{{$lines->item->item_description}}</td>
                                        <td class="text-center">{{$lines->qty}}</td>
                                        @if($store_pullout->transaction_type == 2)
                                            @php
                                                $problems = explode(',', $lines->problems);
                                                $problem_details = explode(',', $lines->problem_details);
                                                $problem_pairs = array_map(null, $problems, $problem_details);
                                            @endphp
                                            <td>
                                                @foreach ($problem_pairs as $pair)
                                                    {{ trim($pair[0]) }} - {{ trim($pair[1]) }}
                                                    @if (!$loop->last)
                                                        <br>
                                                    @endif
                                                @endforeach
                                            </td>
                                        @endif
                                        <td>
                                            @foreach ($lines->serials as $serial)
                                                <input type="text" class="form-control serial-input mb-1" name="serial[]" style="text-align:center; margin-top: 5px;" readonly value=" {{$serial->serial_number}}">
                                            @endforeach
                                        </td>
                                    </tr>    
                                @endforeach
                                <tr class="tableInfo">
                                    <td colspan="3" align="right"><strong>Total Qty</strong></td>
                                    <td align="left" colspan="1">
                                        <input type='text' name="total_quantity" class="form-control text-center" id="totalQuantity" value="{{$store_pullout->calculateTotals()}}" readonly>
                                    </td>
                                    <td colspan="1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if(!empty($store_pullout->memo))
                <div class="col-md-12">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td style="width: 10%">
                                    <b>Note:</b>
                                </td>
                                <td>
                                    <p style="padding:10px 15; align-items:center">{{ $store_pullout->memo }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        <div class='panel-footer'>
            <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">Back</a>
            <button class="btn btn-warning pull-right" type="submit" id="btnSubmit"> <i class="fa fa-edit" ></i> Update</button>
        </div>
        </form>
    </div>

@endsection

@push('bottom')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#schedule_date").datepicker({ 
            startDate: "today",
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true,
        });

        $("form").bind("keypress", function(e) {
            if (e.keyCode == 13) {
                return false;
            }
        });

        $('#btnSubmit').click(function(e) {
            e.preventDefault();
            if($('#do_number').val() === '' || $('#do_number').val() === null){
                Swal.fire({
                    type: 'warning',
                    title: 'DO# required!',
                    icon: 'warning',
                    confirmButtonColor: "#3c8dbc",
                }); 
                event.preventDefault();
            }else{
                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    returnFocus: false,
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).attr('disabled','disabled');
                        $('#create_do').submit(); 
                        Swal.fire({
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            title: "Please wait while saving...",
                            didOpen: () => Swal.showLoading()
                        });
                    }
                });
            }
        
        });
    });
</script>
@endpush