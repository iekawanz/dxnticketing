$(document).ready(function() {
    $('#btnSubmitTicket').click(submitTicket);
    $('#btnSend').click(sendResponse);
    $('#btnCloseTicket').click(closeTicket);
    $('#btnReopenTicket').click(reopenTicket);

    $('#file').change(function() {
        var files = this.files;
        var maxFileSize = 25 * 1024 * 1024; //(25MB)
        var allowedTypes = [
            'image/jpeg', 'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
            'multipart/x-zip'
        ];
        var isValid = true;

        $.each(files, function(index, file) {
            if (!allowedTypes.includes(file.type)) {
                errormsg = `Invalid file type: ${file.name}. Allowed: JPG, PNG, PDF, DOCX, TXT, ZIP.`;
                $('#file').addClass('is-invalid');
                $('#file_error').addClass('text-danger');
                $("#file_error").html(errormsg);
                $(this).val('');
                isValid = false;
                return false;
            }
            if (file.size > maxFileSize) {
                errormsg = 'File size exceeds the maximum limit of 25MB.';
                $('#file').addClass('is-invalid');
                $('#file_error').addClass('text-danger');
                $("#file_error").html(errormsg);
                $(this).val('');
                isValid = false;
                return false;
            }
        });

        if(isValid)
        {
            var msg = '(Max file allowed: 25MB, allow all file type pdf, png, jpg, docx, txt, zip)';
            $('#file').removeClass('is-invalid');
            $('#file_error').removeClass('text-danger');
            $("#file_error").html(msg);
        }

        /*if (!allowedTypes.includes(file.type)) 
        {
            errormsg = 'Invalid file type. Please upload an image, PDF, DOC, DOCX or TXT.';
            $('#file').addClass('is-invalid');
            $('#file_error').addClass('text-danger');
            $("#file_error").html(errormsg);
            $(this).val('');
            return false;
        }
        else if(file.size > maxFileSize)
        {
            errormsg = 'File size exceeds the maximum limit of 2MB.';
            $('#file').addClass('is-invalid');
            $('#file_error').addClass('text-danger');
            $("#file_error").html(errormsg);
            $(this).val('');
            return false;
        }
        else
        {
            var msg = '(Max file allowed: 2MB, allow all file type pdf, png, jpg, docx, txt)';
            $('#file').removeClass('is-invalid');
            $('#file_error').removeClass('text-danger');
            $("#file_error").html(msg);
        }*/
    });

    var table = $('#myTable').DataTable({
        paging: true,
        pageLength: 10,
        lengthChange: false,
        autoWidth: false,
        deferRender: true,
        order: [[6, 'desc']],
        language: {
            paginate: {
                next: '<span class="material-symbols-outlined">chevron_right</span>',
                previous: '<span class="material-symbols-outlined">chevron_left</span>'
            }
        },
        searchCols: [
            null, null, null, null, null, null, null,
            { search: "In Progress" } // column 7 filter
        ],
        columns: [
            { width: "5%" },
            { width: "15%" },
            { width: "8%" },
            { width: "50%" },
            { width: "20%" },
            null,
            null,
            null
        ],
        columnDefs: [
            { targets: [5, 6, 7], visible: false, searchable: true }
        ],
        drawCallback: function(settings) {
            var api = this.api();
            api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + api.page.info().start;
            });
        },
        initComplete: function(settings, json) {
            $('.dataTables_paginate').appendTo('ul');
            $('.dataTables_info').appendTo('.page-info');
            $('.dataTables_length select').appendTo('.page-length');
            $('#hidetab').show();
        }
    });


    $('#entries-select').on('change', function() {
        var selectedValue = $(this).val();
        table.page.len(selectedValue).draw();
    });

    $("#myTable_paginate").addClass("pagination pagination-lg d-flex justify-content-center align-items-center");

    $('#search').on('input', function() {
        var selectedValue = $(this).val();
        $('.dataTables_filter input').val(selectedValue).keyup();
    });

    $('#filter_type').on('change', function() {
        var selectedValue = $(this).val();
        $('.dataTables_filter input').val(selectedValue).keyup();
    });

    $('#filter_status').on('change', function() {
        var selectedValue = $(this).val();
        //$('.dataTables_filter input').val(selectedValue).keyup();

        var table = $('#myTable').DataTable();
        table.column(7).search("^" + selectedValue + "$", true, false).draw();
    });

    let intervalId;
    $('#myTable tbody').on('click', 'tr', function(e) {
        var isVisible = $("#collapseTicket").is(":visible");
        var delay = "";

        if(isVisible)
        {
            $("#collapseTicket").on('hide.bs.collapse', function (e) {
                e.preventDefault(); // Prevent the collapse from hiding
            });
        }

        if ($('#collapseTicket').hasClass('show')) {
            delay = 0;
        } else {
            delay = 3000;
        }
        
        var id = $(this).data('id');

        if (intervalId) {
            clearInterval(intervalId);
        }

        intervalId = setInterval(function() {
            fetchRowData(id);
        }, 15000);

        var postData = 'id='+encodeURIComponent(id) +'&func=getResponse';
        $('#loadImage').show();

        $.ajax({
            type: "POST",
            url: "actions/Ticket.php",
            async:true,
            data: postData,
            success: function(msg,ret){
                
                if( ret != 'success' )
                {
                    return;
                }
                try {
                    var result = eval( '(' + msg +')' );
    
                    if( result.status == '1' )
                    {  
                        setTimeout(function() {
                            if(result.data.ticket_status == 'Completed')
                            {
                                $('#response_title, #response_input').hide();
                                $('#response_reopen').show();
                            }
                            else
                            {
                                $('#response_title, #response_input').show();
                                $('#response_reopen').hide();
                            }
                                
                            $('#loadImage').hide();
                            $('#id, #ticket_id, #reopen_ticket_id').val(result.data.id);

                            $('#subject_title').text(result.data.subject);
                            $('#response_list').html(result.html);

                            //Assign Ticket Info
                            $('#ticket_name').text(result.data.name);
                            $('#ticket_level').text(result.data.level);
                            $('#ticket_category').text(result.data.category);
                            $('#ticket_created_date').text(result.data.accepted_date);
                            $('#ticket_closed_date').text(result.data.closed_date);

                            $('.layer-message').removeClass('d-none');
                            $('.table.table-hover.bg-white.mt-4').removeClass('d-none');
                            //$('#response_input').addClass('d-block');
                            //$('#collapseInfo').addClass('d-block');

                            //Only the person who rise the ticket able to close the ticket.
                            if(result.data.btn_closed_ticket == 1)
                            {
                                if(result.data.ticket_status == 'Completed')
                                    $('.btn-text').removeClass('d-none');

                                $('.btn-blue').removeClass('d-none');
                                $('.btn-outline').removeClass('d-none');
                            }
                            else
                            {
                                if(result.data.ticket_status == 'Completed')
                                    $('.btn-outline reopen').addClass('d-none');

                                $('.btn-blue').removeClass('d-none');
                                $('.btn-outline').addClass('d-none');
                            }

                        }, delay);
                        $('#collapseTicket').collapse('show');
                        $('#response_list').scrollTop(0);
                    }
                    else
                    {
                        return false;
                    }
                }
                catch(E)
                {
                    return;   
                } 
    
            }
        });
    });
    
    $('#myTable tbody tr').on('click', function(e) {
        var isVisible = $("#collapseTicket").is(":visible");
        var delay = "";

        if(isVisible)
        {
            $("#collapseTicket").on('hide.bs.collapse', function (e) {
                e.preventDefault(); // Prevent the collapse from hiding
            });
        }

        if ($('#collapseTicket').hasClass('show')) {
            delay = 0;
        } else {
            delay = 3000;
        }
        
        var id = $(this).data('id');

        /*setInterval(function() {
            fetchRowData(id);
        }, 10000);*/

        var postData = 'id='+encodeURIComponent(id) +'&func=getResponse';
        $('#loadImage').show();

        $.ajax({
            type: "POST",
            url: "actions/Ticket.php",
            async:true,
            data: postData,
            success: function(msg,ret){
                
                if( ret != 'success' )
                {
                    return;
                }
                try {
                    var result = eval( '(' + msg +')' );
    
                    if( result.status == '1' )
                    {  
                        setTimeout(function() {
                            if(result.data.ticket_status == 'Completed')
                            {
                                $('#response_title, #response_input').hide();
                                $('#response_reopen').show();
                            }
                            else
                            {
                                $('#response_title, #response_input').show();
                                $('#response_reopen').hide();
                            }
                                
                            $('#loadImage').hide();
                            $('#id, #ticket_id, #reopen_ticket_id').val(result.data.id);

                            $('#subject_title').text(result.data.subject);
                            $('#response_list').html(result.html);

                            //Assign Ticket Info
                            $('#ticket_name').text(result.data.name);
                            $('#ticket_level').text(result.data.level);
                            $('#ticket_category').text(result.data.category);
                            $('#ticket_created_date').text(result.data.accepted_date);
                            $('#ticket_closed_date').text(result.data.closed_date);

                            //Only the person who rise the ticket able to close the ticket.
                            if(result.data.btn_closed_ticket == 1)
                            {
                                if(result.data.ticket_status == 'Completed')
                                    $('.btn-text').removeClass('d-none');

                                $('.btn-blue').removeClass('d-none');
                                $('.btn-outline').removeClass('d-none');
                            }
                            else
                            {
                                if(result.data.ticket_status == 'Completed')
                                    $('.btn-outline reopen').addClass('d-none');

                                $('.btn-blue').removeClass('d-none');
                                $('.btn-outline').addClass('d-none');
                            }

                        }, delay);
                        $('#collapseTicket').collapse('show');
                        $('#response_list').scrollTop(0);
                    }
                    else
                    {
                        return false;
                    }
                }
                catch(E)
                {
                    return;   
                } 
    
            }
        });
    });

    $(document).on('click', 'body', function(event) {
        if (
            $(event.target).closest('#successModal2').length > 0 ||
            $(event.target).closest('#closeticketModal').length > 0 ||
            $(event.target).closest('#ratingModal').length > 0 ||
            $(event.target).closest('#reopenticketModal').length > 0
        ) 
        {
            event.preventDefault();
        } 
        else 
        {
            if (!$(event.target).closest('#collapseTicket').length > 0) 
            {
                /*if (!$(event.target).is("tr, tr *")) {
                    $("#collapseTicket").off('hide.bs.collapse');
                    $('#subject_title').html('&nbsp');
                    $('#response_list').html('');
                    $("#collapseTicket").collapse('hide');
                }*/
            }
            else
            {
                $(document).on('click', '.btn-mini, .btn-outline ms-2', function() {
                    // Unbind the hide event for #collapseTicket
                    $("#collapseTicket").off('hide.bs.collapse');
                    $('.layer-message').addClass('d-none');
                    $('.table.table-hover.bg-white.mt-4').addClass('d-none');
                    // Collapse the #collapseTicket element
                    $("#collapseTicket").collapse('hide');
                });
            }
        }
    });

    $('#btnOK').on('click', function() {
        location.reload();
    });

    $('.star').on('mouseover', function() {
        var ratingValue = $(this).data('value');
        highlightStars(ratingValue);
    });

    // Handle mouse out
    $('.star').on('mouseout', function() {
        var selectedRating = $('#rating-value').val();
        if (selectedRating) {
            highlightStars(selectedRating);
        } else {
            $('.star').removeClass('hover');
        }
    });

    // Handle click
    $('.star').on('click', function() {
        var ratingValue = $(this).data('value');
        $('#rating-value').val(ratingValue);
        highlightStars(ratingValue, true);
    });
});

