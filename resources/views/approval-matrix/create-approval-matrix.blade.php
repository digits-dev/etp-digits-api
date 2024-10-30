@extends('crudbooster::admin_template')
@section('content')
    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <style type="text/css">
            .row{
                padding: 14px !important;
            }
            .control-label{
                text-align: right !important;
                margin-top: 10px !important
            }

            .select2-container--default .select2-selection--multiple {
                border-color: #3498db !important;
                border-radius: 7px;
                padding: 6px 0 8px 10px;
            }

            .select2-container {
                width: 100% !important;
            }

            .select2-container--default .select2-selection__choice {
                background-color: #3498db !important;
                color: #ffffff !important;
                border: 1px solid #2980b9 !important;
            }

            .select2-container--default .select2-selection__choice:hover {
                background-color: #2980b9 !important;
                color: #ffffff !important;
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
            <i class="fa fa-circle-o"></i> <b>Add Approval Matrix</b></h3>
        </div>
        <form action="{{route('add-approval-matrix')}}" method="POST" id="userForm" enctype="multipart/form-data">
            <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">

            <div class='panel-body' id="pullout-details">
                <div class="container">
                <div class="row">
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="privilege">Privilege Name  <span class="text-red">*</span></label>
                        <div class="col-sm-5">
                            <select selected data-placeholder="** Please select a Privilege" class="form-control select2" id="privilege" name="privilege" required>
                                <option></option>
                                @foreach($privileges as $priv)
                                    <option value="{{ $priv->id }}">{{ $priv->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="approver_id">Approver Name  <span class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select selected data-placeholder="** Please select a Approver" class="form-control select2" id="approver_id" name="approver_id" required>
                                
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="channel">Channel <span class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select selected data-placeholder="** Please select a Channel" class="form-control select2" id="channel" name="channels_id" required>
                                    <option></option>
                                    @foreach($channels as $chanel)
                                        <option value="{{ $chanel->id }}">{{ $chanel->channel_description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="store_ids">Store List  <span class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select class="form-control select2" id="store_ids" name="store_ids[]" required multiple>
                                    
                                </select>
                            </div>
                        </div>
                    </div>
                
                </div>
            </div>

            <div class='panel-footer'>
                <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default"> <i class="fa fa-chevron-circle-left"></i> Back</a>
                <button class="btn btn-success" type="submit" id="btnSubmit"> <i class="fa fa-save" ></i> Save</button>
            </div>
        </form>
    </div>
@endsection
@push('bottom')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2();

            $('#privilege').change(function () {
                let privilege = $(this).val();
                $.ajax({
                    url: "{{ route('getApprovers') }}",
                    type: "POST",
                    data: {
                        privilege_id: privilege,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        $('#approver_id').empty();
                        $.each(data, function (key, value) {
                            $('#approver_id').append(`<option></option>`);
                            $('#approver_id').append(`<option value="${key}">${value}</option>`);
                        });
                    }
                });
            });

            $('#channel').change(function () {
                let channel = $(this).val();
                $.ajax({
                    url: "{{ route('getStores') }}",
                    type: "POST",
                    data: {
                        channel_id: channel,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        $('#store_ids').empty();
                        $('#store_ids').append(`<option value="all"> All</option>`);
                        $.each(data, function (key, value) {
                            $('#store_ids').append(`<option value="${key}">${value}</option>`);
                        });
                    }
                });
            });

            // Flag to prevent infinite loops
            let preventChangeEventLoop = false;

            $('#store_ids').on('change', function() {
                if (preventChangeEventLoop) return;
                preventChangeEventLoop = true;

                var $selects = $('#store_ids');
                var selectedValues = $(this).val() || []; // Ensure it has an array
                var selectAllOption = 'all';
                var isAllSelected = selectedValues.includes(selectAllOption);

                // Initialize Select2 with allowClear
                $selects.select2({ allowClear: true });

                if (isAllSelected) {
                    // If "Select All" is selected
                    $selects.find('option').each(function() {
                        if (this.value !== selectAllOption) {
                            $(this).prop('disabled', true); // Disable specific store options
                        }
                    });
                    $selects.val([selectAllOption]).trigger('change.select2'); // Retain only "Select All" in the UI
                } else {
                    // If "Select All" is removed or specific stores are selected
                    $selects.find('option[value="' + selectAllOption + '"]').prop('disabled', false);

                    // Enable other specific options
                    $selects.find('option').each(function() {
                        if (this.value !== selectAllOption) {
                            $(this).prop('disabled', false);
                        }
                    });
                }

                // Reset the selected values if "All" is selected, for backend purposes
                if (isAllSelected) {
                    $selects.val([selectAllOption]); // Ensure only "All" is kept as the selected value
                }

                $selects.select2({ allowClear: true }); // Refresh the select2 display with updated options
                preventChangeEventLoop = false;
            });
        });
    </script>
@endpush
