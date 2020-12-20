/*
 * js functions for the app
 */
var userTable;

var boolOptions = [];
boolOptions[0] = "לא";
boolOptions[1] = "כן";



/**
 * Validation function block
 */
function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,})?$/;
    return emailReg.test( $email );
}

function ajaxSend(method, data, url, validator, responseSuccess, responseFail, doneCallback, failCallback) {

    if (validator != null) {

        if (!validator()) {
            return;
        }
    }

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: method,
        url: url,
        data: data

    }).done(function( response ) {

        if (response['status'] === "Success") {

            if (responseSuccess != null) {
                responseSuccess(response);
            }

        } else {

            if (responseFail != null) {
                responseFail(response);
            }
        }

        if (doneCallback != null) {
            doneCallback();
        }

    }).fail(function(e) {

        if (failCallback != null) {
            failCallback();
        }
    })
}


/**
 * Confirmation modal dialog
 * @param message
 * @param title
 * @param handler
 */
function confirmDialog(message, title, handler) {

    $('<div class="modal fade" id="confirm-dialog" role="dialog">\
             <div class="modal-dialog"  role="document">\
                <div class="modal-content">\
                    <div class="modal-header">\
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">\
                            <span aria-hidden="true">&times;</span>\
                        </button>\
                        <h5 class="modal-title">' + title + '</h5>\
                    </div>\
                    <div class="modal-body">\
                        <p>' + message + '</p>\
                    </div>\
                    <div class="modal-footer">\
                         <div class="col float-right">\
                            <button class="btn btn-primary btn-yes">כן</button>\
                            <button class="btn btn-danger btn-no">לא</button>\
                        </div>\
                     </div>\
                   </div>\
               </div>\
            </div>\
          </div>').appendTo('body');

    //Trigger the modal
    $("#confirm-dialog").modal({
        backdrop: 'static',
        keyboard: false
    });

    //Remove the modal once it is closed.
    $("#confirm-dialog").on('hidden.bs.modal', function () {
        $("#confirm-dialog").remove();
    });

    $(".btn-yes").click(function () {
        handler(true);
        $("#confirm-dialog").modal("hide");
    });

    //Pass false to the callback function
    $(".btn-no").click(function () {
        handler(false);
        $("#confirm-dialog").modal("hide");
    });
}


var boolOptions = [];
boolOptions[0] = "לא";
boolOptions[1] = "כן";



function createYesNoSelectBox(selectShownName,selectBoxName,selectedValue,boolText){
    var person = [];
person["firstName"] = "John";
    
    var i;
    var html = "<select class='form-control' id='"+selectBoxName+"'>";
    if((selectShownName != "")) {
        html += "<option value='' disabled='disabled'>" + selectShownName + "</option>";
    }

    for(i=0; i<=1; i++){
        html += "<option value='"+i+"' ";
        if(i==selectedValue)
            html+="selected";
        html += ">"+boolText[i]+"</option>";
    }
    html+= "</select>";
    return html;
}

