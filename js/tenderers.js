/*
 * js functions for the app
 */
var tendererTable;

function tendererDetailDialog(type,id=0) {

    let btnText = "";
    let btnClass = "";
    let isVisiblePass;
    let title = "";

    switch (type) {
        case "add" :
            btnText = "הוסף +";
            btnClass = "btn-add-tenderer";
            isVisiblePass = true;
            title = "הוספת גורם מזמין ";
            break;
        case "edit" :
            btnText = "שמור";
            btnClass = "btn-update-tenderer";
            isVisiblePass = false;
            title = "חידוש נתוני גורם מזמין";
            break;
    }


    html = `<div class="modal fade" id="add-tenderer-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    if(type == "edit") html += `<input type="hidden" id="tenderer-id" value="`+id+`">`;
                    if(type == "edit") html += `<label for="tenderername-add">שם גורם מזמין</label>`;
                    html +=
                        `<div class="form-tenderer">
                            <input type="text"  id="tenderername-add" class="form-control" placeholder="שם גורם מזמין" value="">
                        </div>`;
                    html += `</article>
                </div>
        
              </div>
              <div class="modal-footer">
                  <div class="col float-right">
                    <button id="btn-add-tenderer" class="btn btn-primary btn-lg `+btnClass+`">` + btnText + `</button>
                    <div id="error-add-tenderer" class="text-danger"></div>
                  </div>
              </div>
              
            </div>
          </div>
        </div>`;

    return html;
}

function deletetenderer(id) {
    confirmDialog("האם בטוח שרוצה למחוק משמש?", "הסרת גורם מזמין", (yes) => {

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
                    action: "deleteTenderer",
                    id: id,
                },

            }).done(function( data ) {
                console.log(data)

                if (data['status'] === "OK") {
                    tendererTable.row($("#"+id).parents('tr')).remove().draw( false );

                } else {
                    alert("Delete Fail!");

                }
            });
        }
    });

}

function updatetenderer(id) {

    $(tendererDetailDialog("edit",id)).appendTo('body');

    //Trigger the modal
    $("#add-tenderer-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#add-tenderer-modal").on('hidden.bs.modal', function () {
        $("#add-tenderer-modal").remove();
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
            action: "getTenderer",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#tenderername-add").val(data["data"]['st_name']);


        } else {
            $("#error-add-tenderer").html( data['error'] );
        }
    });



    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });
    // reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });
    // reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });
    // reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });

}

function addtenderer(handler) {

    $(tendererDetailDialog("add")).appendTo('body');

    //Trigger the modal
    $("#add-tenderer-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-tenderer-modal").on('hidden.bs.modal', function () {
        $("#add-tenderer-modal").remove();
    });




    $(document).on('click', '.btn-add-tenderer', function(){
        var tenderername = $("#tenderername-add").val();



        if( tenderername !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addTenderer",
                    name:tenderername
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-tenderer-modal").modal("hide");
                    //handler(data['data']);
                    location.reload();

                } else {
                    var error = data['error'];
                    $("#error-add-tenderer").html( data['error'] );
                }
            });


        } else {
            $("#error-add-tenderer").html("למלא את כל השדות עם נתונים חוקיים!");
        }
    });

    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });
// reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });
// reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });
// reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-tenderer').text('');
    });

}

$("#add-tenderer").click(function () {
    addtenderer((tendererAdded) => {
        if (tendererAdded) {
            //let tenderer = jQuery.parseJSON(tendererAdded[0]);
            if(tendererAdded[0]['date_last_login'] == "0000-00-00 00:00:00"){
                tendererAdded[0]['date_last_login'] = "לא התחבר מעולם";
            }
            tendererTable.row.add( [
                tendererAdded[0]['id'],
                tendererAdded[0]['st_tenderername'],
                tendererAdded[0]['st_email'],
                tendererAdded[0]['st_username'],
                boolOptions[tendererAdded[0]['bool_active']],
                boolOptions[tendererAdded[0]['bool_allowed_create_tenders']],
                boolOptions[tendererAdded[0]['bool_allowed_tenderer_edit']],
                `<button  onclick="deletetenderer(`+ tendererAdded[0]['id'] +`)" id="`+ tendererAdded[0]['id'] +`" type="button" class="btn btn-danger delete-tenderer"></button>
                    <button onclick="updatetenderer(`+ tendererAdded[0]['id'] +`)" id="`+ tendererAdded[0]['id'] +`" type="button" class="btn btn-warning delete-tenderer"></button>`
            ] ).node().id = "row-"+tendererAdded[0]['id'];
            tendererTable.draw( true );
            //location.reload();
        }

    })
});

$(document).ready(function() {
    tendererTable = $('#tenderer-table').DataTable({
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
            lengthMenu: "הצג _MENU_ גורמים מזמינים",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין גורמים מזמינים",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ גורמים מזמינים",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});

$(document).on('click', '.btn-update-tenderer', function(){

    var id = $("#tenderer-id").val();
    var name = $("#tenderername-add").val();

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
                action: "updateTenderer",
                id: id,
                name: name,
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-tenderer-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        $("#error-add-element").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


