/*
 * js functions for the app
 */
var tenderTable;
var userTypes = [];
userTypes[1] = "יכול לצפות";
userTypes[2] = "מחויב להגיב";

$(document).on('click', '.change-status', function(){

    var type= $(this).data("type");
    var tenderId = $("#tender-id-comment-add").val();

    // change selected border color
    $(".change-status").css("border-color","");
    $(this).css("border-color","lightskyblue");

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "changeTenderStatus",
            status: type,
            tenderId: tenderId
        },

    }).done(function( response ) {
        if (response['status'] === "OK") {
            $("#youMustApproveText").hide();

            /*
            update text
             */
            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "getGroupUserData",
                    tenderId: tenderId
                },

            }).done(function( response ) {
                if (response != false) {
                    $("#tenderUsers").html(response);
                }
            });

        } else {
            $("#error-text").css("color","red");
            $("#error-text").text("קרתה שגיאה");
        }
    });


});

$(document).on('click', '#add-comment-btn', function(){
    var comment = $("#comment-add").val();
    var tenderId = $("#tender-id-comment-add").val();


    $("#error-text").css("color","black");
    $("#error-text").text("שולח תגובה...");

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "addComment",
            comment: comment,
            tenderId: tenderId
        },

    }).done(function( response ) {
        $("#error-text").css("color","black");
        $("#error-text").text("");

        if (response['status'] === "OK") {
            $("#error-text").text();
            var html = "";
            var comment = response["comment"][0];
            $("#comment-add").val("");

            html='<hr><strong>'+comment.nickname+'</strong> <br>'+comment.st_comment+'<br>'+comment.date_created;

            $("#comments").prepend(html);
        } else {
            $("#error-text").css("color","red");
            $("#error-text").text("קרתה שגיאה");
        }
    });

});

function deletetender(id) {
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
                    action: "deletetender",
                    id: id,
                },

            }).done(function( data ) {
                console.log(data)

                if (data['status'] === "OK") {
                    tenderTable.row($("#"+id).parents('tr')).remove().draw( false );

                } else {
                    alert("Delete Fail!");

                }
            });
        }
    });

}

function updatetender(id) {

    $(tenderDetailDialog("edit",id)).appendTo('body');

    //Trigger the modal
    $("#add-tender-modal").modal({
        backdrop: 'static',
        keyboard: true
    });

    //Remove the modal once it is closed.
    $("#add-tender-modal").on('hidden.bs.modal', function () {
        $("#add-tender-modal").remove();
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
            action: "gettender",
            id: id }

    }).done(function( data ) {
        if (data['status'] === "OK") {

            $("#password-container").hide();
            $("#tendername-add").val(data["data"]['st_name']);


        } else {
            $("#error-add-tender").html( data['error'] );
        }
    });



    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });
    // reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });
    // reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });
    // reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });

}

function showTender(id){
    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: "ajaxApi.php",
        data: {
            action: "getTender",
            id: id
        },

    }).done(function( data ) {
        console.log(data);

        if (data['status'] === "OK") {
            $(tenderDetailDialog(data['tender'],data['files'],data['comments'])).appendTo('body');
            //Trigger the modal
            $("#add-tender-modal").modal({
                backdrop: 'static',
                keyboard: false
            });

            //Remove the modal once it is closed.
            $("#add-tender-modal").on('hidden.bs.modal', function () {
                $("#add-tender-modal").remove();
            });

        } else {
            alert("קרתה שגיאה!");

        }
    });
}

