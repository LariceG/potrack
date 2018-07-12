<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Common extends CI_Model {
    public function __construct()
	  {
        parent::__construct();
        if(isset($_POST['userRef']))
        {
          $user = $_POST['userRef'];
          $userRow = $this->getrow('dibcase_users', array( 'USER_REF' => $user));
          if(!empty($userRow) && $userRow->timeZone != '')
          date_default_timezone_set($userRow->timeZone);
        }
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

  	public function update($where,$data,$table)
  	{
  		$this->db->where($where);
  		$this->db->update($table,$data);
  		$db_error = $this->db->error();
  		if ($db_error['code'] == 0)
  			return '1';
  		else
  			return '0';
  	}

    public function delete($table,$where)
  	{
  		$this->db->where($where);
  		$this->db->delete($table);
  		$db_error = $this->db->error();
  		if ($db_error['code'] == 0)
  			return '1';
  		else
  			return '0';
  	}

    public function deleteMultiple($table,$where,$values)
    {
      $this->db->where_in($where, $values);
      $this->db->delete($table);
      $db_error = $this->db->error();
  		if ($db_error['code'] == 0)
  			return '1';
  		else
  			return '0';
    }

  	public function insert($table,$data)
  	{
  		$this->db->insert($table,$data);
  		$insert_id = $this->db->insert_id();
  		if( $insert_id != 0)
  			$output = array (1,$insert_id);
  		else
  			$output = array (0);
  		return $output;
  	}

  	public function insert_batch($table,$data)
  	{
  		$this->db->insert_batch($table,$data);
  		$insert_id = $this->db->insert_id();
  		if( $insert_id != 0)
  			$output = array (1,$insert_id);
  		else
  			$output = array (0);
  		return $output;
  	}

  	public function checkexist($table,$where)
  	{
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where($where);
        $output = $this->db->get();
        $output = $output->num_rows();
		    return $output;
  	}

    public function getTableFields($value ='')
    {
      $fields = $this->db->list_fields($value);
      return $fields;
    }

    public function verifyEmail($email)
    {
      $this->db->select('usr.USER_REF');
      $this->db->from('dibcase_users as usr');
      $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
      $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
      $this->db->where('com.COM_EMAIL', $email);
      $result = $this->db->get();
      $result = $result->row();
      if(!empty($result))
      {
        $this->db->set('USER_STATUS',1);
        $this->db->set('USER_VERIFIED',1);
        $this->db->where('USER_REF',$result->USER_REF);
        $this->db->update('dibcase_users');
        $db_error = $this->db->error();
  			if ($db_error['code'] == 0)
        return 1;
        else{
          return 0;
        }
      }
    }

    public function login($user)
  	{
  		  $this->db->select('usr.*');
        $this->db->from('users as usr');
        $this->db->where('usr.userName', $user);
        $result = $this->db->get();
        $result = $result->row();
        return $result;
  	}

    public function adminLogin($empDetail,$role = NULL)
  	{
  		  $this->db->select('usr.USER_REF,usr.USER_STATUS,usr.USER_PASSWORD,usr.USER_ROLE');
        $this->db->from('dibcase_admin as usr');
        $this->db->where('usr.USER_NAME', $empDetail);
        if($role != NULL && $role == 'superadmin')
        $this->db->where('usr.USER_ROLE', 3);
        $result = $this->db->get();
        $result = $result->result();
        return $result;
  	}


	public function userdata($empDetail)
	{
		$this->db->select('emp.*,com.*,usr.USER_PIC,usr.USER_REF');
    $this->db->from('dibcase_users as usr');
    $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
    $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
    $this->db->where('usr.USER_REF', $empDetail['USER_REF']);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
	}


  function forgotPassword( $email = null )
	{
		$this->db->select('usr.USER_REF,usr.USER_STATUS,emp.*');
    $this->db->from('dibcase_users as usr');
    $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
    $this->db->where('emp.EMP_COMPANY_EMAIL',$email);
		$query  = $this->db->get();
		$result = $query->row();
		if(empty($result))
		{
			$response['success'] = false;
			return $response;
		}
		else
		{
      if($result->USER_STATUS == 2)
      {
        $response['success'] 	     = false;
        $response['inactive'] 	     = true;
      }
      else
      {
  			$newPass  = str_pad(rand(0, pow(10, 6)-1), 6, '0', STR_PAD_LEFT);
  			$salt		  = 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
  			$salt 		= 	base64_encode($salt);
  			$salt 		= 	str_replace('+', '.', $salt);
  			$hash 		= 	crypt($newPass, '$2y$10$'.$salt.'$');
  			$newPass1	=	$hash;

  			$this->db->set('USER_PASSWORD',$newPass1);
        $this->db->set('passwordTimeStamp',date('Y-m-d H:i:s', strtotime("+30 days")));

  			$this->db->where('USER_REF',$result->USER_REF);
  			$this->db->update('dibcase_users');
  			$db_error = $this->db->error();
  			if ($db_error['code'] == 0)
  			{
  				$urll 	  = str_replace('api','#',site_url());
  				$loginUrl = $urll.'login';
  				$emailTemplate = getEmailTemplate(2);
  				$variables = array( 'receiver_name' 	 => ucfirst($result->EMP_NAME),
  									'to'				     => $result->EMP_COMPANY_EMAIL,
  									'newPassword'		 => $newPass,
  									'loginUrl'		 	 => $loginUrl,
  								);
  				sendEmail($variables,$emailTemplate);
  			}
  			$response['success'] 	     = true;
      }
		}
		return $response;
	}

  public function dashboardInfo($user,$com,$role)
  {
    if($role == 2)
    {
      $this->db->select('*');
      $this->db->from('dibcase_tasks as tsk');
      $this->db->join('dibcase_task_assigns as asgn','tsk.TSK_ID = asgn.taskID','inner');
      $this->db->where_in('asgn.EmpId', $user);
      $result = $this->db->get();
      $data['task'] = $result->num_rows();
    }

    else if($role == 1)
    {
      $this->db->select('*');
      $this->db->from('dibcase_users as usr');
      $this->db->join('dibcase_employees as emp','usr.EMP_REF = emp.EMP_REF','inner');
      $this->db->where('emp.COM_REF',$com);
      $result = $this->db->get();
      $data['allEmployees'] = $result->num_rows();


      $this->db->select('*');
      $this->db->from('dibcase_users as usr');
      $this->db->join('dibcase_employees as emp','usr.EMP_REF = emp.EMP_REF','inner');
      $this->db->where('emp.COM_REF',$com);
      $this->db->where('usr.USER_STATUS',1);
      $result = $this->db->get();
      $data['activeUsers'] = $result->num_rows();
    }
    return $data;

  }


	public function get($tbl,$whr)
	{
		$this->db->select('*');
    $this->db->from($tbl);
    $this->db->where($whr);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
	}

  public function billingHoursSum($tbl,$typ,$whr)
  {
    $this->db->select_sum('bhMinutes');
    $this->db->from('dibcase_billing_hours');
    $this->db->where($whr);
    if($typ != 'all')
    $this->db->where(array('bhRefType'=>$typ));
    $result = $this->db->get();
    $result = $result->result();
    return $result;
    // $this->db->query("select sum(bhMinutes) where")
  }

  public function getSorted($tbl,$whr,$field,$sort)
	{
		$this->db->select('*');
    $this->db->from($tbl);
    $this->db->where($whr);
    $this->db->order_by($field, $sort);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
	}



  public function getRecent($tbl,$whr,$id,$howmany)
  {
    $this->db->select('*');
    $this->db->from($tbl);
    $this->db->where($whr);
    $this->db->order_by($id, "desc");
    $this->db->limit($howmany);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
  }

  public function getClientNotes($where)
  {
    $this->db->select('notes.*,emp.EMP_NAME,hours.bhMinutes');
    $this->db->from('dibcase_client_notes as notes');
    $this->db->join('dibcase_users as usr','notes.addedBy = usr.USER_REF','left');
    $this->db->join('dibcase_employees as emp','usr.EMP_REF = emp.EMP_REF','left');

    $this->db->join('dibcase_billing_hours as hours',"hours.bhRef = notes.NOTE_ID and hours.bhRefType = 'clientGeneralNotes'",'left');
    $this->db->where('notes.CL_REF',$where['CL_REF']);
    $this->db->order_by('notes.NOTE_ID', "desc");
    $result = $this->db->get();
    $result = $result->result();
    return $result;
  }

  public function getClientRecent($whr)
  {
    $this->db->select('log.type,log.reference,log.CL_REF,log.datetime,emp.EMP_NAME');
    $this->db->from('dibcase_client as cl');
    $this->db->join('dibcase_client_log as log','cl.CL_REF = log.CL_REF','inner');
    $this->db->join('dibcase_users as usr','log.addedBy = usr.USER_REF','inner');
    $this->db->join('dibcase_employees as emp','usr.EMP_REF = emp.EMP_REF','inner');
    $this->db->where('cl.CL_REF',$whr['CL_REF']);
    $this->db->order_by('log.id', "desc");
    $this->db->limit(5);
    $result = $this->db->get();
    $result = $result->result();

    foreach ($result as $key => $value)
    {
      $skip = false;
      switch ($value->type)
      {
        case 'client-notes':
        $param['tbl'] = 'dibcase_client_notes';
        $param['whr'] = array('NOTE_ID' => $value->reference);
        $param['sel']  = 'NOTE_TEXT';
        $skip = false;
          break;

        case 'client-call':
        $param['tbl'] = 'dibcase_client_call';
        $param['whr'] = array('CALL_ID' =>  $value->reference);
        $param['sel']  = 'CALL_NOTE,CALL_TYPE,CALL_CALLER';
        $skip = false;
          break;

        default:
        $skip = true;
          break;
      }

      if(!$skip)
      {
        $this->db->select($param['sel']);
        $this->db->from($param['tbl']);
        $this->db->where($param['whr']);
        $qu = $this->db->get();
        $data = $qu->row();
        $result[$key]->info = $data;
      }
    }
    return $result;
  }

	public function getrow($tbl,$whr)
	{
		$this->db->select('*');
    $this->db->from($tbl);
    $this->db->where($whr);
    $result = $this->db->get();
    $result = $result->row();
    return $result;
	}

  public function getTable($tbl)
	{
		$this->db->select('*');
    $this->db->from($tbl);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
	}

  public function getFields($tbl,$whr,$fields)
	{
		$this->db->select(implode(',',$fields));
    $this->db->from($tbl);
    $this->db->where($whr);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
	}


	public function getcompanyclients($COM_REF,$start = null,$perPage = null,$sort = null,$direction = null,$filter = null ,$searchText = null , $searchAlphabet =  null)
	{
      if($direction == -1)
      $order = 'DESC';
      else if($direction == 1)
      $order = 'ASC';

      $sortField = array(
        'age' => 'cl.CL_AGE',
        'name' => 'cl.CL_LASTNAME',
        'status' => 'cl.CL_STATUS',
        'owner' => 'cl.CL_OWNER',
        'create' => 'cl.CL_DATECREATED',
        'modified' => 'CL_LAST_MODIFIED',
        'closed' => '',
        'age' => 'cl.CL_AGE',
        'email' => 'email.EMAIL',
        'tags' => '',
        'notes' => '',
        'dob' => 'cl.CL_DOB',
        'birthplace' => 'cl.CL_PLACE_OF_BIRTH',
        'phone' => 'phn.PHN_NUMBER',
        'city' => 'cl.CL_CITY',
        'country' => 'cl.CL_COUNTY',
        'address' => 'cl.CL_ADDRESS',
        'education' => 'cl.CL_EDU',
      );

      $query  = "select cl.*,phn.PHN_NUMBER,email.EMAIL,(select MAX(datetime) from dibcase_client_log as log where log.CL_REF = email.CL_REF && type = 'client-update') as lastModified";
      $query .= " from dibcase_client as cl ";
      $query .= "left join dibcase_phones as phn on cl.CL_REF=phn.CL_REF ";
      $query .= "left join dibcase_cleintemails as email on cl.CL_REF=email.CL_REF ";
      $query .= 'WHERE cl.COM_REF = "' . $COM_REF . '"';
      if($filter != NULL && $filter != 8 )
      $query .= '  AND cl.CL_STATUS = "' . $filter . '"';
      if($filter != NULL && $filter == 8 )
      $query .= ' AND cl.CL_STATUS NOT IN (4,5)';
    //  $this->db->where_not_in('cl.CL_STATUS',array(4,5));

      if($searchText != NULL && $searchText != '')
      {
        $query .= " AND (cl.CL_LASTNAME LIKE '%$searchText%')";
      }
      if($searchAlphabet != NULL && $searchAlphabet != 'all')
      $query .= "  AND ( cl.CL_LASTNAME LIKE '$searchAlphabet%') ";
      $query .= " group by cl.CL_REF ";
      if($sort && isset($sortField[$sort]))
      $query .= " order by $sortField[$sort] $order ";
    //  $this->db->order_by("$sortField[$sort]",$order);
      else
      $query .= " order by cl.CL_ID $order ";
      $queryD = $this->db->query($query);
    //  $this->db->order_by('cl.CL_ID', $order);
    //  $result = $this->db->get();
      $total_rows = $queryD->num_rows();

      $querys  = "select cl.*,emp.EMP_NAME as owner , phn.PHN_NUMBER,email.EMAIL,(select MAX(datetime) from dibcase_client_log as log where log.CL_REF = email.CL_REF && type = 'client-update') as lastModified";
      $querys .= " from dibcase_client as cl ";
      $querys .= "left join dibcase_phones as phn on cl.CL_REF=phn.CL_REF ";
      $querys .= "left join dibcase_cleintemails as email on cl.CL_REF=email.CL_REF ";
      $querys .= "left join dibcase_users as usr on cl.CL_OWNER = usr.USER_REF ";
      $querys .= "left join dibcase_employees as emp on usr.EMP_REF = emp.EMP_REF ";
    //  $this->db->join('dibcase_phones as phn','cl.CL_REF=phn.CL_REF AND phn.PHN_PRIORITY = 1','left');
      $querys .= 'WHERE cl.COM_REF = "' . $COM_REF . '"';
      if($filter != NULL && $filter != 8 )
      $querys .= '  AND cl.CL_STATUS = "' . $filter . '"';
      if($filter != NULL && $filter == 8 )
      $querys .= ' AND cl.CL_STATUS NOT IN (4,5)';

      if($searchText != NULL && $searchText != '')
      {
          $querys .= " AND ( cl.CL_LASTNAME LIKE '%$searchText%')";
      //  $where = "( cl.CL_FIRST_NAME LIKE '%$searchText%' or  cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_LASTNAME LIKE '%$searchText%' or cl.CL_NICKNAME LIKE '%$searchText%' or cl.CL_PLACE_OF_BIRTH LIKE '%$searchText%')";
  			//$this->db->where($where);
      }
      if($searchAlphabet != NULL && $searchAlphabet != 'all')
      $querys .= "  AND ( cl.CL_LASTNAME LIKE '$searchAlphabet%') ";
      $querys .= " group by cl.CL_REF ";
      if($sort && isset($sortField[$sort]))
      $querys .= " order by $sortField[$sort] $order ";
      else
      $querys .= " order by cl.CL_ID $order ";
      $querys .= " limit $start, $perPage ";
      $queryDd = $this->db->query($querys);
      $result = $queryDd->result();
      //
      // echo $filter;
      // echo $this->db->last_query();
      // echo "<pre>";
      // print_r($result);
      // echo "<pre>";
      // die;

      return array(
       'total_rows'     => $total_rows,
       'result'     => $result,
       'query'   => $this->db->last_query()
      );
	}


  public function getcompanyclientsCount($cl)
  {
    $this->db->where('COM_REF',$cl);
    $this->db->from('dibcase_client');
    $count = $this->db->count_all_results();

    $this->db->where('COM_REF',$cl);
    $this->db->where('CL_STATUS',1);
    $this->db->from('dibcase_client');
    $active = $this->db->count_all_results();
    return array( 'total' => $count , 'active' => $active );
  }


	public function clientdata($whr)
	{
  		$this->db->select('cl.*,phn.PHN_NUMBER,email.EMAIL');
      $this->db->from('dibcase_client as cl');
      $this->db->join('dibcase_phones as phn','cl.CL_REF=phn.CL_REF','left');
      $this->db->join('dibcase_cleintemails as email','cl.CL_REF=email.CL_REF','left');
      $this->db->where('cl.CL_REF',$whr);
      $result = $this->db->get();
      $result = $result->result();
      return $result;
	}

  public function updateProfileImage($id = null ,$fileName = null ,$type )
	{

    if($type == 'client')
    {
        $field = 'CL_PIC';
        $where = 'CL_REF';
        $table = 'dibcase_client';
    }

    elseif ($type == 'company' )
	  {
      $field = 'COM_PIC';
      $where = 'COM_REF';
      $table = 'dibcase_company';
    }

    elseif ($type == 'user' )
	  {
      $field = 'USER_PIC';
      $where = 'USER_REF';
      $table = 'dibcase_users';
    }

		if( $id <= 0 || $fileName == '' )
			return false;

      $this->db->select($field);
  		$this->db->where($where, $id);
  		$query = $this->db->get($table);

		if( $query->num_rows() > 0 )
		{
      $oldImage = $query->row()->$field;
			if( $oldImage && $oldImage != 'demo.png' )
				unlink('./assets/uploads/profilePic/'.$oldImage);
		}
    $this->db->set($field,$fileName);
		$this->db->where($where,$id);
		$this->db->update($table);
		$db_error = $this->db->error();
		if ($db_error['code'] == 0)
			return '1';
		else
			return '0';
	}



public function getClientClaim ($where)
{
  $this->db->select('*');
  $this->db->from('dibcase_claim');
  $this->db->where('CLM_REF',$where['CLM_REF']);
  $result = $this->db->get();
  $result = $result->row_array();
  $keys = array_keys($result, '0000-00-00');
  foreach ($keys as $key => $value)
  {
    $result[$value] = '';
  }
  $keys = array_keys($result, '00:00:00');
  foreach ($keys as $key => $value)
  {
    $result[$value] = '';
  }

  $this->db->select('con.*,clmcon.choosedTag,clmcon.NOTES');
  $this->db->from('dibcase_claim_contacts as clmcon');
  $this->db->join('dibcase_contacts as con','con.CON_REF = clmcon.CON_ID');
  $this->db->where('clmcon.CLM_REF',$where['CLM_REF']);
  $res = $this->db->get();
  $res = $res->result();

  $this->db->select('clmMEd.MCOD_ID,clmMEd.MCOD_NOTES,med.MCOD_TITLE');
  $this->db->from('dibcase_claim_med_conditions as clmMEd');
  $this->db->join('dibcase_medical_conditions as med','med.MCOD_ID = clmMEd.MCOD_ID');
  $this->db->where('clmMEd.CLM_REF',$where['CLM_REF']);
  $res2 = $this->db->get();
  $res2 = $res2->result();

  $this->db->select('clmmedic.MED_ID,clmmedic.MCOD,medic.MED_TITLE,clmmedic.MED_NOTES');
  $this->db->from('dibcase_claim_medications as clmmedic');
  $this->db->join('dibcase_medications as medic','clmmedic.MED_ID = medic.MED_ID');
  $this->db->join('dibcase_medical_conditions as mcod','clmmedic.MCOD = mcod.MCOD_ID');
  $this->db->where('clmmedic.CLM_REF',$where['CLM_REF']);
  $this->db->order_by('medic.MED_TITLE','asc');
  $res3 = $this->db->get();
  $res3 = $res3->result();

  $this->db->select('con.*,mp.firstVisit,mp.lastVisit,mp.Notes');
  $this->db->from('dibcase_medical_providers as mp');
  $this->db->join('dibcase_contacts as con','con.CON_REF = mp.CON_REF');
  $this->db->where('mp.CLM_REF',$where['CLM_REF']);
  $this->db->order_by('con.CON_LNAME','asc');
  $res4 = $this->db->get();
  $res4 = $res4->result();


  $contacts = array();
  foreach ($res as $key => $value)
  {
    $contacts[$key]['TITLE']    = $value->CON_SAL.' '.$value->CON_FNAME.' '.$value->CON_MIDDLE.' '.$value->CON_LNAME ;
    $contacts[$key]['CON_REF']  = $value->CON_REF;
    $contacts[$key]['CON_EMPLOYER']  = $value->CON_EMPLOYER;
    $contacts[$key]['NOTES']  = $value->NOTES;
    $contacts[$key]['choosedTag']  = ($value->choosedTag == 0 ? '' : $value->choosedTag);

    $tags = $this->common->get('dibcase_tags',array( 'REF_ID' => $value->CON_REF , 'TAG_REF_TYPE' => 'contact' ));
    $contacts[$key]['tags'] = $tags;
  }

  $Mps = array();
  foreach ($res4 as $key => $value)
  {
    $Mps[$key]['Title']    = $value->CON_SAL.' '.$value->CON_FNAME.' '.$value->CON_MIDDLE.' '.$value->CON_LNAME ;
    $Mps[$key]['object']['CON_REF']  = $value->CON_REF;
    $Mps[$key]['object']['CON_EMPLOYER']  = $value->CON_EMPLOYER;
    $Mps[$key]['lastVisit']  = $value->lastVisit;
    $Mps[$key]['firstVisit']  = $value->firstVisit;
    $Mps[$key]['Notes']  = $value->Notes;
  }

  $result['contacts'] = $contacts;
  $result['mCOds'] = $res2;
  $result['medica'] = $res3;
  $result['MedicalProviders'] = $Mps;

  return (object) $result;
}



public function ClaimList($comref,$start = null,$perPage = null,$sort = null,$direction = null,$filter = null ,$searchText = null , $searchAlphabet =  null , $CaseManager = null , $Representative = null , $isAppeals = null)
{
  if($direction == -1)
  $order = 'DESC';
  else if($direction == 1)
  $order = 'ASC';

  $sortField = array(
    'name'    => 'cl.CL_LASTNAME',
    'status'  => 'clm.CLM_STATUS',
    'clmlno'  => 'clm.CLM_ID',
    'clmlvl'  => 'clm.CLM_SSA_CASE_LEVEL',
    'hrgst'   => 'clm.CLM_STATUS',
    'hrgdate' => 'clm.CLM_HEARING_SCHEDULED',
    'hrgtym'  => 'clm.CLM_HEARING_TIME',
    'aplded'  => 'clm.CLM_APPEAL_DEADLINE',
    'lastact' => 'clm.LAST_ACT_DATE',
    'rptst'   => 'clm.CLM_REPRESENTATION_STATUS',
    'aljname' => 'clm.CLM_ALJ_NAME',
  );


  $query  = " select clm.*,cl.CL_SSN,cl.CL_FIRST_NAME,cl.CL_MIDDLE_NAME,cl.CL_LASTNAME,lvl.CLVL_TITLE , ( SELECT COUNT(*) FROM dibcase_claim where  dibcase_claim.CL_REF  = cl.CL_REF) AS claimCount";
  $query .= " from dibcase_client as cl";
  $query .= " inner join dibcase_claim as clm on cl.CL_REF = clm.CL_REF ";
  $query .= " left  join dibcase_claimlevels as lvl on clm.CLM_SSA_CASE_LEVEL = lvl.CLVL_VALUE ";
  $query .= ' WHERE cl.COM_REF = "' . $comref . '"';
  if($filter != NULL)
  $query .= ' AND clm.CLM_STATUS = "' . $filter . '"';
  if($searchText != NULL && $searchText != '')
  {
    $query .= " AND ( lvl.CLVL_TITLE LIKE '%$searchText%' or clm.CLM_REPRESENTATION_STATUS LIKE '%$searchText%' or clm.CLM_STATUS_OF_CASE LIKE '%$searchText%' or clm.CLM_ALJ_NAME LIKE '%$searchText%' or cl.CL_FIRST_NAME LIKE '%$searchText%' or  cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_LASTNAME LIKE '%$searchText%')";
  }

  if($searchAlphabet != NULL && $searchAlphabet != 'all')
  $query .= " AND ( cl.CL_LASTNAME LIKE '$searchAlphabet%')";

  if($Representative != NULL && $Representative != 'all')
  $query .= ' AND clm.CLM_REP_PRIMARY = "' . $Representative . '"';

  if($CaseManager != NULL && $CaseManager != 'all')
  $query .= ' AND clm.CLM_CASE_MGR = "' . $CaseManager . '"';

  if( $isAppeals && $isAppeals != false )
  $query .= ' AND ( clm.CLM_APPEAL_DEADLINE != NULL  || clm.CLM_APPEAL_DEADLINE != "0000-00-00" )';


  if($sort && isset($sortField[$sort]))
  $query .= " order by $sortField[$sort] $order ";
  else
  $query .= " order by clm.CLM_ID $order ";
  $queryDd = $this->db->query($query);
  $num_rows = $queryDd->num_rows();

  // $this->db->select('clm.*,cl.CL_FIRST_NAME,cl.CL_MIDDLE_NAME,cl.CL_LASTNAME,lvl.CLVL_TITLE , ( SELECT COUNT(*) FROM dibcase_claim where  dibcase_claim.CL_REF  = cl.CL_REF) AS claimCount');
  // $this->db->from('dibcase_client as cl');
  // $this->db->join('dibcase_claim as clm','cl.CL_REF = clm.CL_REF','inner');
  // $this->db->join('dibcase_claimlevels as lvl','clm.CLM_SSA_CASE_LEVEL = lvl.CLVL_VALUE','left');
  // $this->db->where('cl.COM_REF',$comref);
  // if($filter != NULL)
  // $this->db->where('clm.CLM_STATUS',$filter);
  // if($searchText != NULL && $searchText != '')
  // {
  //   $where = "( cl.CL_FIRST_NAME LIKE '%$searchText%' or  cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_LASTNAME LIKE '%$searchText%')";
  //   $this->db->where($where);
  // }
  // if($searchAlphabet != NULL && $searchAlphabet != 'all')
  // $this->db->where(" cl.CL_FIRST_NAME LIKE '$searchAlphabet%' or  cl.CL_MIDDLE_NAME LIKE '$searchAlphabet%' or cl.CL_MIDDLE_NAME LIKE '$searchAlphabet%' or cl.CL_LASTNAME LIKE '$searchAlphabet%' ");

  // if($Representative != NULL && $Representative != 'all')
  // $this->db->where(" clm.CLM_REP_PRIMARY", $Representative);
  //
  // if($CaseManager != NULL && $CaseManager != 'all')
  // $this->db->where(" clm.CLM_CASE_MGR", $CaseManager);

  // if($sort && isset($sortField[$sort]))
  // $this->db->order_by("$sortField[$sort]",$order);
  // else
  // $this->db->order_by('clm.CLM_ID', $order);
  // $result = $this->db->get();
  // $num_rows = $result->num_rows();


  $querys  = " select clm.*,cl.CL_SSN,cl.CL_FIRST_NAME,cl.CL_MIDDLE_NAME,cl.CL_LASTNAME,lvl.CLVL_TITLE , ( SELECT COUNT(*) FROM dibcase_claim where  dibcase_claim.CL_REF  = cl.CL_REF) AS claimCount";
  $querys .= " from dibcase_client as cl";
  $querys .= " inner join dibcase_claim as clm on cl.CL_REF = clm.CL_REF ";
  $querys .= " left  join dibcase_claimlevels as lvl on clm.CLM_SSA_CASE_LEVEL = lvl.CLVL_VALUE ";
  $querys .= ' WHERE cl.COM_REF = "' . $comref . '"';
  if($filter != NULL)
  $querys .= ' AND clm.CLM_STATUS = "' . $filter . '"';
  if($searchText != NULL && $searchText != '')
  {
    $querys .= " AND ( lvl.CLVL_TITLE LIKE '%$searchText%' or clm.CLM_REPRESENTATION_STATUS LIKE '%$searchText%' or clm.CLM_STATUS_OF_CASE LIKE '%$searchText%' or clm.CLM_ALJ_NAME LIKE '%$searchText%' or cl.CL_FIRST_NAME LIKE '%$searchText%' or  cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_MIDDLE_NAME LIKE '%$searchText%' or cl.CL_LASTNAME LIKE '%$searchText%')";
  }

  if($searchAlphabet != NULL && $searchAlphabet != 'all')
  $querys .= " AND ( cl.CL_LASTNAME LIKE '$searchAlphabet%')";

  if($Representative != NULL && $Representative != 'all')
  $querys .= ' AND clm.CLM_REP_PRIMARY = "' . $Representative . '"';

  if($CaseManager != NULL && $CaseManager != 'all')
  $querys .= ' AND clm.CLM_CASE_MGR = "' . $CaseManager . '"';

  if( $isAppeals && $isAppeals != false )
  $querys .= ' AND ( clm.CLM_APPEAL_DEADLINE != NULL  || clm.CLM_APPEAL_DEADLINE != "0000-00-00" )';


  if($sort && isset($sortField[$sort]))
  $querys .= " order by $sortField[$sort] $order ";
  else
  $querys .= " order by clm.CLM_ID $order ";
  $querys .= " limit $start, $perPage ";
  $queryy = $this->db->query($querys);
  $result2 = $queryy->result();


  // echo $this->db->last_query();
  // echo "<pre>";
  // print_r($result);
  // die;
  return array
  (
   'total_rows'   => $num_rows,
   'result'       => $result2
  );

}



public function allCLaimList($comref = null , $cl = null)
{
  $this->db->select('clm.*,cl.CL_FIRST_NAME,cl.CL_MIDDLE_NAME,cl.CL_LASTNAME,cl.CL_SSN');
  $this->db->from('dibcase_client as cl');
  $this->db->join('dibcase_claim as clm','cl.CL_REF=clm.CL_REF','inner');
  if($comref != null)
  $this->db->where('cl.COM_REF',$comref);
  if($cl != null)
  $this->db->where('clm.CL_REF',$cl);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}



public function recentSlug($where)
{
  $this->db->select('templateSlug');
  $this->db->from('dibcase_client_list_template as tmp');
  $this->db->where($where);
  $this->db->order_by('tmp.id desc');
  $this->db->limit(1);
  $result = $this->db->get();
  $result = $result->row();
  if(!empty($result ))
  return $result->templateSlug;
}


public function dibcase_client_template_slugs($data)
{
  $this->db->select('tmp.templateName,tmp.templateSlug');
  $this->db->from('dibcase_client_list_template as tmp');
  $this->db->where($data);
  $this->db->group_by('tmp.templateSlug');
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function ListEmployee($post,$start,$perPage)
{
  $direction  = $post['direction'];
  // $sortField  = $post['sortField'];
  $sort  = $post['SORT'];
  $COM_REF    = $post['COM_REF'];
  $filter     = $post['filter'];
  $searchText = $post['searchText'];
  $searchAlphabet = $post['searchAlphabet'];


  if($direction == -1)
  $order = 'DESC';
  else if($direction == 1)
  $order = 'ASC';

  $sortField = array(
    'name' => 'emp.EMP_NAME',
    'email' => 'emp.EMP_COMPANY_EMAIL',
    'phone' => 'emp.EMP_PERS_PHONE',
    'status' => 'emp.EMP_STATUS',
    'loginName' => 'usr.USER_NAME',
    'role' => 'emp.EMP_ROLE',
    'create' => 'emp.USER_SIGNUP_DATE',
  );

  $this->db->select('emp.*,usr.*');
  $this->db->from('dibcase_users as usr');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
  $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
  $this->db->where('com.COM_REF', $COM_REF );
  $this->db->where('usr.USER_ROLE != 1');

  if($filter != NULL)
  $this->db->where('emp.EMP_STATUS', $COM_REF);

  if($searchText != NULL && $searchText != '')
  {
    $where = "( emp.EMP_NAME LIKE '%$searchText%' or  emp.EMP_ADDRESS LIKE '%$searchText%')";
    $this->db->where($where);
  }
  if($searchAlphabet != NULL && $searchAlphabet != 'all')
  $this->db->where("emp.EMP_NAME LIKE '$searchAlphabet%' ");

  if($sort && isset($sortField[$sort]))
  $this->db->order_by("$sortField[$sort]",$order);
  else
  $this->db->order_by('emp.EMP_ID', $order);
  $result = $this->db->get();
  $num_rows = $result->num_rows();



  $this->db->select('emp.*,usr.*');
  $this->db->from('dibcase_users as usr');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
  $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
  $this->db->where('com.COM_REF', $COM_REF );
  $this->db->where('usr.USER_ROLE != 1');

  if($filter != NULL)
  $this->db->where('emp.EMP_STATUS', $COM_REF);

  if($searchText != NULL && $searchText != '')
  {
    $where = "( emp.EMP_NAME LIKE '%$searchText%' or  emp.EMP_ADDRESS LIKE '%$searchText%')";
    $this->db->where($where);
  }
  if($searchAlphabet != NULL && $searchAlphabet != 'all')
  $this->db->where("emp.EMP_NAME LIKE '$searchAlphabet%' ");

  if($sort && isset($sortField[$sort]))
  $this->db->order_by("$sortField[$sort]",$order);
  else
  $this->db->order_by('emp.EMP_ID', $order);
  $this->db->limit($perPage, $start);
  $result = $this->db->get();
  $result = $result->result();

  return array (
   'total_rows'     => $num_rows,
   'result'     => $result
  );

}

public function allEmployee($comref)
{
  $this->db->select('emp.*,usr.*');
  $this->db->from('dibcase_users as usr');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
  $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
  $this->db->where('com.COM_REF', $comref );
  $this->db->where('usr.USER_ROLE != 1');
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}


public function allEmployeeSearch($com,$key)
{
  $this->db->select('emp.*,usr.*');
  $this->db->from('dibcase_users as usr');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
  $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
  $this->db->where('com.COM_REF', $com );
  $this->db->where("emp.EMP_NAME like '%$key%' ");
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}


public function ListTask($empref,$COM_REF,$Completion,$Case,$sort,$direction,$user)
{
  // die($user);
  if($direction == -1)
  $order = 'DESC';
  else if($direction == 1)
  $order = 'ASC';

  $this->db->select('tsk.*,cl.CL_FIRST_NAME,cl.CL_MIDDLE_NAME,cl.CL_LASTNAME,cl.CL_SSN');
  $this->db->from('dibcase_tasks as tsk');
  $this->db->where('tsk.COM_REF', $COM_REF );
  if($Completion == 'Complete')
  $this->db->where('tsk.TSK_STATUS', 3 );
  else
  $this->db->where('tsk.TSK_STATUS !=', 3);
  // $this->db->where('tsk.EmpId', $empref );
  if( $Case != 0)
  {
    $this->db->where('tsk.REF_ID', $Case );
  }

  $this->db->join('dibcase_task_assigns as asgn','tsk.TSK_ID = asgn.taskID','inner');
  $this->db->join('dibcase_client as cl','tsk.CL_REF = cl.CL_REF','left');
  $this->db->where('asgn.EmpId', $empref );
  $this->db->where("( (tsk.isPrivate = 0) OR ( ( tsk.isPrivate = 1 and  asgn.EmpId = (SELECT EMP_REF FROM dibcase_users WHERE USER_REF = '$user') ) OR ( tsk.isPrivate = 1 and  tsk.TSK_ADDEDBY = '$user' ) ))");
  $this->db->group_by('tsk.TSK_ID');
  $this->db->order_by($sort,$order);
  $result = $this->db->get();
  $result = $result->result();

  // echo $this->db->last_query();

  return array (
   'result'     => $result
  );
}


public function ClientTask($client)
{
  $this->db->select('tsk.*,emp.EMP_NAME');
  $this->db->from('dibcase_tasks as tsk');
  $this->db->join('dibcase_users as usr','usr.USER_REF = tsk.TSK_ADDEDBY','left');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF = usr.EMP_REF','left');
  $this->db->where('tsk.CL_REF', $client );
  $this->db->order_by('tsk.TSK_CREATE_DATE','DESC');
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}


public function getTaskTags()
{
  $this->db->select('id,name');
  $this->db->from('dibcase_task_tags');
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function getTaskTagsAssigns($task)
{
  $this->db->select('tagId');
  $this->db->from('dibcase_task_tags_assigns');
  $this->db->where($task);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function ListTaskAsssigns($com,$AssignedTo,$user = null)
{
  $this->db->select('EmpId,EMP_NAME');
  $this->db->from('dibcase_task_assigns as asgn');
  $this->db->join('dibcase_tasks as task','asgn.taskID = task.TSK_ID','inner');
  $this->db->where('asgn.COM_REF',$com);
  if($AssignedTo != 0)
  $this->db->where('asgn.EmpId',$AssignedTo);
  $this->db->where("( (task.isPrivate = 0) OR ( ( task.isPrivate = 1 and  asgn.EmpId = (SELECT EMP_REF FROM dibcase_users WHERE USER_REF = '$user') ) OR ( task.isPrivate = 1 and  task.TSK_ADDEDBY = '$user' ) ))");
  $this->db->group_by('asgn.EmpId');
  $query = $this->db->get('');
  $result = $query->result();
  return $result;
}


public function TaskTagsAssigns($task)
{
  $this->db->select('asgn.*,tags.*');
  $this->db->from('dibcase_task_tags_assigns as asgn');
  $this->db->join('dibcase_task_tags as tags','asgn.TSK_ID = tags.id','inner');
  $this->db->where('asgn.TSK_ID',$task);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function taskCOmments($task)
{
  $this->db->select('comments.*,emp.EMP_NAME');
  $this->db->from('dibcase_task_comments as comments');
  $this->db->join('dibcase_users as usr','comments.addedBy = usr.USER_REF','inner');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF = usr.EMP_REF','inner');
  $this->db->where('comments.taskID',$task);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}


public function getdueTasks($post)
{
  $this->db->select('*');
  $this->db->from('dibcase_tasks');
  // $this->db->join('dibcase_client as cl','tsk.CL_REF = cl.CL_REF','left');
  $this->db->where('COM_REF',$post['ref']);
  // $date = date('Y-m-d');
  $startObj = new DateTime();
  $date = $startObj->format('Y-m-d');

  if($post['dueFilter'] == 'today')
  $this->db->where('DATE(TSK_DUE_DATE)',$date);

  elseif($post['dueFilter'] == 'thisWeek')
  {
    $this->db->where("WEEKOFYEAR ( TSK_DUE_DATE ) = WEEKOFYEAR(NOW())");
  }

  elseif($post['dueFilter'] == 'pastDue')
  {
    $this->db->where("WEEKOFYEAR ( TSK_DUE_DATE ) <= WEEKOFYEAR(NOW())");
  }
  elseif($post['dueFilter'] == 'thisMonth')
  {
    $m = date('m');
    $this->db->where('MONTH ( TSK_DUE_DATE ) = ',$m);
  }
  $this->db->where('TSK_STATUS !=',3);
  $this->db->order_by('TSK_ID','desc');
  $result = $this->db->get();
  $result = $result->result();
  // echo $this->db->last_query();
  // die;
  return $result;
}


public function getAct($tbl,$whr)
{
  $this->db->select('*');
  $this->db->from($tbl);
  $this->db->where($whr);
  $this->db->order_by('ACT_STATUS','DESC');
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function taskReminder($type,$user,$RefType)
{
  $time = date('H:i:00');
  $date = date('Y-m-d');
  $query = $this->db->query("SELECT *
      FROM dibcase_reminders
      WHERE addedBy = '$user'
      and REF_TYPE = '$RefType'
      and reminderType = '$type'
      and
      (
        ( CountType in ('weeks','days') and DATE(dateCheck) = '$date' )
        OR
        ( CountType in ('hours','minute') and DATE(dateCheck) = '$date' and TIME(dateCheck) = '$time' )
      )");

    $result = $query->result();
    return $result;
}

public function emailReminderCHeck($CountType,$type,$user = NULL,$RefType = null)
{
  $this->db->select('*');
  $this->db->from('dibcase_reminders');

  if($CountType == 'week-days')
  $this->db->where('DATE(dateCheck)',"CURDATE()",FALSE);
  else if($CountType == 'hours-minute')
  {
    $time = date('H:i:00');
    $this->db->where('DATE(dateCheck)',"CURDATE()",FALSE);
    $this->db->where('TIME(dateCheck)',$time);
  }

  $this->db->where('reminderType','Email');

  if($CountType == 'week-days')
  {
    $this->db->where_in('CountType',array('week','days'));
  }
  else if($CountType == 'hours-minute')
  {
    $this->db->where_in('CountType',array('hours','minutes'));
  }

  if($RefType)
  $this->db->where('REF_TYPE',$RefType);

  if($type == 'users')
  $this->db->group_by('addedBy');
  elseif($type == 'reminders')
  {
    $this->db->group_by('REF_ID');
    $this->db->where('addedBy',$user);
  }

  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function emailByUserRef($USER_REF)
{
  $this->db->select('emp.EMP_COMPANY_EMAIL');
  $this->db->from('dibcase_users as usr');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
  $this->db->where('usr.USER_REF', $USER_REF);
  $result = $this->db->get();
  $result = $result->row();
  if(!empty($result))
  return $result->EMP_COMPANY_EMAIL;
}

public function emailByEmpRef($emp)
{
  $this->db->select('emp.EMP_COMPANY_EMAIL');
  $this->db->from('dibcase_employees as emp');
  $this->db->where('emp.EMP_REF', $emp);
  $result = $this->db->get();
  $result = $result->row();
  if(!empty($result))
  return $result->EMP_COMPANY_EMAIL;
}


public function lastUniqueRef($unique,$where,$table,$prefix)
{
  $split = strlen($prefix) + 5;
  $this->db->select($unique);
  $this->db->where($where);
  $this->db->order_by('id','desc');
  $this->db->limit(1);
  $query    		= $this->db->get($table);
  $uniqueNew  = '';
  $currentYear    = date('Y');

  if($query->num_rows() > 0)
  {
    $uniqueNew   = $query->row()->$unique;
    $Tlen          		= strlen($uniqueNew);
    $uniqueNew   = substr($uniqueNew,$split,$Tlen);
    $uniqueNew   = str_pad($uniqueNew + 1, 4, 0, STR_PAD_LEFT);
    $uniqueNew   = $prefix.'-'.$currentYear.$uniqueNew;
  }
  else
  {
    $uniqueNew  = $prefix.'-'.$currentYear.'0001';
  }
  return $uniqueNew;
}

public function headerSearch($type,$searchText,$com)
{
  $this->db->select('*');
  $this->db->from($type);

  if($type == 'dibcase_client')
  $where = "( CL_FIRST_NAME LIKE '%$searchText%' or  CL_MIDDLE_NAME LIKE '%$searchText%' or  CL_LASTNAME LIKE '%$searchText%' )";

  else if($type == 'dibcase_contacts')
  $where = "( CON_FNAME LIKE '%$searchText%' or CON_MIDDLE LIKE '%$searchText%' or CON_LNAME LIKE '%$searchText%')";

  $this->db->where($where);
  $this->db->where('COM_REF',$com);
  $result = $this->db->get();
  $result = $result->result();
  if(!empty($result))
  return $result;
  else
  return array();
}

public function clientClaimDetails($claimRef,$clRef)
{
  $this->db->select('clm.*,cl.CL_FIRST_NAME ,  cl.CL_MIDDLE_NAME , cl.CL_LASTNAME , cl.CL_SSN');
  $this->db->from('dibcase_claim as clm');
  $this->db->join('dibcase_client as cl','clm.CL_REF = cl.CL_REF','inner');
  $this->db->where('cl.CL_REF',$clRef);
  $result = $this->db->get();
  $result = $result->result();
  if(!empty($result))
  {
    foreach ($result as $key => $value)
    {
      if($value->CLM_REF == $claimRef)
      {
        $a = $key + 1;
        $echo['claim'] = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4).' (Claim '.$a.' of '.count($result).' )';
        return $echo;
      }
    }
  }
}

public function clientDetails($clRef)
{
  $this->db->select('cl.CL_FIRST_NAME ,  cl.CL_MIDDLE_NAME , cl.CL_LASTNAME , cl.CL_SSN');
  $this->db->from('dibcase_client as cl');
  $this->db->where('cl.CL_REF',$clRef);
  $result = $this->db->get();
  $value = $result->row();
  $cl = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4 );
  if(!empty($value))
  return $cl;
}


// public function lastUniqueRef($unique,$where,$table,$prefix)
// {
//   $split = strlen($prefix) + 5;
//   $this->db->select($unique);
//   $this->db->where($where);
//   $this->db->order_by('id','desc');
//   $this->db->limit(1);
//   $query    		= $this->db->get($table);
//   $uniqueNew  = '';
//   $currentYear    = date('Y');
//
//   if($query->num_rows() > 0)
//   {
//     $uniqueNew   = $query->row()->$unique;
//     $Tlen          		= strlen($uniqueNew);
//     $uniqueNew   = substr($uniqueNew,$split,$Tlen);
//     $uniqueNew   = str_pad($uniqueNew + 1, 4, 0, STR_PAD_LEFT);
//     $uniqueNew   = $prefix.'-'.$currentYear.$uniqueNew;
//   }
//   else
//   {
//     $uniqueNew  = $prefix.'-'.$currentYear.'0001';
//   }
//   return $uniqueNew;
// }



public function updateClientsForm($where,$data,$table,$CL_STATUS,$closed)
{
  $this->db->where($where);
  $this->db->delete('dibcase_phones');

  $this->db->where($where);
  $this->db->delete('dibcase_cleintemails');

  // echo "<pre>";
  // print_r($data['phones']);
  // print_r($data['emails']);

  if(!empty($data['phones']))
  {
    // print_r($data['phones']);
    foreach ($data['phones'] as $key => $value)
    {
      if(isset($value['PHN_ID']))
      {
        unset($data['phones'][$key]['PHN_ID']);
      }
      if(isset($value['PHN_NOTES']))
      {
        unset($data['phones'][$key]['PHN_NOTES']);
      }
      $data['phones'][$key]['CL_REF'] = $where['CL_REF'];
    }
    $this->db->insert_batch('dibcase_phones',$data['phones']);
  }

  if(!empty($data['emails']))
  {
    // print_r($data['emails']);
    foreach ($data['emails'] as $key => $value)
    {
      if(isset($value['EML_ID']))
      {
        unset($data['emails'][$key]['EML_ID']);
      }
      $data['emails'][$key]['CL_REF'] = $where['CL_REF'];
    }
    $this->db->insert_batch('dibcase_cleintemails',$data['emails']);
  }
  // print_r($data['phones']);
  // print_r($data['emails']);
  // die;

  $this->db->where('CL_REF',$where['CL_REF']);
  $this->db->update('dibcase_client',array( 'CL_STATUS' => $CL_STATUS , 'CL_CLOSED_DATE' => $closed ));

  $db_error = $this->db->error();
  if ($db_error['code'] == 0)
    return '1';
  else
    return '0';
}

public function ClientAutocompleteSearch($com,$key)
{
  $this->db->select('*');
  $this->db->from('dibcase_client as cl');
  $where = "( cl.CL_FIRST_NAME LIKE '%$key%' or  cl.CL_MIDDLE_NAME LIKE '%$key%' or cl.CL_LASTNAME LIKE '%$key%' or cl.CL_NICKNAME LIKE '%$key%') and cl.COM_REF = '$com'";
  $this->db->where($where);
  $this->db->where('cl.COM_REF',$com);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}


public function ClaimAutocompleteSearch($com,$cl,$key)
{
  $this->db->select('cl.CL_REF , clm.CLM_REF , cl.CL_FIRST_NAME ,  cl.CL_MIDDLE_NAME , cl.CL_LASTNAME , cl.CL_SSN , ( SELECT COUNT(*) FROM dibcase_claim where  dibcase_claim.CL_REF  = cl.CL_REF ) AS claimCount');
  $this->db->from('dibcase_claim as clm');
  $this->db->join('dibcase_client as cl','clm.CL_REF=cl.CL_REF','inner');
  $where = "( cl.CL_FIRST_NAME LIKE '%$key%' or  cl.CL_MIDDLE_NAME LIKE '%$key%' or cl.CL_LASTNAME LIKE '%$key%' or cl.CL_NICKNAME LIKE '%$key%') and cl.COM_REF = '$com'  ";
  $this->db->where($where);
  if($cl != 'all')
  $this->db->where('cl.CL_REF',$cl);
  $this->db->where('cl.COM_REF',$com);
  $result = $this->db->get();
  $result = $result->result();
  // echo $this->db->last_query();
  return $result;
}


public function updateTaskStatus($actID)
{
  $result = $this->db->query("select * from dibcase_activity  where TSK_ID = (select TSK_ID from dibcase_activity where ACT_ID = '$actID')");
  $total = $result->num_rows();

  $result = $this->db->query("select * from dibcase_activity  where ACT_STATUS = 1 and TSK_ID = (select TSK_ID from dibcase_activity where ACT_ID = '$actID')");
  $done = $result->num_rows();

  if($done == 0)
  $st = 0;
  else if($done == $total)
  $st = 3;
  else
  $st = 1;


  $this->db->query("update dibcase_tasks set TSK_STATUS = '$st' where TSK_ID = (select TSK_ID from dibcase_activity where ACT_ID = '$actID')");
}


public function updateTaskStatusTaskId($taskID)
{
  $result = $this->db->query("select * from dibcase_activity  where TSK_ID = '$taskID'");
  $total = $result->num_rows();

  $result = $this->db->query("select * from dibcase_activity  where ACT_STATUS = 1 and TSK_ID = '$taskID'");
  $done = $result->num_rows();

  if($done == 0)
  $st = 0;
  else if($done == $total)
  $st = 3;
  else
  $st = 1;

  $this->db->query("update dibcase_tasks set TSK_STATUS = '$st' where TSK_ID = '$taskID'");
}



public function deleteCompany($com)
{
  $query[1]  = "delete dibcase_client,dibcase_client_call,dibcase_client_log,dibcase_client_notes,dibcase_cleintemails,dibcase_phones,dibcase_claim,dibcase_claim_contacts";
  $query[1] .= " from dibcase_company";
  $query[1] .= " left join dibcase_client        on dibcase_client.COM_REF   = dibcase_company.COM_REF";
  $query[1] .= " left join dibcase_client_call   on dibcase_client.CL_REF    = dibcase_client_call.CL_REF";
  $query[1] .= " left join dibcase_client_log    on dibcase_client.CL_REF    = dibcase_client_log.CL_REF";
  $query[1] .= " left join dibcase_client_notes  on dibcase_client.CL_REF    = dibcase_client_notes.CL_REF";
  $query[1] .= " left join dibcase_cleintemails  on dibcase_client.CL_REF    = dibcase_cleintemails.CL_REF";
  $query[1] .= " left join dibcase_phones        on dibcase_client.CL_REF    = dibcase_phones.CL_REF";
  $query[1] .= " left join dibcase_claim              on dibcase_client.CL_REF   = dibcase_claim.CL_REF";
  $query[1] .= " left join dibcase_claim_contacts     on dibcase_claim.CLM_REF   = dibcase_claim_contacts.CLM_REF";
  $query[1] .= " where dibcase_company.COM_REF = '$com'";



  $query[2]  = "delete dibcase_tasks,dibcase_activity,dibcase_task_assigns,dibcase_task_comments,dibcase_task_reminders,dibcase_task_tags_assigns,dibcase_reminders";
  $query[2] .= " from dibcase_company";
  $query[2] .= " left join dibcase_tasks                 on dibcase_company.COM_REF  = dibcase_tasks.COM_REF";
  $query[2] .= " left join dibcase_activity              on dibcase_tasks.TSK_ID     = dibcase_activity.TSK_ID";
  $query[2] .= " left join dibcase_task_assigns          on dibcase_tasks.TSK_ID     = dibcase_task_assigns.taskID";
  $query[2] .= " left join dibcase_task_comments         on dibcase_tasks.TSK_ID     = dibcase_task_comments.taskID";
  $query[2] .= " left join dibcase_task_reminders        on dibcase_tasks.TSK_ID     = dibcase_task_reminders.TSK_ID";
  $query[2] .= " left join dibcase_task_tags_assigns     on dibcase_tasks.TSK_ID     = dibcase_task_tags_assigns.TSK_ID";
  $query[2] .= " left join dibcase_reminders             on dibcase_tasks.TSK_ID  = dibcase_reminders.REF_ID ";
  $query[2] .= " where dibcase_reminders.REF_TYPE = 'task' ";
  $query[2] .= " and dibcase_company.COM_REF = '$com'";

  $query[3]  = "delete dibcase_events,dibcase_event_attendee,dibcase_reminders,dibcase_contacts";
  $query[3] .= " from dibcase_company";
  $query[3] .= " left join dibcase_contacts         on dibcase_company.COM_REF  = dibcase_contacts.COM_REF";
  $query[3] .= " left join dibcase_events                 on dibcase_company.COM_REF   = dibcase_events.COM_REF";
  $query[3] .= " left join dibcase_event_attendee         on dibcase_events.EVENT_REF  = dibcase_event_attendee.EVENT_REF";
  $query[3] .= " left join dibcase_reminders              on dibcase_events.EVENT_REF  = dibcase_reminders.REF_ID ";
  $query[3] .= " and dibcase_reminders.REF_TYPE = 'event'";
  $query[3] .= " where dibcase_company.COM_REF = '$com'";


  $query[4]  = "delete dibcase_users,dibcase_employees,dibcase_settings,dibcase_client_list_template,dibcase_tags,dibcase_task_tags";
  $query[4] .= " from dibcase_company";
  $query[4] .= " left join dibcase_employees on dibcase_company.COM_REF  = dibcase_employees.COM_REF";
  $query[4] .= " left join dibcase_users     on dibcase_users.EMP_REF    = dibcase_employees.EMP_REF";
  $query[4] .= " left join dibcase_settings  on dibcase_users.USER_REF   = dibcase_settings.USER_REF";
  $query[4] .= " left join dibcase_client_list_template  on dibcase_users.USER_REF   = dibcase_client_list_template.USER_REF";
  $query[4] .= " left join dibcase_tags      on dibcase_company.COM_REF  = dibcase_tags.COM_REF";
  $query[4] .= " left join dibcase_task_tags     on dibcase_company.COM_REF     = dibcase_task_tags.COM_REF";
  $query[4] .= " where dibcase_company.COM_REF = '$com'";
  $query[4] .= " and dibcase_users.USER_ROLE = 2";

  foreach ($query as $key => $v)
  {
    $result = $this->db->query($v);
  }

  $this->db->select('usr.USER_REF');
  $this->db->from('dibcase_users as usr');
  $this->db->join('dibcase_employees as emp','emp.EMP_REF=usr.EMP_REF','inner');
  $this->db->join('dibcase_company as com','com.COM_REF=emp.COM_REF','inner');
  $this->db->where('com.COM_REF', $com);
  $get = $this->db->get();
  $row = $get->row();


  $this->update(array('USER_REF'=>$row->USER_REF),array('USER_STATUS' => 4 ),'dibcase_users');

  $db_error = $this->db->error();
  if ($db_error['code'] == 0)
    return '1';
  else
    return '0';
}

public function mcodSearch($com,$key)
{
  $this->db->select('*');
  $this->db->from('dibcase_medical_conditions as med');
  $where = "( med.MCOD_TITLE LIKE '%$key%')";
  $this->db->where($where);
  // $this->db->where('cl.COM_REF',$com);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}

public function medicSearch($com,$key)
{
  $this->db->select('*');
  $this->db->from('dibcase_medications as medic');
  $where = "( medic.MED_TITLE LIKE '%$key%')";
  $this->db->where($where);
  // $this->db->where('cl.COM_REF',$com);
  $result = $this->db->get();
  $result = $result->result();
  return $result;
}


public function getPurchaseOrdes($start ,$perPage,$type , $client = null)
{
  $this->db->select('orders.*,cl.*');
  $this->db->from('orders');
  $this->db->join('client as cl','orders.clientId = cl.clientId','inner');
  if($type == 'forDesigner')
  {
    $where = "orders.orderStatus IN (1,2,3,4)";
    $this->db->where($where);
  }
  else if($type == 'forProduction')
  {
    $where = "orders.orderStatus IN (5,6,7) or orders.orderStatus > 7";
    $this->db->where($where);
  }

  else if($type == 'forClient')
  {
    $where = "orders.clientId = '$client'";
    $this->db->where($where);
  }

  $tempdb = clone $this->db;
  $num_results = $tempdb->count_all_results();
  $result = $this->db->get();
  $result = $result->result();

  // echo $this->db->last_query();
  // die;
  if($result)
  return array('total' => $num_results, 'result' => $result);
  else
  return array('total' => $num_results, 'result' => array());
}

public function PurchaseOrderDetails($orderid)
{
  $this->db->select('orders.*,cl.*');
  $this->db->from('orders');
  $this->db->join('client as cl','orders.clientId = cl.clientId','inner');
  $this->db->where('orders.orderId', $orderid);
  $result = $this->db->get();
  $result = $result->row();
  return $result;
}


public function getCount($table,$where = null)
{
  $this->db->select('*');
  $this->db->from('orders');
  if($where != null)
  $this->db->where($where);
  $result = $this->db->get();
  $result = $result->num_rows();
  return $result;
}

public function getCountOr($table,$where = null,$where2 = null,$where3 = null)
{
  $this->db->select('*');
  $this->db->from('orders');
  if($where != null)
  $this->db->where($where);
  if($where2 != null)
  $this->db->or_where($where2);
  if($where3 != null)
  $this->db->or_where($where3);
  $result = $this->db->get();
  $result = $result->num_rows();
  return $result;
}

public function getPomsg($id)
{
  $this->db->select('poc.*,usr.fullName,usr.userName,usr.userType');
  $this->db->from('poComments as poc');
  $this->db->join('users as usr','usr.userId = poc.pomsgAddedBy','inner');
  $this->db->where('poc.pomsgPOld',$id);
  $result = $this->db->get();
  return $result->result();
}

// public function ChangePoStatus($orderId,$status)
// {
// }

public function uniqueId($field,$primary,$table,$prefix)
{
  $this->db->select($field);
  $this->db->order_by($primary,'desc');
  $this->db->limit(1);
  $query    		= $this->db->get($table);
  $id  = '';
  $currentYear    = date('Y');
  if($query->num_rows() > 0)
  {
    $idd   = $query->row()->$field;
    $Tlen            = strlen($idd);
    // echo $Tlen;
    $idd   = substr($idd,10,$Tlen);
    // echo $idd;
    $idd   = str_pad($idd + 1, 5, 0, STR_PAD_LEFT);
   // echo $idd;
   // die;
   $id   = $prefix.$currentYear.$idd;
 }
 else
 {
   $id  = $prefix.$currentYear.'00001';
 }
 return $id;
}

}
?>
