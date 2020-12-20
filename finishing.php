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


$finishingId = filter_input(INPUT_GET, 'finishingId', FILTER_VALIDATE_INT);


// get finishings
$pdo = new DataManagerPDO();
$table = "tbl_finishing";
$fields = ["id","st_name","i_model_id"];

$pdo->select($table, $fields);
if($finishingId){
    $pdo->where('tbl_finishing.id','=',$finishingId,'AND');
}
$pdo->where('i_is_active','=',1);

$finishings = $pdo->orderBy('id','desc')->fetch();


?>


<?php include_once 'includes/indexHead.php' ?>

<?php include_once 'includes/mainNavigation.php' ?>

<div class="modal fade" id="edit-finishing-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">ערוך רמת גימור</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">
                        <input type="hidden" id="finishing-id" value="">


                        <label for="model-id-edit">דגם</label>

                        <div class="form-finishing">
                            <select id="model-id-edit" class="form-control">
                                <option value="0">-- בחר דגם --</option>
                                <?php
                                $modelNames = [];
                                $models = $pdo->select("tbl_models",["id","st_name"])->where("i_is_active","=",1)->fetch();
                                foreach($models as $model) :
                                    $modelNames[$model["id"]] = $model["st_name"];
                                ?>
                                <option value="<?=$model["id"]?>"><?=$model["st_name"]?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <br>

                        <label for="finishingname-add">שם רמת גימור</label>

                        <div class="form-finishing">
                            <input type="text"  id="finishingname-edit" class="form-control" placeholder="שם רמת גימור" value="">
                        </div>
                    </article>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-edit-finishing" class="btn btn-primary btn-lg btn-edit-finishing">שמור שינויים</button>
                    <div id="error-add-finishing" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="add-finishing-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">הוסף רמת גימור</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">

                        <div class="form-finishing">
                            <select id="model-id-add" class="form-control">
                                <option value="0">-- בחר דגם --</option>
                                <?php
                                $models = $pdo->select("tbl_models",["id","st_name"])->where("i_is_active","=",1)->fetch();
                                foreach($models as $model) :
                                    ?>
                                    <option value="<?=$model["id"]?>"><?=$model["st_name"]?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <br>

                        <div class="form-finishing">
                            <input type="text"  id="finishingname-add" class="form-control" placeholder="שם רמת גימור" value="">
                        </div>
                    </article>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-add-finishing" class="btn btn-primary btn-lg btn-add-finishing">הוסף רמת גימור</button>
                    <div id="error-add-finishing" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container">
    <section class="content-section">
        <div class="row justify-content-center">
            <div class="col-lg-12 ">

                <div class="row justify-content-right finishing-control">
                    <div class="col-lg-4">
                        <h1>רמות גימור</h1>
                    </div>
                    <div class="col-lg-8" style="text-align: left;">
                        <button id="add-finishing" type="button" class="btn btn-primary btn-lg btn-primary-page">הוסף רמת גימור</button>
                    </div>
                </div>

                <table id="finishing-table" class="table" Style="width: 100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>דגם</th>
                        <th>שם רמת גימור</th>
                        <th>פעולות</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($finishings) && $finishings) :
                        foreach ($finishings as $finishing) : ?>
                            <tr id="row-<?= $finishing['id'] ?>">
                                <td style="width:10%"><?= $finishing['id'] ?></td>
                                <td style="width:10%">
                                    <?php
                                    if(isset($modelNames[$finishing["i_model_id"]])){
                                        echo $modelNames[$finishing["i_model_id"]];
                                    }
                                    ?>
                                </td>
                                <td style="width:80%"><?= $finishing['st_name'] ?></td>
                                <td style="width:10%">
                                    <div class="dropdown">
                                        <i class="fa fa-caret-down" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                        <div class="dropdown-menu text-center" aria-labelledby="dropdownMenuButton">
                                            <a onclick="updatefinishing(<?= $finishing['id'] ?>)" id="<?= $finishing['id'] ?>" class="dropdown-item">עדכון</a>
                                            <a onclick="deletefinishing(<?= $finishing['id'] ?>)" id="<?= $finishing['id'] ?>"  class="dropdown-item">מחיקה</a>
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
<script src="js/finishing.js"></script>