function companyDetailDialog(type) {

    let btnText = "";
    let btnClass = "";
    let isVisiblePass;
    let title = "";

    switch (type) {
        case "add" :
             btnText = "שמור +";
             btnClass = "btn-add-company";
             isVisiblePass = true;
             title = "הוספת משתמש חדש";
            break;
        case "edit" :
             btnText = "שמור";
             btnClass = "btn-edit-company";
             isVisiblePass = false;
             title = "חידוש נתונים משתמש";
            break;
    }
    

    html = `<div class="modal fade" id="add-company-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                        if(type == "edit") html += `<label for="username-add">שם משתמש</label>`;
                        html +=
                        `<div class="form-group">
                            <input type="text" name="fname" id="username-add" class="form-control" placeholder="שם משתמש" value="">
                        </div>
                        <div class="form-group">`;
                            html += `
                        <div class="form-group">`;
                        if(type == "edit") html += `<label for="email-add">כתובת דוא"ל</label>`;
                        html +=`
                            <input dir="ltr" type="email" name="email" id="email-add" class="form-control" placeholder='כתובת דוא"ל' value="">
                        </div>`;
                        if(type=="add"){
                            html+=`<div class="form-group">
                                <input type="password" id="pass-add" class="form-control" placeholder="סיסמה">
                            </div>`;
                        }
                        html +=`
                        <div class="form-group">`;
                        if(type == "edit") html += `<label for="pass-add">האם פעיל?</label>`;
                        html +=`
                            
                            `+createYesNoSelectBox('האם פעיל?','is-active-add',-1,boolOptions)+`
                        </div>
                        <div class="form-group">`;
                        if(type == "edit") html += `<label for="pass-add">הרשאות ניהול?</label>`;
                        html +=`
                            
                            `+createYesNoSelectBox('האם יכול ליצור מכרזים?','allowed-create-tenders-add',-1,boolOptions)+`
                        </div>
                        <div class="form-group">`;
                        if(type == "edit") html += `<label for="pass-add">האם איש מכירות?</label>`;
                        html +=`
                            
                            `+createYesNoSelectBox('האם יכול לערוך משתמשים?','allowed-edit-users-add',-1,boolOptions)+`
                        </div>
`;
 
                    html += `</article>
                </div>
        
              </div>
              <div class="modal-footer">
                  <div class="col float-right">
                    <button id="btn-add-company" class="btn btn-success">` + btnText + `</button>
                    <div id="error-add-company" class="text-danger"></div>
                  </div>
              </div>
              
            </div>
          </div>
        </div>`;

return html;
}

/**
 * Validation function block
 */
function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,})?$/;
    return emailReg.test( $email );
}

function ajaxSend(method, data, url, validator, responseSuccess, responseFail, doneCallback, failCallback) {

    if (validator != null) {

        if (!validator()) {
            return;
        }
    }

    $.ajaxSetup({
        headers : {
            'T': $('meta[name="T"]').attr('content')
        }
    });
    $.ajax({
        method: method,
        url: url,
        data: data

    }).done(function( response ) {

        if (response['status'] === "Success") {

            if (responseSuccess != null) {
                responseSuccess(response);
            }

        } else {

            if (responseFail != null) {
                responseFail(response);
            }
        }

        if (doneCallback != null) {
            doneCallback();
        }

    }).fail(function(e) {

        if (failCallback != null) {
            failCallback();
        }
    })
}


function autocomplete(inp, arr) {
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) { return false;}
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        /*for each item in the array...*/
        for (i = 0; i < arr.length; i++) {
            /*check if the item starts with the same letters as the text field value:*/
            if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                /*create a DIV element for each matching element:*/
                b = document.createElement("DIV");
                /*make the matching letters bold:*/
                b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                b.innerHTML += arr[i].substr(val.length);
                /*insert a input field that will hold the current array item's value:*/
                b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                /*execute a function when someone clicks on the item value (DIV element):*/
                b.addEventListener("click", function(e) {
                    /*insert the value for the autocomplete text field:*/
                    inp.value = this.getElementsByTagName("input")[0].value;
                    /*close the list of autocompleted values,
                    (or any other open lists of autocompleted values:*/
                    closeAllLists();
                });
                a.appendChild(b);
            }
        }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
            /*If the arrow DOWN key is pressed,
            increase the currentFocus variable:*/
            currentFocus++;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 38) { //up
            /*If the arrow UP key is pressed,
            decrease the currentFocus variable:*/
            currentFocus--;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 13) {
            /*If the ENTER key is pressed, prevent the form from being submitted,*/
            e.preventDefault();
            if (currentFocus > -1) {
                /*and simulate a click on the "active" item:*/
                if (x) x[currentFocus].click();
            }
        }
    });
    function addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }
    function closeAllLists(elmnt) {
        /*close all autocomplete lists in the document,
        except the one passed as an argument:*/
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}


function wait(ms){
    var start = new Date().getTime();
    var end = start;
    while(end < start + ms) {
        end = new Date().getTime();
    }
}

