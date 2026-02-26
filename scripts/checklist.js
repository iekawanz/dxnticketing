$(document).ready(function() {
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    $("#completeModal").modal({
        backdrop: "static",
        keyboard: false,
    });

    //$('.btn-blue, .btnAddUpdateChecklist').click(addUpdateChecklist);
    $('#btnDeleteChecklist').click(deleteChecklist);
    $('#btnAddChecklist').click(addChecklist);
    $('#btnUpdateChecklist').click(editChecklist);
    $('#btnUpdate').click(updateChecklistStatus);

    $("#openTranslationModalButton, #openPaymentGatewayModalButton, #openDomainModalButton, #openFrontendQCModalButton, #openBackendQCModalButton, #openTrainingModalButton, #openOthersModalButton").click(function() {
        //Type value
        var type = $(this).data('type');

        //Set all the field become empty
        $('#project_type, #name, #pic, #comment').val('');

        //Checklist Type
        $('#checklist_type').val(type);
    });

    $('#file').change(function() {
        var file = this.files[0];
        var maxFileSize = 2 * 1024 * 1024; //(2MB)
        var allowedTypes = [
            'image/jpeg', 'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];

        if (!allowedTypes.includes(file.type)) 
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
        }
    });

    $('.edit-link').on('click', function() {
        var id = $(this).data('id');
        
        var postData = 'id='+encodeURIComponent(id) +'&func=getChecklistInfo';

        $.ajax({
            type: "POST",
            url: "actions/Checklist.php",
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
                        //Param
                        $('#edit_id').val(result.data.id);
                        $('#edit_checklist_type').val(result.data.checklist_type);
                        $('#edit_project_type').val(result.data.project);
                        $('#edit_name').val(result.data.name);
                        $('#edit_pic').val(result.data.pic);
                        $('#edit_comment').val(result.data.comment);
                        
                        return false;
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

    $('.delete-link').on('click', function() {
        var id = $(this).data('id');
        $('#delete_id').val(id);
    });

    $('input[name="translation[]"], input[name="payment[]"], input[name="domain[]"], input[name="frontend[]"], input[name="backend[]"], input[name="training[]"], input[name="others[]"]').change(function() {
        var title = 'Completed';
        var msg = 'Are you sure you want to move this note to completed list?';
        $('#status').val('Completed');

        $('#update_status_id').val($(this).val());
        $('#text-title').text(title);
        $('#text-msg').text(msg);
    });

    $('input[name="completed_translation[]"], input[name="completed_payment[]"], input[name="completed_domain[]"], input[name="completed_frontend[]"], input[name="completed_backend[]"], input[name="completed_training[]"], input[name="completed_others[]"]').change(function() {
        var title = 'Uncompleted';
        var msg = 'Are you sure you want to move this note to to do list?';
        $('#status').val('To Do');

        $('#update_status_id').val($(this).val());
        $('#text-title').text(title);
        $('#text-msg').text(msg);
    });

    $('#btnCancel, .btn-close').click(function() {
        var status = $('#status').val();

        if(status == 'Completed')
            $('input[name="translation[]"], input[name="payment[]"], input[name="domain[]"], input[name="frontend[]"], input[name="backend[]"], input[name="training[]"], input[name="others[]"]').prop('checked', false);
        else
            $('input[name="completed_translation[]"], input[name="completed_payment[]"], input[name="completed_domain[]"], input[name="completed_frontend[]"], input[name="completed_backend[]"], input[name="completed_training[]"], input[name="completed_others[]"]').prop('checked', true);
    });
});

function addChecklist()
{
    errormsg = "";

    if($('#project_type').val() == "")
    {
        errormsg = 'Please select project type.';
        $('#project_type').addClass('is-invalid');
        $("#project_error").html(errormsg);
        return false;
    }
    else
    {
        $('#project_type').removeClass('is-invalid');
        $("#project_error").html("&nbsp;");
    }

    if($('#name').val() == "")
    {
        errormsg = 'Please key in item.';
        $('#name').addClass('is-invalid');
        $("#name_error").html(errormsg);
        return false;
    }
    else
    {
        $('#name').removeClass('is-invalid');
        $("#name_error").html("&nbsp;");
    }

    if($('#pic').val() == "")
    {
        errormsg = 'Please select country pic.';
        $('#pic').addClass('is-invalid');
        $("#pic_error").html(errormsg);
        return false;
    }
    else
    {
        $('#pic').removeClass('is-invalid');
        $("#pic_error").html("&nbsp;");
    }

    if($('#comment').val() == "")
    {
        errormsg = 'Please key in comments.';
        $('#comment').addClass('is-invalid');
        $('#comment_error').addClass('text-danger');
        $("#comment_error").html(errormsg);
        return false;
    }
    else
    {
        var msg = 'Length (Max <b>2000</b> characters)';
        $('#comment').removeClass('is-invalid');
        $('#comment_error').removeClass('text-danger');
        $("#comment_error").html(msg);
    }

    if(errormsg == '')
    {
        var postData = new FormData();

        var checklist_type = $('#checklist_type').val();
        var project_type = $('#project_type').val();
        var name = encodeURIComponent($('#name').val());
        var pic = $('#pic').val();
        var comment = encodeURIComponent($('#comment').val());

        postData.append("project_type", project_type );
        postData.append("checklist_type", checklist_type );
        postData.append("name", name );
        postData.append("pic", pic );
        postData.append("comment", comment );
        postData.append("func", "addNewChecklist" );

        var file = $("#file").prop("files")[0];
        postData.append("file", file);

        $('#loadImage').show();

        $.ajax({
            type: "POST",
            url: "actions/Checklist.php",
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
                        $('#openModal').modal('hide');
                        $('#successChecklistModal').modal('show');
                        $("#success_msg").html(result.msg);
                        
                        $('#btnOK').on('click', function() {
                            location.reload();
                        });
                    }
                    else
                    {
                        if(result.type == 'Project')
                        {
                            errormsg = result.msg;
                            $('#project_type').addClass('is-invalid');
                            $("#project_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#project_type').removeClass('is-invalid');
                            $("#project_error").html("&nbsp;");
                        }

                        if(result.type == 'Item')
                        {
                            errormsg = result.msg;
                            $('#name').addClass('is-invalid');
                            $("#name_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#name').removeClass('is-invalid');
                            $("#name_error").html("&nbsp;");
                        }

                        if(result.type == 'PIC')
                        {
                            errormsg = result.msg;
                            $('#pic').addClass('is-invalid');
                            $("#pic_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#pic').removeClass('is-invalid');
                            $("#pic_error").html("&nbsp;");
                        }

                        if(result.type == 'Comment')
                        {
                            errormsg = result.msg;
                            $('#comment').addClass('is-invalid');
                            $("#comment_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            var msg = 'Length (Max <b>2000</b> characters)';

                            $('#comment').removeClass('is-invalid');
                            $('#comment_error').removeClass('text-danger');
                            $("#comment_error").html(msg);
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
                            var msg = '(Max file allowed: 2MB, allow all file type pdf, png, jpg, docx, txt)';
                            $('#file').removeClass('is-invalid');
                            $('#file_error').removeClass('text-danger');
                            $("#file_error").html(msg);
                        }

                        if(result.type == 'Error')
                        {
                            alert(result.msg);
                            return false;
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

function editChecklist()
{
    errormsg = "";

    if($('#edit_project_type').val() == "")
    {
        errormsg = 'Please select project type.';
        $('#edit_project_type').addClass('is-invalid');
        $("#edit_project_error").html(errormsg);
        return false;
    }
    else
    {
        $('#edit_project_type').removeClass('is-invalid');
        $("#edit_project_error").html("&nbsp;");
    }

    if($('#edit_name').val() == "")
    {
        errormsg = 'Please key in item.';
        $('#edit_name').addClass('is-invalid');
        $("#edit_name_error").html(errormsg);
        return false;
    }
    else
    {
        $('#edit_name').removeClass('is-invalid');
        $("#edit_name_error").html("&nbsp;");
    }

    if($('#edit_pic').val() == "")
    {
        errormsg = 'Please select country pic.';
        $('#edit_pic').addClass('is-invalid');
        $("#edit_pic_error").html(errormsg);
        return false;
    }
    else
    {
        $('#edit_pic').removeClass('is-invalid');
        $("#edit_pic_error").html("&nbsp;");
    }

    if($('#edit_comment').val() == "")
    {
        errormsg = 'Please key in comments.';
        $('#edit_comment').addClass('is-invalid');
        $('#edit_comment_error').addClass('text-danger');
        $("#edit_comment_error").html(errormsg);
        return false;
    }
    else
    {
        var msg = 'Length (Max <b>2000</b> characters)';
        $('#edit_comment').removeClass('is-invalid');
        $('#edit_comment_error').removeClass('text-danger');
        $("#edit_comment_error").html(msg);
    }

    if(errormsg == '')
    {
        var postData = new FormData();

        var id = $('#edit_id').val();
        var checklist_type = $('#edit_checklist_type').val();
        var project_type = $('#edit_project_type').val();
        var name = encodeURIComponent($('#edit_name').val());
        var pic = $('#edit_pic').val();
        var comment = encodeURIComponent($('#edit_comment').val());

        postData.append("id", id );
        postData.append("project_type", project_type );
        postData.append("checklist_type", checklist_type );
        postData.append("name", name );
        postData.append("pic", pic );
        postData.append("comment", comment );
        postData.append("func", "editChecklist" );

        var file = $("#edit_file").prop("files")[0];
        postData.append("file", file);

        $('#loadImage').show();

        $.ajax({
            type: "POST",
            url: "actions/Checklist.php",
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
                        $('#editModal').modal('hide');
                        $('#successChecklistModal').modal('show');
                        $("#success_msg").html(result.msg);
                        
                        $('#btnOK').on('click', function() {
                            location.reload();
                        });
                    }
                    else
                    {
                        if(result.type == 'Project')
                        {
                            errormsg = result.msg;
                            $('#edit_project_type').addClass('is-invalid');
                            $("#edit_project_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#edit_project_type').removeClass('is-invalid');
                            $("#edit_project_error").html("&nbsp;");
                        }

                        if(result.type == 'Item')
                        {
                            errormsg = result.msg;
                            $('#edit_name').addClass('is-invalid');
                            $("#edit_name_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#edit_name').removeClass('is-invalid');
                            $("#edit_name_error").html("&nbsp;");
                        }

                        if(result.type == 'PIC')
                        {
                            errormsg = result.msg;
                            $('#edit_pic').addClass('is-invalid');
                            $("#edit_pic_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            $('#edit_pic').removeClass('is-invalid');
                            $("#edit_pic_error").html("&nbsp;");
                        }

                        if(result.type == 'Comment')
                        {
                            errormsg = result.msg;
                            $('#edit_comment').addClass('is-invalid');
                            $("#edit_comment_error").html(errormsg);
                            return false;
                        }
                        else
                        {
                            var msg = 'Length (Max <b>2000</b> characters)';

                            $('#edit_comment').removeClass('is-invalid');
                            $('#edit_comment_error').removeClass('text-danger');
                            $("#edit_comment_error").html(msg);
                        }

                        if(result.type == 'File')
                        {
                            var msg = result.msg;
                            $('#edit_file').addClass('is-invalid');
                            $('#edit_file_error').addClass('text-danger');
                            $("#edit_file_error").html(msg);
                            $(this).val('');
                            return false;
                        }
                        else
                        {
                            var msg = '(Max file allowed: 2MB, allow all file type pdf, png, jpg, docx, txt)';
                            $('#edit_file').removeClass('is-invalid');
                            $('#edit_file_error').removeClass('text-danger');
                            $("#edit_file_error").html(msg);
                        }

                        if(result.type == 'Error')
                        {
                            alert(result.msg);
                            return false;
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

function deleteChecklist()
{
    var id = $('#delete_id').val();

    var postData = 'id='+encodeURIComponent(id)+'&func=deleteChecklist';

    $.ajax({
        type: "POST",
        url: "actions/Checklist.php",
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
                    $('#deleteModal').modal('hide');
                    $('#successChecklistModal').modal('show');
                    $("#success_msg").html(result.msg);
                    
                    $('#btnOK').on('click', function() {
                        location.reload();
                    });
                }
                else
                {
                    alert(result.msg);
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

function updateChecklistStatus()
{
    var id = $('#update_status_id').val();
    var status = $('#status').val();
    
    var postData = 'id='+encodeURIComponent(id)+'&status='+encodeURIComponent(status)+'&func=updateChecklistStatus';

    $.ajax({
        type: "POST",
        url: "actions/Checklist.php",
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
                    $('#completeModal').modal('hide');
                    $('#successChecklistModal').modal('show');
                    $("#success_msg").html(result.msg);
                    
                    $('#btnOK').on('click', function() {
                        location.reload();
                    });
                }
                else
                {
                    alert(result.msg);
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

function loadData()
{
    var about = $('#aboutFilter').val();
    var country = $('#countryFilter').val();
    
    var postData = 'about='+encodeURIComponent(about)+'&country='+encodeURIComponent(country)+'&func=searchChecklist';

    $.ajax({
        type: "POST",
        url: "actions/Checklist.php",
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
                    location.reload();
                }
                else
                {
                    alert(result.msg);
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