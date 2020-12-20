<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'classes/DataManagerPDO.php';
 
// init response json class
$response = new stdClass();

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

$userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
$orderId = filter_input(INPUT_POST, 'orderId', FILTER_VALIDATE_INT);

$where = "";

// DB table to use
$table = 'tbl_log';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array(
        'db'        => 'date_created',
        'dt'        => 0,
        'formatter' => function( $d, $row ) {
            $t = strtotime($d);
            return date('d/m/Y H:i:s',$t);
        }
    ),
    array(
        'db' => 'st_subject',
        'dt' => 2,
        'formatter' => function( $d, $row ) {
            $data = jeepEmojiDecode($row['st_subject']);
            return $data;
        }
    ),
    array(
        'db' => 'i_type',
        'dt' => 1,
        'formatter' => function( $d, $row ) {
            if($row['i_type'] == 2){
                return "SMS";
            }
            if($row['i_type'] == 1){
                return "אימייל";
            }

            return "אחר";
        }
    ),

    array(
        'db' => 'i_type',
        'dt' => 1,
        'formatter' => function( $d, $row ) {
            if($row['i_type'] == 2){
                return "SMS";
            }
            if($row['i_type'] == 1){
                return "אימייל";
            }

            return "אחר";
        }
    ),
    array(
        'db' => 'st_message',
        'dt' => 6,
        'formatter' => function( $d, $row ) {
            $data = jeepEmojiDecode($row['st_message']);
            return $data;
        }
    ),
    array(
        'db' => 'i_tender_id',
        'dt' => 4,
        'formatter' => function( $d, $row ) {
            if($row['i_tender_id'] == 0){
                return 'לא מקושר למכרז';
            }
            return "<a href='tender.php?tenderId=".$row['i_tender_id']."'>".$row['i_tender_id']."</a>";
        }
    ),
    array(
        'db' => 'i_status',
        'dt' => 5,
        'formatter' => function( $d, $row ) {
            if($row['i_status'] == 1){
                return "תקין";
            }

            return "שגיאה";
        }
    ),
    array( 'db' => 'st_receiver','dt' => 3)
);
 
// SQL server connection information
$sql_details = array(
    'user' => DBUSER,
    'pass' => DBPASS,
    'db'   => DBNAME,
    'host' => DBHOST
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'assets/datatables/ssp.class.php' );
 
echo json_encode(
    SSP::complex( $_POST, $sql_details, $table, $primaryKey, $columns, $where )
);