function addtender(handler) {

    $(tenderDetailDialog("add")).appendTo('body');

    //Trigger the modal
    $("#add-tender-modal").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#add-tender-modal").on('hidden.bs.modal', function () {
        $("#add-tender-modal").remove();
    });




    $(document).on('click', '.btn-add-tender', function(){
        var tendername = $("#tendername-add").val();



        if( tendername !== "" ) {

            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addtender",
                    name:tendername
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    $("#add-tender-modal").modal("hide");
                    //handler(data['data']);
                    location.reload();

                } else {
                    var error = data['error'];
                    $("#error-add-tender").html( data['error'] );
                }
            });


        } else {
            $("#error-add-tender").html("למלא את כל השדות עם נתונים חוקיים!");
        }
    });

    // reset errors texts
    $('#fname-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });
// reset errors texts
    $('#lname-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });
// reset errors texts
    $('#email-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });
// reset errors texts
    $('#pass-add').on('keydown', function () {
        $('#error-add-tender').text('');
    });

}

$("#add-tender").click(function () {
    addtender((tenderAdded) => {
        if (tenderAdded) {
            //let tender = jQuery.parseJSON(tenderAdded[0]);
            if(tenderAdded[0]['date_last_login'] == "0000-00-00 00:00:00"){
                tenderAdded[0]['date_last_login'] = "לא התחבר מעולם";
            }
            tenderTable.row.add( [
                tenderAdded[0]['id'],
                tenderAdded[0]['st_tendername'],
                tenderAdded[0]['st_email'],
                tenderAdded[0]['st_username'],
                boolOptions[tenderAdded[0]['bool_active']],
                boolOptions[tenderAdded[0]['bool_allowed_create_tenders']],
                boolOptions[tenderAdded[0]['bool_allowed_tender_edit']],
                `<button  onclick="deletetender(`+ tenderAdded[0]['id'] +`)" id="`+ tenderAdded[0]['id'] +`" type="button" class="btn btn-danger delete-tender"></button>
                    <button onclick="updatetender(`+ tenderAdded[0]['id'] +`)" id="`+ tenderAdded[0]['id'] +`" type="button" class="btn btn-warning delete-tender"></button>`
            ] ).node().id = "row-"+tenderAdded[0]['id'];
            tenderTable.draw( true );
            //location.reload();
        }

    })
});



$(document).ready(function() {
    $(document).on('change', '.final-result', function() {
        var finalResult = $(this).val();
        var tenderId = $(this).data("tenderid");

        var companyName = "#company"+tenderId;
        console.log(companyName);
        $(companyName).hide();

        console.log(finalResult+' '+tenderId);

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateFinalResult",
                finalResult: finalResult,
                tenderId: tenderId
            }

        }).done(function( data ) {
            var today = new Date();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();

            if (data['status'] === "OK") {
                $(".final-result-text"+tenderId).css("color","green");
                $(".final-result-text"+tenderId).text("התוצאה עודכנה בהצלחה"+" "+time);

                if(finalResult == 1){
                    $(".company"+tenderId).show();
                }
                else{
                    $(".company"+tenderId).hide();
                }


            } else {
                var today = new Date();
                var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();

                $(".final-result-text"+tenderId).css("color","red");
                $(".final-result-text"+tenderId).text(data['error'] +" "+time);
            }
        });
    });
    $(document).on('change', '.final-status', function() {

        $(this).closest('.final-status-text').css( "color","green");
        var finalStatus = $(this).val();
        var tenderId = $(this).data("tenderid");

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateFinalStatus",
                finalStatus: finalStatus,
                tenderId: tenderId
            }

        }).done(function( data ) {
            var today = new Date();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            if (data['status'] === "OK") {

                $(".final-status-text"+tenderId).css("color","green");
                $(".final-status-text"+tenderId).text("הסטטוס עודכן בהצלחה"+" "+time);




                $(".commentsx"+tenderId).show();

            } else {
                $(".final-status-text"+tenderId).css("color","red");
                $(".final-status-text"+tenderId).text(data['error']+" "+time);
            }
        });
    });


    $(document).on('keyup', '.status-comments', function() {
        var resultComments = $(this).val();
        var tenderId = $(this).data("tenderid");


        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateResultComments",
                resultComments: resultComments,
                tenderId: tenderId
            }

        }).done(function( data ) {
            var today = new Date();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            if (data['status'] === "OK") {

                $(".final-status-text"+tenderId).css("color","green");
                $(".final-status-text"+tenderId).text("הטקסט עודכן"+" "+time);

            } else {

                $(".final-status-text"+tenderId).css("color","red");
                $(".final-status-text"+tenderId).text(data['error']+" "+time);
            }
        });
    });
    $(document).on('keyup', '.company-name', function() {
        var companyName = $(this).val();
        var tenderId = $(this).data("tenderid");


        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateCompanyWon",
                companyWon: companyName,
                tenderId: tenderId
            }

        }).done(function( data ) {

            var today = new Date();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            if (data['status'] === "OK") {

                $(".final-result-text"+tenderId).css("color","green");
                $(".final-result-text"+tenderId).text("הטקסט עודכן"+" "+time);

            } else {

                $(".final-result-text"+tenderId).css("color","red");
                $(".final-result-text"+tenderId).text(data['error']+" "+time);
            }
        });
    });



    /*
    tenderTable = $('#tender-table').DataTable({
        fixedHeader: {
            header: true,
            footer: false
        },
        "processing": true,
        "serverSide": false,
        responsive: true,
        "aaSorting": [],
        //"order": [[ 0, "desc" ]],
        "columnDefs": [
            { "targets": 0 },
            { "targets": 1 },
            { "targets": 2 ,"type":"datetime"},
            { "targets": 3 }
        ],
        language: {
            search: "",
            searchPlaceholder: "חיפוש",
            lengthMenu: "הצג _MENU_ מכרזים",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין מכרזים",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ מכרזים",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
*/
    $('#bids-history-table').DataTable({
        "pageLength": 50,
        "order": [[ 1, "desc" ]],
        "columnDefs": [
            { "targets": 0 },
            { "targets": 1 },
            { "targets": 2 }
        ],
        language: {
            search: "",
            searchPlaceholder: "חיפוש",
            lengthMenu: "הצג _MENU_ הצעות",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין הצעות",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ הצעות",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
});

$(document).on('click', '.btn-update-tender', function(){

    var id = $("#tender-id").val();
    var name = $("#tendername-add").val();

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
                action: "updatetender",
                id: id,
                name: name,
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                $("#add-tender-modal").modal("hide");
                location.reload();
            } else {
                $("#error-add-element").html( data['error'] );
            }
        });


    } else {
        $("#error-add-element").html("למלא את כל השדות עם נתונים חוקיים!");
    }
});


$(document).on('click', '#send-bid', function(){

    var id = $("#tender-id").val();
    var bidAmount = $("#bid-amount").val();

    console.log(id);
    console.log(bidAmount);

    if( id !=="" && bidAmount !="") {
        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });
        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "suggestBid",
                id: id,
                bidAmount: bidAmount
            }

        }).done(function( data ) {

            if (data['status'] === "OK") {
                location.reload();
            } else {
                $("#bid-errors").text( data['error'] );
            }
        });


    } else {
        alert("אנא הכנס הצעת מחיר!");
    }
});



