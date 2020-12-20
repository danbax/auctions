/*
 * js functions for the app
 */
var groupTable;

function deletegroup(id) {
    confirmDialog("האם בטוח שרוצה למחוק משמש?", "הסרת חטיבה", (yes) => {

        if (yes) {
            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "deleteGroupUser",
                    id: id,
                },

            }).done(function( data ) {
                console.log(data)

                if (data['status'] === "OK") {
                    groupTable.row($("#"+id).parents('tr')).remove().draw( false );

                } else {
                    alert("Delete Fail!");

                }
            });
        }
    });

}

function updategroup(id) {

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "getGroupUser",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {
            var userData = data["data"];
            $("#user-name-title").text(userData.nickname);
            $("#select-edit-type").val(userData.groupUserType);
            $("#update-user-id").val(userData.userId);

            $("#update-group-modal").modal("show");


        } else {
            $("#error-add-group").html( data['error'] );
        }
    });



    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-group').text('');
    });
    // reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-group').text('');
    });
    // reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-group').text('');
    });
    // reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-group').text('');
    });

}

$(document).on('click', '#btn-update-group', function(){
    var userId = $("#update-user-id").val();
    var type = $("#select-edit-type").val();
    var groupId = $("#groupId").val();


    if( type !== "") {

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateGroupUser",
                userId:userId,
                groupId:groupId,
                type:type
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-group-modal").modal("hide");


                //handler(data['data']);
                location.reload();

            } else {
                var error = data['error'];
                $("#error-add-group").html( data['error'] );
            }
        });


    } else {
        $("#error-add-group").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});

    $(document).on('click', '#btn-add-group', function(){
        var userId = $("#userId").val();
        var type = $("#type").val();
        var groupId = $("#groupId").val();

        if( groupId !== "",type !== "",userId !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addGroupUser",
                    userId:userId,
                    groupId:groupId,
                    type:type
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-group-modal").modal("hide");


                    //handler(data['data']);
                    location.reload();

                } else {
                    var error = data['error'];
                    $("#error-add-group").html( data['error'] );
                }
            });


        } else {
            $("#error-add-group").html("למלא את כל השדות עם נתונים חוקיים!");
        }
    });



$(document).ready(function() {
    groupTable = $('#group-table').DataTable({
        "columnDefs": [
            { "targets": 0 },
            { "targets": 1 },
            { "targets": 2 },
            { "orderable": false, "targets": 2 }
        ],
        language: {
            search: "חיפוש",
            lengthMenu: "הצג _MENU_ חטיבות",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין חטיבות",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ חטיבות",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});

$(document).on('click', '.update-user-btn', function(){

    var id = $(this).data("id");


    if( id !=="") {
        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "getGroupUser",
                id: id
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {

                $("#update-user-id").val();

               console.log(data);

            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        $("#error-add-element").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


