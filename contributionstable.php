<?php

require_once 'contributionstable.civix.php';
/**
 * Implementation of hook_civicrm_tokens
 */
function contributionstable_civicrm_tokens(&$tokens) {
  $tokens['contributions'] = array('contributions.itemized' => 'Contributions Itemized', 'contributions.total' => 'Contributions Total');
}
/**
 * Implementation of hook_civicrm_tokensValues
 */
function contributionstable_civicrm_tokenValues( &$values, $cids, $job = null, $tokens = array(), $context = null ) {
  if (!empty($tokens['contributions'])){
    $contributions = array('contributions.itemized' => '', 'contributions.total' => '');
    $rows = array();
    $header = "
        <table style='text-align:center'>
          <thead>
            <tr>
              <th width='175px'>".ts("Date Received")."</th>
              <th width='175px'>".ts("Tax Deductible Amount")."</th>
            </tr>
          </thead>
    ";
    $year = date('Y');
    $last_year = $year -1;
    foreach ($cids as $cid) {
      $dao = &CRM_Core_DAO::executeQuery("
      SELECT con.total_amount, (con.total_amount - con.non_deductible_amount) as deductible_amount, con.total_amount, con.receive_date, cc.display_name
      FROM civicrm_contribution con
      LEFT JOIN civicrm_contact cc on con.contact_id=cc.id
      LEFT JOIN civicrm_financial_type ft on con.financial_type_id=ft.id
      WHERE contact_id = ".$cid
      .
     " AND con.receive_date BETWEEN '".$last_year."-01-01' AND '".$year."-01-01'
       AND ft.is_deductible=1
;"
      );
      $contributions_total = 0;
      while ($dao->fetch()) {
        if ($dao->deductible_amount){
          $rows[] = '
            <tr>
              <td>' . date('m/d/Y', strtotime($dao->receive_date)) . '</td>
              <td>$' .($dao->deductible_amount). '</td>
            </tr>
            ';
            $contributions_total += $dao->deductible_amount;
         }
         else{
          $rows[] = '
            <tr>
              <td>' . date('m/d/Y', strtotime($dao->receive_date)) . '</td>
              <td>$' .($dao->total_amount). '</td>
            </tr>
            ';
            $contributions_total += $dao->total_amount;

        }
      }
      $contributions_total = "$".$contributions_total;
      $table = $header;
      if (!empty($rows)){
        foreach ($rows as $row){
          $table .= $row;
        }
      }
      $table .= "
        </table>";

      $contributions = array('contributions.itemized' => $table, 'contributions.total' => $contributions_total);
      $values[$cid] = empty($values[$cid]) ? $contributions : $values[$cid] + $contributions;
    }
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function contributionstable_civicrm_config(&$config) {
  _contributionstable_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 */
function contributionstable_civicrm_install() {
  return _contributionstable_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_enable
 */
function contributionstable_civicrm_enable() {
  return _contributionstable_civix_civicrm_enable();
}

// /**
//  * Implements hook_civicrm_postInstall().
//  *
//  * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
//  */
// function contributionstable_civicrm_postInstall() {
//   _contributionstable_civix_civicrm_postInstall();
// }

// /**
//  * Implements hook_civicrm_entityTypes().
//  *
//  * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
//  */
// function contributionstable_civicrm_entityTypes(&$entityTypes) {
//   _contributionstable_civix_civicrm_entityTypes($entityTypes);
// }
