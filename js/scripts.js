$( document ).ready(function() {
    $("#sendSms").click(function(){
        var phone = $("#phone").val();
        var smsMessage = $("#smsMessage").val();

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: { action: 'sendSms', phone:phone,smsMessage:smsMessage }

        }).done( function( data ) {

            if (data['status'] === "OK") {
                alert('ההודעה נשלחה בהצלחה');

            } else {
                alert('שגיאה');
            }
        });

    });

    $("#sendEmail").click(function(){
        var email = $("#email").val();
        var subject = $("#subject").val();
        var emailMessage = $("#emailMessage").val();



        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: { action: 'sendEmail', email:email,subject:subject,emailMessage:emailMessage }

        }).done( function( data ) {

            if (data['status'] === "OK") {
                alert('ההודעה נשלחה בהצלחה');

            } else {
                alert('שגיאה');
            }
        });
    });
});

$(document).ready(function() {
    /**
     * Login
     */

    $("#login-submit").click(function() {

        var username = $("#username").val();
        var password = $("#password").val();
        var notAnswered = $("#notAnswered").val();

        if(username != "" && password != ""){

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: { action: 'login', email: username, password: password }

            }).done( function( data ) {

                if (data['status'] === "OK") {

                    window.location = data['url'];

                } else {
                    $("#error-text").html( data['message'] );
                }
            });

        } else {
            $("#error-text").html( 'אנא מלא את כל השדות הדרושים' );
        }
    });

    $('document').keypress(function (e) {
        var key = e.which;
        if(key == 13)  // the enter key code
        {
            $("#login-submit").click();
        }
    });
});
