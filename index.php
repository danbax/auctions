<?php
include_once 'includes/config.php';
include_once 'classes/DataManagerPDO.php';

$tenderName = "";
if(isset($_SESSION['lastTenderId']) && $_SESSION['lastTenderId'] && is_numeric($_SESSION['lastTenderId'])){
    $pdo = new DataManagerPDO();
    $tender = $pdo->select("tbl_tenders",["st_name"])->where("id","=",$_SESSION['lastTenderId'])->fetch();
    if($tender){
        $tenderName = $tender[0]["st_name"];
    }
}


include_once 'includes/indexHead.php';
?>
<style>
    body{
        background-color:#58595B;
        padding-top:0;
    }
</style>
<div class="container">
 
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10 text-">
            <div class="card" style="margin-top:10%" >
                <article class="card-body main" style="text-align: center;">
                    <img src="img\logo.png" class="logo-login"/>
                    <h4 class="card-title text-center mb-4 mt-1">ניהול מכרזים</h4>
                    <?php if($tenderName) : ?>
                    <p>עליך להתחבר כדי לגשת למכרז "<?=$tenderName?>".</p>
                    <?php endif; ?>
                    <hr>
                    <p id="error-text" class="text-danger text-center"></p>
                    <form>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text"> <i class="fa fa-user"></i></span>
                                </div>
                                <input  style="direction: ltr;text-align: right;" id="username" name="" class="form-control di" placeholder='דואר אלקטרוני או שם משתמש' type="text" autofocus="">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text"> <i class="fa fa-lock"></i> </span>
                                </div>
                                <input style="direction: ltr;text-align: right;" id="password" class="form-control" placeholder="סיסמה" type="password"  dir="ltr">
                            </div>
                        </div>
                        <div class="form-group">
                            <button id="login-submit" type="button" class="btn btn-primary col-lg-6"> התחברות  </button>
                        </div>
                    </form>
                    <p>
                        שכחת סיסמה? נא צור קשר עם הנציג שלך 
                    </p>
                </article>
            </div>
        </div>
    </div>
 
</div>
 
<?php include_once 'includes/indexFooter.php' ?>

<script>
   $( document ).keypress(function (e) {
    var key = e.which;
    if(key == 13)  // the enter key code
     {
       $('#login-submit').click();
       return false;  
     }
   });   
</script>

