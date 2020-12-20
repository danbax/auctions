<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';

if (!isset($_SESSION['user']['id'])) {
	header('Location: index.php');
}

require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR .  '/Permission.php';

// Initialization //////////////////////////////////////////////////////////////////////////////////////////////////////
$data = new DataManager();
$roles = new Permission();
$pdo = new DataManagerPDO();

$booleanToText = array();
$booleanToText[0] = 'לא';
$booleanToText[1] = 'כן';


/*************************
 * edit tender
 *************************/
$tenderId = filter_input(INPUT_GET,"editTenderId",FILTER_VALIDATE_INT);
if(!$tenderId){
    $tenderId = 0;
}
$editTender = false;
if($tenderId){
    $table = "tbl_tenders";
    $fields = ["id","st_name","st_description","date_created","date_last_update","i_finishing_id",
        "st_comments","st_sub_name","fl_starting_amount","fl_highest_amount","date_start_date","date_end_date",
    "st_production_year","i_model_id","st_licensing","i_classification","i_min_increase"];

    $now = date('Y-m-d');
    $tender = $pdo->select($table, $fields)
        ->where("id","=",$tenderId)
        ->fetch();

    if($tender){
        $tender = $tender[0];


        // get files
        $table = "tbl_files";
        $fields = ["id","st_file_name","st_file_original_name","date_uploaded","fl_size","i_tender_id","st_ext"];
        $files = $pdo->select($table, $fields)->where("i_tender_id","=",$tenderId)->fetch();
        if($tender){
            $editTender = true;
        }
    }
}


?>


<?php include_once 'includes/indexHead.php' ?>
<?php include_once 'includes/mainNavigation.php' ?>
<?php
/*************************
 * check permissions
 *************************/
if (!$isAdmin) {
    header('Location: tenders.php');
}
?>

