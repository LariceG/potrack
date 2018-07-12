 <div style="width:60%; margin-left:auto ; margin-right:auto ">
   <img src="http://1wayit.com/dibcase_app/api/assets/logo.png">
   <?php
      // echo "<pre>";
      // print_r($task);
      // print_r($events);
      // print_r($query);
      // echo "</pre>";
    ?>
   <?php  if(!empty($task)) { ?>
   <h3>Task Reminders</h3>
   <ul class="content-bx-panel" style="padding: 0px; margin-top: 0; width:100%; ">
     <?php foreach($task as $rm) { ?>
     <li style="background: #fff none repeat scroll 0 0;
      border-radius: 5px;
      box-shadow: 0 0 2px #bfbcbc;
      display: block;
      margin-bottom: 14px;
      padding: 15px; font-family: 'Roboto', sans-serif; font-size:14px">
       <?php
        $a = getRow('dibcase_tasks', array('TSK_ID' => $rm->REF_ID ));
        ?>
       <h3 style="margin-top: 10px;
      margin-bottom: 10px;"><?php echo $a->TSK_TITLE ?></h3>
       <p style = "margin: 0 0 10px;"><?php echo $a->TSK_NOTE ?></p>
       <p style = "margin: 0 0 10px;">Due date - <?php echo $a->TSK_DUE_DATE; ?></p>
     </li>
     <?php } ?>
   </ul>
   <?php } ?>

   <?php  if(!empty($events)) { ?>
   <h3>Event Reminders</h3>
   <ul class="content-bx-panel" style="padding: 0px; margin-top: 0; width:100%; ">
     <?php foreach($events as $rm) { ?>
     <li style="background: #fff none repeat scroll 0 0;
      border-radius: 5px;
      box-shadow: 0 0 2px #bfbcbc;
      display: block;
      margin-bottom: 14px;
      padding: 15px; font-family: 'Roboto', sans-serif; font-size:14px">
       <?php
        $a = getRow('dibcase_events', array('idd' => $rm->REF_ID ));
        ?>
       <h3 style="margin-top: 10px;
      margin-bottom: 10px;"><?php echo $a->EVENT_TITLE ?></h3>
       <p style = "margin: 0 0 10px;"><?php echo $a->EVENT_NOTES ?></p>
       <p style = "margin: 0 0 10px;">Start date - <?php echo $a->EVENT_START_DATE; ?></p>
       <p style = "margin: 0 0 10px;">End date - <?php echo $a->EVENT_END_DATE; ?></p>
     </li>
     <?php } ?>
   </ul>
   <?php } ?>
 </div>
