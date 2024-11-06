@extends('crudbooster::admin_template')
@section('content')
    @push('head')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <style type="text/css">
            .row {
                padding: 14px !important;
            }

            .control-label {
                text-align: right !important;
                margin-top: 10px !important;
            }

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
            <i class="fa fa-circle-o"></i> <b>Edit Approval Matrix</b></h3>
        </div>
        <form action="{{ route('update-approval-matrix') }}" method="POST" id="userForm" enctype="multipart/form-data">
            <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
            <input type="hidden" value="{{ $approval_matrix->id }}" name="approval_matrix_id" id="approval_matrix_id">

            <div class='panel-body' id="pullout-details">
                <div class="container">
                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="privilege">Privilege Name <span
                                    class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select data-placeholder="** Please select a Privilege" class="form-control select2"
                                    id="privilege" name="privilege" required>
                                    <option></option>
                                    @foreach ($privileges as $priv)
                                        <option value="{{ $priv->id }}"
                                            {{ $approval_matrix->cms_privileges_id == $priv->id ? 'selected' : '' }}>
                                            {{ $priv->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="approver_id">Approver Name <span
                                    class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select data-placeholder="** Please select an Approver" class="form-control select2"
                                    id="approver_id" name="approver_id" required>
                                    <!-- Existing approver will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="channel">Channel <span
                                    class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select data-placeholder="** Please select a Channel" class="form-control select2"
                                    id="channel" name="channels_id" required>
                                    <option></option>
                                    @foreach ($channels as $chanel)
                                        <option value="{{ $chanel->id }}"
                                            {{ $approval_matrix->channel_id == $chanel->id ? 'selected' : '' }}>
                                            {{ $chanel->channel_description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="store_ids">Store List <span
                                    class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <select class="form-control select2" id="store_ids" name="store_ids[]" required multiple>
                                    <!-- Existing stores will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='panel-footer'>
                <a href="{{ CRUDBooster::mainpath() }}" class="btn btn-default"> <i class="fa fa-chevron-circle-left"></i>
                    Back</a>
                <button class="btn btn-success pull-right" type="submit" id="btnSubmit"> <i class="fa fa-save"></i> Save
                    Changes</button>
            </div>
        </form>
    </div>
@endsection
@push('bottom')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2({
                allowClear: true
            });

            let token = $("#token").val();

            // Load initial approver based on privilege
            $('#privilege').change(function() {
                loadApprovers($(this).val(), {{ $approval_matrix->cms_users_id }});
            }).trigger('change');

            // Load initial stores based on channel
            $('#channel').change(function() {
                loadStores($(this).val(), @json($approval_matrix->store_list));
            }).trigger('change');

            // Load approvers based on selected privilege
            function loadApprovers(privilegeId, selectedApproverId) {
                $.ajax({
                    url: "{{ route('getApprovers') }}",
                    type: "POST",
                    data: {
                        privilege_id: privilegeId,
                        _token: token
                    },
                    success: function(data) {
                        $('#approver_id').empty().append('<option></option>');
                        $.each(data, function(key, value) {
                            let selected = key == selectedApproverId ? 'selected' : '';
                            $('#approver_id').append(
                                `<option value="${key}" ${selected}>${value}</option>`);
                        });
                    }
                });
            }

            // Load stores based on selected channel
            function loadStores(channelId, selectedStoreIds) {
                $.ajax({
                    url: "{{ route('getStores') }}",
                    type: "POST",
                    data: {
                        channel_id: channelId,
                        _token: token
                    },
                    success: function(data) {
                        $('#store_ids').empty().append('<option value="all">All</option>');
                        $.each(data, function(key, value) {
                            let selected = selectedStoreIds.includes(key.toString()) ?
                                'selected' : '';
                            $('#store_ids').append(
                                `<option value="${key}" ${selected}>${value}</option>`);
                        });
                    }
                });
            }

            // Handle "Select All" for stores
            $('#store_ids').on('change', function() {
                let selectedValues = $(this).val() || [];
                const selectAllOption = 'all';
                const isAllSelected = selectedValues.includes(selectAllOption);

                if (isAllSelected) {
                    $('#store_ids').val([selectAllOption]).trigger('change');
                    $('#store_ids option').not(`[value="${selectAllOption}"]`).prop('disabled', true);
                } else {
                    $('#store_ids option').prop('disabled', false);
                }
            });
        });
    </script>
@endpush
