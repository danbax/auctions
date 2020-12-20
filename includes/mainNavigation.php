<?php
/*************************
 * check permissions
 *************************/
$pdo = new DataManagerPDO();
$isAllowed = $pdo->select("tbl_users",["bool_is_admin"])
    ->where("id","=",$_SESSION['user']['id'])
    ->fetch();


$isAdmin = false;
if($isAllowed[0]["bool_is_admin"]) {
    $isAdmin = true;
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand top-links" href="tenders.php">
        <img src="img/logo.png" alt="Logo" style="width:50px; height:40px;">
    </a>
    <?php
        $url = $_SERVER['REQUEST_URI'];
        
        $urls = array();
        $texts = array();

    array_push($urls,"tenders.php");
    array_push($texts,"מכרזים");

    if($isAdmin){
        array_push($urls,"createTender.php");
        array_push($texts,"יצירת מכרז");
        array_push($urls,"models.php");
        array_push($texts,"דגמים");
        array_push($urls,"finishing.php");
        array_push($texts,"רמות גימור");
        array_push($urls,"users.php");
        array_push($texts,"משתמשים");
        array_push($urls,"tenders.php?showHistory=true");
        array_push($texts,"היסטוריית מכרזים");
        array_push($urls,"sendSms.php");
        array_push($texts,"שליחת הודעות");
        array_push($urls,"logs.php");
        array_push($texts,"לוג");
    }


        $links = array();
        for($i=0; $i<sizeof($urls); $i++){
            $link = new stdClass();
            $link->url = $urls[$i];
            $link->text = $texts[$i];
            array_push($links,$link);
        }
    ?>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav">
            <?php foreach($links as $link) : ?>
            <li class="nav-item">
                <a class="top-links nav-link <?php if($url == '/'.$link->url) echo 'menu-active';?>" href="<?=$link->url?>">
                    <?=$link->text?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <ul class="navbar-nav mr-auto">
            <li class="nav-item" style="float:left;">
                <a class="nav-link" style="font-weight: bold" href="logout.php">התנתקות</a>
            </li>
        </ul>
    </div>


</nav>



