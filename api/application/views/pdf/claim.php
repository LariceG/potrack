
<?php

  $a = array();
  foreach ($ClaimListCustom[0] as $key => $value)
  {
    if($ClaimListCustom[0][$key] == 0 && $key != 'filter_column')
    {
      $a[] = $key;
    }
  }

  $sortField = array(
    'status_column'=> 'Claim Status',
    'clmno_column'=> 'Claim No',
    'clmlvl_column' => 'Claim Level',
    'hrgst_column' => 'Hearing Status',
    'hrgdate_column' => 'Hearing Date',
    'hrgtym_column' => 'Hearing Time',
    'filter_column' => 'Actions',
    'custmname_column' => 'Name/SSN No',
    'alj_column' => 'ALJ Name',
  );

  $db = array(
    'status_column'=> 'CLM_STATUS',
    'clmno_column'=> 'CLM_ID',
    'clmlvl_column' => 'CLM_SSA_CASE_LEVEL',
    'hrgst_column' => 'CLM_STATUS',
    'hrgdate_column' => 'CLM_SSA_HEARING_FILE_DATE',
    'hrgtym_column' => 'CLM_HEARING_TIME',
    'custmname_column' => 'CLM_SSN',
    'alj_column' =>  'CLM_ALJ_NAME'
    );

    //$db = array_flip($db);

 ?>
 <h2 align="center">Claims List</h2>
 <table border="1" cellpadding="2">
 <tr>
   <?php
   foreach ($a as $key => $value)
   {
     echo "<th>";
     echo $sortField[$value];
     echo "</th>";
   }
   ?>
 </tr>

 <?php
 // print_r($result['result']);
 
  for( $i = 0 ; $i<count($result['result']); $i++)
  {
    echo "<tr>";
    foreach ($a as $key => $value)
    {
      $stName = array('all','prospect','lead','active','inactive','defered','closed');
      echo "<td>";

      if( $db[$value] == 'CLM_SSN')
      echo $result['result'][$i]->CL_LASTNAME.','.$result['result'][$i]->CL_FIRST_NAME.' '. substr( $result['result'][$i]->CL_MIDDLE_NAME ,0,1).'. #'.substr( $result['result'][$i]->CL_SSN,-4);

      elseif(  $db[$value] == 'CLM_STATUS')
      echo ucfirst($stName[$result['result'][$i]->$db[$value]]);

      elseif(  $db[$value] == 'CLM_SSA_HEARING_FILE_DATE' || $db[$value] == 'CLM_SSA_ERE_DATE' || $db[$value]== 'CLM_HEARING_SCHEDULED' || $db[$value] == 'CLM_SSA_ONSET_DATE' || $db[$value] == 'CLM_APPEAL_DEADLINE'  || $db[$value] == 'CLM_CLOSE_DATE'  || $db[$value] == 'CLM_FED_FILE_DATE' || $db[$value] == 'CLM_LAST_CONTACT_DATE' || $db[$value] == 'CLM_DATE_RETAINED' || $db[$value] == 'CLM_SSA_PFL' || $db[$value] == 'CLM_SSA_AOD'  || $db[$value] == 'CLM_SSA_INITIAL_FILE_DATE'  || $db[$value] == 'CLM_SSA_INITIAL_DENIAL_DATE'  || $db[$value] == 'CLM_RECON_FILE_DATE'  || $db[$value] == 'CLM_RECON_DENIAL_DATE'  || $db[$value] == 'CLM_VA_DATE_LAST_WORKED'  || $db[$value] == 'CLM_VA_DATE_LAST_CLM'   || $db[$value] == 'CLM_VA_DATE_LAST_RATING'   || $db[$value] == 'CLM_VA_NOD_FILE_DATE'   || $db[$value] == 'CLM_WC_1ST_ACC_DATE'   || $db[$value] == 'CLM_WC_2ND_ACC_DATE'   || $db[$value] == 'CLM_WC_3RD_ACC_DATE'   || $db[$value] == 'CLM_WC_4TH_ACC_DATE'   || $db[$value] == 'CLM_SSA_DLI'   || $db[$value] == 'CLM_SSA_DATE_LAST_WORKED'   || $db[$value] == 'CLM_STATUS_DATE'   || $db[$value] == 'AC_REQ_DATE'   || $db[$value] == 'AC_STATUS_DATE'     || $db[$value] == 'CLM_FED_TRANSCRIPT_DATE_DATE'     || $db[$value] == 'CLM_FED_APPEAL_DATE'     || $db[$value] == 'CLM_FED_DECISION_DATE'     || $db[$value] == 'CLM_FED_OBJ_DECISION_DATE'     || $db[$value] == 'CLM_CIRC_APPEAL_DATE'     || $db[$value] == 'CLM_CIRC_COURT_DATE'     || $db[$value] == 'CLM_CIR_COURT_DEC_DATE'     || $db[$value] == 'SOCIAL_SEC_DENI_DATE'     || $db[$value] == 'INJ_DATE'     || $db[$value] == 'ACC_DATE'     || $db[$value] == 'ACC_RPT_DATE'    )
      echo goodDateMDY($result['result'][$i]->$db[$value]);

      elseif(  $db[$value] == 'CLM_HEARING_TIME' )
      echo goodDateTimeMDY($result['result'][$i]->$db[$value]);

      // elseif( $db[$value] == 'lastModified' )
      // {
      //   if(goodDateTimeMDY($result['result'][$i]->$db[$value]) != '')
      //   echo goodDateTimeMDY($result['result'][$i]->$db[$value]);
      //   else
      //   echo goodDateTimeMDY($result['result'][$i]->CL_DATECREATED);
      // }
      //
      else
      echo $result['result'][$i]->$db[$value];

      echo "</td>";
    }
    echo "</tr>";
  }
  ?>


</table>
