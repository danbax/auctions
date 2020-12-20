/*
 * js functions for the app
 */
var groupTable;

function groupDetailDialog(type,id=0) {

    let btnText = "";
    let btnClass = "";
    let isVisiblePass;
    let title = "";

    switch (type) {
        case "add" :
            btnText = "הוסף +";
            btnClass = "btn-add-group";
            isVisiblePass = true;
            title = "הוספת חטיבה חדשה";
            break;
        case "edit" :
            btnText = "שמור";
            btnClass = "btn-update-group";
            isVisiblePass = false;
            title = "חידוש נתוני חטיבה";
            break;
    }


    html = `<div class="modal fade" id="add-group-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    if(type == "edit") html += `<input type="hidden" id="group-id" value="`+id+`">`;
                    if(type == "edit") html += `<label for="groupname-add">שם חטיבה</label>`;
                    html +=
                        `<div class="form-group">
                            <input type="text"  id="groupname-add" class="form-control" placeholder="שם חטיבה" value="">
                        </div>`;
                    html += `</article>
                </div>
        
              </div>
              <div class="modal-footer">
                  <div class="col float-right">
                    <button id="btn-add-group" class="btn btn-primary btn-lg `+btnClass+`">` + btnText + `</button>
                    <div id="error-add-group" class="text-danger"></div>
                  </div>
              </div>
              
            </div>
          </div>
        </div>`;

    return html;
}

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
                    action: "deleteGroup",
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

    $(groupDetailDialog("edit",id)).appendTo('body');

    //Trigger the modal
    $("#add-group-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#add-group-modal").on('hidden.bs.modal', function () {
        $("#add-group-modal").remove();
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
            action: "getGroup",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#groupname-add").val(data["data"]['st_name']);


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

function addgroup(handler) {

    $(groupDetailDialog("add")).appendTo('body');

    //Trigger the modal
    $("#add-group-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-group-modal").on('hidden.bs.modal', function () {
        $("#add-group-modal").remove();
    });




    $(document).on('click', '.btn-add-group', function(){
        var groupname = $("#groupname-add").val();



        if( groupname !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addGroup",
                    name:groupname
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

$("#add-group").click(function () {
    addgroup((groupAdded) => {
        if (groupAdded) {
            //let group = jQuery.parseJSON(groupAdded[0]);
            if(groupAdded[0]['date_last_login'] == "0000-00-00 00:00:00"){
                groupAdded[0]['date_last_login'] = "לא התחבר מעולם";
            }
            groupTable.row.add( [
                groupAdded[0]['id'],
                groupAdded[0]['st_groupname'],
                groupAdded[0]['st_email'],
                groupAdded[0]['st_username'],
                boolOptions[groupAdded[0]['bool_active']],
                boolOptions[groupAdded[0]['bool_allowed_create_tenders']],
                boolOptions[groupAdded[0]['bool_allowed_group_edit']],
                `<button  onclick="deletegroup(`+ groupAdded[0]['id'] +`)" id="`+ groupAdded[0]['id'] +`" type="button" class="btn btn-danger delete-group"></button>
                    <button onclick="updategroup(`+ groupAdded[0]['id'] +`)" id="`+ groupAdded[0]['id'] +`" type="button" class="btn btn-warning delete-group"></button>`
            ] ).node().id = "row-"+groupAdded[0]['id'];
            groupTable.draw( true );
            //location.reload();
        }

    })
});

$(document).ready(function() {
    groupTable = $('#group-table').DataTable({
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

$(document).on('click', '.btn-update-group', function(){

    var id = $("#group-id").val();
    var name = $("#groupname-add").val();

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
                action: "updateGroup",
                id: id,
                name: name,
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-group-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        $("#error-add-element").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


