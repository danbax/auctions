<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';

$id = filter_input(INPUT_GET, 'tenderId', FILTER_VALIDATE_INT);

$_SESSION['lastTenderId'] = $id; // save last tender session before logout

if (!isset($_SESSION['user']['id'])) {
	header('Location: index.php');
}

require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR .  '/Permission.php';

// Initialization //////////////////////////////////////////////////////////////////////////////////////////////////////
$data = new DataManager();
$roles = new Permission();

$pdo = new DataManagerPDO();

$isAllowed = $pdo->select("tbl_users",["bool_is_admin"])
    ->where("id","=",$_SESSION['user']['id'])
    ->fetch();


$isAdmin = false;
if($isAllowed[0]["bool_is_admin"]) {
    $isAdmin = true;
}

$table = "tbl_tenders";
$fields = ["tbl_tenders.id as id","tbl_tenders.st_name","st_description","date_created","date_last_update","bool_canceled",
    "st_comments","st_sub_name","fl_starting_amount","fl_highest_amount","date_start_date","date_end_date",
    "st_production_year","i_model_id","st_licensing","i_classification","i_min_increase",
"tbl_models.st_name as modelName"];

$now = date('Y-m-d');
$tender = $pdo->select($table, $fields)
    ->leftJoin("tbl_models","tbl_models.id","=","tbl_tenders.i_model_id")
    ->where("tbl_tenders.id","=",$id)
    ->fetch();

if(!$tender)
{
    header('Location: tenders.php');
}

$tender = $tender[0];

// get files
$table = "tbl_files";
$fields = ["st_file_name","st_file_original_name","date_uploaded","fl_size","i_tender_id","st_ext"];
$files = $pdo->select($table, $fields)->where("i_tender_id","=",$id)->fetch();

// get comments
$table = "tbl_tenders_comments,tbl_users";
$fields = ["tbl_tenders_comments.id","st_comment","tbl_tenders_comments.date_created","tbl_users.st_username as nickname"];
$comments = $pdo->select($table, $fields)->where("i_tender_id","=",$id,'AND')
    ->whereConstant("tbl_users.id","=","tbl_tenders_comments.i_user_id")
    ->orderBy("date_created","desc")->fetch();

// get bids history
$table = "tbl_user_bids";
$fields = ["tbl_user_bids.i_tender_id","tbl_user_bids.i_user_id","tbl_user_bids.fl_bid","tbl_users.st_username as nickname","tbl_users.st_friendly_name as friendlyName","date_created"
    ];

$bids = $pdo->select($table,$fields)
    ->leftJoin("tbl_users","tbl_users.id","=","tbl_user_bids.i_user_id")
    ->where("tbl_user_bids.i_tender_id","=",$id)
    ->fetch();

$isAllowedTender = true;
if(!$isAdmin){
    if($tender["date_start_date"] > (date('Y-m-d H:i:s'))){
        $isAllowedTender = false;
    }
}

?>


<?php include_once 'includes/indexHead.php' ?>

<?php include_once 'includes/mainNavigation.php' ?>

<style>

    .img-container {
        margin: 20px;

    }

    .img-container img {
        width: 200px;
        height: auto;
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition: .3s;
        -webkit-transition: .3s;
        -moz-transition: .3s;

    }
    .img-container img:hover{
        transform: scale(0.97);
        -webkit-transform: scale(0.97);
        -moz-transform: scale(0.97);
        -o-transform: scale(0.97);
        opacity: 0.75;
        -webkit-opacity: 0.75;
        -moz-opacity: 0.75;
        transition: .3s;
        -webkit-transition: .3s;
        -moz-transition: .3s;
    }
</style>

