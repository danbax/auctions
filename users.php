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

$productsSum = 0;
$servicesSum = 0;

$booleanToText = array();
$booleanToText[0] = 'לא';
$booleanToText[1] = 'כן';


$userId = filter_input(INPUT_GET, 'userId', FILTER_VALIDATE_INT);


// get users
$pdo = new DataManagerPDO();
$table = "tbl_users";
$fields = ["id","st_username","st_email","st_phone","datetime_last_login","bool_is_admin","st_friendly_name"];

$pdo->select($table, $fields);
if($userId){
    $pdo->where('tbl_users.id','=',$userId);
}

$users = $pdo->orderBy('id','desc')->fetch();



?>


<?php include_once 'includes/indexHead.php' ?>
<?php include_once 'includes/mainNavigation.php' ?>
<?php
if (!$isAdmin) {
    header('Location: tenders.php');
}
?>

<!-- edit user -->
<div class="modal fade" id="edit-user-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">עריכת משתמש</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">
                        <input type="hidden" id="user-id" value="">
                        <label for="username-edit">שם משתמש</label>
                        <div class="form-group">
                            <input type="text"  id="username-edit" class="form-control" placeholder="אנא הכנס שם משתמש" value="">
                        </div>
                        <label for="friendly-name-edit">שם לתצוגה</label>
                        <div class="form-group">
                            <input type="text"  id="friendly-name-edit" class="form-control" placeholder="אנא הכנס שם לתצוגה" value="">
                        </div>
                        <div class="form-group">
                            <div class="form-group">
                                <label for="email-edit">כתובת דוא"ל</label>
                                <input type="email"  id="email-edit" class="form-control" placeholder='אנא הכנס כתובת דוא"ל' value="">
                            </div>

                            <div class="form-group">
                                <label for="nickname-edit">טלפון נייד</label>
                                <input type="text" name="phone" id="phone-edit" class="form-control" placeholder='אנא הכנס טלפון נייד' value="">
                            </div>

                            <div class="form-group">
                                <label for="is-admin-edit">האם Admin?</label>
                                <select id="is-admin-edit" class="form-control">
                                    <option value="-1" disabled>אנא בחר אפשרות</option>
                                    <option value="1" >כן</option>
                                    <option value="0" >לא</option>
                                </select>
                            </div>
                    </article>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-update-user" class="btn btn-primary btn-lg btn-update-user">שמור שינויים</button>
                    <div id="error-edit-user" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="add-user-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">הוסף שם משתמש</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">
                        <label for="username-add">שם משתמש</label>

                        <div class="form-group">
                            <input type="text"  id="username-add" class="form-control" placeholder="שם משתמש" value="">
                        </div>
                        <label for="friendly-name-add">שם לתצוגה</label>

                        <div class="form-group">
                            <input type="text"  id="friendly-name-add" class="form-control" placeholder="שם לתצוגה" value="">
                        </div>
                        <div class="form-group">

                            <div class="form-group">
                                <label for="email-add">כתובת דוא"ל</label>
                                <input  dir="ltr" type="email"  id="email-add" class="form-control" placeholder='כתובת דוא"ל' value="">
                            </div>

                            <div class="form-group">
                                <label for="phone-add">טלפון נייד</label>

                                <input type="text" name="phone" id="phone-add" class="form-control" placeholder='טלפון נייד' value="">
                            </div>
                            <div class="form-group">
                                <label for="pass-add">סיסמא</label>
                                <input type="password" id="pass-add" class="form-control" placeholder="סיסמה">
                            </div>

                            <div class="form-group">
                                <label for="is-admin-add">האם Admin?</label>
                                <select id="is-admin-add" class="form-control">
                                    <option value="-1" disabled>אנא בחר אפשרות</option>
                                    <option value="1" >כן</option>
                                    <option value="0" >לא</option>
                                </select>
                            </div>
                    </article>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-add-user" class="btn btn-primary btn-lg btn-add-user">הוסף משתמש</button>
                    <div id="error-add-user" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- change password modal -->
<div class="modal fade" id="change-password-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold" id="change-password-title">שנה סיסמא</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">
                        <input type="hidden" id="change-password-user-id" value="">
                        <input type="text"  id="new-password-add" class="form-control" placeholder="סיסמא חדשה" value="">

                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-change-password" class="btn btn-primary btn-lg ">שנה סיסמא</button>
                    <div id="error-change-password" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="container">
    <section class="content-section">
        <div class="row justify-content-center">
            <div class="col-lg-12 ">

                <div class="row justify-content-right user-control">
                    <div class="col-lg-4">
                        <h1>משתמשים</h1>
                    </div>
                    <div class="col-lg-8" style="text-align: left;">
                        <button  id="add-user" type="button" class="btn btn-primary btn-lg btn-primary-page">הוסף משתמש</button>
                    </div>
                </div>

                <table id="user-table" class="table display  no-wrap" >
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>שם משתמש</th>
                        <th>שם לתצוגה</th>
                        <th>דוא"ל</th>
                        <th>טלפון נייד</th>
                        <th>תאריך התחברות אחרון</th>
                        <th>האם Admin?</th>
                        <th>פעולות</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($users)) :
                        foreach ($users as $user) : ?>
                            <tr id="row-<?= $user['id'] ?>">
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['st_username'] ?></td>
                                <td><?= $user['st_friendly_name'] ?></td>
                                <td><?= $user['st_email'] ?></td>
                                <td><?= $user['st_phone'] ?></td>
                                <td><?php if( $user['datetime_last_login'] == "0000-00-00 00:00:00" || $user['datetime_last_login'] ==null) echo "טרם התחבר"; else echo $user['datetime_last_login'] ?></td>
                                <td><?= $booleanToText[$user['bool_is_admin']] ?></td>
                                <td>
                                    <div class="dropdown">
                                        <i class="fa fa-caret-down" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>

                                        <div class="dropdown-menu text-center" aria-labelledby="dropdownMenuButton"><a href="userOrders.php?userId=<?=$user['id']?>" class="dropdown-menu"><i class="fa fa-shopping-cart "></i>הזמנות </a>
                                            <a onclick="changePassword(<?= $user['id'] ?>)" id="<?= $user['id'] ?>"  class="dropdown-item">שנה סיסמא</a>
                                            <a onclick="updateUser(<?= $user['id'] ?>)" id="<?= $user['id'] ?>" class="dropdown-item">עדכון</a>
                                            <a onclick="deleteUser(<?= $user['id'] ?>)" id="<?= $user['id'] ?>"  class="dropdown-item">מחיקה</a>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <h3 class="text-danger"><?= $data->getError() ?></h3>
                    <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </section>

    <?php include_once 'includes/footer.php' ?>

</div>

<?php include_once 'includes/indexFooter.php' ?>
<script src="js/users.js"></script>