function submitTicket()
{
    tinymce.triggerSave();
    errormsg = "";

    if($('#type').val() == "")
    {
        errormsg = 'Please select option.';
        $('#type').addClass('is-invalid');
        $("#type_error").html(errormsg);
        return false;
    }
    else
    {
        $('#type').removeClass('is-invalid');
        $("#type_error").html("&nbsp;");
    }

    if($('#about').val() == "")
    {
        errormsg = 'Please select about.';
        $('#about').addClass('is-invalid');
        $("#about_error").html(errormsg);
        return false;
    }
    else
    {
        $('#about').removeClass('is-invalid');
        $("#about_error").html("&nbsp;");
    }

    if($('#subject').val() == "")
    {
        errormsg = 'Please key in subject.';
        $('#subject').addClass('is-invalid');
        $("#subject_error").html(errormsg);
        return false;
    }
    else
    {
        $('#subject').removeClass('is-invalid');
        $("#subject_error").html("&nbsp;");
    }

    if($('#detail').val() == "")
    {
        errormsg = 'Please key in detail.';
        $('#detail').addClass('is-invalid');
        $('#detail_error').addClass('text-danger');
        $("#detail_error").html(errormsg);
        return false;
    }
    else
    {
        var msg = 'Length (Max <b>2000</b> characters)';
        $('#detail').removeClass('is-invalid');
        $('#detail_error').removeClass('text-danger');
        $("#detail_error").html(msg);
    }

    if ($("#country_pic").length === 1) 
    {
        if($('#country_pic').val() == "")
        {
            errormsg = 'Please select Country PIC.';
            $('#country_pic').addClass('is-invalid');
            $("#pic_error").html(errormsg);
            return false;
        }
        else
        {
            $('#country_pic').removeClass('is-invalid');
            $("#pic_error").html("&nbsp;");
        }
    }

    if(errormsg == '')
    {
        var postData = new FormData(); 

        var type = $('#type').val();
        var about = $('#about').val();
        var subject = encodeURIComponent($('#subject').val());
        var detail = encodeURIComponent($('#detail').val());
        var country_pic = $('#country_pic').val();

        postData.append("type", type );
        postData.append("about", about );
        postData.append("subject", subject );
        postData.append("detail", detail );
        postData.append("country_pic", country_pic );
        postData.append("func", "addNewTicket" );

        //var file = $("#file").prop("files")[0];
        //postData.append("file", file);

        var isValid = true;
        var files = $("#file")[0].files;
        var maxFileSize = 25 * 1024 * 1024;
        var allowedTypes = ["image/jpeg", "image/png", "application/pdf", "application/msword", 
                            "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "text/plain",
                            "application/zip","application/x-zip-compressed","multipart/x-zip"];
        if(files.length > 0)
        {
            for (let i = 0; i < files.length; i++) 
            {
                if (!allowedTypes.includes(files[i].type)) 
                {
                    errormsg = "Invalid file type: " + files[i].name +". Allowed: JPG, PNG, PDF, DOCX, TXT, ZIP.";
                    $('#file').addClass('is-invalid');
                    $('#file_error').addClass('text-danger');
                    $("#file_error").html(errormsg);
                    $(this).val('');
                    isValid = false;
                    return false;
                }

                if (files[i].size > maxFileSize) 
                {
                    errormsg = "File size exceeds the maximum limit of 25MB.";
                    $('#file').addClass('is-invalid');
                    $('#file_error').addClass('text-danger');
                    $("#file_error").html(errormsg);
                    $(this).val('');
                    isValid = false;
                    return false;
                }
                postData.append("file[]", files[i]);
            }
        }

        if(isValid)
        {
            var msg = '(Max file allowed: 25MB, allow all file type pdf, png, jpg, docx, txt, zip)';
            $('#file').removeClass('is-invalid');
            $('#file_error').removeClass('text-danger');
            $("#file_error").html(msg);
        }

        $('#loadImage').show();
        
        $.ajax({
            type: "POST",
            url: "actions/Ticket.php",
            async:true,
            data: postData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(msg,ret){
                
                if( ret != 'success' )
                {
                    return;
                }
                try {
                    var result = eval( '(' + msg +')' );

                    $('#loadImage').hide();
    
                    if( result.status == '1' )
                    {
                        tinymce.get('detail').setContent('');
                        $("input[type='text']").val("");
                        $('#type, #about, #file, #country_pic').val('');
                        $('#successModal').modal('show');
                        $("#success_msg").html(result.msg);
                    }
                    else
                    {
                        if(result.type == 'Type')
                        {
                            errormsg = result.msg;
                            $('#type').addClass('is-invalid');
                            $("#type_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#type').removeClass('is-invalid');
                            $("#type_error").html("&nbsp;");
                        }

                        if(result.type == 'About')
                        {
                            errormsg = result.msg;
                            $('#about').addClass('is-invalid');
                            $("#about_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#about').removeClass('is-invalid');
                            $("#about_error").html("&nbsp;");
                        }

                        if(result.type == 'Subject')
                        {
                            errormsg = result.msg;
                            $('#subject').addClass('is-invalid');
                            $("#subject_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#subject').removeClass('is-invalid');
                            $("#subject_error").html("&nbsp;");
                        }

                        if(result.type == 'Detail')
                        {
                            errormsg = result.msg;
                            $('#detail').addClass('is-invalid');
                            $('#detail_error').addClass('text-danger');
                            $("#detail_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            var msg = 'Length (Max <b>2000</b> characters)';
                            $('#detail').removeClass('is-invalid');
                            $('#detail_error').removeClass('text-danger');
                            $("#detail_error").html(msg);
                        }

                        if(result.type == 'File')
                        {
                            var msg = result.msg;
                            $('#file').addClass('is-invalid');
                            $('#file_error').addClass('text-danger');
                            $("#file_error").html(msg);
                            $(this).val('');
                            return false;
                        }
                        else
                        {
                            var msg = '(Max file allowed: 25MB, allow all file type pdf, png, jpg, docx, txt, zip)';
                            $('#file').removeClass('is-invalid');
                            $('#file_error').removeClass('text-danger');
                            $("#file_error").html(msg);
                        }

                        if(result.type == 'CountryPIC')
                        {
                            errormsg = result.msg;
                            $('#country_pic').addClass('is-invalid');
                            $("#pic_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#country_pic').removeClass('is-invalid');
                            $("#pic_error").html("&nbsp;");
                        }
                    }
                }
                catch(E)
                {
                    return;   
                } 
    
            }
        });
        
    }

}

function sendResponse()
{
    tinymce.triggerSave();
    var errormsg = '';
    if($('#response').val() == '')
    {
        errormsg = 'Please key in response.';
        $('#response').addClass('is-invalid');
        $("#response_error").html(errormsg);
        return false;
    }
    else
    {
        //Hide the validation checking
        $('#response').removeClass('is-invalid');
        $("#response_error").html("&nbsp;");
    }
    
    if(errormsg == '')
    {
        var postData = new FormData();
        var id = encodeURIComponent($('#id').val());
        var response = encodeURIComponent($('#response').val());
        //var file = $("#file").prop("files")[0];

        postData.append("id", id);
        postData.append("response", response);
        //postData.append("file", file);
        postData.append("func", "addNewResponse" );

        var isValid = true;
        var files = $("#file")[0].files;
        var maxFileSize = 25 * 1024 * 1024;
        var allowedTypes = ["image/jpeg", "image/png", "application/pdf", "application/msword", 
                            "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "text/plain",
                            "application/zip","application/x-zip-compressed","multipart/x-zip"];
        if(files.length > 0)
        {
            for (let i = 0; i < files.length; i++) 
            {
                if (!allowedTypes.includes(files[i].type)) 
                {
                    errormsg = "Invalid file type: " + files[i].name +". Allowed: JPG, PNG, PDF, DOCX, TXT, ZIP.";
                    $('#file').addClass('is-invalid');
                    $('#file_error').addClass('text-danger');
                    $("#file_error").html(errormsg);
                    $(this).val('');
                    isValid = false;
                    return false;
                }

                if (files[i].size > maxFileSize) 
                {
                    errormsg = "File size exceeds the maximum limit of 25MB.";
                    $('#file').addClass('is-invalid');
                    $('#file_error').addClass('text-danger');
                    $("#file_error").html(errormsg);
                    $(this).val('');
                    isValid = false;
                    return false;
                }
                postData.append("file[]", files[i]);
            }
        }

        if(isValid)
        {
            var msg = '(Max file allowed: 25MB, allow all file type pdf, png, jpg, docx, txt, zip)';
            $('#file').removeClass('is-invalid');
            $('#file_error').removeClass('text-danger');
            $("#file_error").html(msg);
        }

        $('#loadImage').show();

        $.ajax({
                type: "POST",
                url: "actions/Ticket.php",
                async:true,
                data: postData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(msg,ret){
                    
                    if( ret != 'success' )
                    {
                        return;
                    }
                    try {
                        var result = eval( '(' + msg +')' );
        
                        if( result.status == '1' )
                        {
                            tinymce.get('response').setContent('');
                            fetchRowData(result.id);
                            $('#response, #file').val('');
                            $('#successModal2').modal('show');
                            $("#success_msg").html(result.msg);
                        }
                        else
                        {
                            if(result.type == 'Response')
                            {
                                var msg = 'Please key in response.';
                                $('#response').addClass('is-invalid');
                                $("#response_error").html(msg);
                                return false;
                            }
                            else
                            {
                                $('#response').removeClass('is-invalid');
                                $("#response_error").html("&nbsp;");
                            }

                            if(result.type == 'File')
                            {
                                var msg = result.msg;
                                $('#file').addClass('is-invalid');
                                $('#file_error').addClass('text-danger');
                                $("#file_error").html(msg);
                                $(this).val('');
                                return false;
                            }
                            else
                            {
                                var msg = '(Max file allowed: 25MB, allow all file type pdf, png, jpg, docx, txt, zip)';
                                $('#file').removeClass('is-invalid');
                                $('#file_error').removeClass('text-danger');
                                $("#file_error").html(msg);
                            }
                        }
                    }
                    catch(E)
                    {
                        return;   
                    }
                }
            });
    }
}

function closeTicket()
{
    $('#ratingModal').modal('hide');
    $("#collapseTicket").off('hide.bs.collapse');
    $("#collapseTicket").collapse('hide');
    
    var postData = new FormData(); 

    var postData =  'id='+encodeURIComponent($('#ticket_id').val()) +
                    '&rating='+encodeURIComponent($('#rating-value').val()) +
                    '&func=rateTicket';

    $.ajax({
        type: "POST",
        url: "actions/Ticket.php",
        async:true,
        data: postData,
        success: function(msg,ret){
            
            if( ret != 'success' )
            {
                return;
            }
            try {
                var result = eval( '(' + msg +')' );

                if( result.status == '1' )
                {
                    $('#successCloseTicketModal').modal('show');
                    $("#successcloseticket_msg").html(result.msg);
                }
                else
                {
                    return false;
                }
            }
            catch(E)
            {
                return;   
            } 

        }
    });
}

function reopenTicket()
{
    $("#collapseTicket").off('hide.bs.collapse');
    $("#collapseTicket").collapse('hide');

    var postData = new FormData(); 

    var postData =  'id='+encodeURIComponent($('#reopen_ticket_id').val()) +
                    '&func=updateTicketStatus';

    $.ajax({
        type: "POST",
        url: "actions/Ticket.php",
        async:true,
        data: postData,
        success: function(msg,ret){
            
            if( ret != 'success' )
            {
                return;
            }
            try {
                var result = eval( '(' + msg +')' );

                if( result.status == '1' )
                {
                    $('#reopenticketModal').modal('hide');
                    $('#successCloseTicketModal').modal('show');
                    $("#successcloseticket_msg").html(result.msg);
                }
                else
                {
                    return false;
                }
            }
            catch(E)
            {
                return;   
            }
        }
    });
}

function fetchRowData(id)
{
    var postData = 'id='+encodeURIComponent(id) +'&func=getResponse';
    $.ajax({
        type: "POST",
        url: "actions/Ticket.php",
        async:true,
        data: postData,
        success: function(msg,ret){
            
            if( ret != 'success' )
            {
                return;
            }
            try {
                var result = eval( '(' + msg +')' );

                if( result.status == '1' )
                {
                    //setTimeout(function() {
                        $('#loadImage').hide();
                        $('#id').val(result.data.id);
                        $('#subject_title').html(result.data.subject);
                        $('#response_list').html(result.html);
                    //}, 3000);
                    //$('#collapseTicket').collapse('show');
                }
                else
                {
                    return false;
                }
            }
            catch(E)
            {
                return;   
            } 

        }
    });
}

// Function to highlight stars
function highlightStars(ratingValue, isSelected) {
    $('.star').each(function() {
        var starValue = $(this).data('value');
        if (starValue <= ratingValue) {
            $(this).addClass(isSelected ? 'checked' : 'checked');
        } else {
            $(this).removeClass(isSelected ? 'checked' : 'checked');
        }
    });
}