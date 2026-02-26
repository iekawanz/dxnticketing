<?php
require('application_top.php');
require('session.php');

// Check if session has expired
if (!isset($_SESSION['loginData'])) {
    echo '<script type="text/javascript">
            window.parent.location.href = "login.php";
          </script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Content-Type" content="text/html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DXN Customer Support Ticketing System</title>
    <meta name="title" content="" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="copyright" content="" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

    <!--******** CSS ********-->
    <link href="css/reset.css" rel="stylesheet" type="text/css" media="all" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Material+Icons" rel="stylesheet">
    <link href="vendor/fontawesome-pro-6.0.0-beta2-web/css/all.css" rel="stylesheet">
    <link href="vendor/bootstrap-5.2.3/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/common.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/overwrite-bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <link href="css/animate.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        table.dataTable tbody td {
            white-space: nowrap;
        }

        .slide-panel {
            position: fixed;
            top: 0;
            left: 250px;
            /* offset from left */
            width: calc(100% - 250px);
            height: 100vh;
            background: #f8f9fa;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.3);
            transform: translateX(100%);
            transition: transform 0.4s ease-in-out;
            z-index: 9999;
            overflow-y: auto;
        }

        .slide-panel.show {
            transform: translateX(0);
            /* Slide into view */
        }

        .close-btn {
            cursor: pointer;
            margin-left: 1rem;
            padding: 0;
            font-size: 1.8rem;
        }


        .close-btn:hover {
            background-color: #ff4d4d;
            color: #fff;
        }

        #ticketTable tbody tr {
            cursor: pointer;
        }

        #ticketTable tbody tr:hover {
            background-color: #f0f8ff;
        }

        #ticketTable tbody td {
            font-size: 14px;
        }

        #ticketTable thead th {
            font-size: 14px;
        }

        .layer-header {
            display: flex;
            /* enable flexbox */
            justify-content: space-between;
            align-items: center;
            font-size: 1.2rem;
            line-height: 1.2;
            padding: 1rem;
            color: black;
            background-color: #dae3e5;
            border-bottom: 1px solid #ccc;
            font-weight: 700;
        }

        .msg {
            position: relative;
            border-bottom: 1px solid #b2cfce;
            padding: 1.4rem;
            background-color: white;
        }

        #response_list {
            height: 40vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .tox-tinymce-aux,
        .tox-dialog,
        .tox-fullscreen {
            z-index: 20000 !important;
        }
    </style>
</head>

