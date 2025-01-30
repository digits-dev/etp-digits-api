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
                -webkit-touch-callout: none;
                /* iOS Safari */
                -webkit-user-select: none;
                /* Safari */
                -khtml-user-select: none;
                /* Konqueror HTML */
                -moz-user-select: none;
                /* Old versions of Firefox */
                -ms-user-select: none;
                /* Internet Explorer/Edge */
                user-select: none;
                /* Non-prefixed version, currently supported by Chrome, Edge, Opera and Firefox */
            }
        </style>
    @endpush

    @if (g('return_url'))
        <p><a title='Return' href='{{ g('return_url') }}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
                &nbsp; {{ trans('crudbooster.form_back_to_list', ['module' => CRUDBooster::getCurrentModule()->name]) }}</a>
        </p>
    @else
        <p><a title='Main Module' href='{{ CRUDBooster::mainpath() }}' class="noprint"><i class='fa fa-chevron-circle-left'></i>
                &nbsp; {{ trans('crudbooster.form_back_to_list', ['module' => CRUDBooster::getCurrentModule()->name]) }}</a>
        </p>
    @endif

    <div class='panel panel-default'>
        <div class='panel-heading'>
            <h3 class="box-title text-center"><b>Delivery Details</b></h3>
        </div>

        <div class='panel-body' id="dr-details">

            <div class="col-md-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-header">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>DR:</b>
                                </td>
                                <td>
                                    {{ $deliveries->dr_number }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>ST:</b>
                                </td>
                                <td>
                                    {{ $deliveries->document_number }}
                                </td>
                            </tr>

                            <tr>
                                <td style="width: 30%">
                                    <b>PO:</b>
                                </td>
                                <td>
                                    {{ $deliveries->customer_po }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4 col-md-offset-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="st-received-details">
                        <tbody>
                            <tr>
                                <td style="width: 30%">
                                    <b>Received Date:</b>
                                </td>
                                <td>
                                    {{ $deliveries->received_date }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>To:</b>
                                </td>
                                <td>
                                    {{ $deliveries->customer_name }}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%">
                                    <b>Status:</b>
                                </td>
                                <td>
                                    {{ $deliveries->orderStatus->style }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <br>

            <div class="col-md-12">
                <div class="box-header text-center">
                    <h3 class="box-title"><b>Delivery Items</b></h3>
                </div>

                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-bordered noselect" id="dr-items">
                            <thead>
                                <tr style="background: #0047ab; color: white">
                                    <th width="5%" class="text-center">Line #</th>
                                    <th width="15%" class="text-center">Digits Code</th>
                                    <th width="15%" class="text-center">UPC Code</th>
                                    <th width="35%" class="text-center">Item Description</th>
                                    <th width="5%" class="text-center">Qty</th>
                                    <th width="25%" class="text-center">Serial #</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deliveries->lines as $line)
                                    <tr>
                                        <td class="text-center">{{ $line->line_number }} </td>
                                        <td class="text-center">{{ $line->item->digits_code }} </td>
                                        <td class="text-center">{{ $line->item->upc_code }}</td>
                                        <td>{{ $line->item->item_description }}</td>
                                        <td class="text-center">{{ $line->shipped_quantity }}</td>
                                        <td>
                                            @foreach ($line->serials as $serial)
                                                {{ $serial->serial_number }}<br>
                                            @endforeach
                                        </td>

                                    </tr>
                                @endforeach

                                <tr class="tableInfo">
                                    <td colspan="1" align="center"><strong>SKU: {{ count($deliveries->lines) }}</strong></td>
                                    <td colspan="3" align="right">
                                        <strong>Total Qty</strong></td>
                                    <td align="center" colspan="1">
                                        <strong>{{ $deliveries->total_qty }}</strong>
                                    </td>
                                    </td>
                                    <td colspan="1"></td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class='panel-footer'>
            @if (g('return_url'))
                <a href="{{ g('return_url') }}" class="btn btn-default">Cancel</a>
            @else
                <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">Cancel</a>
            @endif
        </div>
    </div>
@endsection
@push('bottom')
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endpush
