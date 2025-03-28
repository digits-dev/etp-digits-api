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
            .select2-selection__arrow {
                height: 34px !important;
            }
            .select2-container--default .select2-selection--single {
                border-radius: 0px !important
            }

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

    <div class="panel panel-default" id="pullout_form">
        <div class="panel-heading">
            <h3 class="box-title text-center"><b>PULLOUT FORM</b></h3>
        </div>

        <div class="panel-body">

            <div class="col-md-12">
                <p style="font-size:16px; color:red; text-align:center;"><b>**PLEASE DO NOT MANUALLY TYPE THE DIGITS
                        CODE**</b></p>
            </div>

            <form action="{{ route('post-strma-pullout') }}" method="POST" id="str_create" autocomplete="off"
                role="form" enctype="multipart/form-data">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <input type="hidden" name="transfer_rma" id="transfer_rma" value="">
                <input type="hidden" name="transfer_branch" id="transfer_branch" value="">
                <input type="hidden" name="transfer_org" id="transfer_org" value="{{ $transfer_org }}">

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Pullout From: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" required name="pullout_from"
                            id="pullout_from">
                            <option value="">Please select a store</option>
                            @foreach ($transfer_from as $data)
                                <option value="{{ $data->warehouse_code }}">{{ $data->store_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Pullout To: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" required name="pullout_to"
                            id="pullout_to">
                            <option value="">Please select a store</option>
                            @foreach ($transfer_to as $data)
                                <option data-id="{{ $data->id }}" value="{{ $data->warehouse_code }}">
                                    {{ $data->store_name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="stores_id_destination_to" id="stores_id_destination_to">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Pullout Reason: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" required name="reason" id="reason">
                            <option value="">Please select a reason</option>
                            @foreach ($reasons as $data)
                                <option value="{{ $data->bea_reason }}"
                                    data-multiple-items="{{ $data->allow_multi_items }}">{{ $data->pullout_reason }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Transport By: <span class="required">*</span></label>
                        <select class="form-control select2" style="width: 100%;" required name="transport_type"
                            id="transport_type">
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

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Memo:</label>
                        <input class="form-control" type="text" name="memo" id="memo" maxlength="120" />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Pullout Date: <span class="required">*</span></label>
                        <input type='date' required name='pullout_date' id="pullout_date" onkeydown="return false"
                            autocomplete="off" class='form-control' required />
                    </div>
                </div>

                <br>

                <div class="col-md-12">
                    <h4 style="color: red;"><b>Note: </b></h4>
                    <h5 style="color: red;"><b>*If an item is a customer return, please use DAS (Digits Aftersales
                            System).</b>
                    </h5>
                    <h5 style="color: red;"><b>*No imaginary transaction. </b></h5>
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
                                        <th width="25%" class="text-center">Item Description</th>
                                        <th width="5%" class="text-center">Qty</th>
                                        <th width="25%" class="text-center">Problem Details</th>
                                        <th width="25%" class="text-center">Serial #</th>
                                        <th width="5%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="dynamicRows"></tr>
                                </tbody>
                                <tfoot>
                                    <tr class="tableInfo">
                                        <td colspan="2" align="right"><strong>Total Qty</strong></td>
                                        <td align="left" colspan="1">
                                            <input type='text' name="total_quantity" class="form-control text-center"
                                                id="totalQuantity" value="0" readonly>
                                        </td>
                                        </td>
                                        <td colspan="3"></td>
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
            <a href="#" id="cancelBtn" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary pull-right" type="button" id="btnSubmit"> <i class="fa fa-save"></i>
                Create</button>
        </div>
        </form>
    </div>

    <!-- The Modal -->
    <div class="modal fade" id="SerialModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="exampleModalCenterTitle"> <i class="fa fa-barcode"></i> Serial Number
                        <b><span id="scanned_code" style="color: yellow"></span></b>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">Serial Number</label>
                        <input type="text" name="createSerial" id="createSerial" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="CancelSerial()">Cancel</button>
                    {{-- <button type="button" class="btn btn-success">Create</button> --}}
                </div>
            </div>
        </div>
    </div>

@endsection


@push('bottom')
    <script src="{{ asset('vendor/crudbooster/assets/select2/dist/js/select2.full.min.js') }}"></script>
    <script src='https://cdn.jsdelivr.net/gh/admsev/jquery-play-sound@master/jquery.playSound.js'></script>

    <script>
        $(document).ready(function() {
            $(function(){
                $('body').addClass("sidebar-collapse");
            });
            $('#pullout_to').select2();
            $('#pullout_from').select2();
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

        $('#pullout_to').change(function() {
            const selectedDataId = $(this).find('option:selected').data('id');
            $('#stores_id_destination_to').val(selectedDataId);
        })

        function checkSelects() {
            const pullout_from = $('#pullout_from').val();
            const pullout_to = $('#pullout_to').val();
            const reason = $('#reason').val();
            const transport_by = $('#transport_type').val();

            if (pullout_from && pullout_to && reason && transport_by) {
                $('#item_search').attr('disabled', false);
            } else {
                $('#item_search').attr('disabled', true);
            }
        }

        $('#pullout_from, #pullout_to, #reason, #transport_type').on('change', checkSelects);
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
                            const problemRow = response.problems;
                            const digitsCode = row.digits_code;
                            const qty = 1;
                            const existingRow = tbody.find(
                                    `input[name="scanned_digits_code[]"][value="${digitsCode}"]`)
                                .closest('tr');

                            if (existingRow.length) {
                                // Item already exists; increment qty and show modal if serials are needed
                                const currentQty = parseInt(existingRow.find('input[name="qty[]"]')
                                .val()) || 0;
                                existingRow.find('input[name="qty[]"]').val(currentQty + 1);

                                // Track the row and show modal to enter additional serials if item has serials
                                if (row.has_serial == 1) {
                                    currentSerialRow = existingRow;
                                    $('#SerialModal').modal('show');
                                }
                            } else {
                                scannedDigitsCodes[digitsCode] = qty;
                                let problemOptions = '';
                                problemRow.forEach(problem => {
                                    problemOptions +=
                                        `<option data-id="${problem.id}" value="${problem.problem_details}">${problem.problem_details}</option>`;
                                });

                                const tr = `
                                    <tr>
                                        <td class="text-center">
                                            <input type="text" class="form-control" name="scanned_digits_code[]" id="scanned_digits_code" style="text-align:center" readonly value="${digitsCode || ''}">
                                            <input type="hidden" class="form-control" name="current_srp[]" style="text-align:center" readonly value="${row.current_srp || ''}">
                                        </td>
                                        <td class="text-center"><input type="text" class="form-control" name="item_description[]" style="text-align:center" readonly value="${row.item_description || ''}"></td>
                                        <td class="text-center"><input type="text" class="form-control" name="qty[]" style="text-align:center" readonly value="${qty}"></td>
                                        <td class="text-center">
                                            <select class="form-control select2 problems" style="width: 100%;" required name="problems[]" id="problems" multiple="multiple">
                                                ${problemOptions}
                                            </select>
                                            <input class="form-control problem_details" type="text" name="other_problem" id="other_problem" placeholder="Other problem here." style="display:none; margin-top: 5px;" />
                                            <input class="form-control" type="hidden" name="all_problems[]" id="all_problems" placeholder="Problem" style="margin-top: 5px;" readonly />
                                        </td>
                                        <td class="text-center serial-container">
                                            ${row.has_serial == 1 ? `<input type="text" class="form-control serial-input" name="serial[]" style="text-align:center" readonly>` : ''}
                                            <input type="hidden" class="form-control all-serial-input" name="allSerial[]" style="text-align:center" readonly>
                                        </td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
                                    </tr>
                                `;
                                tbody.append(tr);

                                const lastSelect = tbody.find('.select2').last();
                                lastSelect.select2({
                                    placeholder: 'Select problems',
                                    allowClear: true
                                });

                                lastSelect.on('change', function() {
                                    const selectedValues = $(this).val() || [];
                                    const row = $(this).closest('tr');
                                    const allProblemsInput = row.find(
                                        'input[name="all_problems[]"]');
                                    const otherProblemInput = row.find('.problem_details');

                                    const hasOthers = selectedValues.some(value => value
                                        .toLowerCase() === 'others');
                                    if (hasOthers) {
                                        otherProblemInput.show().attr('required', true);
                                    } else {
                                        otherProblemInput.hide().removeAttr('required');
                                        otherProblemInput.val('');
                                    }

                                    const updateAllProblems = () => {
                                        const problems = [...selectedValues.filter(value =>
                                            value.toLowerCase() !== 'others')];

                                        if (hasOthers && otherProblemInput.val().trim() !==
                                            '') {
                                            problems.push(
                                                `OTHERS - ${otherProblemInput.val().trim()}`
                                                );
                                        }
                                        allProblemsInput.val(problems.join(', '));
                                    };

                                    updateAllProblems();

                                    otherProblemInput.off('input').on('input', function() {
                                        updateAllProblems();
                                    });
                                });


                                if (row.has_serial == 1) {
                                    currentSerialRow = tbody.find(
                                        `input[name="scanned_digits_code[]"][value="${digitsCode}"]`
                                        ).closest('tr');
                                    $('#SerialModal').modal('show');
                                    $('#scanned_code').text(digitsCode);
                                }
                                updatedQtyInput = tbody.find(
                                        `input[name="scanned_digits_code[]"][value="${digitsCode}"]`)
                                    .closest('tr').find('input[name="qty[]"]');
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

            if (updatedQtyInput) {
                $(updatedQtyInput).css('background-color', 'yellow');
            }

            $('#totalQuantity').val(totalQty);
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

            const form = document.getElementById('str_create');
            if (form.checkValidity()) {
                Swal.fire({
                    title: 'Confirmation',
                    text: "Are you sure you want to create ST RMA?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, create it!',
                    reverseButtons: true,
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

        const dateInput = document.getElementById('pullout_date');
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);

        $('#SerialModal').on('shown.bs.modal', function () {
            $('#createSerial').trigger('focus');
        });
    </script>
@endpush
