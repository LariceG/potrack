 <h2 align="center">Task List</h2>
 <table border="1" cellpadding="2" style=" width:100%; font-size:14px; font-family:arial">
   <thead>
     <tr style="background-color:#e8ecec">
       <th>Task Name</th>
       <th>Tags</th>
       <th>Status</th>
       <th>Due Date</th>
       <th>Case Link</th>
       <th>Date Complete</th>
     </tr>
  </thead>
  <tbody>
   <?php

  function tags($tags)
  {
    $at = '';
    foreach ($tags as $key => $value)
    {
        $at  .= $value->name.';';
    }
    return $at;
  }

  function acts($acts)
  {
    $st = '<table><tr>';
    foreach ($acts as $key => $value)
    {
      if($value->ACT_STATUS == 1)
        $st  .= '<td style="width:15px"><img  src="'.base_url().'assets/checklist_active.png"> </td>';
      else if($value->ACT_STATUS == 0)
        $st  .= '<td style="width:15px"><img src="'.base_url().'assets/checklist_inactive.png"> </td>';
    }
        $st   .= '</tr></table>';
    return $st;
  }


   foreach ($result as $key => $value)
   {
       echo '<tr style="background-color:#f2f2f2">';
        echo '<td colspan="6">'.$value->EMP_NAME.'</td>';
       echo "</tr>";

     foreach ($value->tasks as $k => $v)
     {
       echo "<tr>";
         echo "<td>".$v->TSK_TITLE."</td>";
         echo "<td>".tags($v->tags)."</td>";
         echo "<td>".acts($v->acts)."</td>";
         echo "<td>".$v->TSK_DUE_DATE."</td>";
         echo "<td></td>";
         echo "<td>".$v->TSK_COMPLETE_DATE."</td>";
       echo "</tr>";
     }
   }

  ?>
</tbody>
</table>
