$(document).ready(function() {
    $('#btnCreateAdmin').click(addOrUpdateAdmin);
    $('#btnSaveAdmin').click(addOrUpdateAdmin);
    $('#btnDeleteAdmin').click(deleteAdmin);
    $('.popUpDelete').click(popUpDelete);

    $('#btnLogin').click(processLogin);
    $('#btnLogout').click(processLogout);
    $('#btnSavePassword').click(savePassword);

    $('#eyePassword').click(showPassword);
    $('#currpass').click(showCurrPassword);
    $('#newpass').click(showNewPassword);
    $('#conpass').click(showConfirmPassword);

    $('#successModal').on('hidden.bs.modal', function (e) {
        // Do something when the modal is closed
        location.reload();
    });

    $('#level').change(function() 
    {
        //If previous level is country manager not allow to change the level.
        if($('#prev_level').val() == 3)
        {
            errormsg = 'Country Manager cannot be update to higher level.';
            $('#level').addClass('is-invalid');
            $("#level_error").html(errormsg);
            $('#level').val(3);
            return false;
        }
        else
        {
            //Hide the validation checking
            $('#email').removeClass('is-invalid');
            $("#email_error").html("&nbsp;");

            if($(this).val() == 3)
                $('#div_countries').attr('style', 'display: block;');
            else
                $('#div_countries').attr('style', 'display: none;');
        }
    });
});

