/*
 * datatable actions for companies.php page
 */
var elementTable;
$(document).ready(function() {
     elementTable = $('#element-table').DataTable({
        "pageLength": 100,
        "aaSorting": [],
//        "lengthChange": true,
//        "processing": true,
//        "serverSide": true,
//        "ajax": {
//            "url": "ajaxLogSearch.php",
//            "type": "POST",
//            "data": function(d) {
//                d.startdate = 2;
//                d.enddate =  2;
//            }
//        },
        "columnDefs": [
            { "targets": 0 },
            { "targets": 1 }
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
});
