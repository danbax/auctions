<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';

define("NO_DATE","0000-00-00 00:00:00");


if (!isset($_SESSION['user']['id'])) {
        header('Location: index.php');
}
else{
    $currentUserId = $_SESSION['user']['id'];
}

$isShowingHistory = false;
if(isset($_GET['showHistory'])){
    $isShowingHistory = true;
}

require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR .  '/Permission.php';


// Initialization //////////////////////////////////////////////////////////////////////////////////////////////////////
$data = new DataManager();
$roles = new Permission();

$statuses = array();
$statuses[0] = "ממתין לתשובה";
$statuses[1] = "מאושר";
$statuses[2] = "נדחה";


$tenderId = filter_input(INPUT_GET, 'tenderId', FILTER_VALIDATE_INT);

$pdo = new DataManagerPDO();
$countUser = $pdo->select("tbl_users",["count(id) as count"])->fetch();
$countUser = $countUser[0]['count'];

$isAllowed = $pdo->select("tbl_users",["bool_is_admin"])
    ->where("id","=",$_SESSION['user']['id'])
    ->fetch();


$isAdmin = false;
if($isAllowed[0]["bool_is_admin"]) {
    $isAdmin = true;
}

?>

<?php include_once 'includes/indexHead.php' ?>

<?php include_once 'includes/mainNavigation.php' ?>

<div class="containerTenders">
    <section class="content-section">
        <div class="row justify-content-center">
            <div class="col-lg-12 ">

                <div class="row justify-content-right tender-control">
                    <div class="col-lg-4">
                        <?php if($isShowingHistory) : ?>
                            <h1>היסטוריית מכרזים</h1>
                        <?php else: ?>
                            <h1>מכרזים</h1>
                        <?php endif; ?>
                    </div>
                </div>


                <?php
                if($isAdmin):
                    $response = checkSmsBalance();
                    if($response != null) :
                        if($response->status == RESULT_SUCCESS) :
                            ?>
                            <div class="alert alert-<?php if($response->balance<$countUser) echo 'danger'; else echo 'info';?>">
                                <b>היתרה שנשארה בחשבון שירות הודעות ה-SMS היא:</b> ₪<?=$response->balance?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <b>שגיאה בקבלת היתרה משרות הודעות ה-SMS:</b> <?=$response->message?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>

                <table id="tender-table" class="table table-striped table-bordered ">
                    <thead>
                    <tr>
                        <th>מספר מכרז</th>
                        <th>עמוד מכרז</th>
                        <th>שם המכרז</th>
                        <th>כותרת משנה</th>
                        <th>שם הדגם</th>
                        <th>שם רמת גימור</th>
                        <th>שנת ייצור</th>
                        <th>סיווג</th>
                        <th>תאריך פתיחת מכרז</th>
                        <th>אורך המכרז בימים</th>
                        <th>תאריך סגירה</th>
                        <th>ההצעה הנוכחית הגבוה ביותר</th>
                        <th>מחיר קניה</th>
                        <?php if($isShowingHistory) : ?>
                        <th>סכום מכירה סופי</th>
                        <th>סוכן שזכה במכרז</th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>


                    </tbody>
                </table>

            </div>
        </div>
    </section>

    <?php include_once 'includes/footer.php' ?>

</div>

<?php include_once 'includes/indexFooter.php' ?>
<script src="js/tenders.js?ver=<?=rand(1,100)?>"></script>
<script>
    var table;

    $('#tender-table tbody').on( 'click', 'a', function () {
        var data = table.row( $(this).parents('tr') ).data();
        window.location.href= 'tender.php?tenderId='+data.id;
    } );



    $(document).ready(function() {
        table = $('#tender-table').DataTable( {
            dom: '<"pull-left"B><"pull-right"f>rtip',
            buttons: [
                {
                    className: "dtBtn",
                    extend: 'colvis',
                    columns: ':gt(0)',
                    text: ' הצג עמודות '
                },
                {
                    className: "dtBtn",
                    extend: 'csv',
                    text:'יצא כ- CSV <i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'ניתן לסנן את העמודות היוצאות',
                    bom: true,
                    charset: 'UTF-8',
                    exportOptions: {
                    <?php if($isShowingHistory) : ?>
                        columns: [ 0, 1,2,3,4,5,6,7,8,9,10,11,12,13,14 ],
                    <?php else : ?>
                        columns: [ 0, 1,2,3,4,5,6,7,8,9,10,11,12 ],
                    <?php endif; ?>
                        //columns: ':visible:not(.not-exported)',
                        //rows: '.selected'
                    }

                },
                ],
            "pageLength": 50,
            "processing": true,
            "serverSide": false,
            responsive: true,
            "aaSorting": [],
            responsive: true,
            <?php if($isShowingHistory) : ?>
            ajax: "getTenders.php?showHistory=true",
            <?php else: ?>
            ajax: "getTenders.php",
            <?php endif; ?>
            columns: [
                {  data: "id" },
                { data: "url" },
                { data: "name" },
                { data: "subName" },
                { data: "modelName" },
                { data: "finishingName" },
                { data: "productionYear" },
                { data: "classification" },
                { data: "startDate" },
                { data: "daysBetween" },
                { data: "endDate" },
                { data: "highestAmount" },
                { data: "startingAmount" }
                <?php if($isShowingHistory) : ?>
                ,
                { data: "highestAmount" },
                { data: "winnerName" }
                <?php endif; ?>
            ],

            "rowCallback": function( row, data, index ) {
                if ( data["admin"] == "true" )
                {
                    $('td', row).css('color', 'grey');
                }

                if ( data["won"] == "true" )
                {
                    $('td', row).css('color', 'green');
                }

            },

            fixedHeader: {
                header: true,
                footer: false,
                headerOffset: 50
            },
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-5x fa-fw"></i><span class="sr-only">טוען...</span> ',
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
        } );
    } );

</script>
<style>
    .tenderRow:hover{
        background-color:#ecf0f1;
        cursor:pointer;
    }
</style>
<script src="js/datatables/buttons.colVis.min.js"></script
<script src="js/datatables/dataTables.buttons.min.js"></script>
<script src="js/datatables/buttons.flash.min.js"></script>
<script src="js/datatables/jszip.min.js"></script>
<script src="js/datatables/pdfmake.min.js"></script>
<script src="js/datatables/vfs_fonts.js"></script>
<script src="js/datatables/buttons.html5.min.js"></script>
<script src="js/datatables/buttons.print.min.js"></script>
