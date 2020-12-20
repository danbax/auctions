/*
 * js functions for the app
 */
var workTypeTable;

function workTypeDetailDialog(type,id=0) {

    let btnText = "";
    let btnClass = "";
    let isVisiblePass;
    let title = "";

    switch (type) {
        case "add" :
            btnText = "הוסף +";
            btnClass = "btn-add-workType";
            isVisiblePass = true;
            title = "הוספת סוג עבודה ";
            break;
        case "edit" :
            btnText = "שמור";
            btnClass = "btn-update-workType";
            isVisiblePass = false;
            title = "חידוש נתוני סוג עבודה";
            break;
    }


    html = `<div class="modal fade" id="add-workType-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
            
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">` + title + `</h4>
              </div>
              <div class="modal-body mx-3">
              
                  <div class="card">
                    <article class="card-body">`;
                    if(type == "edit") html += `<input type="hidden" id="workType-id" value="`+id+`">`;
                    if(type == "edit") html += `<label for="workTypename-add">שם סוג עבודה</label>`;
                    html +=
                        `<div class="form-workType">
                            <input type="text"  id="workTypename-add" class="form-control" placeholder="שם סוג עבודה" value="">
                        </div>`;
                    html += `</article>
                </div>
        
              </div>
              <div class="modal-footer">
                  <div class="col float-right">
                    <button id="btn-add-workType" class="btn btn-primary btn-lg `+btnClass+`">` + btnText + `</button>
                    <div id="error-add-workType" class="text-danger"></div>
                  </div>
              </div>
              
            </div>
          </div>
        </div>`;

    return html;
}

function deleteworkType(id) {
    confirmDialog("האם בטוח שרוצה למחוק סוג עבודה?", "הסרת סוג עבודה", (yes) => {

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
                    action: "deleteWorkType",
                    id: id,
                },

            }).done(function( data ) {
                console.log(data)

                if (data['status'] === "OK") {
                    workTypeTable.row($("#"+id).parents('tr')).remove().draw( false );

                } else {
                    alert("Delete Fail!");

                }
            });
        }
    });

}

function updateworkType(id) {

    $(workTypeDetailDialog("edit",id)).appendTo('body');

    //Trigger the modal
    $("#add-workType-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#add-workType-modal").on('hidden.bs.modal', function () {
        $("#add-workType-modal").remove();
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
            action: "getWorkType",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#workTypename-add").val(data["data"]['st_name']);


        } else {
            $("#error-add-workType").html( data['error'] );
        }
    });



    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });
    // reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });
    // reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });
    // reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });

}

function addworkType(handler) {

    $(workTypeDetailDialog("add")).appendTo('body');

    //Trigger the modal
    $("#add-workType-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-workType-modal").on('hidden.bs.modal', function () {
        $("#add-workType-modal").remove();
    });




    $(document).on('click', '.btn-add-workType', function(){
        var workTypename = $("#workTypename-add").val();



        if( workTypename !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addWorkType",
                    name:workTypename
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-workType-modal").modal("hide");
                    //handler(data['data']);
                    location.reload();

                } else {
                    var error = data['error'];
                    $("#error-add-workType").html( data['error'] );
                }
            });


        } else {
            $("#error-add-workType").html("למלא את כל השדות עם נתונים חוקיים!");
        }
    });

    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });
// reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });
// reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });
// reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-workType').text('');
    });

}

$("#add-workType").click(function () {
    addworkType((workTypeAdded) => {
        if (workTypeAdded) {
            //let workType = jQuery.parseJSON(workTypeAdded[0]);
            if(workTypeAdded[0]['date_last_login'] == "0000-00-00 00:00:00"){
                workTypeAdded[0]['date_last_login'] = "לא התחבר מעולם";
            }
            workTypeTable.row.add( [
                workTypeAdded[0]['id'],
                workTypeAdded[0]['st_workTypename'],
                workTypeAdded[0]['st_email'],
                workTypeAdded[0]['st_friendly_name'],
                boolOptions[workTypeAdded[0]['bool_active']],
                boolOptions[workTypeAdded[0]['bool_allowed_create_tenders']],
                boolOptions[workTypeAdded[0]['bool_allowed_workType_edit']],
                `<button  onclick="deleteworkType(`+ workTypeAdded[0]['id'] +`)" id="`+ workTypeAdded[0]['id'] +`" type="button" class="btn btn-danger delete-workType"></button>
                    <button onclick="updateworkType(`+ workTypeAdded[0]['id'] +`)" id="`+ workTypeAdded[0]['id'] +`" type="button" class="btn btn-warning delete-workType"></button>`
            ] ).node().id = "row-"+workTypeAdded[0]['id'];
            workTypeTable.draw( true );
            //location.reload();
        }

    })
});

$(document).ready(function() {
    workTypeTable = $('#workType-table').DataTable({
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

$(document).on('click', '.btn-update-workType', function(){

    var id = $("#workType-id").val();
    var name = $("#workTypename-add").val();

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
                action: "updateWorkType",
                id: id,
                name: name,
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-workType-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        $("#error-add-element").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