<div class="container">
<section class="content-section">
    <div class="row justify-content-center">
        <div class="col-lg-12 ">

            <div class="row justify-content-right tender-control">
                <div class="col-lg-12">
                    <h1><?=$tender['st_name']?></h1>
                </div>
            </div>
            <input type="hidden" value="<?=$id?>" id="tender-id">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <?php if($tender["bool_canceled"]) : ?>
                                <p class="alert alert-danger">המכרז בוטל</p>
                            <?php else: ?>
                            <h3>פרטי המכרז</h3>

                            <p>
                                <b>תיאור: </b> <?=$tender["st_description"]?>
                            </p>
                            <?php if($tender["st_comments"]) : ?>
                            <p>
                                <b>הערות: </b> <?=$tender["st_comments"]?>
                            </p>
                            <?php endif; ?>
                            <p>
                                <b>שנת ייצור: </b> <?=$tender["st_production_year"]?>
                            </p>
                            <p>
                                <b>רישוי: </b> <?=$tender["st_licensing"]?>
                            </p>
                            <?php if(isset($classification[$tender["i_classification"]])) : ?>
                            <p>
                                <b>סיווג: </b> <?=$classification[$tender["i_classification"]]?>
                            </p>
                            <?php endif; ?>
                            <p>
                                <b>דגם: </b> <?=$tender["modelName"]?>
                            </p>
                            <p>
                                <b>תאריך התחלת מכרז: </b> <?=date('H:i:s d/m/Y',strtotime($tender['date_start_date']))?>
                            </p>
                            <b>תאריך סיום מכרז: </b> <?=date('H:i:s d/m/Y',strtotime($tender['date_end_date']))?>
                            <?php if($isAdmin) : ?>
                                <hr><a class="btn btn-secondary" href="createTender.php?editTenderId=<?=$tender["id"]?>"><i class="fa fa-pencil"></i> עריכת מכרז</a>
                                <?php
                            $diff_time=(strtotime($tender["date_start_date"]) - strtotime(date("Y/m/d H:i:s")))/60;
                            if($diff_time < 15) :
                                // we cannot cancel
                                ?>
                                <hr><span>לא ניתן לבטל את המכרז שכן המכרז יתחיל בתוך פחות מרבע שעה.</span>
                            <?php else: ?>
                                <hr><a class="btn btn-danger" id="cancelTender" data-id="<?=$tender["id"]?>" style="color:white;cursor:pointer"><i class="fa fa-times"></i> ביטול מכרז</a>
                                <span id="error-cancel-tender" style="color:red"></span>
                            <?php endif;?>
                            <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-lg-6">
                            <h3>הצעת מחיר</h3>
                            <p>
                                <b>ההצעה הגבוה ביותר: </b> <?=getPrice($tender["fl_highest_amount"])?>
                            </p>
                            <?php if($isAllowedTender) : ?>
                            <?php if(strtotime($tender['date_end_date']) > strtotime('now')) : ?>
                            <div>
                                <p>הגדל הצעה: </p>
                                <input id="bid-amount" min="<?=$tender["fl_highest_amount"]+$tender["i_min_increase"]?>" type="number" class="form-control" placeholder="סכום מינימאלי: <?=getPrice($tender["fl_highest_amount"]+$tender["i_min_increase"])?> ">
                                <br>
                                <p class="btn btn-primary" id="send-bid">הצע</p>
                                <div id="bid-errors"></div>
                            </div>
                            <?php else: ?>
                            <p>המכרז הסתיים.לא ניתן להגיש יותר הצעות</p>
                            <?php endif; ?>
                            <?php else : ?>
                                <div class="alert alert-danger">המכרז יתחיל ב- <?=date('d/m/Y H:i:s',strtotime($tender["date_start_date"]))?></div>
                            <?php endif; ?>
                            <?php if(sizeof($files)) : ?>
                                <?php
                                    // init images arrays
                                    $images = [];
                                    $captions = [];
                                ?>
                                <hr>
                                <h3>תמונות וקבצים מצורפים</h3>
                            <div class="img-container">
                                <?php
                                $tenderImages = [];
                                $tenderFiles = [];
                                for($i=0; $i<sizeof($files); $i++) {
                                    if (empty($files[$i]["st_file_original_name"]))
                                        $files[$i]["st_file_original_name"] = "קובץ " . $files[$i]["st_ext"] . " ללא שם";
                                    ?>
                                    <?php
                                    $ext = strtolower($files[$i]["st_ext"]);
                                    if ($ext == "png" || $ext == "jpg" || $ext == "jpeg") {
                                        //pictures
                                        $tenderImages[] = $files[$i]["st_file_name"];
                                    } else {
                                        // files
                                        $file = new stdClass();
                                        $file->name = $files[$i]["st_file_name"];
                                        $file->originalName = $files[$i]["st_file_original_name"];
                                        $file->size = number_format($files[$i]["fl_size"] / 1000, 0); //KB

                                        $tenderFiles[] = $file;
                                }
                                }?>
                            </div>
                                <div class="img-container">
                                    <?php if($tenderImages) : ?>
                                    <h5>תמונות</h5>
                                        <?php foreach($tenderImages as $image) : ?>
                                            <i class="reset-this" href="<?=$image?>" title="<?=$tender["st_name"]?>"><img src="<?=$image?>" width="75" height="75"></i>
                                        <?php endforeach; ?>
                                        <hr>
                                    <?php endif; ?>
                                    <?php if($tenderFiles) : ?>
                                        <h5>קבצים מצורפים</h5>
                                        <?php foreach($tenderFiles as $file) : ?>
                                        <a dir="rtl" href="fileDownload.php?file=<?=$file->name?>">
                                            <?=$file->originalName?>
                                        </a>(<?=$file->size?> KB)
                                        <br>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>


                            <?php if(sizeof($bids) && $isAdmin) : ?>
                                <hr>
                                <h3>היסטוריית הצעות</h3>
                                <table class="table" id="bids-history-table">
                                    <thead>
                                    <tr>
                                        <th>משתמש</th>
                                        <th>הצעה</th>
                                        <th>תאריך</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($bids as $bid) : ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if($bid["friendlyName"]){
                                                echo $bid["friendlyName"];
                                            }else{
                                                echo $bid["nickname"];
                                            }
                                            ?>
                                        </td>
                                        <td data-order="<?=$bid["fl_bid"]?>"><?=getPrice($bid["fl_bid"])?></td>
                                        <td><?=date('d/m/Y H:i:s',strtotime($bid["date_created"]))?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

	<?php include_once 'includes/footer.php' ?>

