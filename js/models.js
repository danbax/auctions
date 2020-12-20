/*
 * js functions for the app
 */
var modelTable;

$(document).on('click', '#btn-edit-model', function(){

    var id = $("#model-id").val();
    var name = $("#modelname-edit").val();

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
                action: "updateModel",
                id: id,
                name: name
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-model-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        alert("למלא את כל השדות עם נתונים חוקיים!");
    }
});


function deletemodel(id) {

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "deleteModel",
            id: id,
        },

    }).done(function( data ) {
        console.log(data)

        if (data['status'] === "OK") {
            modelTable.row($("#"+id).parents('tr')).remove().draw( false );

        } else {
            alert("Delete Fail!");

        }
    });
}

function updatemodel(id) {


    //Trigger the modal
    $("#edit-model-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#edit-model-modal").on('hidden.bs.modal', function () {
        $("#add-model-modal").remove();
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
            action: "getModel",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#modelname-edit").val(data["data"]['st_name']);
            $("#model-id").val(id);


        } else {
            $("#error-add-model").html( data['error'] );
        }
    });

}

$("#add-model").click(function () {

    //Trigger the modal
    $("#add-model-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-model-modal").on('hidden.bs.modal', function () {
        $("#add-model-modal").remove();
    });




    $(document).on('click', '.btn-add-model', function(){
        var modelname = $("#modelname-add").val();



        if( modelname !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addModel",
                    name:modelname
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-model-modal").modal("hide");
                    //handler(data['data']);
                    location.reload();

                } else {

                }
            });


        } else {
            $("#error-add-model").html("למלא את כל השדות עם נתונים חוקיים!");
        }
    });
});

$(document).ready(function() {
    modelTable = $('#model-table').DataTable({
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
            lengthMenu: "הצג _MENU_ סוגי עבודות",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין סוגי עבודות",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ סוגי עבודות",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});

