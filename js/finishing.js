/*
 * js functions for the app
 */
var finishingTable;

$(document).on('click', '#btn-edit-finishing', function(){

    var id = $("#finishing-id").val();
    var name = $("#finishingname-edit").val();
    var modelId = $("#model-id-edit").val();



    if( id !=="" && name !="") {
        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateFinishing",
                id: id,
                name: name,
                modelId:modelId
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-finishing-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        alert("למלא את כל השדות עם נתונים חוקיים!");
    }
});


function deletefinishing(id) {

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "deleteFinishing",
            id: id,
        },

    }).done(function( data ) {
        console.log(data)

        if (data['status'] === "OK") {
            finishingTable.row($("#"+id).parents('tr')).remove().draw( false );

        } else {
            alert("Delete Fail!");

        }
    });
}

function updatefinishing(id) {


    //Trigger the modal
    $("#edit-finishing-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#edit-finishing-modal").on('hidden.bs.modal', function () {
        $("#add-finishing-modal").remove();
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
            action: "getFinishing",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#finishingname-edit").val(data["data"]['st_name']);
            $("#model-id-edit").val(data["data"]['i_model_id']);
            $("#finishing-id").val(id);




        } else {
            $("#error-add-finishing").html( data['error'] );
        }
    });

}

$("#add-finishing").click(function () {

    //Trigger the modal
    $("#add-finishing-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-finishing-modal").on('hidden.bs.modal', function () {
        $("#add-finishing-modal").remove();
    });




    $(document).on('click', '.btn-add-finishing', function(){
        var finishingname = $("#finishingname-add").val();
        var modelId = $("#model-id-add").val();

        if( finishingname !== "" && modelId != 0) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addFinishing",
                    name:finishingname,
                    modelId:modelId
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-finishing-modal").modal("hide");
                    //handler(data['data']);
                    location.reload();

                } else {

                }
            });


        } else {
            alert("למלא את כל השדות עם נתונים חוקיים!");
        }
    });
});

$(document).ready(function() {
    finishingTable = $('#finishing-table').DataTable({
        //"responsive": true,
        "aaSorting": [],
        "columnDefs": [
            { "targets": 0 },
            { "targets": 1 },
            { "targets": 2 },
            { "orderable": false, "targets": 2 }
        ],
        language: {
            searchPlaceholder: "חיפוש",
            search: "",
            lengthMenu: "הצג _MENU_ רמות גימור",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין רמות גימור",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ רמות גימור",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});

