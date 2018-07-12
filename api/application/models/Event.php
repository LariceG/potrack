<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

    class Event extends CI_Model {
    public function __construct()
	  {
        parent::__construct();
    }

    public function goodDateTime($date)
    {
      $a = date_format(date_create($date),"Y/m/d H:i:s");
      return $a;
    }

    public function goodDate($date)
    {
      $date = str_replace('/', '-', $date);
      $newDate = date("Y/m/d", strtotime($date));
      return $newDate;
    }

    public function goodDateCondition($date)
    {
      $new = ($date != '' ? $this->common->goodDate($date) : '');
      return $new;
    }

    public function goodDateTimeCondition($date)
    {
      $new = ($date != '' ? $this->common->goodDateTime($date) : '');
      return $new;
    }

    public function listEvent($post)
    {
      $tags = array();
      foreach ($post['tags'] as $key => $value)
      {
        $tags[] = $value['id'];
      }

      $cases = array();
      foreach ($post['clients'] as $k => $v)
      {
        $clientCases = clientCases($v->CLIENT_REF);
        foreach ( $clientCases as $kk => $vv )
        {
          // print_r($vv);
          if(!isset($cases[$vv->CLM_REF]))
          $cases[] = $vv->CLM_REF;
        }
      }



      $query  = ' select atndy.ATNDY_REF , ev.idd , ev.EVENT_REF , ev.EVENT_TITLE as title , ev.EVENT_START_DATE as start , ev.EVENT_START_TIME as startTime , ev.EVENT_END_DATE as end , ev.EVENT_END_TIME as endTime , ev.CLR as color , ev.addedBy';
      $query .= ' from dibcase_events as ev';
      if(!empty($post['tags']))
      $query .= ' left join dibcase_tags as tags on ev.EVENT_REF = tags.REF_ID ';
      $query .= ' left join dibcase_event_attendee as atndy on ev.EVENT_REF = atndy.EVENT_REF ';
      $query .= ' where ev.COM_REF = '.$post['COM_REF'];

      if($post['get'] != 'all' && !empty($post['get']))
      {
        $imp = "'" . implode( "','", $post['get'] ) . "'";
        $query .= ' and ( ev.addedBy in ('.$imp.')';
        $query .= ' or  atndy.ATNDY_REF in ('.$imp.') )';
      }

      if($post['eventType'] != 'all' && ( count($post['eventType']) >= 1  && !in_array('other',$post['eventType']) ) && !empty($post['eventType']))
      {
        $imp = "'" . implode( "','", $post['eventType'] ) . "'";
        $query .= ' and ev.EVENT_TYPE in ('.$imp.')';
      }

      if( count($post['eventType']) == 1 &&  $post['eventType'][0] == 'other')
      $query .= " and ev.EVENT_TYPE not in ('new_client','in_office','hearing')";


      if( count($post['eventType']) > 1 &&  in_array('other',$post['eventType']) )
      {
        $result = array_diff(array('new_client','in_office','hearing'),$post['eventType']);
        $imp = "'" . implode( "','", $result ) . "'";
        $query .= " and ev.EVENT_TYPE not in (".$imp.")";
      }

      if(!empty($post['tags']))
      $query .= ' and tags.TAG_ID in ('.implode(',',$tags).') )';

      if(!empty($cases))
      $query .= ' and ev.EVENT_CASE_REF in ('.implode(',',$cases).') )';

      $query .= ' order by ev.EVENT_START_DATE ASC';
      $result = $this->db->query($query);
      //  echo $this->db->last_query(); die;
      $result = $result->result();
      return $result;
    }

    public function todayEvents($post)
    {
      $startObj = new DateTime();
      $today = $startObj->format('Y-m-d');

      $query  = ' select atndy.ATNDY_REF , ev.idd , ev.EVENT_REF , ev.EVENT_TITLE as title , ev.EVENT_START_DATE as start , ev.EVENT_START_TIME as startTime , ev.EVENT_END_DATE as end , ev.EVENT_END_TIME as endTime , ev.CLR as color , ev.addedBy , ev.EVENT_ALL_DAY , ev.EVENT_NOTES , ev.EVENT_TYPE , ev.EVENT_CREATE , cl.CL_FIRST_NAME , cl.CL_MIDDLE_NAME , cl.CL_LASTNAME , cl.CL_SSN , emp.EMP_NAME ';
      $query .= ' from dibcase_events as ev';
      $query .= ' left join dibcase_event_attendee as atndy on ev.EVENT_REF = atndy.EVENT_REF ';
      $query .= ' left join dibcase_client as cl on ev.EVENT_CL_REF = cl.CL_REF ';
      $query .= ' left join dibcase_users as usr on usr.USER_REF = ev.addedBy ';
      $query .= ' left join dibcase_employees as emp on emp.EMP_REF = usr.EMP_REF ';
      //   $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
      $query .= ' where ev.COM_REF = '.$post['COM_REF'];
      $query .= " and DATE(ev.EVENT_START_DATE) = '$today' ";
      $query .= ' order by ev.EVENT_START_DATE DESC , ev.EVENT_START_TIME ASC';
      $result = $this->db->query($query);
      $result = $result->result();
      return $result;
    }


    public function eventAttendees($ref)
    {
      $query  = ' select emp.EMP_NAME ';
      $query .= ' from dibcase_event_attendee as evAn';
      $query .= ' left join dibcase_users as usr on usr.USER_REF = evAn.ATNDY_REF ';
      $query .= ' left join dibcase_employees as emp on emp.EMP_REF = usr.EMP_REF ';
      $query .= ' where evAn.EVENT_REF = '.$ref;
      $result = $this->db->query($query);
      $result = $result->result();
      return $result;
    }

    public function employeeColor($ref)
    {
      $this->db->select('EMP_CLR');
      $this->db->from('dibcase_employees as emp');
      $this->db->join('dibcase_users as usr', 'usr.EMP_REF = emp.EMP_REF');
      $this->db->where('usr.USER_REF',$ref);
      $output = $this->db->get();
      $output = $output->row();
      if(!empty($output))
      return $output->EMP_CLR;
    }


    public function allEmployee($comref)
    {
      $this->db->select('emp.*,usr.*');
      $this->db->from('dibcase_users as usr');
      $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
      $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
      $this->db->where('com.COM_REF', $comref );
      $result = $this->db->get();
      $result = $result->result();
      return $result;
    }

    // public function EventData()
    // {
    //   $this->db->select('emp.*,usr.*');
    //   $this->db->from('dibcase_users as usr');
    //   $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
    //   $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
    //   $this->db->where('com.COM_REF', $comref );
    //   $result = $this->db->get();
    //   $result = $result->result();
    //   return $result;
    // }

    public function eventTagsSearch($key)
    {
      $this->db->select('*');
      $this->db->from('dibcase_tags');
      $this->db->like('TAG_TITLE',$key,'after');
      $result = $this->db->get();
      $result = $result->result();
      return $result;
    }




    public function searchCLientTags($key)
    {
      $this->db->select('*');
      $this->db->from('dibcase_client as cl');
      $where = "( cl.CL_FIRST_NAME LIKE '%$key%' or  cl.CL_MIDDLE_NAME LIKE '%$key%' or cl.CL_LASTNAME LIKE '%$key%' or cl.CL_NICKNAME LIKE '%$key%')";
      $this->db->where($where);
      $result = $this->db->get();
      $result = $result->result();
      return $result;
    }

    public function userApiCredentials($user)
    {
      $this->db->select('usr.USER_CREDENTIALS');
      $this->db->from('dibcase_employees as emp');
      $this->db->join('dibcase_users as usr', 'usr.EMP_REF = emp.EMP_REF');
      $this->db->where('usr.USER_REF',$user);
      $output = $this->db->get();
      $output = $output->row();
      if(!empty($output))
      return $output->USER_CREDENTIALS;
      else
      return null;
    }

    public function userRefreshToken($user)
    {
      $this->db->select('usr.refreshToken');
      $this->db->from('dibcase_users as usr');
      $this->db->where('usr.USER_REF',$user);
      $output = $this->db->get();
      $output = $output->row();
      if(!empty($output))
      return $output->refreshToken;
      else
      return null;
    }

    public function ListTask($COM_REF,$empref)
    {
      $this->db->select('tsk.*');
      $this->db->from('dibcase_tasks as tsk');
      $this->db->where('tsk.COM_REF', $COM_REF );
      // if($Completion == 'Complete')
      // $this->db->where('tsk.TSK_STATUS', 3 );
      // else
      // $this->db->where('tsk.TSK_STATUS !=', 3);

      // $this->db->where('tsk.EmpId', $empref );

      // if( $Case != 0)
      // {
      //   $this->db->where('tsk.REF_ID', $Case );
      // }

      $this->db->join('dibcase_task_assigns as asgn','tsk.TSK_ID = asgn.taskID','inner');
      //$this->db->where('asgn.EmpId', $empref );
      $this->db->group_by('tsk.TSK_ID');

      $result = $this->db->get();
      $result = $result->result();
      return $result;
    }

    public function contactTagSearch($COM_REF,$key)
    {
      $this->db->select('*');
      $this->db->from('dibcase_contacts');
      $this->db->where(" ( CON_FNAME like  '%$key%' or CON_MIDDLE like  '%$key%' or CON_LNAME like  '%$key%' or CON_JOBTITLE like  '%$key%' or CON_CITY  like  '%$key%' or CON_STATE like  '%$key%' or CON_EMPLOYER like  '%$key%' )");
      $this->db->where('COM_REF', $COM_REF );
      $result = $this->db->get();
      $result = $result->result();
      return $result;
    }

    public function getContacts($post)
    {
      $limit = $post['perPage'];
      $start = $post['start'];

      if($post['direction'] == -1)
      $order = 'DESC';
      else if($post['direction'] == 1)
      $order = 'ASC';

      $sort = $post['sort'];

      $sortField = array(
        'CON_FNAME' => 'con.CON_LNAME',
        'CON_EMPLOYER' => 'con.CON_EMPLOYER',
        'CON_JOBTITLE' => 'con.CON_JOBTITLE',
        'CON_CITY' => 'con.CON_CITY',
        'CON_ZIP' => 'con.CON_ZIP',
        'CON_STATE' => 'con.CON_STATE',
        'CON_FAX1' => 'con.CON_FAX1',
        'CON_LAST_EDITED' =>'con.CON_LAST_EDITED'
        );

      $query  = 'select con.* ';
      $query .= ' from dibcase_contacts as con';
      $query .= ' left join dibcase_tags as tags on con.CON_REF = tags.REF_ID and tags.TAG_REF_TYPE = "contact" ';
      $query .= ' where con.COM_REF = '.$post['COM_REF'];

      if($post['searchText'] != NULL && $post['searchText'] != '')
      {
        $searchText = $post['searchText'];
        $query .= " and ( con.CON_FNAME LIKE '%$searchText%' or  con.CON_MIDDLE LIKE '%$searchText%' or con.CON_LNAME LIKE '%$searchText%' or con.CON_EMPLOYER LIKE '%$searchText%' or con.CON_JOBTITLE LIKE '%$searchText%' or con.CON_CITY LIKE '%$searchText%' OR tags.TAG_TITLE LIKE '%$searchText%' )";
      }
      if($post['searchAlphabet'] != NULL && $post['searchAlphabet'] != 'all')
      {
        $searchAlphabet = $post['searchAlphabet'];
        $query .= " and ( con.CON_LNAME LIKE '$searchAlphabet%' ) ";
      }
      $query .= ' GROUP BY con.CON_REF';
      $result = $this->db->query($query);
      $num_rows = $result->num_rows();
      // echo $this->db->last_query();
      // echo "<br><br><br><br>";

      $query  = 'select con.* ';
      $query .= ' from dibcase_contacts as con';
      $query .= ' left join dibcase_tags as tags on con.CON_REF = tags.REF_ID and tags.TAG_REF_TYPE = "contact" ';
      $query .= ' where con.COM_REF = '.$post['COM_REF'];

      if($post['searchText'] != NULL && $post['searchText'] != '')
      {
        $searchText = $post['searchText'];
        $query .= " and ( con.CON_FNAME LIKE '%$searchText%' or  con.CON_MIDDLE LIKE '%$searchText%' or con.CON_LNAME LIKE '%$searchText%' or con.CON_EMPLOYER LIKE '%$searchText%' or con.CON_JOBTITLE LIKE '%$searchText%' or con.CON_CITY LIKE '%$searchText%' OR tags.TAG_TITLE LIKE '%$searchText%' )";
      }
      if($post['searchAlphabet'] != NULL && $post['searchAlphabet'] != 'all')
      {
        $searchAlphabet = $post['searchAlphabet'];
        $query .= " and ( con.CON_LNAME LIKE '$searchAlphabet%' ) ";
      }
      $query .= ' GROUP BY con.CON_REF';
      if($order && isset($sortField[$sort])){
        $query .= ' ORDER BY '.$sortField[$sort].' '.$order;
      }
      else{
        $query .= ' ORDER BY con.CON_ID DESC';
      }
      $query .= " limit $start , $limit";

      // $this->db->limit($perPage, $start);
      $result = $this->db->query($query);
    //  echo $this->db->last_query(); die;
      $result = $result->result();
      // print_r($result); die;
      return array($result,$num_rows,$this->db->last_query());
    }


    function AppealDeadlines($COM_REF,$today)
    {
      $startObj = new DateTime();
      $date = $startObj->format('Y-m-d');

      $query  = ' select emp.EMP_NAME , clm.CLM_CREATED , clm.CLM_ADDEDBY , cl.CL_REF , clm.CLM_NOTES , clm.CLM_APPEAL_DEADLINE , clm.CLM_APPEAL_DEADLINE as start , clm.CLM_APPEAL_DEADLINE as end , cl.CL_FIRST_NAME , cl.CL_MIDDLE_NAME , cl.CL_LASTNAME , cl.CL_SSN , clm.CLM_REF';
      $query .= ' from dibcase_claim as clm';
      $query .= ' left join dibcase_client as cl on cl.CL_REF = clm.CL_REF';
      $query .= ' left join dibcase_claimlevels as lvl on lvl.CLVL_VALUE = clm.CLM_SSA_CASE_LEVEL';
      $query .= ' left join dibcase_users as usr on usr.USER_REF = clm.CLM_ADDEDBY';
      $query .= ' left join dibcase_employees as emp on emp.EMP_REF = usr.EMP_REF';
      $query .= ' where cl.COM_REF = '.$COM_REF;
      if($today == 'today')
      $query .= " and DATE(clm.CLM_APPEAL_DEADLINE) = '$date' ";
      $query .= ' order by clm.CLM_ID DESC';
      $result =   $this->db->query($query);
      $result =   $result->result();
      return $result;
    }


}
?>
