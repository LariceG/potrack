<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:*");
class EmployeeController extends CI_Controller {

 public function __construct()
 {
    parent::__construct();
    $this->load->Model('employee');
		$this->load->Model('common');
 }

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function addEmployee()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$postData= array_change_key_case($postData,CASE_UPPER);
			$date = date("d M Y H:i:s");
			$date1 = date("d M Y H:i:s");
			$usernameexist 				= $this->common->checkexist('dibcase_users',array('USER_NAME' => $postData['USER_NAME']));
			if( $usernameexist == 0 )
			{
					$emp['COM_REF'] 						= $postData['COM_REF'];
					$emp['EMP_REF'] 						=  strtotime($date) . rand(0,9999);
					$emp['EMP_NAME'] 						=  $postData['EMP_NAME'];
					$emp['EMP_PERS_PHONE'] 			=  $postData['EMP_PERS_PHONE'];
					$emp['EMP_OFFICE_PHONE'] 		=  $postData['EMP_OFFICE_PHONE'];
          $emp['EMP_OFC_PHN_EXT'] 		=  isset($postData['EMP_OFC_PHN_EXT']) ? $postData['EMP_OFC_PHN_EXT'] : '';
					$emp['EMP_COMPANY_EMAIL'] 	=  $postData['EMP_COMPANY_EMAIL'];
					$emp['EMP_PERSONAL_EMAIL'] 	=  $postData['EMP_PERSONAL_EMAIL'];
					$emp['EMP_ADDRESS'] 				=  $postData['EMP_ADDRESS'];
					$emp['EMP_STATUS'] 					=  $postData['EMP_STATUS'];
					$emp['USER_SIGNUP_DATE'] 		=  $postData['EMP_ADDRESS'];
					$emp['USER_SIGNUP_DATE'] 		=  date('Y-m-d H:i:s');
					$emp['EMP_ROLE'] 					=  $postData['USER_ROLE'];
					$emp['EMP_DOB'] 					=  goodDateCondition($postData['EMP_DOB']);
					$emp['EMP_CLR'] 		     =  isset($postData['EMP_CLR']) ? $postData['EMP_CLR'] : '';

					$output = $this->common->insert('dibcase_employees',$emp);
					if($output[0] == 1)
					{
						$date = date("d M Y H:i:s");
						$date1 = date("d M Y H:i:s");
						$user['USER_REF'] = strtotime($date) . rand(0,9999);
						$user['EMP_REF'] 		=  $emp['EMP_REF'];
						$user['USER_STATUS'] 	=  1;
						$user['USER_ROLE'] 		=  2;
						$user['passwordTimeStamp'] 		=  date('Y-m-d H:i:s', strtotime("+30 days"));

						$user['USER_NAME'] = $postData['USER_NAME'];

						$salt						= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
						$salt 					= 	base64_encode($salt);
						$salt 					= 	str_replace('+', '.', $salt);
						$hash 					= 	crypt($postData['USER_PASSWORD'], '$2y$10$'.$salt.'$');
						$user['USER_PASSWORD']	=	$hash;

						$output 				= $this->common->insert('dibcase_users',$user);
						$result["success"] 		= 'Employee  added successfully.';
					}
			}
			else
			{
					$result["error"] 	= 'Username Already Exist';
					$result["usernameexist"] 	= ' ';
			}

			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}


	public function updateEmployee()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$postData= array_change_key_case($postData,CASE_UPPER);
			$date = date("d M Y H:i:s");
			$date1 = date("d M Y H:i:s");

			$emp['EMP_NAME'] 						=  $postData['EMP_NAME'];
			$emp['EMP_PERS_PHONE'] 			=  $postData['EMP_PERS_PHONE'];
			$emp['EMP_OFFICE_PHONE'] 		=  $postData['EMP_OFFICE_PHONE'];
      $emp['EMP_OFC_PHN_EXT'] 		=  $postData['EMP_OFC_PHN_EXT'];
			$emp['EMP_COMPANY_EMAIL'] 	=  $postData['EMP_COMPANY_EMAIL'];
			$emp['EMP_PERSONAL_EMAIL'] 	=  $postData['EMP_PERSONAL_EMAIL'];
			$emp['EMP_ADDRESS'] 				=  $postData['EMP_ADDRESS'];
			$emp['EMP_STATUS'] 					=  $postData['EMP_STATUS'];
			$emp['EMP_ROLE'] 					  =  $postData['USER_ROLE'];
			$emp['EMP_DOB'] 					  =  goodDateCondition($postData['EMP_DOB']);
      $emp['EMP_CLR'] 		        =  $postData['EMP_CLR'];


			$output = $this->common->update(array('EMP_REF' => $postData['EMP_REF']),$emp,'dibcase_employees');
			if($output == 1)
			{
        if(isset($postData['USER_PASSWORD']))
        {
          $salt						= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
          $salt 					= 	base64_encode($salt);
          $salt 					= 	str_replace('+', '.', $salt);
          $hash 					= 	crypt($postData['USER_PASSWORD'], '$2y$10$'.$salt.'$');
          $user['USER_PASSWORD']	=	$hash;
          $user['passwordTimeStamp'] 		=  date('Y-m-d H:i:s', strtotime("+30 days"));
        }
        $user['USER_STATUS'] 	=  $postData['USER_STATUS'];
        $output 				      =  $this->common->update(array('EMP_REF' => $postData['EMP_REF']),$user,'dibcase_users');
				$result["success"] 		=  'Employee updated successfully.';
        // $result["query"] 		  =  $this->db->last_query();
			}

			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}


	public function ListEmployee()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			if(isset($postData['page']))
			{
				$page    = $postData['page'];
			  $perPage   = $postData['perPage'];
				if( $perPage <= 0 )
			   $perPage = 10;

				 if( $page <= 0 )
	 		   $page = 1;

				 $start = ($page-1) * $perPage;
			}
			if(!isset($postData['SORT']))
			$postData['SORT'] = null;

			if(!isset($postData['filter']))
			$postData['filter'] = null;

			if(!isset($postData['searchText']))
			$postData['searchText'] = null;

			if(!isset($postData['searchAlphabet']))
			$postData['searchAlphabet'] = null;


			$data = $this->common->ListEmployee($postData,$start,$perPage);
			if(!empty($data))
			{
				$result["success"] 			=  true;
				$result["data"]    			=  $data;
			}
			else
			{
				$result["error"] 			=  'No Records Found';
			}
		}
		else
		{
				$result["error"] 	= 'Username Already Exist';
				$result["usernameexist"] 	= ' ';
		}

		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}

	public function allEmployee()
	{
	  if(!empty($_POST))
	  {
	    $postData	=	json_decode($_POST['data'], true);
	    $response = $this->common->allEmployee($postData['COM_REF']);
	    if($response)
	    {
	      $result["success"]   =  TRUE;
	      $result["data"]   =  $response;
	    }
	    else
	    {
	      $result['data']			=	array();
	    }
	  }
	  else
	  {
	    $result["error"]   =  'No input data';
	  }
	  header('Content-Type: application/json');
	  echo json_encode($result);exit;
	}

  public function allEmployeeSearch($com,$key)
	{
    $response = $this->common->allEmployeeSearch($com,$key);
	  header('Content-Type: application/json');
	  echo json_encode($response);exit;
	}


  public function queryy()
  {
    $response = $this->common->getTable('dibcase_employees');
    foreach ($response as $key => $value)
    {
      if($value->EMP_NAME == '')
      {
        $response = $this->common->getrow('dibcase_company',array('COM_REF' => $value->COM_REF));
        $this->common->update(array('EMP_REF' => $value->EMP_REF),array('EMP_NAME' => $response->COM_NAME.' admin'),'dibcase_employees');
      }
    }
  }


}
?>
