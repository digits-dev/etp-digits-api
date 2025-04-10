@extends('crudbooster::admin_template')
@section('content')

    @push('head')
        <link rel='stylesheet' href='<?php echo asset('vendor/crudbooster/assets/select2/dist/css/select2.min.css'); ?>' />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style type="text/css">
            .select2-selection__choice{
                    font-size:14px !important;
                    color:black !important;
            }
            .select2-selection__rendered {
                line-height: 31px !important;
            }
            .select2-container .select2-selection--single {
                height: 35px !important;
            }
            .select2-container--default .select2-selection--single {
                border-radius: 0px !important
            }

            .select2-container .select2-selection--single {
                height: 35px
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #3c8dbc !important;
                border-color: #367fa9 !important;
                color: #fff !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: #fff !important;
            }

            .swal2-popup {
                font-size: 16px !important;
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

    <div class='panel panel-default noselect' id='sts_form'>
        <div class='panel-heading'>
            <h3 class="box-title text-center"><b>STOCK TRANSFER FORM</b></h3>
        </div>

        <div class='panel-body'>

            <div class="col-md-12">
                <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS
                        CODE**</b></p>
            </div>

            <form action="{{route('post-sts-transfer')}}" method="POST" id="sts_create" autocomplete="off" role="form"
                enctype="multipart/form-data">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Transfer From: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" name="transfer_from" id="transfer_from"
                            required>
                            <option value="">Please select a store</option>
                            @foreach ($transfer_from as $data)
                                <option data-id="{{ $data->id }}" value="{{ $data->warehouse_code }}">{{ $data->store_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Transfer To: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" name="transfer_to" id="transfer_to"
                            required>
                            <option value="">Please select a store</option>
                            @foreach ($transfer_to as $data)
                                <option data-id="{{ $data->id }}" value="{{ $data->warehouse_code }}">{{ $data->store_name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="stores_id_destination_to" id="stores_id_destination_to">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Reason: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" name="reason" id="reason" required>
                            <option value="">Please select a reason</option>
                            @foreach ($reasons as $data)
                                <option value="{{ $data->id }}">{{ $data->pullout_reason }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Transport By: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" name="transport_type" id="transport_type"
                            required>
                            <option value="">Please select a transport type</option>
                            @foreach ($transport_type as $data)
                                <option value="{{ $data->id }}">{{ $data->transport_type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-md-offset-9" id="hand_carriers" style="display: none">
                    <div class="form-group">
                        <label class="control-label">Hand Carrier:</label>
                        <input class="form-control" type="text" name="hand_carrier" id="hand_carrier"
                            placeholder="First name Last name" />
                    </div>

                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Scan Digits Code</label>
                        <input class="form-control" type="text" name="item_search" id="item_search" />
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label class="control-label">Memo:</label>
                        <input class="form-control" type="text" name="memo" id="memo" maxlength="120" />
                    </div>
                </div>

                <br>

                <div class="col-md-12">
                    <h4 style="color: red;"><b>Note:</b> Maximum lines <b><span id="sku_count">0</span></b>/100
                        skus/serials. </h4>
                </div>

                <div class="col-md-12">
                    <div class="box-header text-center">
                        <h3 class="box-title"><b>Stock Transfer Items</b></h3>
                    </div>

                    <div class="box-body no-padding noselect">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="st_items" style="border: 1px solid">
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
                                    <tr class="dynamicRows">
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="tableInfo">
                                        <td colspan="3" class="td-right"
                                            style="border: 1px solid; text-align: right;"><strong>Total Qty</strong></td>
                                        <td class="td-center" colspan="1" class="noselect"
                                            style="text-align: center; border: 1px solid">
                                            <input type="text" name="totalQuantity" id="totalQuantity" class="form-control" value="0" readonly style="text-align:center"></td>
                                        <td colspan="2" style="border: 1px solid"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS
                            CODE**</b></p>
                </div>

        </div>

        <div class='panel-footer'>
            <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary pull-right" type="button" id="btnSubmit"> <i class="fa fa-save"></i>
                Create</button>
        </div>
        </form>
    </div>

    <!-- Modal -->
    <div class="modal fade" data-backdrop="static" data-keyboard="false" id="scan_serial" role="dialog">

        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">

                <div class="modal-header alert-info">
                    <h4 class="modal-title"><b>Item Code: </b><span id="scanned_item_code" style="color:black;"></span>
                    </h4>
                </div>

                <input type="hidden" name="serial_field" id="serial_field">

                <div class="modal-body">

                    <div class="container-fluid">

                        <div class="row">
                            <div class="form-group">
                                <label for="scanned_serial">Serial #:</label>
                                <input class='form-control' type='text' name='scanned_serial' tabindex="1"
                                    id="scanned_serial" autofocus required>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="cancelSerial()" class="btn btn-default" style="margin-right:15px;"
                        data-dismiss="modal">Cancel</button>
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
                    <button type="button" onclick="changeFocus()" id="close_error" class="btn btn-info pull-right"
                        data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
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
                    <button type="button" id="close_limit" class="btn btn-info pull-right" data-dismiss="modal"><i
                            class="fa fa-times"></i> Close</button>
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
                    <button type="button" onclick="changeFocus()" id="close_error" class="btn btn-info pull-right"
                        data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                </div>

            </div>
        </div>
    </div>

    <!-- The Modal -->
    <div class="modal fade" id="SerialModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
            <h4 class="modal-title" id="exampleModalCenterTitle"> <i class="fa fa-barcode"></i> Serial Number <b><span id="scanned_code" style="color: yellow"></span></b></h4>
            </div>
            <div class="modal-body">
            <div class="form-group">
                <label for="">Serial Number</label>
                <input type="text" name="createSerial" id="createSerial" class="form-control">
            </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="CancelSerial()">Cancel</button>
            {{-- <button type="button" class="btn btn-success">Create</button> --}}
            </div>
        </div>
        </div>
    </div>

    <div id="loadingModal" class="modal">
        <div class="modal-content">
            <div class="spinner"></div>
            <p>Loading, please wait...</p>
        </div>
    </div>

@endsection

@push('bottom')
    <script src='<?php echo asset('vendor/crudbooster/assets/select2/dist/js/select2.full.min.js'); ?>'></script>
    <script src='https://cdn.jsdelivr.net/gh/admsev/jquery-play-sound@master/jquery.playSound.js'></script>

    <script>
        $(document).ready(function() {
            $(function(){
                $('body').addClass("sidebar-collapse");
            });
            $('#transfer_to').select2();
            $('#transfer_from').select2();
            $('#reason').select2();
            $('#transport_type').select2();

            $('#transport_type').change(function() {
                let transport_type = $('#transport_type').val();
                if (transport_type == 2) {
                    $('#hand_carriers').show();
                    $('#hand_carrier').prop('required',true);
                } else {
                    $('#hand_carriers').hide();
                    $('#hand_carrier').prop('required',false);
                }

            });
        })

        function playScanSound(){
            $.playSound('https://assets.mixkit.co/active_storage/sfx/931/931-preview.mp3');
        }

        function erroScanSound(){
            $.playSound('https://assets.mixkit.co/active_storage/sfx/950/950-preview.mp3');
        }

        $('#transfer_to').change(function(){
            const selectedDataId = $(this).find('option:selected').data('id');
            $('#stores_id_destination_to').val(selectedDataId);
        })

        function checkSelects() {
            const transfer_from = $('#transfer_from').val();
            const transfer_to = $('#transfer_to').val();
            const reason = $('#reason').val();
            const transport_by = $('#transport_type').val();
            
            if (transfer_from && transfer_to && reason && transport_by) {
                $('#item_search').attr('disabled', false); 
            } else {
                $('#item_search').attr('disabled', true); 
            }
        }

        $('#transfer_from, #transfer_to, #reason, #transport_type').on('change', checkSelects);
        $('#item_search').attr('disabled', true);

        $('#item_search').on('copy paste cut', function(e) {
            e.preventDefault();
        });

        $('#item_search').on('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        let currentSerialRow = null; // row tracker
        let pendingSerials = []; // serials tracker

        $('#item_search').keypress(function(event) {
            if (event.which === 13) {
                $(this).prop('disabled', true);
                event.preventDefault();

                let scannedDigitsCodes = {};
                const digits_code = $(this).val();
                $('#scanningSpinner').show();

                $.ajax({
                    url: "{{ route('scan-digits-code') }}",
                    method: 'POST',
                    data: {
                        digits_code: digits_code,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            playScanSound();
                            const tbody = $('#st_items tbody');
                            const row = response.data;
                            const digitsCode = row.digits_code;
                            const qty = 1;
                            const existingRow = tbody.find(`input[name="scanned_digits_code[]"][value="${digitsCode}"]`).closest('tr');

                            if (existingRow.length) {
                                const currentQty = parseInt(existingRow.find('input[name="qty[]"]').val()) || 0;
                                const totalQty = $('#totalQuantity').val();

                                if (totalQty < 100) {
                                    playScanSound();
                                    existingRow.find('input[name="qty[]"]').val(currentQty + 1);
                                    
                                    if (row.has_serial == 1) {
                                        currentSerialRow = existingRow;
                                        $('#SerialModal').modal('show');
                                    }

                                } else {
                                    erroScanSound();
                                    Swal.fire({
                                        icon: "warning",
                                        title: "Maximum Lines Exceeded",
                                        text: "The total skus has reached the maximum limit of 100.",
                                        confirmButtonText: "OK",
                                        allowEscapeKey: false, 
                                        allowOutsideClick: false, 
                                        allowEnterKey: false

                                    })
                                }
                            } else {
                                scannedDigitsCodes[digitsCode] = qty;

                                const tr = `
                                    <tr>
                                        <td class="text-center">
                                            <input type="text" class="form-control" name="scanned_digits_code[]" id="scanned_digits_code" style="text-align:center" readonly value="${digitsCode || ''}">
                                            <input type="hidden" class="form-control" name="current_srp[]" style="text-align:center" readonly value="${row.current_srp || ''}">
                                        </td>
                                        <td class="text-center"><input type="text" class="form-control" name="upc_code[]" style="text-align:center" readonly value="${row.upc_code || ''}"></td>
                                        <td class="text-center"><input type="text" class="form-control" name="item_description[]" style="text-align:center" readonly value="${row.item_description || ''}"></td>
                                        <td class="text-center"><input type="text" class="form-control" name="qty[]" style="text-align:center" readonly value="${qty}"></td>
                                        <td class="text-center serial-container">
                                            ${row.has_serial == 1 ? `<input type="text" class="form-control serial-input" name="serial[]" style="text-align:center" readonly>` : ''} 
                                            <input type="hidden" class="form-control all-serial-input" name="allSerial[]" style="text-align:center" readonly>
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
                                    </tr>
                                `;
                                tbody.append(tr);

                                if (row.has_serial == 1) {
                                    currentSerialRow = tbody.find(`input[name="scanned_digits_code[]"][value="${digitsCode}"]`).closest('tr');
                                    $('#SerialModal').modal('show');
                                    $('#scanned_code').text(digitsCode);
                                }
                                updatedQtyInput = tbody.find(`input[name="scanned_digits_code[]"][value="${digitsCode}"]`).closest('tr').find('input[name="qty[]"]');
                            }

                            updateTotalQuantity(updatedQtyInput);
                        } else {
                            erroScanSound();
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                html: "<h5><strong>Invalid digits code:</strong> <br> No matching data found, please try again!</h5>",
                                confirmButtonText: '<i class="fa fa-thumbs-up"></i> Okay',
                                preConfirm: () => {
                                    $('#item_search').trigger('focus'); 
                                }
                            });
                        }
                        $('#scanningSpinner').hide();
                        $('#item_search').val("");
                        $('#item_search').prop('disabled', false); 
                        $('#item_search').trigger('focus'); 
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + error);
                        $('#scanningSpinner').hide();
                        $('#item_search').prop('disabled', false); 
                    }
                });
            }
        });

        function removeRow(button) {
            const row = $(button).closest('tr');
            row.remove();
            updateTotalQuantity(); 
            $('#item_search').trigger('focus'); 
        }

        function updateTotalQuantity(updatedQtyInput) {
            let totalQty = 0;

            $('#st_items tbody').find('input[name="qty[]"]').css('background-color', '');
            
            $('#st_items tbody').find('input[name="qty[]"]').each(function() {
                let qty = parseInt($(this).val()) || 0;
                totalQty += qty;
            });

            if (totalQty > 100) {
                erroScanSound();
                CancelSerial();

                    Swal.fire({
                        icon: "warning",
                        title: "Maximum Lines Exceeded",
                        text: "The total skus has reached the maximum limit of 100.",
                        confirmButtonText: "OK",
                        allowEscapeKey: false, 
                        allowOutsideClick: false, 
                        allowEnterKey: false
                    });
                
                return; 
            }

            if (updatedQtyInput) {
                $(updatedQtyInput).css('background-color', 'yellow');
            }

            $('#totalQuantity').val(totalQty);
            $('#sku_count').text(totalQty);
        }

        $('#createSerial').keypress(function(event) {
            if (event.which === 13) {  
                event.preventDefault();
                const serial = $('#createSerial').val().trim();

                if (serial) {
                    const allSerialsInTable = $('.serial-input').map(function() {
                        return $(this).val();
                    }).get();

                    if (allSerialsInTable.includes(serial)) {
                        erroScanSound();
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            html: "<h5><strong>Serial number already exists</strong> <br> Please double check your serial and enter again.</h5>",
                            confirmButtonText: '<i class="fa fa-thumbs-up"></i> Okay'
                        });
                    } else {
                        playScanSound();

                        const serialContainer = currentSerialRow.find('.serial-container');
                        const qty = parseInt(currentSerialRow.find('input[name="qty[]"]').val());

                        if (qty > 1) {
                            const newSerialInput = `
                                <input type="text" class="form-control serial-input mb-1" name="serial[]" style="text-align:center; margin-top: 5px;" readonly value="${serial}">
                            `;
                            serialContainer.append(newSerialInput);
                        } else {
                            const singleSerialInput = serialContainer.find('.serial-input');
                            singleSerialInput.val(serial);
                        }

                        const allSerials = serialContainer.find('.serial-input').map(function() {
                            return $(this).val();
                        }).get().join(', ');
                        serialContainer.find('.all-serial-input').val(allSerials);

                        $('#createSerial').val('');  
                        $('#SerialModal').modal('hide'); 
                        $('#item_search').trigger('focus'); 
                    }
                }
            }
        });

        function CancelSerial() {
            if (currentSerialRow) {
                const qtyInput = currentSerialRow.find('input[name="qty[]"]');
                let qty = parseInt(qtyInput.val()) || 0;

                if (qty > 1) {
                    qtyInput.val(qty - 1);
                } else {
                    currentSerialRow.remove();
                }

                updateTotalQuantity();
                currentSerialRow = null;
                
            }
             
            $('#SerialModal').modal('hide');
            $('#SerialModal').on('hidden.bs.modal', function() {
                $('#item_search').trigger('focus'); 
            });
        }

        $('#btnSubmit').on('click', function(e) {
            e.preventDefault(); 

            const form = document.getElementById('sts_create');
            if (form.checkValidity()) {
                Swal.fire({
                    title: 'Confirmation',
                    text: "Are you sure you want to create STS?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, create it!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            title: "Please wait while saving...",
                            didOpen: () => Swal.showLoading()
                        });
                        
                        form.submit(); 
                    }
                });
            } else {
                form.reportValidity();
            }
        });

        $(document).ready(function() {
            $(document).on("cut copy paste", function(e) {
                e.preventDefault();
            });
        });

        $('#SerialModal').on('shown.bs.modal', function () {
            $('#createSerial').trigger('focus');
        });

    </script>
@endpush
