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

</style>
@endpush

    @if(g('return_url'))
        <p><a title='Return' href='{{g("return_url")}}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
            &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
    @else
        <p><a title='Main Module' href='{{CRUDBooster::mainpath()}}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
            &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
    @endif

    <div class='panel panel-default'>
        <div class='panel-heading'>  
        <h3 class="box-title text-center"><b>Review Pullout</b></h3>
        </div>

        <div class='panel-body'>
            <form action="{{ $action_url }}" method="POST" id="approval_pullout" autocomplete="off" role="form" enctype="multipart/form-data">
            <input type="hidden" name="_token" id="token" value="{{csrf_token()}}" >
            <input type="hidden" value="" name="approval_action" id="approval_action">
            <input type="hidden" name="channel_id" id="channel_id" value="{{$store_pullout->channel_id}}" >
            <input type="hidden" name="transport_type" id="transport_type" value="{{$store_pullout->transport_types_id}}" >
            <input type="hidden" name="transaction_type" id="transaction_type" value="{{$store_pullout->transaction_type}}" >
            <input type="hidden" name="reason_so" id="reason_so" value="{{$store_pullout->reason_so}}" >
            <input type="hidden" name="reason_mo" id="reason_mo" value="{{$store_pullout->reason_mo}}" >

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td>
                                    <b>ST/REF:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->document_number }}
                                    <input type="hidden" name="st_number" id="st_number" value="{{$store_pullout->document_number}}" >
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <b>Reason:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->reasons->pullout_reason }} 
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <b>Transport By:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->transport_types->transport_type }} @if(!empty($store_pullout->hand_carrier)) : {{ $store_pullout->hand_carrier }} @endif
                                </td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <b>From:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->storesfrom->store_name }} 
                                    <input type="hidden" name="transfer_from" id="transfer_from" value="{{$transfer_from->pos_warehouse}}" >
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <b>To:</b>
                                </td>
                                <td>
                                    {{ $store_pullout->storesto->store_name }} 
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>

            <br>

            <div class="col-md-12">
                <div class="box-header text-center">
                    <h3 class="box-title"><b>Pullout Items</b></h3>
                </div>
                
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-bordered noselect" id="st-items">
                            <thead>
                                <tr style="background: #0047ab; color: white">
                                    <tr style="background: #0047ab; color: white">
                                        <th width="10%" class="text-center">Digits Code</th>
                                        <th width="15%" class="text-center">UPC Code</th>
                                        <th width="15%" class="text-center">Brand</th>
                                        <th width="35%" class="text-center">Item Description</th>
                                        <th width="5%" class="text-center">Qty</th>
                                        <th width="20%" class="text-center">Serial #</th>
                                    </tr>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($store_pullout->lines))
                                    @foreach ($store_pullout->lines as $lines)
                                        <tr>
                                            <td class="text-center">{{$lines->item_code}}</td>
                                            <td class="text-center">{{$lines->item->upc_code}} </td>
                                            <td class="text-center">{{$lines->item->brand}} </td>
                                            <td class="text-center">{{$lines->item->item_description}}</td>
                                            <td class="text-center">{{$lines->qty}}</td>
                                            <td>
                                                @foreach ($lines->serials as $serial)
                                                    <input type="text" class="form-control serial-input mb-1" name="serial[]" style="text-align:center; margin-top: 5px;" readonly value=" {{$serial->serial_number}}">
                                                @endforeach
                                            </td>  
                                        </tr>    
                                    @endforeach
                                @endif
                                <tr class="tableInfo">
                                    <td colspan="4" align="right"><strong>Total Qty</strong></td>
                                    <td align="center" colspan="1"><input type='text' name="total_quantity" class="form-control text-center" id="totalQuantity" value="{{$store_pullout->calculateTotals()}}" readonly> </td>
                                    <td colspan="1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <h4><b>Note:</b></h4>
                <p>{{ $store_pullout->memo }}</p>
            </div>

            </div>

        <div class='panel-footer'>
            <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">{{ trans('message.form.back') }}</a>
            <button class="btn btn-danger pull-right" type="button" id="btnReject" style="margin-left: 5px;"> <i class="fa fa-thumbs-down" ></i> Reject</button>
            <button class="btn btn-success pull-right" type="button" id="btnApprove"> <i class="fa fa-thumbs-up" ></i> Approve</button>
        </div>
        </form>
    </div>

@endsection

@push('bottom')
<script type="text/javascript">
$(document).ready(function() {

    $("form").bind("keypress", function(e) {
        if (e.keyCode == 13) {
            return false;
        }
    });

    $(function(){
        $('body').addClass("sidebar-collapse");
    });

    $('#btnApprove').click(function() {
        $(this).attr('disabled','disabled');
        $('#approval_action').val('1');
        $('#approval_pullout').submit(); 
    });

    $('#btnReject').click(function() {

        $(this).attr('disabled','disabled');
        $('#approval_action').val('0');
        $('#approval_pullout').submit(); 
        
    });
});

</script>
@endpush