/*
 * js functions for the app
 */
var userTable;

$(document).ready(function() {
    userTable = $('#user-table').DataTable({
        "responsive": true,
        "aaSorting": [],
        "columnDefs": [
            { "targets": 0 },
            { "targets": 1 },
            { "targets": 2 },
            { "targets": 3 },
            { "targets": 4 },
            { "targets": 5 },
            { "targets": 6 }
        ],
        language: {
            searchPlaceholder: "חיפוש",
            search: "",
            lengthMenu: "הצג _MENU_ משתמשים",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין משתמשים",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ משתמשים",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});


$(document).on('click', '#btn-change-password', function(){
    var newPassword = $("#new-password-add").val();
    var userId = $("#change-password-user-id").val();

    if( newPassword !== "") {

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updatePassword",
                password: newPassword,
                userId: userId
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-user-modal").modal("hide");
                //handler(data['data']);
                location.reload();

            } else {
                $("#error-change-password").html( "קרתה שגיאה!" );
            }
        });


    } else {
        $("#error-add-user").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});

function changePassword(id) {


    //Trigger the modal
    $("#change-password-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#change-password-modal").on('hidden.bs.modal', function () {
        $("#change-password-modal").remove();
    });

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });

    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "getUser",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            var friendlyNameChangePassword = data["data"]['st_username'];

            $("#change-password-title").val("שנה סיסמא עבור המשתמש "+friendlyNameChangePassword);
            $("#change-password-user-id").val(id);

        } else {
            $("#error-add-user").html( data['error'] );
        }
    });
}



function deleteUser(id) {

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "deleteUser",
            id: id,
        },

    }).done(function( data ) {
        console.log(data)

        if (data['status'] === "OK") {
            userTable.row($("#"+id).parents('tr')).remove().draw( false );

        } else {
            alert("Delete Fail!");

        }
    });
}

function updateUser(id) {


    //Trigger the modal
    $("#edit-user-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#edit-user-modal").on('hidden.bs.modal', function () {
        $("#add-user-modal").remove();
    });

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "getUser",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {
            $("#user-id").val(id);
            $("#username-edit").val(data["data"]['st_username']);
            $("#email-edit").val(data["data"]['st_email']);
            $("#phone-edit").val(data["data"]['st_phone']);
            $("#is-admin-edit").val(data["data"]['bool_is_admin']);
            $("#friendly-name-edit").val(data["data"]['st_friendly_name']);

        } else {
            $("#error-add-user").html( data['error'] );
        }
    });
}


$("#add-user").click(function () {
    //Trigger the modal
    $("#add-user-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-user-modal").on('hidden.bs.modal', function () {
        $("#add-user-modal").remove();
    });






});

$(document).on('click', '.btn-add-user', function(){
    var username = $("#username-add").val();
    var email = $("#email-add").val();
    var pass = $("#pass-add").val();
    var phone = $("#phone-add").val();
    var isAdmin = $("#is-admin-add").val();
    var friendlyName = $("#friendly-name-add").val();

    if( username !== "" &&
        email !== "" &&
        pass !== "" &&
        phone !== "" &&
        isAdmin !== ""
    ) {

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "addUser",
                email: email,
                username:username,
                password:pass,
                phone:phone,
                isAdmin:isAdmin,
                friendlyName:friendlyName
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-user-modal").modal("hide");
                //handler(data['data']);
                location.reload();

            } else {
                var error = data['error'];
                if(error.includes("Duplicate entry")){
                    data['error'] = "האימייל כבר שייך למשתמש אחר!"
                }
                $("#error-add-user").html( data['error'] );
            }
        });


    } else {
        console.log("למלא את כל השדות עם נתונים חוקיים!");
        $("#error-add-user").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});

$(document).on('click', '.btn-update-user', function(){
    var id = $("#user-id").val();
    var username = $("#username-edit").val();
    var email = $("#email-edit").val();
    var phone = $("#phone-edit").val();
    var isAdmin = $("#is-admin-edit").val();
    var friendlyName = $("#friendly-name-edit").val();

    if( id !=="" &&
        username !== "" &&
        email !== "" &&
        phone !== "" &&
        isAdmin !== ""
    ) {
        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateUser",
                id: id,
                email: email,
                username:username,
                phone:phone,
                isAdmin:isAdmin,
                friendlyName:friendlyName
            }

        }).done(function( data ) {

            console.log(data);
            if (data['status'] === "OK") {
                location.reload();
            } else {
                $("#error-edit-user").html( data['error'] );
            }
        });


    } else {
        console.log("למלא את כל השדות עם נתונים חוקיים!");
        $("#error-edit-user").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


