<?php
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $stampIds = $_POST['sids'];
    $stampIdsArray = explode(',', $stampIds);
    $mod = $_POST['modifier'];
    $originalModifer = $_POST['dmod'];
    $where = 'WHERE stamp_id = ' . intval($stampIdsArray[0]);
    for ($i = 1; $i < count($stampIdsArray); ++$i) {
        $where .= ' OR stamp_id = ' . intval($stampIdsArray[$i]);
    }
    mysqli_query($sqlConnection, "UPDATE timestamp_list SET stamp_special = '$mod' $where");
    // todo: get old modifier, if any
    foreach ($stampIdsArray as $stamp) {
        logTransaction($sqlConnection, $stamp, "MODIFIER", 0, $mod);
    }
    mysqli_close($sqlConnection);
}