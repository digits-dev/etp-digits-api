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

    @if(CRUDBooster::getCurrentMethod() == 'getDetail')
        @if(g('return_url'))
            <p><a title='Return' href='{{g("return_url")}}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
            &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
        @else
            <p><a title='Main Module' href='{{CRUDBooster::mainpath()}}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
            &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
        @endif
    @endif

    <div class='panel panel-default'>
        <div class='panel-heading'>  
        <h3 class="box-title text-center"><b>Stock Transfer Details</b></h3>
        </div>

        <div class='panel-body' id="st-details">

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header-1">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>ST:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->document_number }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>Received ST:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->received_document_number }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>Reason:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->reasons->pullout_reason }} 
                                </td>
                            </tr>
                            @if(!is_null($store_transfer->approved_by) || !empty($store_transfer->approved_by))
                                <tr>
                                    <td width="30%"><b>Approved By:</b></td>
                                    <td>{{ $store_transfer->approved_by }} / {{ $store_transfer->approved_at != null ? date('M d, Y',strtotime($store_transfer->approved_at)) : "" }}</td>
                                    
                                </tr>
                            @elseif(!is_null($store_transfer->rejected_by) || !empty($store_transfer->rejected_by))
                                <tr>
                                    <td width="30%"><b>Rejected By:</b></td>
                                    <td>{{ $store_transfer->rejected_by }} / {{ $store_transfer->rejected_at != null ? date('M d, Y',strtotime($store_transfer->rejected_at)) : "" }}</td>
                                        
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4">
            </div>

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header-2">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>Transfer Date:</b>
                                </td>
                                <td>
                                    @if(!empty($store_transfer->scheduled_at)) {{ $store_transfer->scheduled_at }} @else {{ $store_transfer->transfer_date }} @endif  
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>Transport By:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->transportTypes->transport_type }} @if(!empty($store_transfer->hand_carrier)) : {{ $store_transfer->hand_carrier }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>From:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->storesFrom->store_name }} 
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>To:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->storesTo->store_name }} 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <br>

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
                                    <th width="20%" class="text-center">Serial #</th>
                              
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($store_transfer->lines as $lines)
                                    <tr>
                                        <td class="text-center">{{$lines->item_code}}</td>
                                        <td class="text-center">{{$lines->item->upc_code}}</td>
                                        <td>{{$lines->item->item_description}}</td>
                                        <td class="text-center">{{$lines->qty}}</td>
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
                                            <input type='text' name="total_quantity" class="form-control text-center" id="totalQuantity" value="{{$store_transfer->calculateTotals()}}" readonly>
                                        </td>
                                        <td colspan="1"></td>
                                    </tr>
                           
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">
                <h4><b>Note:</b></h4>
                <p>{{ $store_transfer->memo }}</p>
            </div>

        </div>

        <div class='panel-footer'>
            <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">Back</a>
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


    @if($store_transfer->statuses->order_status == "RECEIVED")
        $("#st-details").attr("style",'background-image: url("https://dms.digitstrading.ph/public/images/received.png"); background-repeat: no-repeat; background-position: top center; background-size: 500px 300px;');
    @elseif($store_transfer->statuses->order_status  == "VOID")
        $("#st-details").attr("style",'background-image: url("https://dms.digitstrading.ph/public/images/void.png"); background-repeat: no-repeat; background-position: top center; background-size: 500px 300px;');
    @elseif($store_transfer->statuses->order_status  == "CLOSED")
        $("#st-details").attr("style",'background-image: url("https://dms.digitstrading.ph/public/images/closed.png"); background-repeat: no-repeat; background-position: top center; background-size: 500px 300px;');
    @else
    
    @endif

});
</script>
@endpush