</div>

<?php include_once 'includes/indexFooter.php' ?>
<script src="js/tenders.js?ver=<?=rand(1,100)?>"></script>


<script>
    $(document).ready(function(){

        $('#cancelTender').on( 'click', function () {
            $.ajaxSetup({
                headers : {
                    'T': $('meta[name="T"]').attr('content')
                }
            });
            $.ajax({
                method: "POST",
                url: "ajaxApi.php",
                data: {
                    action: "cancelTender",
                    tenderId: <?=$tender["id"]?>,
                }

            }).done(function( data ) {

                if (data['status'] === "OK") {
                    location.reload();
                } else {
                    $("#error-cancel-tender").html( data['error'] );
                }
            });
        } );
/*
        $(".img-container").popupLightbox({
            width: 600,
            height: 450
        });
*/
        $(document).ready(function() {
            $('.img-container').magnificPopup({
                delegate: 'i',
                type: 'image',
                tLoading: 'טוען תמונה #%curr%...',
                mainClass: 'mfp-img-mobile',
                gallery: {
                    enabled: true,
                    navigateByImgClick: true,
                    preload: [0,1] // Will preload 0 - before current, and 1 after the current image
                },
                image: {
                    tError: '<a href="%url%">לא הצלחנו לטעון את התמונה #%curr%</a> .',
                    titleSrc: function(item) {
                        return item.el.attr('title') + '<small>PDAC</small>';
                    }
                }
            });
        });


    });
</script>