function processLogin()
{
    var errormsg = '';

    //Email Validation
    if($('#email').val() == "")
    {
        errormsg = 'Please key in email address.';
        $('#email').addClass('is-invalid');
        $("#email_error").html(errormsg);
        return false;
    }
    else if( !validateEmail( $('#email').val() ) )
    {
        errormsg = 'Please make sure email is in the correct format.';
        $('#email').addClass('is-invalid');
        $("#email_error").html(errormsg);
        return false;
    }
    else
    {
        $('#email').removeClass('is-invalid');
        $('#email_error').attr('style', 'display: none !important;');
    }
    
    //Password Validation
    if($('#password').val() == "")
    {
        errormsg = 'Please key in password.';
        $('#password').addClass('is-invalid');
        $("#password_error").html(errormsg);
        return false;
    }
    else
    {
        $('#password').removeClass('is-invalid');
        $('#password_error').attr('style', 'display: none !important;');
    }
        

    if(errormsg == '')
    {
        var postData = 'email='+encodeURIComponent($('#email').val()) +'&password=' + encodeURIComponent($('#password').val()) +'&func=login';

        $.ajax({
            type: "POST",
            url: "actions/Admin.php",
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
                        window.location.href = result.redirect;
                    }
                    else
                    {
                        $('#password').addClass('is-invalid');
                        $('#password_error').show();
                        $("#password_error").html(result.msg);
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

function processLogout()
{
    var postData = 'func=logout';

    $.ajax({
        type: "POST",
        url: "actions/Admin.php",
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
                    window.location.href = result.redirect;
                }
            }
            catch(E)
            {
                return;   
            } 

        }
   });
}

function addOrUpdateAdmin()
{
    var buttonType = $(this).data('type');

    errormsg = "";

    if($('#name').val() == "")
    {
        errormsg = 'Please key in admin name.';
        $('#name').addClass('is-invalid');
        $("#name_error").html(errormsg);
        return false;
    }
    else
    {
        $('#name').removeClass('is-invalid');
        $("#name_error").html("&nbsp;");
    }

    if($('#email').val() == "")
    {
        errormsg = 'Please key in email address.';
        $('#email').addClass('is-invalid');
        $("#email_error").html(errormsg);
        return false;
    }
    else if( !validateEmail( $('#email').val() ) )
    {
        errormsg = 'Please make sure email is in the correct format.';
        $('#email').addClass('is-invalid');
        $("#email_error").html(errormsg);
        return false;
    }
    else
    {
        $('#email').removeClass('is-invalid');
        $("#email_error").html("&nbsp;");
    }

    if($('#status').val() == "")
    {
        errormsg = 'Please select status.';
        $('#status').addClass('is-invalid');
        $("#status_error").html(errormsg);
        return false;
    }
    else
    {
        $('#status').removeClass('is-invalid');
        $("#status_error").html("&nbsp;");
    }

    if($('#level').val() == "")
    {
        errormsg = 'Please select admin level.';
        $('#level').addClass('is-invalid');
        $("#level_error").html(errormsg);
        return false;
    }
    else
    {
        $('#level').removeClass('is-invalid');
        $("#level_error").html("&nbsp;");
    }

    if(errormsg == '')
    {
        var postData = 'name='+encodeURIComponent($('#name').val()) +
        '&email=' + encodeURIComponent($('#email').val())  +
        '&status=' + encodeURIComponent($('#status').val())  +
        '&level=' + encodeURIComponent($('#level').val())  +
        '&type=' + buttonType  +
        '&func=addOrUpdateAdmin';

        if(buttonType == 'Edit')
            postData += '&prev_email=' + encodeURIComponent($('#prev_email').val()) + '&id=' + encodeURIComponent($('#id').val());

        if($('#level').val() == 3)
            postData += '&fk_countries=' + encodeURIComponent($('#fk_countries').val());

        $.ajax({
            type: "POST",
            url: "actions/Admin.php",
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
                        $('#name, #email, #level').removeClass('is-invalid');
                        $('#name_error, #email_error, #level_error').html('&nbsp;');
                        $('#successModal').modal('show');
                        $("#success_msg").html(result.msg);
                        $("input[type='text']").val("");
                        $('#level').prop('selectedIndex', 0);
                    }
                    else
                    {
                        if(result.type == 'name')
                        {
                            $('#name').addClass('is-invalid');
                            $("#name_error").html(result.msg);
                            return false;
                        }
                        else
                        {
                            $('#email').removeClass('is-invalid');
                            $("#email_error").html("&nbsp;");
                        }

                        if(result.type == 'email')
                        {
                            $('#email').addClass('is-invalid');
                            $("#email_error").html(result.msg);
                            return false;
                        }
                        else
                        {
                            $('#email').removeClass('is-invalid');
                            $("#email_error").html("&nbsp;");
                        }

                        if(result.type == 'status')
                        {
                            $('#status').addClass('is-invalid');
                            $("#status_error").html(result.msg);
                            return false;
                        }
                        else
                        {
                            $('#status').removeClass('is-invalid');
                            $("#status_error").html("&nbsp;");
                        }
                        

                        if(result.type == 'level')
                        {
                            $('#level').addClass('is-invalid');
                            $("#level_error").html(result.msg);
                            return false;
                        }
                        else
                        {
                            $('#level').removeClass('is-invalid');
                            $("#level_error").html("&nbsp;");
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

function popUpDelete()
{
    var id = $(this).data('id');
    $('#deleteModal').modal('show');
    $('#admin_id').val(id);
}

function deleteAdmin()
{
    $('#deleteModal').modal('hide');
    
    var postData = 'id='+encodeURIComponent($('#admin_id').val()) +'&func=deleteAdmin';

    $.ajax({
        type: "POST",
        url: "actions/Admin.php",
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
                    $('#successModal').modal('show');
                    $("#success_msg").html(result.msg);
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

function showAdminInfo(id, name, email, level, countries, status)
{
    $('#id').val(id);
    $('#name').val(name);
    $('#email').val(email);
    $('#prev_email').val(email);
    $('#level').val(level);
    $('#prev_level').val(level);
    $('#status').val(status);

    if(level == 3)
    {
        $('#div_countries').attr('style', 'display: block;');
        $('#fk_countries').val(countries);
    }
    else
    {
        $('#div_countries').attr('style', 'display: none;');
    }

    //Hide create button
    $('#btnCreateAdmin').addClass('d-none');

    //Show save button
    $('#btnSaveAdmin').removeClass('d-none');
    $('#btnSaveAdmin').addClass('d-block');
}

function savePassword()
{
    var errormsg = "";

    //Current Password Validation
    if($('#current_password').val() == "")
    {
        errormsg = "Please complete the Current Password.";
        $('#current_password').addClass('is-invalid');
        $("#showErrCurrPass").html(errormsg);
        return false;
    }
    else
    {
        $('#current_password').removeClass('is-invalid');
        $('#showErrCurrPass').html("&nbsp");
    }
    //End

    //New Password Validation
    if($('#new_password').val() == "")
    {
        errormsg = "Please complete the New Password.";
        
        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }
    else if( $('#new_password').val().length < 8 )
    {
        errormsg = "Password must be at least 8 characters long.";

        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }
    else if( $('#new_password').val() != $('#confirm_password').val() )
    {
        errormsg = "New passwords do not match with confirm password.";

        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }
    else if( !$('#new_password').val().match(/[a-z]/) )
    {
        errormsg = "Password must contain at least one lowercase letter.";

        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }
    else if( !$('#new_password').val().match(/[A-Z]/) )
    {
        errormsg = "Password must contain at least one uppercase letter.";

        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }
    else if( !$('#new_password').val().match(/\d/) )
    {
        errormsg = "Password must contain at least one digit.";

        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }
    /*else if(!$('#new_password').val().match(/[\'^£$%&*()}{!@#~?><>,|=_+¬-]/))
    {
        errormsg = "Password must contain at least one special character.";

        $('#new_password').addClass('is-invalid');
        $("#showErrNewPass").html(errormsg);
        return false;
    }*/
    else
    {
        $('#new_password').removeClass('is-invalid');
        $("#showErrNewPass").html("&nbsp;");
    }
    //End

    var postData = 'currentpassword='+encodeURIComponent($('#current_password').val()) +
    '&password=' + encodeURIComponent($('#new_password').val())  +
    '&confirmpassword=' + encodeURIComponent($('#confirm_password').val())  +
    '&func=changePassword';

    $.ajax({
        type: "POST",
        url: "actions/Admin.php",
        async:true,
        data: postData,
        success: function(msg,ret){
            
            if( ret != 'success' )
            {
                alert('Connection Error.');
                return;
            }
            try {
                var result = eval( '(' + msg +')' );

                if( result.status == '1' )
                {
                    $('#current_password, #new_password').removeClass('is-invalid');
                    $('#showErrCurrPass, #showErrNewPass').html('&nbsp;');
                    $('#successModal').modal('show');
                    $("input[type='text'], input[type='password']").val("");
                }
                else
                {
                    if(result.type == 'NewPassword')
                    {
                        $('#new_password').addClass('is-invalid');
                        $("#showErrNewPass").html(result.msg);
                        return false;
                    }
                    else
                    {
                        $('#current_password').addClass('is-invalid');
                        $("#showErrCurrPass").html(result.msg);
                        return false;
                    }
                }
            }
            catch(E)
            {
                alert('Connection Error.');
                return;   
            } 
        }
   });
}

function showPassword()
{
    if ($('#password').attr("type") === "password") 
    {
        $('#password').attr("type", "text");
        $('#btnShow').html("visibility");
    }
    else
    {
        $('#password').attr("type", "password");
        $('#btnShow').html("visibility_off");
    }
}

function showCurrPassword()
{
    if ($('#current_password').attr("type") === "password") 
    {
        $('#current_password').attr("type", "text");
        $('#currpass').html("visibility");
    }
    else
    {
        $('#current_password').attr("type", "password");
        $('#currpass').html("visibility_off");
    }
}

function showNewPassword()
{
    if ($('#new_password').attr("type") === "password") 
    {
        $('#new_password').attr("type", "text");
        $('#newpass').html("visibility");
    }
    else
    {
        $('#new_password').attr("type", "password");
        $('#newpass').html("visibility_off");
    }
}

function showConfirmPassword()
{
    if ($('#confirm_password').attr("type") === "password") 
    {
        $('#confirm_password').attr("type", "text");
        $('#conpass').html("visibility");
    }
    else
    {
        $('#confirm_password').attr("type", "password");
        $('#conpass').html("visibility_off");
    }
}

//Email Validation
function validateEmail(email) 
{
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}


