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


$modelId = filter_input(INPUT_GET, 'modelId', FILTER_VALIDATE_INT);


// get models
$pdo = new DataManagerPDO();
$table = "tbl_models";
$fields = ["id","st_name"];

$pdo->select($table, $fields);
if($modelId){
    $pdo->where('tbl_models.id','=',$modelId,'AND');
}
$pdo->where('i_is_active','=',1);

$models = $pdo->orderBy('id','desc')->fetch();

?>


<?php include_once 'includes/indexHead.php' ?>

<?php include_once 'includes/mainNavigation.php' ?>

<div class="modal fade" id="edit-model-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">ערוך דגם</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">
                        <input type="hidden" id="model-id" value="">
                        <label for="modelname-add">שם דגם</label>

                        <div class="form-model">
                            <input type="text"  id="modelname-edit" class="form-control" placeholder="שם דגם" value="">
                        </div>
                    </article>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-edit-model" class="btn btn-primary btn-lg btn-edit-model">שמור שינויים</button>
                    <div id="error-add-model" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="add-model-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title w-100 font-weight-bold">הוסף דגם</h4>
            </div>
            <div class="modal-body mx-3">

                <div class="card">
                    <article class="card-body">
                        <div class="form-model">
                            <input type="text"  id="modelname-add" class="form-control" placeholder="שם דגם" value="">
                        </div>
                    </article>
                </div>

            </div>
            <div class="modal-footer">
                <div class="col float-right">
                    <button id="btn-add-model" class="btn btn-primary btn-lg btn-add-model">הוסף דגם</button>
                    <div id="error-add-model" class="text-danger"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container">
    <section class="content-section">
        <div class="row justify-content-center">
            <div class="col-lg-12 ">

                <div class="row justify-content-right model-control">
                    <div class="col-lg-4">
                        <h1>דגמים</h1>
                    </div>
                    <div class="col-lg-8" style="text-align: left;">
                        <button id="add-model" type="button" class="btn btn-primary btn-lg btn-primary-page">הוסף דגם</button>
                    </div>
                </div>

                <table id="model-table" class="table" Style="width: 100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>שם דגם</th>
                        <th>פעולות</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($models) && $models) :
                        foreach ($models as $model) : ?>
                            <tr id="row-<?= $model['id'] ?>">
                                <td style="width:10%"><?= $model['id'] ?></td>
                                <td style="width:80%"><?= $model['st_name'] ?></td>
                                <td style="width:10%">
                                    <div class="dropdown">
                                        <i class="fa fa-caret-down" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                        <div class="dropdown-menu text-center" aria-labelledby="dropdownMenuButton">
                                            <a onclick="updatemodel(<?= $model['id'] ?>)" id="<?= $model['id'] ?>" class="dropdown-item">עדכון</a>
                                            <a onclick="deletemodel(<?= $model['id'] ?>)" id="<?= $model['id'] ?>"  class="dropdown-item">מחיקה</a>
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
<script src="js/models.js"></script>

