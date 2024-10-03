@extends('crudbooster::admin_template')
@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .modal-centered {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100%;
        }

        .modal-dialog {
            margin: 0 auto;
        }

        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .spinner {
            width: 55px;
            height: 55px;
            border: 10px solid rgba(253, 43, 43, 0.2);
            border-left-color: #de0303;
            border-radius: 50%;
            animation: spin 0.5s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Custom styles for making SweetAlert bigger */
        .swal-wide {
            width: 500px !important; /* Adjust the width as needed */
            font-size: 1.2em !important; /* Larger text if needed */
        }
    </style>

@endpush

@section('content')

    <!-- Modal for Change Password -->
    <div class="modal fade" id="tos-modal" role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
            <h5 class="modal-title" id="modalLabel"><b>Change Password</b></h5>
            </div>
            <div class="modal-body">
            <form id="changePasswordForm">
                <input type="hidden" name="waive_count" id="waive_count" value="{{ $user->waive_count }}">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>

                    <!-- Password strength meter -->
                    <div class="mt-3">
                        <label for="passwordStrengthBar" class="form-label">Password Strength:</label>
                        <div id="passwordStrengthBar" style="height: 10px; width: 100%; background-color: lightgray; border-radius: 5px;">
                            <div id="passwordStrengthFill" style="height: 100%; width: 0%; background-color: red; border-radius: 5px;"></div>
                        </div>
                    </div>
                    <ul class="list-unstyled mt-2">
                        <li id="lengthRequirement" style="color: red;">* Use at least 8 characters</li>
                        <li id="uppercaseRequirement" style="color: red;">* Include at least one uppercase letter (A-Z)</li>
                        <li id="numberRequirement" style="color: red;">* Add at least one number (0-9)</li>
                        <li id="specialCharRequirement" style="color: red;">* Include at least one special character (like !, @, #, $)</li>
                    </ul>
                </div>
                <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                </div>
            </form>
            </div>
            <div class="modal-footer bg-danger">
            <button type="button" class="btn btn-danger" id="btnWaive"><i class="fa fa-refresh"></i>Waive</button>
            <button type="button" class="btn btn-primary" id="btnSubmit"><i class="fa fa-key"></i>Change Password</button>
            </div>
        </div>
        </div>
    </div>

    <!-- Spinner (hidden by default) -->
    <div class="spinner-overlay" id="spinner" style="display: none;">
        <div class="spinner"></div>
    </div>

@endsection

@push('bottom')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Change Password Modal
            $("#tos-modal").modal('show');

            // When the modal is shown
            $('#tos-modal').on('shown.bs.modal', function () {
                // Push a new state to prevent back navigation
                history.pushState(null, null, window.location.href);

                // Listen for the popstate event, which is triggered when the user clicks the back button
                window.onpopstate = function (event) {
                    // Optionally, alert the user when they try to navigate away
                    Swal.fire({
                        title: 'Warning!',
                        text: 'You cannot go back while changing your password.',
                        icon: 'warning',
                        confirmButtonText: 'Okay',
                    });
                    // Prevent the back button from functioning by re-pushing the state
                    history.pushState(null, null, window.location.href);
                };
            });

            // When the modal is hidden (closed)
            $('#tos-modal').on('hidden.bs.modal', function () {
                // Remove the popstate event listener when the modal is closed
                window.onpopstate = null;
            });
        });

        $('#password, #confirm_password').on('paste', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Password',
                html: '<h5>Pasting is not allowed.</h5>',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
            return;
        });

        $('#password, #confirm_password').on('copy', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Password',
                html: '<h5>Copying is not allowed.</h5>',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        });

        $('#password, #confirm_password').on('cut', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Password',
                html: '<h5>Cutting is not allowed.</h5>',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        });

        $('#password, #confirm_password').on('contextmenu', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Password',
                html: '<h5>Right-click is not allowed.</h5>',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        });

        $('#password').on('input', function() {
            const password = $(this).val();

            // Password conditions
            const lengthCheck = password.length >= 8;
            const uppercaseCheck = /[A-Z]/.test(password);
            const numberCheck = /\d/.test(password);
            const specialCharCheck = /[\W_]/.test(password); // Special characters or underscore

            // Update UI elements based on validation
            $('#lengthRequirement').css('color', lengthCheck ? 'green' : 'red');
            $('#uppercaseRequirement').css('color', uppercaseCheck ? 'green' : 'red');
            $('#numberRequirement').css('color', numberCheck ? 'green' : 'red');
            $('#specialCharRequirement').css('color', specialCharCheck ? 'green' : 'red');

            // Calculate password strength score
            let strengthScore = 0;
            if (lengthCheck) strengthScore++;
            if (uppercaseCheck) strengthScore++;
            if (numberCheck) strengthScore++;
            if (specialCharCheck) strengthScore++;

            // Update the strength meter and color
            const meterFill = $('#passwordStrengthFill');
            let meterValue = '0%';
            let meterColor = 'red';  // Default: weak

            if (strengthScore === 1) {
                meterValue = '25%';
                meterColor = 'red'; // Weak password
            } else if (strengthScore === 2) {
                meterValue = '50%';
                meterColor = 'yellow'; // Good password
            } else if (strengthScore === 3) {
                meterValue = '75%';
                meterColor = 'yellow'; // Better password
            } else if (strengthScore === 4) {
                meterValue = '100%';
                meterColor = 'green'; // Strong password
            }

            // Update meter width and background color
            meterFill.css({
                'width': meterValue,
                'background-color': meterColor
            });

        });

        $('#btnSubmit').on('click', function(event) {
            event.preventDefault();

            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();

            // Password validation
            const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

            if (!passwordRegex.test(password)) {
                Swal.fire({
                    title: 'Invalid Password',
                    html: '<h5>Password must meet all the requirements.</h5>',
                    icon: 'error',
                    confirmButtonText: 'Okay'
                });
                return;
            }

            // Confirm password check
            if (password !== confirmPassword) {
                Swal.fire({
                    title: 'Password Mismatch',
                    html: '<h5>Password and Confirm Password do not match.</h5>',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
                return;
            }

            // If valid, proceed with the form submission
            $('#spinner').show();

            $.ajax({
                url: "{{ route('change-password') }}",
                method: 'POST',
                data: {
                    password: password,
                    password_confirmation: confirmPassword,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#spinner').hide();
                    if (response.success) {
                        $('#tos-modal').modal('hide');
                        Swal.fire({
                            title: 'Thank You!',
                            html: `<h5 style="font-size: 120%;">Your password has been changed successfully. <br> Please hold on for a few seconds.</h5>
                                <h1 id="timer-countdown"><b><strong> 3 </strong></b></h1>`,
                            icon: 'success',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            timer: 3000,
                            timerProgressBar: true,
                            backdrop: 'rgba(147, 250, 165, 0.7)',
                            willOpen: () => {
                                const timerElement = Swal.getHtmlContainer().querySelector('#timer-countdown');
                                let timeLeft = 3;
                                const timerInterval = setInterval(() => {
                                    timerElement.textContent = --timeLeft;
                                }, 1000);
                                Swal.willClose = () => clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                                window.location.href = '{{ route('getLogout') }}';
                            }
                        });
                    } else {
                        Swal.fire('Error', 'An error occurred while updating the password.', 'error');
                    }
                },
                error: function() {
                    $('#spinner').hide();
                    Swal.fire('Error', 'An error occurred during the request.', 'error');
                }
            });
        });

        $('#btnWaive').on('click', function(event) {
            const waive = parseInt($('#waive_count').val(), 10);

            if (waive >= 4) {
                Swal.fire({
                    title: 'Oops Sorry!',
                    html: '<h5 style="font-size: 120%;">You have already reached the waive limit, <br> Please update your password.</h5>',
                    icon: 'error',
                    confirmButtonText: '<i class="fa fa-thumbs-up"></i> Okay Got it!',
                });
                event.preventDefault();
                return;
            }

            const newWaive = waive + 1;

            $('#spinner').show();

            $.ajax({
                url: "{{ route('waive-change-password') }}",
                type: 'POST',
                data: {
                    waive: newWaive,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#tos-modal').modal('hide');
                    $('#spinner').hide();
                    if (response.success) {
                        $('#tos-modal').modal('hide');
                        window.location.href = '{{ CRUDBooster::adminPath() }}';
                    } else {
                        Swal.fire('Error', 'An error occurred while updating the password.', 'error');
                    }
                },
                error: function() {
                    $('#spinner').hide();
                    Swal.fire('Error', 'An error occurred during the request.', 'error');
                }
            });
        });

    </script>
@endpush
