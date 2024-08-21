<?php
require_once("../pdoConnect.php");


// 採用GET取得查詢條件的變數
$searchName = isset($_GET["searchName"]) ? $_GET["searchName"] : '';
$searchPromotionType = isset($_GET["searchPromotionType"]) ? $_GET["searchPromotionType"] : '';
$searchStartTime = isset($_GET["searchStartTime"]) ? $_GET["searchStartTime"] : '';
$searchEndTime = isset($_GET["searchEndTime"]) ? $_GET["searchEndTime"] : '';

// 給予查詢條件的陣列與參數 陣列
$conditions = [];
$params = [];

// 檢查每項條件的值與對應的SQL並加到condition內
if (isset($searchName) && $searchName !== "") {
    $conditions[] = "d.Name LIKE :searchName";
    $params[':searchName'] = "%" . $searchName . "%";
} else {
}

if (isset($searchPromotionType)  && $searchPromotionType !== "") {
    $conditions[] = "d.PromotionType = :searchPromotionType";
    $params[':searchPromotionType'] = $searchPromotionType;
}

if ((isset($searchStartTime) && $searchStartTime !== "") && (isset($searchEndTime) && $searchEndTime !== "")) {
    $conditions[] = "(d.StartTime <= :searchStartTime AND d.EndTime >= :searchEndTime)";
    $params[':searchStartTime'] = $searchStartTime;
    $params[':searchEndTime'] = $searchEndTime;
}

$sqlAll = "SELECT 
d.ID, 
d.Name, 
d.StartTime, 
d.EndTime, 
d.Value,
d.ConditionMinValue,
d.PromotionType,
sc1.Description AS PromotionCondition,
sc2.Description AS CalculateType,
sc3.Description AS MemberLevel, 
sc4.Description AS PromotionType 
FROM Discount d 
JOIN SystemCode sc1 ON d.PromotionCondition = sc1.Value AND sc1.Type='PromotionCondition'
JOIN SystemCode sc2 ON d.CalculateType = sc2.Value AND sc2.Type='CalculateType'
JOIN SystemCode sc3 ON d.MemberLevel = sc3.Value AND sc3.Type='MemberLevel'
JOIN SystemCode sc4 ON d.PromotionType = sc4.Value AND sc4.Type='PromotionType'";

// 如果condition非空，則在sql最後加上所有的條件
if (!empty($conditions)) {
    $sqlAll .= " WHERE " . implode(" AND ", $conditions);
}

// echo $sqlAll;
// print_r($params);

$stmt = $dbHost->prepare($sqlAll);