<body>
    <div class="layer-right">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-5">
            <h1>My Ticket</h1>
        </div>
        <div class="form-general row mb-3">
            <div class="col-sm-4">
                <label class="text-start">Search</label>
                <input type="text" class="form-control" id="search" name="search">
            </div>
            <div class="col-sm-4">
                <label class="text-start">Category</label>
                <select id="filter_type" name="filter_type" class="form-control">
                    <option value="">All</option>
                    <option value="Enquire">Enquire</option>
                    <option value="Request">Request</option>
                    <option value="Complaint">Complaint</option>
                </select>
            </div>
            <div class="col-sm-4">
                <label class="text-start">Status</label>
                <select id="filter_status" name="filter_status" class="form-control">
                    <option value="">All</option>
                    <option value="In Progress" selected>In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>

        <table id="ticketTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Ticket No</th>
                    <th>Platform</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Req Close</th>
                </tr>
            </thead>
        </table>

        <div id="ticketDetailPanel" class="slide-panel">
            <div class="icon-loader" id="loadImage" style="margin-top: 200px; display:none;">
                <img src="images/icon-loading.gif">
            </div>
            <input type="hidden" id="id" name="id" value="" />
            <!-- Header with subject title + close button on same row -->
            <div class="d-flex justify-content-between align-items-center layer-header">
                <div id="subject_title" class="flex-grow-1">&nbsp;</div>
                <?php if($_SESSION['loginData']['allow_close'] == true){ ?>
                <input type="checkbox" id="chkRequestClose" class="form-check-input me-2"> Request to close the ticket
                &nbsp;
                <?php } ?>
                <div class="close-btn" onclick="closeDetailPanel()">×</div>
            </div>

            <div class="layer-message form-general">
                <!-- Responses will appear here -->
                <div class="layer-msg" id="response_list"></div>

                <!-- Response input -->
                <div class="layer-header" id="response_title">Response</div>
                <div class="p-3" id="response_input">
                    <div id="response_alert" class="alert d-none" role="alert"></div>
                    <textarea rows="2" class="tiny form-control" id="response" name="response"></textarea>
                    <small class="d-block text-end pe-4 lh-1 text-danger" id="response_error">&nbsp;</small>
                    <input type="file" id="file" name="file[]" class="my-3"
                        accept="image/*, .pdf, .doc, .docx, .txt, .zip" multiple>
                    <small class="d-block text-end pe-4 lh-1" id="file_error">
                        (Max file allowed: 2MB, allow all file type pdf, png, jpg, docx, txt, zip)
                    </small>

                    <div>&nbsp;</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" id="btnSend" class="btn-blue d-flex d-none align-items-center">
                            <span class="material-symbols-outlined">send</span>
                            <p>Submit</p>
                        </button>
                        <button type="button" id="btnClose" class="btn-outline d-flex d-none align-items-center"
                            data-bs-toggle="modal" data-bs-target="#closeticketModal">
                            <span class="material-symbols-outlined">check</span>
                            <p>Close Ticket</p>
                        </button>
                    </div>
                </div>

                <!-- Reopen section -->
                <div class="p-3" id="response_reopen">
                    <div class="d-flex justify-content-end">
                        <button type="button" id="btnReopen" class="btn-outline reopen d-none d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#reopenticketModal">
                            <p>Re-open Ticket</p>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ticket Info Table -->
            <table class="table table-hover bg-white mt-4">
                <thead class="table-primary">
                    <tr>
                        <th class="py-2">
                            <a class="d-flex justify-content-between align-items-center text-black text-decoration-none"
                                data-bs-toggle="collapse" href="#collapseInfo" role="button" aria-expanded="false"
                                aria-controls="collapseInfo">
                                <b>Ticket Info</b>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="collapse show" id="collapseInfo">
                    <tr>
                        <th id="ticket_name"></th>
                    </tr>
                    <tr>
                        <th id="ticket_level"></th>
                    </tr>
                    <tr>
                        <th id="ticket_category"></th>
                    </tr>
                    <tr>
                        <th id="ticket_created_date"></th>
                    </tr>
                    <tr>
                        <th id="ticket_closed_date"></th>
                    </tr>
                </tbody>
            </table>
            <!-- Modal -->
            <div class="modal fade" id="closeticketModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <!-- Modal Body -->
                        <div class="modal-body pt-0">
                            <div class="mx-auto text-center">
                                <h2 class="modal-title">Close Ticket</h2>
                                <div class="min-height">
                                    <p>Are you sure you want to close this ticket?</p>
                                </div>

                                <button type="submit" class="btn-blue" data-bs-toggle="modal"
                                    data-bs-target="#ratingModal">YES</button>

                                <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal"
                                    aria-label="Close">NO</button>
                            </div>
                        </div>
                        <!-- Modal Footer -->
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="successModal2">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
                        <!-- Modal Header -->
                        <div class="modal-header justify-content-end pb-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body d-flex align-items-center text-center p-4">
                            <div class="mx-auto">
                                <p class="mb-3" id="success_msg"></p>
                                <button class="btn-blue w-100 mt-5" type="button" data-bs-dismiss="modal">OK</button>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="successCloseTicketModal">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
                        <!-- Modal Header -->
                        <div class="modal-header justify-content-end pb-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body d-flex align-items-center text-center p-4">
                            <div class="mx-auto">
                                <p class="mb-3" id="successcloseticket_msg"></p>
                                <button class="btn-blue w-100 mt-5" type="button" data-bs-dismiss="modal"
                                    id="btnOK">OK</button>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="ratingModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <!-- Modal Body -->
                        <div class="modal-body pt-0">
                            <div class="mx-auto text-center">
                                <h2 class="modal-title">Services Rating</h2>
                                <div class="min-height">
                                    <p>Please rate your experience</p>
                                    <div class="layer-rate">
                                        <span class="star" data-value="1" style="cursor: pointer;">&#9733;</span>
                                        <span class="star" data-value="2" style="cursor: pointer;">&#9733;</span>
                                        <span class="star" data-value="3" style="cursor: pointer;">&#9733;</span>
                                        <span class="star" data-value="4" style="cursor: pointer;">&#9733;</span>
                                        <span class="star" data-value="5" style="cursor: pointer;">&#9733;</span>
                                    </div>
                                    <div class="layer-rate mb-5">
                                        <span class="fs-6 grey">Terrible</span>
                                        <span class="fs-6 grey">Poor</span>
                                        <span class="fs-6 grey">Fair</span>
                                        <span class="fs-6 grey">Good</span>
                                        <span class="fs-6 grey">Amazing</span>
                                    </div>
                                </div>
                                <input type="hidden" id="rating-value" name="rating" value="">
                                <input type="hidden" id="ticket_id" name="ticket_id" value="">
                                <button type="submit" id="btnCloseTicket" class="btn-blue">Submit</button>
                            </div>
                        </div>
                        <!-- Modal Footer -->
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="reopenticketModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content mx-auto w-100 rounded-4 bg-opacity-50">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <!-- Modal Body -->
                        <div class="modal-body pt-0">
                            <div class="mx-auto text-center">
                                <h2 class="modal-title">Re-open Ticket</h2>
                                <div class="min-height">
                                    <p>Are you sure you want to re-open this ticket?</p>
                                </div>

                                <button type="submit" class="btn-blue" id="btnReopenTicket">YES</button>

                                <button type="submit" class="btn-outline ms-2" data-bs-dismiss="modal"
                                    aria-label="Close">NO</button>
                            </div>
                        </div>
                        <input type="hidden" id="reopen_ticket_id" name="reopen_ticket_id" value="">
                        <!-- Modal Footer -->
                        <div class="modal-footer">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="vendor/tinymce/tinymce.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>

    <script>
        var table;
        var intervalId = null;
        $(document).ready(function () {
            table = $('#ticketTable').DataTable({  // ✅ save it in a variable
                serverSide: true,
                processing: true,
                searching: false,
                ajax: {
                    url: 'load_ticket_data.php',
                    type: 'GET',
                    data: function (d) {
                        d.custom_search = $('#search').val();
                        d.filter_type = $('#filter_type').val();
                        d.filter_status = $('#filter_status').val();
                    }
                },
                deferRender: true,
                pageLength: 10,
                lengthChange: true,
                lengthMenu: [10, 25, 50, 100],
                order: [[4, 'desc']],
                columns: [
                    { width: "5%", data: 'no' },
                    { width: "15%", data: 'ticket_no' },
                    { width: "5%", data: 'platform' },
                    { width: "45%", data: 'subject' },
                    { width: "15%", data: 'created_date' },
                    {
                        width: "12%",
                        data: 'request_close',
                        className: 'text-center',
                        render: function (data, type, row) {
                            return data == 1 ? '<i class="fas fa-check fa-lg text-success"></i>' : '';
                        }
                    }
                ],
                createdRow: function (row, data) {
                    // assign data-id to each row so you can fetch it on click
                    $(row).attr('data-id', data.id);
                    $(row).css('background-color', data.row_color);
                }
            });

            // ✅ This now works, because `table` is defined
            $('#search, #filter_type, #filter_status').on('change keyup', function () {
                table.ajax.reload();
            });

            $('#ticketTable tbody').on('click', 'tr', function () {
                var $clickedRow = $(this);
                var row = table.row(this);
                var id = $clickedRow.data('id');

                if (!id) return;

                // Close if already shown
                if (row.child.isShown()) {
                    row.child.hide();
                    $clickedRow.next('.loader-row').remove();
                    return;
                }

                // Clear previous loader
                $('.loader-row').remove();

                // Insert loader row directly after the clicked row
                var colCount = $clickedRow.find('td').length;
                var loaderRow = `
                    <tr class="loader-row">
                        <td colspan="${colCount}" class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </td>
                    </tr>
                `;
                $clickedRow.after(loaderRow);

                // Clear any previous refresh timer
                if (intervalId) clearInterval(intervalId);

                // Refresh every 15s only the response_list section
                intervalId = setInterval(function () {
                    refreshResponseList(id);
                }, 15000);


                // Show loader inside row first
                //row.child('<div style="padding: 20px;">Loading...</div>').show();

                $.ajax({
                    type: "POST",
                    url: "actions/Ticket.php",
                    data: { id: id, func: 'getResponse1' },
                    success: function (msg, ret) {
                        $('.loader-row').remove();
                        if (ret !== 'success') return;

                        try {
                            var result = eval('(' + msg + ')');
                            if (result.status == '1') {
                                // ✅ Inject response HTML into child row
                                //row.child(result.html).show();
                                openDetailPanel(result.html);
                                $('#id').val(result.data.id);
                                $('#id, #ticket_id, #reopen_ticket_id').val(result.data.id);

                                $('#subject_title').text(result.data.subject);
                                $('#ticket_name').text(result.data.name);
                                $('#ticket_level').text(result.data.level);
                                $('#ticket_category').text(result.data.category);
                                $('#ticket_created_date').text(result.data.accepted_date);
                                $('#ticket_closed_date').text(result.data.closed_date);

                                let isChecked = (Number(result.data.request_close) === 1);
                                $('#chkRequestClose').prop('checked', isChecked);

                                // Always hide btnSend if ticket is closed
                                if (result.data.btn_closed_ticket == 1) {
                                    //$('#btnSend').addClass('d-none'); // 🔴 HIDE Send button

                                    if (result.data.ticket_status == 'Completed') {
                                        // If ticket is completed, show Reopen button
                                        $('#btnSend').addClass('d-none');
                                        $('#btnReopen').removeClass('d-none');
                                        $('#btnClose').addClass('d-none');
                                        $('#response_title, #response_input').hide();
                                    } else {
                                        // In-progress: show Close, hide Reopen
                                        $('#btnSend').removeClass('d-none');
                                        $('#btnClose').removeClass('d-none');
                                        $('#btnReopen').addClass('d-none');
                                    }
                                } else {
                                    // Ticket is NOT closed
                                    $('#btnSend').removeClass('d-none');  // ✅ Show Send button
                                    $('#btnClose').addClass('d-none');
                                    $('#btnReopen').addClass('d-none');
                                }

                            }
                        } catch (e) {
                            //row.child('<div style="padding: 20px; color: red;">Error loading ticket</div>').show();
                            $clickedRow.after(`<tr class="loader-row"><td colspan="${colCount}" class="text-danger text-center">Error loading ticket</td></tr>`);
                        }
                    },
                    error: function () {
                        $('.loader-row').remove();
                        $clickedRow.after(`<tr class="loader-row"><td colspan="${colCount}" class="text-danger text-center">Failed to load data</td></tr>`);
                    }
                });
            });

            $('#btnSend').on('click', function () {
                sendResponse();
            });
            $('#btnCloseTicket').on('click', function () {
                closeTicket();
            });
            $('#btnReopenTicket').on('click', function () {
                reopenTicket();
            });

            $('.star').on('mouseover', function () {
                var ratingValue = $(this).data('value');
                highlightStars(ratingValue);
            });

            // Handle mouse out
            $('.star').on('mouseout', function () {
                var selectedRating = $('#rating-value').val();
                if (selectedRating) {
                    highlightStars(selectedRating);
                } else {
                    $('.star').removeClass('hover');
                }
            });

            // Handle click
            $('.star').on('click', function () {
                var ratingValue = $(this).data('value');
                $('#rating-value').val(ratingValue);
                highlightStars(ratingValue, true);
            });
        });

        function openDetailPanel(html) {
            $('#response_list').html(html);
            $('#ticketDetailPanel').addClass('show');
        }

        function closeDetailPanel() {
            clearInterval(intervalId);
            $('#response_alert').addClass('d-none').text('');
            $('#ticketDetailPanel').removeClass('show');
        }

        function refreshResponseList(ticketId) {
            $.ajax({
                type: 'POST',
                url: 'actions/Ticket.php',
                data: { id: ticketId, func: 'getResponse1' },
                success: function (msg) {
                    try {
                        var result = JSON.parse(msg);
                        if (result.status === '1') {
                            // ✅ Update ONLY the response_list (the .msg content)
                            $('#response_list').html(result.html);
                        }
                    } catch (e) {
                        console.error('Failed to refresh response list:', e);
                    }
                }
            });
        }

        function sendResponse() {
            tinymce.triggerSave();
            var errormsg = '';
            if ($('#response').val() == '') {
                errormsg = 'Please key in response.';
                $('#response_alert')
                    .removeClass('d-none alert-success')
                    .addClass('alert-danger')
                    .text(errormsg);
                return false;
            }
            else {
                //Hide the validation checking
                $('#response_alert').addClass('d-none').text('');
            }

            if (errormsg == '') {
                var postData = new FormData();
                var id = encodeURIComponent($('#id').val());
                var response = encodeURIComponent($('#response').val());
                //var file = $("#file").prop("files")[0];

                postData.append("id", id);
                postData.append("response", response);
                //postData.append("file", file);
                postData.append("func", "addNewResponse");

                var isValid = true;
                var files = $("#file")[0].files;
                var maxFileSize = 25 * 1024 * 1024;
                var allowedTypes = ["image/jpeg", "image/png", "application/pdf", "application/msword",
                    "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "text/plain",
                    "application/zip", "application/x-zip-compressed", "multipart/x-zip"];
                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        if (!allowedTypes.includes(files[i].type)) {
                            errormsg = "Invalid file type: " + files[i].name + ". Allowed: JPG, PNG, PDF, DOCX, TXT, ZIP.";
                            $('#response_alert')
                                .removeClass('d-none alert-success')
                                .addClass('alert-danger')
                                .text(errormsg);
                            $(this).val('');
                            isValid = false;
                            return false;
                        }

                        if (files[i].size > maxFileSize) {
                            errormsg = "File size exceeds the maximum limit of 25MB.";
                            $('#response_alert')
                                .removeClass('d-none alert-success')
                                .addClass('alert-danger')
                                .text(errormsg);
                            $(this).val('');
                            isValid = false;
                            return false;
                        }
                        postData.append("file[]", files[i]);
                    }
                }

                if (isValid) {
                    $('#response_alert').addClass('d-none').text('');
                }

                $('#loadImage').show();

                $.ajax({
                    type: "POST",
                    url: "actions/Ticket.php",
                    async: true,
                    data: postData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (msg, ret) {

                        if (ret != 'success') {
                            return;
                        }
                        try {
                            var result = eval('(' + msg + ')');

                            if (result.status == '1') {
                                $('#loadImage').hide();
                                tinymce.get('response').setContent('');
                                refreshResponseList(result.id);

                                $('#response, #file').val('');
                                $('#successModal2').modal('show');
                                $("#success_msg").html(result.msg);
                            }
                            else {
                                if (result.type == 'Response') {
                                    var msg = 'Please key in response.';
                                    $('#response_alert')
                                        .removeClass('d-none alert-success')
                                        .addClass('alert-danger')
                                        .text(msg);
                                }
                                else {
                                    //$('#response').removeClass('is-invalid');
                                    //$("#response_error").html("&nbsp;");
                                    $('#response_alert').addClass('d-none').text('');
                                }

                                if (result.type == 'File') {
                                    var msg = result.msg;
                                    $('#response_alert')
                                        .removeClass('d-none alert-success')
                                        .addClass('alert-danger')
                                        .text(msg);
                                    $(this).val('');
                                    return false;
                                }
                                else {
                                    $('#response_alert').addClass('d-none').text('');
                                }
                            }
                        }
                        catch (E) {
                            return;
                        }
                    }
                });
            }
        }

        function closeTicket() {
            $('#ratingModal').modal('hide');
            $("#collapseTicket").off('hide.bs.collapse');
            $("#collapseTicket").collapse('hide');

            var postData = new FormData();

            var postData = 'id=' + encodeURIComponent($('#ticket_id').val()) +
                '&rating=' + encodeURIComponent($('#rating-value').val()) +
                '&func=rateTicket';

            $.ajax({
                type: "POST",
                url: "actions/Ticket.php",
                async: true,
                data: postData,
                success: function (msg, ret) {

                    if (ret != 'success') {
                        return;
                    }
                    try {
                        var result = eval('(' + msg + ')');

                        if (result.status == '1') {
                            $('#successCloseTicketModal').modal('show');
                            $("#successcloseticket_msg").html(result.msg);
                        }
                        else {
                            return false;
                        }
                    }
                    catch (E) {
                        return;
                    }

                }
            });
        }

        function reopenTicket() {
            $("#collapseTicket").off('hide.bs.collapse');
            $("#collapseTicket").collapse('hide');

            var postData = new FormData();

            var postData = 'id=' + encodeURIComponent($('#reopen_ticket_id').val()) +
                '&func=updateTicketStatus';

            $.ajax({
                type: "POST",
                url: "actions/Ticket.php",
                async: true,
                data: postData,
                success: function (msg, ret) {

                    if (ret != 'success') {
                        return;
                    }
                    try {
                        var result = eval('(' + msg + ')');

                        if (result.status == '1') {
                            $('#reopenticketModal').modal('hide');
                            $('#successCloseTicketModal').modal('show');
                            $("#successcloseticket_msg").html(result.msg);
                        }
                        else {
                            return false;
                        }
                    }
                    catch (E) {
                        return;
                    }
                }
            });
        }

        // Function to highlight stars
        function highlightStars(ratingValue, isSelected) {
            $('.star').each(function () {
                var starValue = $(this).data('value');
                if (starValue <= ratingValue) {
                    $(this).addClass(isSelected ? 'checked' : 'checked');
                } else {
                    $(this).removeClass(isSelected ? 'checked' : 'checked');
                }
            });
        }

        $(document).on('click', function (e) {
            if (
                !$(e.target).closest(
                    '#ticketDetailPanel, tr, .tox, .tox-dialog, .tox-tinymce-aux'
                ).length
            ) {
                $('#response_alert').addClass('d-none').text('');
                closeDetailPanel();
            }
        });

        (function ($) {
            'use strict';

            $(document).ready(function () {
                if (typeof tinymce !== 'undefined') {
                    tinymce.init({
                        selector: '.tiny',
                        menubar: false,
                        height: 150,
                        forced_root_block: "",
                        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak ' +
                            'searchreplace wordcount visualblocks visualchars code fullscreen ' +
                            'insertdatetime media nonbreaking save table contextmenu directionality ' +
                            'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc',
                        toolbar: "undo redo | formatselect | bold italic underline strikethrough | " +
                            "link | preview fullscreen | forecolor",
                        branding: false,
                        setup: function (editor) {
                            editor.on('init', function () {
                                this.getBody().querySelectorAll('a').forEach(function (link) {
                                    link.removeAttribute('style'); // Remove inline styles from links
                                });
                                console.log('TinyMCE initialized successfully.');
                            });

                            editor.on("focus", function () {
                                editor.getContainer().style.height = "300px"; // Expand height on focus
                            });

                            editor.on("blur", function () {
                                editor.getContainer().style.height = "150px"; // Shrink height on blur
                            });
                        }
                    });
                } else {
                    console.error('TinyMCE is not loaded.');
                }
            });


        })(jQuery);

        $(document).on('change', '#chkRequestClose', function () {
            let isChecked = $(this).is(':checked') ? 1 : 0;
            let ticket_id = $('#ticket_id').val(); // or from data-attribute

            $.ajax({
                url: 'actions/Ticket.php',
                type: 'POST',
                data: {
                    id: ticket_id,
                    request: isChecked,
                    func: "requestCloseTicket"
                },
                success: function (response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.status === '1') {
                            var row = table.row('[data-id="' + ticket_id + '"]');
                            var rowData = row.data();
                            rowData.request_close = isChecked;
                            row.data(rowData).invalidate(); // re-render row
                        } else {
                            alert('Failed to update request close status.');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                },
                error: function () {
                    alert('Failed to update ticket status');
                }
            });
        });

    </script>
</body>

</html>