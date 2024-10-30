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

    @if(g('return_url'))
        <p><a title='Return' href='{{g("return_url")}}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
            &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
        @else
        <p><a title='Main Module' href='{{CRUDBooster::mainpath()}}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
            &nbsp; {{trans("crudbooster.form_back_to_list",['module'=>CRUDBooster::getCurrentModule()->name])}}</a></p>
    @endif

    <div class='panel panel-default'>
        <div class='panel-heading'>  
        <h3 class="box-title text-center"><b>CONFIRM STOCK TRANSFER</b></h3>
        </div>

        <div class='panel-body'>
            <form action="{{ $action_url }}" method="POST" id="confirm_st" autocomplete="off" role="form" enctype="multipart/form-data">
            <input type="hidden" name="_token" id="token" value="{{csrf_token()}}" >
            <input type="hidden" name="transport_type" id="transport_type" value="{{$store_transfer->transport_type}}" >
            <input type="hidden" name="header_id" id="header_id" value="{{$store_transfer->id}}" >
            <input type="hidden" value="" name="approval_action" id="approval_action">
            <input type="hidden" value="{{$store_transfer->ref_number}}" name="ref_number" id="ref_number">

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>Reference #:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->ref_number }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Transport By:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->transportTypes->transport_type }} @if(!empty($store_transfer->hand_carrier)) : {{ $store_transfer->hand_carrier }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>Reason:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->reasons->pullout_reason }} 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-4 col-md-offset-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-store">
                        <tbody>
                            <tr>
                                <td>
                                    <b>ST:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->document_number }}
                                    <input type="hidden" name="st_number" id="st_number" value="{{ $store_transfer->document_number }}" >
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>From:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->storesfrom->store_name }} 
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b>To:</b>
                                </td>
                                <td>
                                    {{ $store_transfer->storesto->store_name }} 
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
                                        <td class="text-center">{{$lines->item_code }} <input type="hidden" name="digits_code[]" value="{{$lines->item_code}}"></td>
                                        @if(is_null($store_transfer->location_id_from) || empty($store_transfer->location_id_from))
                                            <td class="text-center">{{$lines->item->upc_code}} </td>
                                        @endif
                                        <td>{{$lines->item->item_description}}<input type="hidden" name="price[]" value="{{ $item['price'] }}"/>
                                        </td>
                                        <td class="text-center">{{$lines->qty}}<input type="hidden" name="st_quantity[]" id="stqty_{{ $item['digits_code'] }}" value="{{ $item['st_quantity'] }}"/>
                                        </td>
                                        @if(is_null($store_transfer->location_id_from) || empty($store_transfer->location_id_from))
                                            <td>
                                                @foreach ($lines->serials as $serial)
                                                    <input type="text" class="form-control serial-input mb-1" name="serial[]" style="text-align:center; margin-top: 5px;" readonly value=" {{$serial->serial_number}}">
                                                @endforeach
                                            </td>
                                        @endif
                                        
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
            
            @if(!empty($store_transfer->memo))
                <div class="col-md-12">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td style="width: 10%">
                                    <b>Note:</b>
                                </td>
                                <td>
                                    <p style="padding:10px 15; align-items:center">{{ $store_transfer->memo }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        <div class='panel-footer'>
            <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">Back</a>
            <button class="btn btn-danger pull-right" type="button" id="btnReject" style="margin-left: 5px;"> <i class="fa fa-thumbs-down" ></i> Reject</button>
            <button class="btn btn-success pull-right" type="button" id="btnApprove"> <i class="fa fa-thumbs-up" ></i> Confirm</button>
            
        </div>
        </form>
    </div>

@endsection

@push('bottom')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            Swal.fire({
                title: 'Are you sure you want to confirm?',
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
                    $('#approval_action').val('1');
                    $('#confirm_st').submit(); 
                    Swal.fire({
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        title: "Please wait while saving...",
                        didOpen: () => Swal.showLoading()
                    });
                }
            });
        
        });

        $('#btnReject').click(function() {
            Swal.fire({
                title: 'Are you sure you want to reject?',
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
                    $('#approval_action').val('0');
                    $('#confirm_st').submit(); 
                    Swal.fire({
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        title: "Please wait while saving...",
                        didOpen: () => Swal.showLoading()
                    });
                }
            });
        
        });
    });
</script>
@endpush
