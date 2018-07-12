
<?php

  $a = array();
  foreach ($ClientListCustom[0] as $key => $value)
  {
    if($ClientListCustom[0][$key] == 0 && $key != 'filter_column')
    {
      $a[] = $key;
    }
  }

  $sortField = array(
    'status_column'=> 'Client Status',
    'owner_column'=> 'Client owner',
    'datecreated_column' => 'Creation Timestamp',
    'lastmodified_column' => 'Last Modified',
    'closeddate_column' => 'Closed Date',
    'age_column' => 'Age',
    'email_column' => 'Email',
    'tags_column' => 'Tags',
    'notes_column' => 'Notes',
    'dob_column' => 'DOB',
    'spousename_column' => 'Spouse Name',
    'spouseIncome_column' => 'Spouse Income',
    'spouseIncomeSrc_column' => 'Spouse Income Source',
    'birthPlace_column' => 'Birth Place',
    'phn_column' => 'Phone',
    'city_column' => 'City',
    'country_column' => 'Country',
    'address_column' => 'Address',
    'edu_column' => 'Education',
    'filter_column' => 'Filters',
    'custmname_column' => 'Client Name'
  );

  $db = array(
      'status_column'=> 'CL_STATUS',
      'owner_column'=> 'CL_OWNER',
      'datecreated_column' => 'CL_DATECREATED',
      'lastmodified_column' => 'lastModified',
      'closeddate_column' => 'CL_CLOSED_DATE',
      'age_column' => 'CL_AGE',
      'email_column' => 'EMAIL',
      'tags_column' => 'CL_REFERER',
      'notes_column' => 'CL_SOC_SEC',
      'dob_column' => 'CL_DOB',
      'spousename_column' => 'CL_SPOUSE_NAME',
      'spouseIncome_column' => 'CL_SPOUSE_NAME_INCOME',
      'spouseIncomeSrc_column' => 'CL_SPOUSE_NAME_INCOME_SOURCE',
      'birthPlace_column' => 'CL_PLACE_OF_BIRTH',
      'phn_column' => 'PHN_NUMBER',
      'city_column' => 'CL_CITY',
      'country_column' => 'CL_COUNTY',
      'address_column' => 'CL_ADDRESS',
      'edu_column' => 'CL_EDU',
      'custmname_column' => 'CL_FIRST_NAME'
    );

    //$db = array_flip($db);

 ?>
 <h2 align="center">Client List</h2>
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
  for( $i = 0 ; $i<count($result['result']); $i++)
  {
    echo "<tr>";
    foreach ($a as $key => $value)
    {
      $stName = array('All','active','inactive','defered','closed');
      echo "<td>";

      if( $db[$value] == 'CL_FIRST_NAME' || $db[$value] == 'CL_LAST_NAME' || $db[$value] == 'CL_MIDDLE_NAME')
      echo $result['result'][$i]->CL_LASTNAME.','.$result['result'][$i]->CL_FIRST_NAME.' '. substr( $result['result'][$i]->CL_MIDDLE_NAME ,0,1).'. #'.substr( $result['result'][$i]->CL_SSN,-4);

      elseif(  $db[$value] == 'CL_STATUS')
      echo ucfirst($stName[$result['result'][$i]->$db[$value]]);

      elseif(  $db[$value] == 'CL_DOB' || $db[$value] == 'CL_CLOSED_DATE' )
      echo goodDateMDY($result['result'][$i]->$db[$value]);

      elseif(  $db[$value] == 'CL_DATECREATED' )
      echo goodDateTimeMDY($result['result'][$i]->$db[$value]);

      elseif( $db[$value] == 'lastModified' )
      {
        if(goodDateTimeMDY($result['result'][$i]->$db[$value]) != '')
        echo goodDateTimeMDY($result['result'][$i]->$db[$value]);
        else
        echo goodDateTimeMDY($result['result'][$i]->CL_DATECREATED);
      }

      else
      echo $result['result'][$i]->$db[$value];

      echo "</td>";
    }
    echo "</tr>";
  }
  ?>

</table>
