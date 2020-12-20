<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';

if (!isset($_SESSION['user']['id'])) {
	header('Location: index.php');
	exit;
}

require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR .  '/Permission.php';

$pdo = new DataManagerPDO();


?>


<?php include_once 'includes/indexHead.php' ?>

<?php include_once 'includes/mainNavigation.php' ?>

<div class="container">
<section class="content-section">
    <div class="row justify-content-center">
        <div class="col-lg-12 ">

            <div class="row justify-content-right Element-control">
                <div class="col-lg-8">
                    <h2>לוג הודעות יוצאות</h2>
                    <hr>
                    <br><br>

                </div>
            </div>

            <table id="element-table" class="table display no-wrap" style="width:100%">
                <thead>
                    <tr>
                        <th>תאריך</th>
                        <th>סוג</th>
                        <th>כותרת</th>
                        <th>מקבל</th>
                        <th>מכרז</th>
                        <th>סטטוס</th>
                        <th>הודעה</th>
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
<?php
/* data table post array */
$dataTablePostArray = array();
if(isset($userId) && $userId!=0) {
    array_push($dataTablePostArray,"d.userId = $userId");
}

if(isset($orderId) && $orderId!=0) {
    array_push($dataTablePostArray,"d.orderId = $orderId");
}

if(isset($showErrors) && $showErrors) {
    array_push($dataTablePostArray,"d.showErrors = true");
}
?>
<script>
/*
 * datatable actions for logs.php page
 */
var elementTable;
$(document).ready(function() {
    function datatableInit(data){
     elementTable = $('#element-table').DataTable({
         dom: '<"pull-right"f>rtip',
        "order": [[ 0, "desc" ]],
        "pageLength": 100,
        "aaSorting": [],
        "lengthChange": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajaxLogSearch.php",
            "type": "POST",
            "data": function(d) {
                <?php echo implode(',', $dataTablePostArray); ?>
            }
        },
        "columnDefs": [
            { "targets": 0,"orderable":true },
            { "targets": 1 },
            { "targets": 2 },
            { "targets": 3 },
            { "targets": 4 },
            { "targets": 5 },
            { "targets": 6 }
        ],
        language: {
            search: "חיפוש",
            lengthMenu: "הצג _MENU_ רשומות",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין רשומות",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ רשומות",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
    });
    }
    
    datatableInit();
});
</script>
