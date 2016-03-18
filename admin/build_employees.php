<?php
include 'admin_functions.php';
if (sessionCheck()) {
    $json = $_SESSION['json'];
    $table = '<table id="employees-all" class="bordered centered responsive-table highlight">
                  <thead>
                      <tr>
                          <th data-field="checkbox"><p>
                           <input type="checkbox" id="checkall"/><label for="checkall"/>
                          </p></th>
                          <th data-field="id">ID</th>
                          <th data-field="adpid">ADP ID</th>
                          <th data-field="name">Name</th>
                          <th data-field="companycode">Company Code</th>
                          <th data-field="department">Deparment Code</th>
                      </tr>
                  </thead>
                  <tbody>';
    $employees = json_decode($json, true);
    foreach ($employees as $person) {
        $t_id = $person['id'];
        $t_adp = $person['adpid'];
        $t_name = $person['name'];
        $t_company = $person['companycode'];
        $t_department = $person['departmentcode'];
        $_checkbox = "<p><input type=\"checkbox\" id=\"id$t_id\"/><label for=\"id$t_id\"></label></p>";
        $table .= "<tr user-id=\"$t_id\">
                    <td>$_checkbox</td>
                    <td>$t_id</td>
                    <td>$t_adp</td>
                    <td>$t_name</td>
                    <td>$t_company</td>
                    <td>$t_department</td>
                   </tr>";
    }
    echo $table.'</tbody></table>';
}
