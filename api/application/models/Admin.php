<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin extends CI_Model {
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

	public function getFirms($sortField,$direction)
	{
    if($sortField == 'joined')
    $SORT = 'com.COM_SIGNUP_DATE';

    if($direction == 1)
    $dir = 'ASC';
    else
    $dir = 'DESC';

		$this->db->select('com.*,count(emp.EMP_ID) as employeesCount,usr.USER_STATUS,usr.USER_REF');
    $this->db->from('dibcase_company as com');
    $this->db->join('dibcase_employees as emp','emp.COM_REF  = com.COM_REF','LEFT');
    $this->db->join('dibcase_users as usr','emp.EMP_REF  = usr.EMP_REF','LEFT');
    $this->db->group_by('com.COM_REF');
    $this->db->order_by($SORT,$dir);
    $result = $this->db->get();
    $result = $result->result();
    return $result;
	}

  public function DashboardInfo()
  {

  }

  public function FirmDetails($firm)
  {
    // $this->db->select('com.*,count(emp.EMP_ID) as employeesCount,count(clm.CLM_REF) as claimCount,count(cl.CL_REF) as clientCount,usr.USER_STATUS,usr.USER_REF');
    $this->db->select('com.*,count(emp.EMP_ID) as employeesCount,usr.USER_STATUS,usr.USER_REF');
    $this->db->from('dibcase_company as com');
    $this->db->join('dibcase_employees as emp','emp.COM_REF  = com.COM_REF','LEFT');
    $this->db->join('dibcase_users as usr','emp.EMP_REF  = usr.EMP_REF','LEFT');
    // $this->db->join('dibcase_client as cl','cl.COM_REF  = com.COM_REF','LEFT');
    // $this->db->join('dibcase_claim as clm','clm.CL_REF  = cl.CL_REF','LEFT');
    $this->db->where('com.COM_REF',$firm);
    $result = $this->db->get();
    $result = $result->row();
    return $result;
  }

  public function getFields($value='')
  {
    $fields = $this->db->list_fields($value);
    return $fields;
  }




}
?>