try {
    $stmt->execute($params);
    $discountAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $discountAllcount = $stmt->rowCount();
} catch (PDOException $e) {
    echo "預處理陳述式執行失敗！ <br/>";
    echo "Error: " . $e->getMessage() . "<br/>";
    $db_host = NULL;
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>促銷管理</title>

    <?php include("../headlink.php") ?>
</head>

<body>
    <?php include("../modals.php") ?>


    <script src="../assets/static/js/initTheme.js"></script>
    <div id="app">
        <?php include("../sidebar.php") ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>
            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>促銷管理</h3>
                            <p class="text-subtitle text-muted"></p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html"><i
                                                class="fa-solid fa-house"></i></a></li>
                                    <li class="breadcrumb-item active" aria-current="page">促銷管理</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="card">
                        <div class="card-body">
                            <form action="/G5midTerm/Promotion/DiscountList.php" method="GET">
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 col-12">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">促銷名稱</span>
                                            <input type="text" class="form-control" placeholder="" aria-label="Username" aria-describedby="basic-addon1" name="searchName"
                                                value="<?= $searchName ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">促銷時間</span>
                                            </div>
                                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" placeholder="Select date.." readonly="readonly" name="searchStartTime" value="<?= $searchStartTime ?>">
                                            <input type="text" class="form-control mb-3 flatpickr-no-config flatpickr-input" placeholder="Select date.." readonly="readonly" name="searchEndTime" value="<?= $searchEndTime ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-4 col-12">
                                        <div class="input-group mb-3">
                                            <label class="input-group-text" for="inputGroupSelect01">促銷方式</label>
                                            <select class="form-select" id="inputGroupSelect01" name="searchPromotionType">
                                                <option value="" <?= ($searchPromotionType == "") ? 'selected' : '' ?>></option>
                                                <option value="1" <?= ($searchPromotionType == 1) ? 'selected' : '' ?>>自動套用</option>
                                                <option value="2" <?= ($searchPromotionType == 2) ? 'selected' : '' ?>>優惠券</option>
                                            </select>
                                        </div>
                                    </div>



                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary me-1 mb-1"><i class="fa-solid fa-magnifying-glass"></i></button>
                                        <a class="btn btn-light-secondary me-1 mb-1" href="DiscountList.php" id="resetBtn"><i class="fa-solid fa-delete-left"></i></a>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="dataTable-top">
                                <label>每頁</label>
                                <div class="dataTable-dropdown">
                                    <select class="dataTable-selector form-select" id="itemsPerPage" onchange="changeItemsPerPage()">
                                        <option value="5" <?= ($per_page == 5) ? 'selected' : '' ?>>5</option>
                                        <option value="10" <?= ($per_page == 10) ? 'selected' : '' ?>>10</option>
                                        <option value="15" <?= ($per_page == 15) ? 'selected' : '' ?>>15</option>
                                        <option value="20" <?= ($per_page == 20) ? 'selected' : '' ?>>20</option>
                                        <option value="25" <?= ($per_page == 25) ? 'selected' : '' ?>>25</option>
                                    </select><label>筆</label>
                                </div>
                                <div class="col-auto">
                                    <a class="btn btn-primary me-1 mb-1" href="DiscountCreate.php"><i class="fa-solid fa-circle-plus"></i></a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr class="text-nowrap">
                                                <th>ID</th>
                                                <th>促銷名稱</th>
                                                <th>促銷時間</th>
                                                <th>滿足條件</th>
                                                <th>折扣數</th>
                                                <th>會員等級</th>
                                                <th>促銷方式</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($discountAll as $discount): ?>
                                                <tr>
                                                    <td><?= $discount["ID"] ?></td>
                                                    <td><?= $discount["Name"] ?></td>
                                                    <td><?= $discount["StartTime"] ?> ~<br> <?= $discount["EndTime"] ?></td>
                                                    <td><?php echo $discount["PromotionCondition"];
                                                        if ($discount["ConditionMinValue"] != 0) {
                                                            echo number_format($discount["ConditionMinValue"]);
                                                        } ?></td>
                                                    <td><?php echo number_format($discount["Value"]) . $discount["CalculateType"] ?></td>
                                                    <td><?= $discount["MemberLevel"] ?></td>
                                                    <td><?= $discount["PromotionType"] ?></td>
                                                    <td>
                                                        <a class="btn btn-primary" href="DiscountEdit.php?id=<?= $discount["ID"] ?>"> <i class="fa-solid fa-pen-to-square"></i></a>
                                                    </td>
                                                    <td>
                                                        <a href="#" class="btn btn-primary delete-btn"
                                                            data-id="<?= $discount["ID"] ?>"
                                                            data-name="<?= $discount["Name"] ?>"
                                                            data-starttime="<?= $discount["StartTime"] ?>"
                                                            data-endtime="<?= $discount["EndTime"] ?>"><i class="fa-solid fa-trash-can"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <ul class="pagination pagination-primary justify-content-center mt-3">
                                    <li class="page-item"><a class="page-link" href="#">
                                            <span aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                                        </a></li>
                                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item active"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item"><a class="page-link" href="#">
                                            <span aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                                        </a></li>
                                </ul>
                            </div>
                        </div>

                    </div>
            </div>
            </section>
        </div>
        <?php include("../footer.php") ?>
    </div>
    </div>
    <?php include("../js.php") ?>

    <script>
        // 點擊刪除按鈕後，將資料傳至modal
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmDeleteButton = document.getElementById('confirmDelete');

            let currentDeleteId = null;

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    currentDeleteId = this.getAttribute('data-id');

                    // 輸入內容
                    document.getElementById('modalDiscountID').textContent = this.getAttribute('data-id');
                    document.getElementById('modalDiscountName').textContent = this.getAttribute('data-name');
                    // document.getElementById('modalDiscountTime').textContent = `${this.getAttribute('data-starttime')} ~ ${this.getAttribute('data-endtime')}`;
                    document.getElementById('modalDiscountStartTime').textContent = this.getAttribute('data-starttime');
                    document.getElementById('modalDiscountEndTime').textContent = this.getAttribute('data-endtime');


                    deleteModal.show();
                });
            });

            confirmDeleteButton.addEventListener('click', function() {
                if (currentDeleteId) {
                    window.location.href = `doDeleteDiscount.php?id=${currentDeleteId}`;
                }
            });
        });
    </script>
</body>

</html>