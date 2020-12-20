/*
 * js functions for the app
 */
var dateTable;

function dateDetailDialog(type,id=0) {

    let btnText = "";
    let btnClass = "";
    let isVisiblePass;
    let title = "";

    switch (type) {
        case "add" :
            btnText = "הוסף +";
            btnClass = "btn-add-date";
            isVisiblePass = true;
            title = "הוספת שם תאריך ";
            break;
        case "edit" :
            btnText = "שמור";
            btnClass = "btn-update-date";
            isVisiblePass = false;
            title = "חידוש נתוני שם תאריך";
            break;
    }


    html = `<div class="modal fade" id="add-date-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    if(type == "edit") html += `<input type="hidden" id="date-id" value="`+id+`">`;
                    if(type == "edit") html += `<label for="datename-add">שם שם תאריך</label>`;
                    html +=
                        `<div class="form-date">
                            <input type="text"  id="datename-add" class="form-control" placeholder="שם שם תאריך" value="">
                        </div>`;
                    html += `</article>
                </div>
        
              </div>
              <div class="modal-footer">
                  <div class="col float-right">
                    <button id="btn-add-date" class="btn btn-primary btn-lg `+btnClass+`">` + btnText + `</button>
                    <div id="error-add-date" class="text-danger"></div>
                  </div>
              </div>
              
            </div>
          </div>
        </div>`;

    return html;
}

function deletedate(id) {
    confirmDialog("האם בטוח שרוצה למחוק שם תאריך?", "הסרת שם תאריך", (yes) => {

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
                    action: "deleteDateName",
                    id: id,
                },

            }).done(function( data ) {
                console.log(data)

                if (data['status'] === "OK") {
                    dateTable.row($("#"+id).parents('tr')).remove().draw( false );

                } else {
                    alert("Delete Fail!");

                }
            });
        }
    });

}

function updatedate(id) {

    $(dateDetailDialog("edit",id)).appendTo('body');

    //Trigger the modal
    $("#add-date-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#add-date-modal").on('hidden.bs.modal', function () {
        $("#add-date-modal").remove();
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
            action: "getDateName",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#datename-add").val(data["data"]['st_name']);


        } else {
            $("#error-add-date").html( data['error'] );
        }
    });



    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-date').text('');
    });
    // reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-date').text('');
    });
    // reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-date').text('');
    });
    // reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-date').text('');
    });

}

function adddate(handler) {

    $(dateDetailDialog("add")).appendTo('body');

    //Trigger the modal
    $("#add-date-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-date-modal").on('hidden.bs.modal', function () {
        $("#add-date-modal").remove();
    });




    $(document).on('click', '.btn-add-date', function(){
        var datename = $("#datename-add").val();



        if( datename !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addDateName",
                    name:datename
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-date-modal").modal("hide");
                    //handler(data['data']);
                    location.reload();

                } else {
                    var error = data['error'];
                    $("#error-add-date").html( data['error'] );
                }
            });


        } else {
            $("#error-add-date").html("למלא את כל השדות עם נתונים חוקיים!");
        }
    });

    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-date').text('');
    });
// reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-date').text('');
    });
// reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-date').text('');
    });
// reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-date').text('');
    });

}

$("#add-date").click(function () {
    adddate((dateAdded) => {
        if (dateAdded) {
            //let date = jQuery.parseJSON(dateAdded[0]);
            if(dateAdded[0]['date_last_login'] == "0000-00-00 00:00:00"){
                dateAdded[0]['date_last_login'] = "לא התחבר מעולם";
            }
            dateTable.row.add( [
                dateAdded[0]['id'],
                dateAdded[0]['st_datename'],
                dateAdded[0]['st_email'],
                dateAdded[0]['st_username'],
                boolOptions[dateAdded[0]['bool_active']],
                boolOptions[dateAdded[0]['bool_allowed_create_tenders']],
                boolOptions[dateAdded[0]['bool_allowed_date_edit']],
                `<button  onclick="deletedate(`+ dateAdded[0]['id'] +`)" id="`+ dateAdded[0]['id'] +`" type="button" class="btn btn-danger delete-date"></button>
                    <button onclick="updatedate(`+ dateAdded[0]['id'] +`)" id="`+ dateAdded[0]['id'] +`" type="button" class="btn btn-warning delete-date"></button>`
            ] ).node().id = "row-"+dateAdded[0]['id'];
            dateTable.draw( true );
            //location.reload();
        }

    })
});

$(document).ready(function() {
    dateTable = $('#date-table').DataTable({
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
            lengthMenu: "הצג _MENU_ שמות תאריכים",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין שמות תאריכים",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ שמות תאריכים",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});

$(document).on('click', '.btn-update-date', function(){

    var id = $("#date-id").val();
    var name = $("#datename-add").val();

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
                action: "updateDateName",
                id: id,
                name: name,
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-date-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        $("#error-add-element").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