<div class="container">
<section class="content-section">
    <div class="row justify-content-center">
        <div class="col-lg-12 ">

            <div class="row justify-content-right group-control">
                <div class="col-lg-4">
                    <h1>צור מכרז</h1>
                </div>
            </div>

            <div class="card" >
                <div class="card-body">
                    <h5 class="card-title">מלא את הפרטים הבאים:</h5>
                    <?php if(!$editTender) : ?>
                    <div class="row"  id="tender-input" style="display:none;" >
                        <div class="col-12">
                            <div style="width:100%">
                                <input type="text" class="form-control" name="tender-name-add" id="tender-name-add" placeholder="אנא הכנס את שם המכרז"
                                       value="<?php
                                       if($editTender){
                                           echo $tender["st_name"];
                                       }
                                       ?>">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-lg-4">
                            <?php if($editTender) : ?>
                                <label for="tender-name-add">שם המכרז</label>
                                <input type="text" class="form-control" name="tender-name-add" id="tender-name-add" placeholder="אנא הכנס את שם המכרז"
                                value="<?php
                                if($editTender){
                                    echo $tender["st_name"];
                                }
                                ?>">
                            <?php endif; ?>
                            <div id="tender-select" <?php if($editTender) : ?>style="display:none;"<?php endif; ?>>
                                <select id="tender-name-select" class="form-control">
                                    <?php
                                    $tenderNames = $pdo->select("tbl_tender_names",["id","st_name"])->fetch();
                                    foreach($tenderNames as $tenderName) :
                                    ?>
                                    <option value="<?=$tenderName["st_name"]?>"><?=$tenderName["st_name"]?></option>
                                    <?php endforeach;?>
                                    <option value="0">אחר</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <?php if($editTender) : ?>
                                <label for="tender-sub-name-add">כותרת משנה</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" name="tender-name-add" id="tender-sub-name-add" placeholder="כותרת משנה"
                                   value="<?php
                                   if($editTender){
                                       echo $tender["st_sub_name"];
                                   }
                                   ?>">
                        </div>
                        <div class="col-lg-4">
                            <?php if($editTender) : ?>
                                <label for="tender-start-amount-add">סכום התחלתי</label>
                            <?php endif; ?>
                            <input type="number" class="form-control" name="tender-name-add" id="tender-start-amount-add" placeholder="סכום התחלתי"
                                   value="<?php
                                   if($editTender){
                                       echo $tender["fl_starting_amount"];
                                   }
                                   ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?php if($editTender) : ?>
                                <label for="tender-date-start-add">תאריך התחלה</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" name="tender-date-add" id="tender-date-start-add" placeholder="תאריך התחלה"
                                   value="<?php
                                   if($editTender){
                                       echo date('d/m/Y H:i:s',strtotime($tender["date_start_date"]));
                                   }
                                   ?>">
                        </div>
                        <div class="col-lg-6">
                            <?php if($editTender) : ?>
                                <label for="tender-date-end-add">תאריך סיום</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" name="tender-date-add" id="tender-date-end-add" placeholder="אנא מלא תאריך סיום"
                                   value="<?php
                                   if($editTender){
                                       echo date('d/m/Y H:i:s',strtotime($tender["date_end_date"]));
                                   }
                                   ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <?php if($editTender) : ?>
                                <label for="model-id-add">שם הדגם</label>
                            <?php endif; ?>
                            <?php $models = $pdo->select("tbl_models",["id","st_name"])->fetch(); ?>
                            <select class="form-control" id="model-add">
                                <option value="0" disabled selected>-- בחר דגם --</option>
                                <?php foreach($models as $model) : ?>
                                    <option value="<?=$model["id"]?>"
                                        <?php if(isset($tender["i_model_id"]) && $tender["i_model_id"] == $model["id"]) { echo 'selected'; } ?>
                                    ><?=$model["st_name"]?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <?php if($editTender) : ?>
                                <label for="model-id-add">רמת גימור </label>
                            <?php endif; ?>
                            <?php
                            if($editTender) {
                                $finishings = $pdo->select("tbl_finishing",["id","st_name"])->where("i_is_active","=",1,'and')
                                    ->where("i_model_id","=",$tender["i_model_id"])->fetch();
                            }
                            ?>
                            <select class="form-control" id="finishing-add">
                                <?php if($editTender) : ?>
                                <option value="0" disabled selected>-- בחר רמת גימור --</option>
                                <?php else : ?>
                                    <option id="finishing-text" value="0" disabled selected>-- יש לבחור תחילה דגם --</option>
                                <?php endif; ?>
                                <?php if($editTender) : ?>
                                <?php foreach($finishings as $finishing) : ?>
                                    <option value="<?=$finishing["id"]?>"
                                        <?php if(isset($tender["i_finishing_id"]) && $tender["i_finishing_id"] == $finishing["id"]) { echo 'selected'; } ?>
                                    ><?=$finishing["st_name"]?></option>
                                <?php endforeach; ?>
                                <?php endif;?>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <?php if($editTender) : ?>
                                <label for="production-date-add">מועד ייצור</label>
                            <?php endif; ?>
                            <select class="form-control" id="production-date-add">
                                <option value="0" disabled selected>-- בחר שנת ייצור --</option>
                                <?php for($year = date('Y'); $year>=(date('Y')-10); $year--) : ?>
                                    <option value="<?=$year?>"
                                    <?php if(isset($tender["st_production_year"]) && $year == $tender["st_production_year"]) echo 'selected'; ?>
                                    ><?=$year?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <?php if($editTender) : ?>
                                <label for="licensing-add">רישוי</label>
                            <?php endif; ?>
                            <input type="text" class="form-control" id="licensing-add" placeholder="אנא הזן מספר רישוי"
                                   value="<?php
                                   if($editTender){
                                       echo $tender["st_licensing"];
                                   }
                                   ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?php if($editTender) : ?>
                                <label for="classification-add">סיווג</label>
                            <?php endif; ?>
                            <select class="form-control" id="classafication-add">
                                <option value="0" disabled selected>-- בחר סיווג --</option>
                                <?php foreach($classification as $index=>$item) : ?>
                                    <option value="<?=$index?>"
                                    <?php if(isset($tender["i_classification"]) && $tender["i_classification"] == $index) echo 'selected'; ?>
                                    ><?=$item?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <?php if($editTender) : ?>
                                <label for="licensing-add">תוספת מינימאלית למחיר</label>
                            <?php endif; ?>
                            <input type="number" class="form-control" name="min-increase-add" id="min-increase-add" placeholder="הזן תוספת מינימאלית"
                                   value="<?php
                                   if($editTender){
                                       echo $tender["i_min_increase"];
                                   }
                                   ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?php if($editTender) : ?>
                                <label for="tender-description-add">תיאור</label>
                            <?php endif; ?>
                            <textarea class="form-control" name="tender-description-add" id="tender-description-add" placeholder="תיאור המכרז - טקסט להזנה חופשית"><?php
                                if($editTender){
                                    echo $tender["st_description"];
                                }
                                ?></textarea>
                        </div>
                        <div class="col-lg-6">
                            <?php if($editTender) : ?>
                                <label for="tender-comments-add">הערות</label>
                            <?php endif; ?>
                            <textarea class="form-control" name="tender-comments-add" id="tender-comments-add" placeholder="הערות (אופציונאלי)"><?php
                                if($editTender){
                                    echo $tender["st_comments"];
                                }
                                ?></textarea>
                        </div>
                    </div>

                    <div id="proceed-to-dates-errors"></div>
                    <?php if(!$editTender) : ?>
                        <button class="btn btn-primary proceed-to-dates">המשך</button>
                    <?php endif; ?>


                    <div id="upload-files-container" <?php if(!$editTender) : ?>style="display:none" <?php endif; ?>>
                        <hr>
                        <h5>הוסף קבצים</h5>
                        <input type="hidden" value="<?=$tenderId?>" id="tender-id">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">

                                <!-- Our markup, the important part here! -->
                                <div id="drag-and-drop-zone" class="dm-uploader p-5">
                                    <h3 class="mb-5 mt-5 text-muted">גרור &amp; ושחרר את הקבצים שלך כאן</h3>

                                    <div class="btn btn-primary btn-block mb-5">
                                        <span>מצא את הקובץ במחשב שלך</span>
                                        <input type="file" title='לחץ כדי להוסיף קובץ' />
                                    </div>
                                </div><!-- /uploader -->

                            </div>
                            <div class="col-md-6 col-sm-12" style="text-align: center">
                                <div class="card h-100">
                                    <div class="card-header">
                                        רשימת קבצים
                                    </div>

                                    <ul class="list-unstyled p-2 d-flex flex-column col" id="files">
                                        <li class="text-muted text-center empty">לא הועלאו קבצים.</li>
                                    </ul>
                                </div>
                            </div>

                            <div id="file-list">
                                <?php
                                if($editTender){
                                    foreach($files as $file) {
                                        echo '<p style="display:inline;">';
                                        echo $file["st_file_original_name"]."(".($file["fl_size"]/1000)." קילו בייט)";
                                        echo ' | <span style="color:red" data-id="'.$file["id"].'" class="delete-file"><i class="fa fa-trash"></i></span>';
                                        echo '<br></p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <!-- /file list -->
                        <?php if($editTender) : ?>
                            <button class="btn btn-primary update-tender">עדכן מכרז</button>
                        <?php else: ?>
                            <button class="btn btn-primary end-tender-creation">צור מכרז</button>
                        <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
</section>

	<?php include_once 'includes/footer.php' ?>

</div>

<?php include_once 'includes/indexFooter.php' ?>
<?php
/**
 * get all finishes by model id
 */

$array = [];
$finishings = $pdo->select("tbl_finishing",["id","st_name","i_model_id"])->where("i_is_active","=",1)->orderBy("i_model_id","desc")->fetch();
foreach ($finishings as $finishing){
    if(!isset($array[$finishing["i_model_id"]])){
        $array[$finishing["i_model_id"]] = [];
    }

    $finish = new stdClass();
    $finish->id = $finishing["id"];
    $finish->name = $finishing["st_name"];

    $array[$finishing["i_model_id"]][] = $finish;
}


?>

<script>
    var finishings = <?=json_encode($array)?>

    console.log(finishings);

    $("#tender-name-select").change(function(){
       if($(this).val() == 0){
           $("#tender-input").show('slow');
       }else{
           $("#tender-input").hide('slow');
           $("#tender-name-add").val('');
       }
    });

    $("#model-add").change(function(){
        if($(this).val() == 0 || $("#model-add").val() == null){
            $("#finishing-add").attr("disabled","disabled");
        }else{
            /*
            get list of finishes by model
             */
            $("#finishing-add").removeAttr("disabled","disabled");
            $("#finishing-text").text("-- בחר רמת גימור --");

            console.log($("#model-add").val());
            var currentFinishings = finishings[$("#model-add").val()];
            console.log(currentFinishings)
            var html = "";
            html += '<option value="0" disabled selected>-- בחר רמת גימור --</option>';
            jQuery.each( currentFinishings, function( index,entry ) {
                html += '<option value="'+entry.id+'" >'+entry.name+'</option>';
            });
            $("#finishing-add").html(html);

        }
    });

    $( document ).ready(function() {
        if($("#model-add").val() == 0 || $("#model-add").val() == null){
            $("#finishing-add").attr("disabled","disabled");
        }
    });



    function getTenderInputData(){
        var name = "";
        <?php if(!$editTender) : ?>
        if($("#tender-name-select").val() != 0){
            name = $("#tender-name-select").val()
        }
        else {
            name = $("#tender-name-add").val();
        }
        <?php else: ?>
            name = $("#tender-name-add").val();
        <?php endif; ?>

        var subName = $( "#tender-sub-name-add" ).val();
        var dateStart = $( "#tender-date-start-add" ).val();
        var dateEnd = $( "#tender-date-end-add" ).val();
        var startAmount = $( "#tender-start-amount-add" ).val();

        var description = $( "#tender-description-add" ).val();
        var comments = $( "#tender-comments-add" ).val();


        var model = $( "#model-add" ).val();
        var productionDate = $( "#production-date-add" ).val();
        var licensing = $( "#licensing-add" ).val();
        var classafication = $( "#classafication-add" ).val();
        var minIncrease = $( "#min-increase-add" ).val();
        var finishing = $( "#finishing-add" ).val();


        var data = [];
        data.name = name;
        data.subName = subName;
        data.dateStart = dateStart;
        data.dateEnd = dateEnd;
        data.startAmount = startAmount;
        data.description = description;
        data.comments = comments;


        data.model = model;
        data.productionDate = productionDate;
        data.licensing = licensing;
        data.classafication = classafication;
        data.minIncrease = minIncrease;
        data.finishing = finishing;
        return data;
    }


    function createTender(){
        $( "#date" ).text("טוען...");


        var data = [];
        data = getTenderInputData();
        var dateTime = data.dateStart.split(" ");
        var onlyDate = dateTime[0].split("/").reverse().join("-");
        var startDate = onlyDate+" "+dateTime[1];


        var dateTime = data.dateEnd.split(" ");
        var onlyDate = dateTime[0].split("/").reverse().join("-");
        var endDate = onlyDate+" "+dateTime[1];

        startDate = new Date(startDate);
        endDate = new Date(endDate);

        var start = startDate.getTime();
        var end = endDate.getTime();


        if((data.name == "" || data.dateStart == "" || data.dateEnd == ""
            || data.startAmount == "" || data.dateStart == ""
            || data.model == null || data.productionDate == null|| data.licensing == "" ||
            data.classafication == null||data.finishing == null|| data.minIncrease == "" || data.finishing == "" || start>end)){

            $( "#proceed-to-dates-errors" ).css("color","red");
            $( "#proceed-to-dates-errors" ).text("עליך למלא את כל הפרטים!");

            if(data.name == ""){
                $( "#tender-name-add" ).addClass("is-invalid");
            }
            else{
                $( "#tender-name-add" ).removeClass("is-invalid");
            }


            if(data.finishing == ""){
                $( "#finishing-add" ).addClass("is-invalid");
            }
            else{
                $( "#finishing-add" ).removeClass("is-invalid");
            }
            if(data.model == null){
                $( "#model-add" ).addClass("is-invalid");
            }
            else{
                $( "#model-add" ).removeClass("is-invalid");
            }
            if(data.productionDate == null){
                $( "#production-date-add" ).addClass("is-invalid");
            }
            else{
                $( "#production-date-add" ).removeClass("is-invalid");
            }
            if(data.finishing == null){
                $( "#finishing-add" ).addClass("is-invalid");
            }
            else{
                $( "#finishing-add" ).removeClass("is-invalid");
            }


            if(data.licensing === ""){
                $( "#licensing-add" ).addClass("is-invalid");
            }
            else{
                $( "#licensing-add" ).removeClass("is-invalid");
            }


            if(data.classafication == null){
                $( "#classafication-add" ).addClass("is-invalid");
            }
            else{
                $( "#classafication-add" ).removeClass("is-invalid");
            }
            if(data.minIncrease == ""){
                $( "#min-increase-add" ).addClass("is-invalid");
            }
            else{
                $( "#min-increase-add" ).removeClass("is-invalid");
            }



            if(data.startAmount == ""){
                $( "#tender-start-amount-add" ).addClass("is-invalid");
            }
            else{
                $( "#tender-start-amount-add" ).removeClass("is-invalid");
            }

            if(data.dateStart == ""){
                $( "#tender-date-start-add" ).addClass("is-invalid");
            }
            else{
                $( "#tender-date-start-add" ).removeClass("is-invalid");

                if(start>end){
                    $( "#tender-date-start-add" ).addClass("is-invalid");
                    alert("לא ניתן לבחור תאריך סיום שעבר זמנו מתאריך ההתחלה!");
                }
                else{
                    $( "#tender-date-start-add" ).removeClass("is-invalid");
                }
            }

            if(data.dateEnd == ""){
                $( "#tender-date-end-add" ).addClass("is-invalid");
            }
            else{
                $( "#tender-date-end-add" ).removeClass("is-invalid");
            }
            if(data.dateStart == ""){
                $( "#tender-date-start-add" ).addClass("is-invalid");
            }
            else{
                $( "#tender-date-start-add" ).removeClass("is-invalid");
            }


        }else{

            // data is valid
            $( "#proceed-to-dates-errors" ).css("color","black");
            $( "#proceed-to-dates-errors" ).text("טוען...");

            $( "#tender-name-add" ).removeClass("is-invalid");
            $( "#tender-sub-name-add" ).removeClass("is-invalid");
            $( "#tender-start-amount-add" ).removeClass("is-invalid");
            $( "#tender-date-start-add" ).removeClass("is-invalid");
            $( "#tender-date-end-add" ).removeClass("is-invalid");
            $( "#tender-description-add" ).removeClass("is-invalid");


            $( "#model-add" ).removeClass("is-invalid");
            $( "#production-date-add" ).removeClass("is-invalid");
            $( "#licensing-add" ).removeClass("is-invalid");
            $( "#classafication-add" ).removeClass("is-invalid");
            $( "#min-increase-add" ).removeClass("is-invalid");
            $( "#finishing-add" ).removeClass("is-invalid");


            return data;

        }
        return false;
    }

    $( ".update-tender" ).click(function() {

        var data = [];
        if(data = createTender()){

        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });

        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "updateTender",
                tenderId: <?=$tenderId?>,
                name: data.name,
                subName: data.subName,
                dateStart: data.dateStart,
                dateEnd: data.dateEnd,
                startingAmount:data.startAmount,
                comments: data.comments,
                minIncrease: data.minIncrease,
                classafication: data.classafication,
                licensing: data.licensing,
                productionDate: data.productionDate,
                model: data.model,
                finishing: data.finishing,
                description: data.description
            },

        }).done(function( data ) {
            console.log(data);

            if (data['status'] === "OK") {


                var tenderId = $( "#tender-id" ).val();
                location.href="tender.php?tenderId="+tenderId;

            } else {
                if(data["error"]){
                    $( "#proceed-to-dates-errors" ).text(data["error"]);
                }else {
                    $("#proceed-to-dates-errors").text("קרתה שגיאה!");
                }
            }
        });
        }
    });


    $(document).on('click',".delete-file" , function(){
        var fileId = $( this ).data("id");
        var container = $(this).parent("p");
        $.ajaxSetup({
            headers : {
                'T': $('meta[name="T"]').attr('content')
            }
        });

        $.ajax({
            method: "POST",
            url: "ajaxApi.php",
            data: {
                action: "deleteFile",
                fileId: fileId
            },

        }).done(function( data ) {
            console.log(data);
            if (data['status'] === "OK") {
                container.fadeOut('normal');
            } else {
                $( "#proceed-to-dates-errors" ).css("color","red");
                if(data["error"]){
                    $( "#proceed-to-dates-errors" ).text(data["error"]);
                }else {
                    $("#proceed-to-dates-errors").text("קרתה שגיאה!");
                }
            }
        });
    });


    $( ".end-tender-creation" ).click(function() {
        var tenderId = $( "#tender-id" ).val();
        location.href="tender.php?tenderId="+tenderId;
    });



    $( ".proceed-to-dates" ).click(function() {
        var data;
        if(data = createTender()){
            console.log(data);
            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "addTender",
                    name: data.name,
                    subName: data.subName,
                    dateStart: data.dateStart,
                    dateEnd: data.dateEnd,
                    startingAmount:data.startAmount,
                    comments: data.comments,
                    minIncrease: data.minIncrease,
                    classafication: data.classafication,
                    licensing: data.licensing,
                    productionDate: data.productionDate,
                    model: data.model,
                    finishing: data.finishing,
                    description: data.description
                },

            }).done(function( data ) {
                console.log(data)

                if (data['status'] === "OK") {

                    $( "#proceed-to-dates-errors" ).css("color","black");
                    $( "#proceed-to-dates-errors" ).text("");

                    $(".proceed-to-dates").hide();
                    $("#dates-add-container").show();
                    $("#upload-files-container").show();



                    $( "#tender-id" ).val(data['tenderId']);

                } else {
                    $( "#proceed-to-dates-errors" ).css("color","red");
                    if(data["error"]){
                        $( "#proceed-to-dates-errors" ).text(data["error"]);
                    }else {
                        $("#proceed-to-dates-errors").text("קרתה שגיאה!");
                    }
                }
            });
        }
    });

    jQuery.datetimepicker.setLocale('he');
    jQuery('#tender-date-start-add').datetimepicker({
        minDate:`6/6/2020`,
        format:'d/m/Y H:i'
    });
    jQuery('#tender-date-end-add').datetimepicker({
        format:'d/m/Y H:i'
    });
    jQuery('#tender-dates-add').datetimepicker({
        format:'d/m/Y H:i'
    });


</script>
<!-- File item template -->
<script type="text/html" id="files-template">
    <li class="media">
        <div class="media-body mb-1">
            <p class="mb-2">
                <strong>%%filename%%</strong> - סטטוס: <span class="text-muted">Waiting</span>
            </p>
            <div class="progress mb-2">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                     role="progressbar"
                     style="width: 0%"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <hr class="mt-1 mb-1" />
        </div>
    </li>
</script>

<!-- Debug item template -->
<script type="text/html" id="debug-template">
    <li class="list-group-item text-%%color%%"><strong>%%date%%</strong>: %%message%%</li>
</script>

