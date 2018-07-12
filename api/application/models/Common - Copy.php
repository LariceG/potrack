<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Common extends CI_Model {
    public function __construct() 
	{
        parent::__construct();
    }
	
    public function generateRefKey() 
	{
		$characters 		= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength 	= strlen($characters);
		$randomString 		= '';
		for ($i = 0; $i < 16; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	public function getUserTypeByDesignationRef( $designationRef = null )
	{
		$this->db->select('userType');
		$this->db->where('designationRef',$designationRef);
		$query   = $this->db->get('skyline_designations');
		$userType = '1';
		if( $query->num_rows() > 0 )
		{
			$userType = $query->row()->userType;			
		}
		return $userType;
	}
	
    public function createEmployee($userLogin, $userProf) 
	{
		$id = $userLogin['id'];
                
        $this->db->select('emailId');
        $this->db->from('skyline_users');
        $this->db->where('emailId', $userLogin['emailId']);
		if( $id > 0 )
			$this->db->where('id!=',$id);
        $output = $this->db->get();
        $output = $output->result();
			
        if(!empty($output)|| count($output) > 0){
			$msg = "2";
			return $msg;
		}
		else
		{
			if( $id > 0 )
			{
				$this->db->select('userRef');
				$this->db->from('skyline_users');
				$this->db->where('id',$id);
				$query   = $this->db->get();
				$userRef = $query->row()->userRef;
				
				$this->db->where('userRef',$userRef);
				$this->db->update('skyline_users',$userLogin);
				
				$this->db->where('userprofileRef',$userRef);
				$this->db->update('skyline_userProfile',$userProf);	
				
				$msg = "3";
				return $msg;
			}
			else
			{
				$this->db->select('userRef');
				$this->db->order_by('id','desc');
				$this->db->limit(1);
				$query    		= $this->db->get('skyline_users');		
				$userRef   = '';
				$currentYear    = date('Y');
				if($query->num_rows() > 0)
				{
					$userRef   = $query->row()->userRef;
					$Tlen            = strlen($userRef);
					$userRef   = substr($userRef,9,$Tlen);			
					$userRef   = str_pad($userRef + 1, 4, 0, STR_PAD_LEFT);			
					$userRef   = 'user-'.$currentYear.$userRef;
				}
				else
				{
					$userRef  = 'user-'.$currentYear.'0001';
				}
				
				$userLogin['userRef'] = $userRef;
				$userProf['userprofileRef'] = $userRef;
				$this->db->insert('skyline_users',$userLogin);
				$this->db->insert('skyline_userProfile',$userProf);
				$msg = $this->db->affected_rows();
				return $msg;
			}
		}
    }
	
    public function loginEmployee($empDetail)
	{
		$this->db->select('skyline_users.userRef,skyline_users.emailId,skyline_users.userType,password,skyline_userProfile.*');
        $this->db->from('skyline_users');
        $this->db->join('skyline_userProfile','skyline_userProfile.userprofileRef=skyline_users.userRef','inner');
        $this->db->where('emailId', $empDetail['emailId']);
        $result = $this->db->get();
        $result = $result->result();
        return $result;
	}
	
	public function addCustomer($customerDetail) 
	{
		$id = isset( $customerDetail['id'] ) ? $customerDetail['id'] : '';
        $this->db->select('emailId');
        $this->db->from('skyline_customer');
		if( $id > 0 )
			$this->db->where('id!=',$id);
        $this->db->where('emailId', $customerDetail['emailId']);
        $output = $this->db->get();
        $output = $output->result();
		
        if(!empty($output)|| count($output) > 0){
			$msg = "2";
			return $msg;
		}
		else
		{
			if( $id > 0 )
			{
				$this->db->where('id',$id);
				$this->db->update('skyline_customer',$customerDetail);	
				$msg = "3";
				return $msg;
			}
			else
			{
				$this->db->select('max(LPAD(`customerNum`+1, 4, "0")) as nextNumber');
				$this->db->from('skyline_customer');
				$output		=	$this->db->get();
				//echo $this->db->last_query(); 
				$output 	= $output->result();
				$nextNumber = $output[0]->nextNumber;
				if($nextNumber == NULL ){
					$customerDetail['customerNum'] = '0001';
				}
				else{
					$customerDetail['customerNum'] = $nextNumber;
				}
				$this->db->insert('skyline_customer',$customerDetail);
			}			
			$msg = $this->db->affected_rows();
			$insert_id = $this->db->insert_id();
			return array($msg,$customerDetail['customerNum'],$insert_id);
		}
		
    }
	
	public function addCar($customerDetail) 
	{
		$id = isset( $customerDetail['id'] ) ? $customerDetail['id'] : '';
        $this->db->select('carRef');
        $this->db->from('skyline_cars');
		if( $id > 0 )
			$this->db->where('id!=',$id);
        $this->db->where('licensePlate', $customerDetail['licensePlate']);
        $output = $this->db->get();
        $output = $output->result();
        if(!empty($output)|| count($output) > 0){
			$msg = "2";
			return $msg;
		}
		else
		{
			if( $id > 0 )
			{
				$this->db->where('id',$id);
				$this->db->update('skyline_cars',$customerDetail);	
				$msg = "3";
				return $msg;
			}
			else
			{
				$this->db->select('max(LPAD(`carNum`+1, 4, "0")) as nextNumber');
				$this->db->from('skyline_cars');
				$output		=	$this->db->get();
				//echo $this->db->last_query(); 
				$output 	= $output->result();
				$nextNumber = $output[0]->nextNumber;
				if($nextNumber == NULL )
				{
					$customerDetail['carNum'] = '0001';
				}
				else
				{
					$customerDetail['carNum'] = $nextNumber;
				}
				$this->db->insert('skyline_cars',$customerDetail);
			}
			$insert_id = $this->db->insert_id();
			$msg = $this->db->affected_rows();
		}
		return array($msg,$customerDetail['carNum'],$insert_id);
    }
	
	public function userType($userprofileRef)
	{
		$output = $this->db->query("SELECT skyline_designations.userType FROM `skyline_userProfile` join skyline_designations on skyline_userProfile.designation = skyline_designations.designationRef WHERE `userprofileRef`='$userprofileRef'");
        $result = $output->row();
		return $result->userType;
	}
	
	public function searchCustomer($detail) 
	{
		$userType = $this->userType($detail['addedBy']);
        $this->db->select('*');
        $this->db->from('skyline_customer');
		if( !empty($detail) )
		{
			$this->db->like('firstName',$detail['searchtext'],'after');
			$this->db->or_like('lastName',$detail['searchtext'],'after');
			$this->db->or_like('mobile',$detail['searchtext'],'after');
			$this->db->or_like('customerRef',$detail['searchtext'],'after');
			if( $userType != 0)
			$this->db->where('addedBy',$detail['addedBy']);
		}
        $output = $this->db->get();
        $output = $output->result();
		return $output;
    }

	 public function latestCustomer($detail) 
	 {
        $this->db->select('*');
        $this->db->from('skyline_customer');
        $this->db->where('addedBy',$detail['addedBy']);        
		$this->db->order_by('id DESC');
        $this->db->limit('10','0');
        $output = $this->db->get();        
        $output = $output->result();        
		return $output;
    }
	
	 public function customerCars($detail) {
        $this->db->select('cars.*,customer.customerNum,customer.customerRef as customerId,customer.firstName,customer.lastName');
        $this->db->from('skyline_cars cars');
        $this->db->join('skyline_customer customer','cars.customerRef = customer.customerRef','left');
        $this->db->where('cars.customerRef',$detail['customerRef']); 
        $output = $this->db->get();        
        $output = $output->result();        
		return $output;
    }
    
    public function addRejectedRequest($requestDetails){
		$this->db->insert('skyline_partRequestRejected',$requestDetails);
		$msg = $this->db->affected_rows();
		return $msg;
	}
	
	public function addNewpartDepartment($departmentPart)
	{		
		$this->db->insert_batch('skyline_departmentParts',$departmentPart);
		return true;
	}
	
	public function requestForCar($details)
	{
		$this->db->select('requestsRef');
		$this->db->order_by('id','desc');
		$this->db->limit(1);
		$query    		= $this->db->get('skyline_carrequest');		
		$requestsRef  = '';
		$currentYear    = date('Y');	 
		if($query->num_rows() > 0)
		{
			$requestsRef   = $query->row()->requestsRef;
			$Tlen            = strlen($requestsRef);
			$requestsRef   = substr($requestsRef,12,$Tlen);			
			$requestsRef   = str_pad($requestsRef + 1, 4, 0, STR_PAD_LEFT);			
			$requestsRef   = 'Request-'.$currentYear.$requestsRef;
		}
		else
		{
			$requestsRef  = 'Request-'.$currentYear.'0001';
		}
		$details['requestsRef'] = $requestsRef;
		$this->db->insert('skyline_carRequest',$details);
		$rows = $this->db->affected_rows();
		$output['rows'] = $rows;
		$output['refId'] = $requestsRef;
		return $output;
	}
	
	public function log($log)
	{
		$this->db->insert('skyline_requestlog',$log);
		$msg = $this->db->affected_rows();	
		return $msg;
	}
		
	public function getAllEmployee($limit = NULL, $start = NULL, $search = NULL) {
        $this->db->select('(select count(userRef) from skyline_users where status = 1) as totalRecords ,skyline_users.userRef,skyline_users.emailId,skyline_users.createdDate,skyline_userProfile.*');
        $this->db->from('skyline_users');
        $this->db->join('skyline_userProfile', 'skyline_userProfile.userprofileRef=skyline_users.userRef', 'left');
        if ($search != "") 
		{
            $this->db->group_start();
            $this->db->or_like('skyline_users.emailId', $search, 'after');
            $this->db->or_like('CONCAT(skyline_userProfile.firstName," ",skyline_userProfile.lastName )', $search, 'after');
            $this->db->or_like('skyline_userProfile.mobile', $search, 'after');
            $this->db->or_like('skyline_userProfile.address', $search, 'after');
            $this->db->group_end();
        }
        $this->db->where('skyline_users.status', '1');
        $this->db->order_by('skyline_users.id DESC');
        $this->db->limit($limit, $start);
        $output = $this->db->get();
        // echo $this->db->last_query(); die();
        return $output->result();
    }
    
	public function checkEmployeeAvailablity( $data = null )
	{
		$this->db->select('*');
		$this->db->where('employeeRef',$data['employeeRef']);
		$this->db->where('bookingDate',$data['bookingDate']);
		$this->db->where(" endTime >= '$data[startTime]' AND endTime <= '$data[endTime]'");
		$query = $this->db->get('skyline_bookingrequest');
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();
			return false;
		}
		else
			return true;
	}
	
	public function createBookingRequest($formData)
	{
		$this->db->select('bookingNumber');
		$this->db->order_by('id','desc');
		$this->db->limit(1);
		$query    		= $this->db->get('skyline_bookingrequest');		
		$bookingNumber  = '';
		$currentYear    = date('Y');	 
		if($query->num_rows() > 0)
		{
			$bookingNumber   = $query->row()->bookingNumber;
			$Tlen            = strlen($bookingNumber);
			$bookingNumber   = substr($bookingNumber,12,$Tlen);			
			$bookingNumber   = str_pad($bookingNumber + 1, 4, 0, STR_PAD_LEFT);			
			$bookingNumber   = 'Booking-'.$currentYear.$bookingNumber;
		}
		else
		{
			$bookingNumber  = 'Booking-'.$currentYear.'0001';
		}
		
		$formData['bookingdata']['bookingNumber'] =  $bookingNumber;
		$this->db->insert('skyline_bookingrequest',$formData['bookingdata']);
		$msg = $this->db->affected_rows();	
		if($msg == 1)
		{
			$parts	=	$formData['deparmentParts'];
			$rows 	= array();
			for ($i = 0; $i<count($parts); $i++) 
			{	
				$rows[] = array(
					'supplierName' 	=> $parts[$i]['supplierName'],
					'partName' 		=> $parts[$i]['partName'],
					'purchasePrice' => $parts[$i]['purchasePrice'],
					'SalePrice' 	=> $parts[$i]['SalePrice'],
					'requestsRef' 	=> $formData['bookingdata']['requestsRef'],
					'quantity' 		=> $parts[$i]['quantity']
				);
			}
			$this->addNewpartDepartment($rows);
			$this->db->set('status',1);
			$this->db->where('requestsRef',$formData['bookingdata']['requestsRef']);
			$this->db->update('skyline_carRequest');			
		}	
		$response['bookingNumber']  = $bookingNumber;
		$response['msg'] 			= $msg;
		return $response;
	}
	
	/*public function getEmployeeBookings( $employeeRef = null )
	{
		$this->db->select('*');
		$this->db->where('employeeRef',$employeeRef);
		$query  = $this->db->get('skyline_bookingrequest');
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}*/
	
	public function getEmployeeBookings( $employeeRef = null )
	{
		$this->db->select('skyline_bookingrequest.*,skyline_customer.customerRef as custRef,skyline_partRequest.description,skyline_customer.firstName,skyline_customer.lastName');
		$this->db->from('skyline_bookingrequest');		
		$this->db->join('skyline_customer','skyline_customer.customerRef = skyline_bookingrequest.customerRef','inner');		
		$this->db->join('skyline_partRequest','skyline_partRequest.requestRef = skyline_bookingrequest.requestsRef','inner');	
		$this->db->where('skyline_bookingrequest.employeeRef',$employeeRef);
		$output = $this->db->get();
		//echo $this->db->last_query();die;       
		$output = $output->result();        
		return $output;		
	}
	public function getAllDepartments()
	{
		$this->db->select('*');
		$query  = $this->db->get('skyline_departments');
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}
	public function getAllDesignations()
	{
		$this->db->select('*');
		$query  = $this->db->get('skyline_designations');
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}
	
	
	public function getDepartmentsArray( $departmentRef = null )
	{
		$departmentRef = explode(',',$departmentRef);
		$departmentRef = "'" . implode("','", $departmentRef) . "'";	
		$this->db->select('departmentRef,name');
		$this->db->where("departmentRef IN ($departmentRef)");
		$query  = $this->db->get('skyline_departments');
		
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}
	public function getDepartmentEmployees($department = NULL) 
	{
        $this->db->select('skyline_users.userRef,skyline_users.emailId,skyline_users.createdDate,skyline_userProfile.*');
        $this->db->from('skyline_users');
        $this->db->join('skyline_userProfile', 'skyline_userProfile.userprofileRef=skyline_users.userRef', 'left');
        $this->db->where('skyline_users.status', '1');
		if( $department != '' )
			$this->db->where("(FIND_IN_SET('$department', skyline_userProfile.department))");
		//$this->db->where("userType NOT IN ('0,1')");
        $this->db->order_by('skyline_users.id DESC');
        $query = $this->db->get();
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		// echo $this->db->last_query();
		// die;
		return $result;
    }
	
	public function getBookingsByDepartment( $department = null, $searchkey = null )
	{
		$this->db->select("skyline_carRequest.*,skyline_carRequest.status as requestStatus,skyline_cars.carModel,skyline_cars.*,skyline_customer.*");
		$this->db->from('skyline_carRequest');		
		$this->db->join('skyline_customer','skyline_customer.customerRef = skyline_carRequest.customerRef','inner');
		$this->db->join('skyline_cars','skyline_cars.carRef = skyline_carRequest.carRef','inner');		
		$this->db->where('skyline_carRequest.assignedTo',$department);
		$this->db->where('skyline_carRequest.status != ',2);
		if( $searchkey != null )
		{
			$where = "( skyline_cars.carModel LIKE '%$searchkey%' or skyline_customer.firstName LIKE '%$searchkey%' or skyline_customer.lastName LIKE '%$searchkey%' or skyline_carRequest.description LIKE '%$searchkey%')";
			$this->db->where($where);
		}
		$query = $this->db->get();
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}
	
	public function calandarBookings($department = NULL,$employeeRef = NULL, $searchkey = null) 
	{
        $this->db->select("skyline_bookingrequest.*,skyline_carRequest.description,skyline_cars.carModel,skyline_cars.*,skyline_customer.*");
		$this->db->from('skyline_bookingrequest');		
		$this->db->join('skyline_carRequest','skyline_carRequest.requestsRef = skyline_bookingrequest.requestsRef','inner');	
		$this->db->join('skyline_customer','skyline_customer.customerRef = skyline_carRequest.customerRef','inner');		
		$this->db->join('skyline_cars','skyline_cars.carRef = skyline_carRequest.carRef','inner');	
		if( $department == 'qBZfItOgyS2u02vU' )	
		{
			$this->db->where('skyline_carRequest.assignedTo',$department);				
		}
		else if( $department != '')
		{
			$this->db->where('skyline_bookingrequest.transferToDepartment',$department);		
		}
		if( $employeeRef != '')
			$this->db->where('skyline_bookingrequest.employeeRef',$employeeRef);
		
		if( $searchkey != null )
		{
			$where = "( skyline_cars.carModel LIKE '%$searchkey%' or skyline_customer.firstName LIKE '%$searchkey%' or skyline_customer.lastName LIKE '%$searchkey%' or skyline_carRequest.description LIKE '%$searchkey%')";
			$this->db->where($where);
		}
		
		$this->db->where('skyline_bookingrequest.bookingstatus !=', 2);				
		$query = $this->db->get();
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();		
			if( $department != 'qBZfItOgyS2u02vU' )
			{
				foreach( $result as $key=>$val )
				{
					$this->db->select('type');
					$this->db->where('requestsRef',$val->requestsRef);
					$this->db->limit('1');
					$this->db->order_by('bwId','desc');
					$query1 = $this->db->get('skyline_booking_worklog');
					$type   = '';
					if( $query1->num_rows() > 0 )
					{
						$type = $query1->row()->type;
					}
					$result[$key]->type = $type;
				}
			}				
		}
		
		return $result;
    }
		
	function forgotPassword($formData=null)
	{
		$this->db->select('skyline_userProfile.firstName,skyline_userProfile.lastName');
        $this->db->from('skyline_users');
        $this->db->join('skyline_userProfile','skyline_userProfile.userprofileRef=skyline_users.userRef','inner');
        $this->db->where('emailId', $formData['emailId']);
        $query  = $this->db->get();
        $result = $query->row();       
		if(empty($result))
		{
			return '0';
		}
		else
		{
			$password	  			=   str_pad(mt_rand(111111, 999999), 6, '0', STR_PAD_LEFT);
			$salt					= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
			$salt 					= 	base64_encode($salt);
			$salt 					= 	str_replace('+', '.', $salt);
			$hash 					= 	crypt($password, '$2y$10$'.$salt.'$');
			$newPassword			=	$hash;
			
			$this->db->set('password',$newPassword);
			$this->db->where('emailId',$formData['emailId']);
			$this->db->update('skyline_users');
			$db_error = $this->db->error();		
			if ($db_error['code'] == 0) 
			{
				$emailTemplate = $this->getEmailTemplate(2);
				$variables = array( 'receiver_name' 	 => ucfirst($result->firstName).' '.ucfirst($result->lastName),
									'to'				 => $formData['emailId'],
									'newPassword'		 => $password,
								);
				$this->sendEmail($variables,$emailTemplate);
				return '1';
			} 	
			else
				return '2';
		}
	}
	
	function getEmailTemplate($id = NULL) 
	{
        $this->db->select('*');
		$this->db->where('id',$id);
		$query    = $this->db->get('skyline_email_templates');
		$result   = array();
		if($query->num_rows() > 0)
		{
			$result   = $query->row_array();
		}		
		return $result;
    }
	
	function sendEmail($variables,$templateData) 
	{
		$this->email_var = array(
			'logo' 		 	=> '<img style="background-color:#566369; border: 5px #566369 solid; width:200px"  src="'.$this->config->item('logo').'" alt="Logo" >',
			'site_title' 	=> $this->config->item('site_title'),
			'site_url'   	=> site_url(),
			'copyrightText' => $this->config->item('copyrightText')
		);
		
		$this->config_email = Array(
			'protocol'  => "ssl",
			'smtp_host' => "mail.docpoke.com.md-in-59.webhostbox.net",
			'smtp_port' => '587',
			'smtp_user' => 'gurdeep@docpoke.com.md-in-59.webhostbox.net',
			'smtp_pass' => 'Admin@786',
			'mailtype'  => "html",
			'wordwrap'  => TRUE,			
			'crlf'  	=> '\r\n',			
			'charset'   => "utf-8"
		);
		
        $variables    = array_merge($variables,$this->email_var);
		$replacements = array();
		foreach($variables as $key=>$val)
		{
			$replacements['({'.$key.'})'] = $val;
		}
		$template = preg_replace ( array_keys( $replacements ), array_values( $replacements ), $templateData['description'] );
		$this->email->initialize($this->config_email);
		$this->email->set_newline("\r\n");  
		$this->email->from($this->config->item('emailFrom'),$this->config->item('emailFromName'));
		$this->email->to($variables['to']); 
		$this->email->subject($templateData['subject']); 
		$this->email->message($template);
		//echo "<pre>";print_r($this->email);die;
		$this->email->send();
		//echo $this->email->print_debugger();die('lol');
		return true;
    }
		
	function changePassword($formData=null)
	{
		$password	  			=   $formData['password'];
		$salt					= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
		$salt 					= 	base64_encode($salt);
		$salt 					= 	str_replace('+', '.', $salt);
		$hash 					= 	crypt($password, '$2y$10$'.$salt.'$');
		$newPassword			=	$hash;
		
		$this->db->set('password',$newPassword);
		$this->db->where('emailId',$formData['emailId']);
		$this->db->update('skyline_users');
		$db_error = $this->db->error();		
		if ($db_error['code'] == 0) 
			return '1';
		else
			return '0';		
	}
	
	public function addBookingWorklog($formData = null )
	{
		$this->db->set('type',$formData['type']);	
		$this->db->set('time',$formData['time']);	
		$this->db->set('requestsRef',$formData['requestsRef']);	
		$this->db->set('addedDate',date('Y-m-d H:i:s'));	
		$this->db->set('status',1);	
		$this->db->insert('skyline_booking_worklog');
		$msg = $this->db->affected_rows();	 
		return $msg;
	}
	
	public function getBookingWorklogByBookingRef( $ref = NULL ) 
	{
        $this->db->select("*");
		$query = $this->db->get("skyline_booking_worklog");
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
    }
	
	/************ getting requests which are not confirmed yet **********/
	public function getPendingBookings( $searchkey = null )
	{
		$this->db->select("skyline_carRequest.*,skyline_carRequest.status as requestStatus,skyline_cars.carModel,skyline_cars.*,skyline_customer.*");
		$this->db->from('skyline_carRequest');		
		$this->db->join('skyline_customer','skyline_customer.customerRef = skyline_carRequest.customerRef','inner');		
		$this->db->join('skyline_cars','skyline_cars.carRef = skyline_carRequest.carRef','inner');
		$this->db->where('skyline_carRequest.status',0);
		if( $searchkey != null )
		{
			$where = "( skyline_cars.carModel LIKE '%$searchkey%' or skyline_customer.firstName LIKE '%$searchkey%' or skyline_customer.lastName LIKE '%$searchkey%' or skyline_carRequest.description LIKE '%$searchkey%')";
			$this->db->where($where);
		}
		$query = $this->db->get();
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}

	public function addCollision($details)
	{
		$this->db->select('collisionRef');
		$this->db->order_by('id','desc');
		$this->db->limit(1);
		$query    		= $this->db->get('skyline_collision');		
		$collisionRef   = '';
		$currentYear    = date('Y');
		if($query->num_rows() > 0)
		{
			$collisionRef   = $query->row()->collisionRef;
			$Tlen            = strlen($collisionRef);
			$collisionRef   = substr($collisionRef,14,$Tlen);			
			$collisionRef   = str_pad($collisionRef + 1, 4, 0, STR_PAD_LEFT);			
			$collisionRef   = 'collision-'.$currentYear.$collisionRef;
		}
		else
		{
			$collisionRef  = 'collision-'.$currentYear.'0001';
		}
	
		$details['collisionRef']  	= $collisionRef;
		$this->db->insert('skyline_collision',$details);
		$rows = $this->db->affected_rows();
		$output['rows'] = $rows;
		$output['collisionRef'] = $collisionRef;
		return $output;
	}
	
	public function addCollisionStep2($rows)
	{
		$this->db->insert('skyline_collisionimages',$rows);
		$rows = $this->db->affected_rows();
		$output['rows'] = $rows;
		return $output;	
	}

	public function updatecollisionnotes($data)
	{
		$this->db->set('notes',$data['notes']);
		$this->db->where('collisionRef',$data['collisionRef']);
		$this->db->update('skyline_collision');
		$rows = $this->db->affected_rows();
		$output['rows'] = $rows;
		return $output;	
	}
	
	public function listCarRentCompanies()
	{
		$this->db->select("*");
		$query = $this->db->get("skyline_rentcompanies");
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}
	
	public function addrentedcar($details)
	{
		$details['date'] = date('d-m-Y');
		$details['time'] = date('h:i');
		$this->db->insert('skyline_rentedCars',$details);
		$msg = $this->db->affected_rows();	
		return $msg;
	}
	
	public function deleteBooking($where)
	{
		$this->db->where($where);
		$this->db->delete('skyline_bookingrequest');
		$this->db->where($where);
		//echo $this->db->last_query();
		$this->db->delete('skyline_booking_worklog');
		//echo $this->db->last_query();
		$msg = $this->db->affected_rows();	
		return $msg;
	}
	
	public function customerHistory($customerRef)
	{
		$data = $this->db->query("SELECT cus.firstName as customer,creq.status as request_status, creq.description ,creq.createDate as createDate , breq.transferToDepartment , breq.bookingstatus ,breq.bookingDate , breq.requestsRef , breq.workorderStatus , cars.carMake ,cars.carModel, user.firstName as assignedto , bwork.type , bwork.addedDate as completedDate FROM skyline_customer as cus inner join skyline_carRequest as creq on cus.customerRef = creq.customerRef  inner join skyline_cars as cars  on cars.carRef = creq.carRef inner join skyline_bookingrequest as breq on breq.requestsRef = creq.requestsRef inner join skyline_userProfile as user  on breq.employeeRef = user.userprofileRef  left join skyline_booking_worklog as bwork on breq.requestsRef = bwork.requestsRef WHERE cus.`customerRef`='$customerRef'");
		$result = $data->result();
		$data = $this->db->query("SELECT  cs.firstName as customer , collisionRef , insCompanyName ,claimNo from skyline_collision as col INNER join skyline_customer as cs on col.customerRef = cs.customerRef where  cs.`customerRef`='$customerRef'");
		$result2 = $data->result();
		
		$output['data'] = $result;
		$output['collision'] = $result2;
		return $output;
	}
	
	public function updatebookingRequest($formData)
	{
		$this->db->set('bookingDate',$formData['bookingDate']);
		$this->db->set('startTime',$formData['startTime']);
		$this->db->set('endTime',$formData['endTime']);
		$this->db->set('startDate',$formData['startDate']);
		$this->db->set('endDate',$formData['endDate']);
		$this->db->set('employeeRef',$formData['employeeRef']);
		$this->db->set('bookingstatus',$formData['bookingstatus']);
		
		$this->db->where('bookingNumber',$formData['bookingNumber']);
		$this->db->update('skyline_bookingrequest');
		$db_error = $this->db->error();		
		if ($db_error['code'] == 0) 
			return '1';
		else
			return '0';
	}
	
	public function completedBookings()
	{
		$this->db->select("bk.bookingNumber,bk.requestsRef,cs.firstName ,cs.lastName,bk.paymentStatus,bk.invoiceStatus");
		$this->db->from('skyline_bookingrequest as bk');
		$this->db->join('skyline_booking_worklog as bkw','bk.requestsRef=bkw.requestsRef','inner');
		$this->db->join('skyline_customer as cs','cs.customerRef=bk.customerRef','inner');
		$this->db->where('bkw.type','end');
		$this->db->group_by('bk.bookingNumber');
		$output = $this->db->get();
		return $output->result();
	}
	
	public function updatebookingStatus($data)
	{
		$type = $data['type'];
		if( $type == 'invoice')
		$field = "invoiceStatus";
		else if( $type == 'payment')
		$field = "paymentStatus";
		else if( $type == 'bookingstatus')
		$field = "bookingstatus";

		$this->db->set($field,$data['status']);
		$this->db->where('requestsRef',$data['requestsRef']);
		$this->db->update('skyline_bookingrequest');
		$db_error = $this->db->error();		
		if ($db_error['code'] == 0) 
			return '1';
		else
			return '0';	
	}
	
	public function updateCarRequestStatus($data)
	{
		$this->db->set('status',$data['status']);
		$this->db->where('requestsRef',$data['requestsRef']);
		$this->db->update('skyline_carRequest');
		$db_error = $this->db->error();		
		if ($db_error['code'] == 0) 
			return '1';
		else
			return '0';	
	}
	
	public function uploadBookingImage($data)
	{
		$this->db->insert('skyline_BookingImages',$data);
		$msg = $this->db->affected_rows();
		return $msg;
	}
	
	public function bookingImages($data)
	{
		$this->db->select("image");
		$this->db->where($data);
		$query = $this->db->get("skyline_BookingImages");
		$result = $query->result();
		$output['images'] = $result;
		return $output;
	}
	
	public function allCarRequests()
	{
		$this->db->select("creq.carRef , creq.status as bookingStatus , creq.requestsRef , (select skyline_booking_worklog.type from skyline_booking_worklog where skyline_booking_worklog.bwId = (select max(bwId) from skyline_booking_worklog as sbw where sbw.requestsRef = creq.requestsRef)) as status , breq.invoiceStatus as invoiceStatus , breq.paymentStatus as paymentStatus");		
		$this->db->join('skyline_bookingrequest as breq','breq.requestsRef=creq.requestsRef','left');
		$this->db->join('skyline_booking_worklog as blog','breq.requestsRef=blog.requestsRef','left');
		$this->db->from('skyline_carRequest as creq');
		$this->db->group_by('creq.requestsRef');
		$this->db->order_by('creq.id ASC');
		$output = $this->db->get();
		// echo $this->db->last_query();
		return $output->result();		
	}
	
	public function addNote($data)
	{
		$this->db->insert('skyline_notes',$data);
		$msg = $this->db->affected_rows();
		return $msg;
	}
	
	public function getNotes()
	{
		$this->db->select("*");
		$query = $this->db->get("skyline_notes");
		$result = array();
		if( $query->num_rows() > 0 )
		{
			$result = $query->result();			
		}
		return $result;
	}
	
	public function customerFilter($data)
	{
        $this->db->select('*');
        $this->db->from('skyline_customer');
		$this->db->like('firstName',$detail['searchtext'],'after');
		$this->db->or_like('lastName',$detail['searchtext'],'after');
		$this->db->or_like('mobile',$detail['searchtext'],'after');
		$this->db->or_like('customerRef',$detail['searchtext'],'after');
        $output = $this->db->get();
        $output = $output->result();
		return $output;
	}
	
	public function employeeCarRequests($data)
	{
        $this->db->select('*');
        $this->db->from('skyline_carrequest');
        $this->db->where('customerRef',$data['customerRef']);
        $output = $this->db->get();
        $output = $output->result();
		return $output;
	}
	
	public function carAudit($carRef)
	{
		$this->db->select('cars.carNum,cars.carRef , cars.createdDate as carAddDate ,creq.createDate as carReqDate , creq.requestsRef ,breq.bookingDate as carBookingDate  , blog.addedDate as workStartDate');
        $this->db->from('skyline_cars as cars');
		$this->db->join('skyline_carRequest as creq','cars.carRef = creq.carRef','left');
		$this->db->join('skyline_bookingrequest as breq','breq.requestsRef = creq.requestsRef','left');
		$this->db->join('skyline_booking_worklog as blog','blog.requestsRef = breq.requestsRef','left');
		//$this->db->group_by('blog.requestsRef');
        $this->db->where('cars.carRef',$carRef);
		$data = $this->db->get();
		$output['carData'] = $data->result();
		
		// $data = $this->db->query("SELECT  cs.firstName as customer , collisionRef , insCompanyName ,claimNo from skyline_collision as col INNER join skyline_customer as cs on col.customerRef = cs.customerRef where  cs.`customerRef`='$customerRef'");
		// $output['collision'] = $data->result();
		return $output;
	}
	
	public function partHistory()
	{
       $this->db->select('breq.bookingNumber ,  parts.partName , parts.purchasePrice ,parts.SalePrice, parts.quantity,  CONCAT ( cus.firstName ," ' . '",cus.lastName ) as customerName , breq.requestsRef');
        $this->db->from('skyline_departmentparts as parts');
		$this->db->join('skyline_bookingrequest as breq','breq.requestsRef = parts.requestsRef','left');
		$this->db->join('skyline_customer as cus','breq.customerRef = cus.customerRef','left');
        $output = $this->db->get();
        $output = $output->result();
		return $output;
	}
	
	public function addInvoicePDF($data)
	{
		$this->db->insert('skyline_invoicepdfs',$data);
		$msg = $this->db->affected_rows();	
		return $msg;
	}
	
	public function partsByBooking($requestsRef)
	{
		$this->db->select("parts.partName , parts.supplierName , parts.purchasePrice , parts.SalePrice,parts.quantity");
		$this->db->from('skyline_departmentParts as parts');
		$this->db->join('skyline_bookingrequest as breq','breq.requestsRef = parts.requestsRef');
		$this->db->where('breq.requestsRef',$requestsRef);
		$dataa = $this->db->get();
		$result3 = $dataa->result();
		$output['parts'] = $result3;	
		return $output;
	}
	
	public function generateWorkOrder($data)
	{
		$this->db->select('workOrderNumber');
		$this->db->order_by('id','desc');
		$this->db->limit(1);
		$query    		= $this->db->get('skyline_workorders');		
		$workOrderNumber  = '';
		$currentYear    = date('Y');	 
		if($query->num_rows() > 0)
		{
			$workOrderNumber   = $query->row()->workOrderNumber;
			$Tlen          		= strlen($workOrderNumber);
			$workOrderNumber   = substr($workOrderNumber,14,$Tlen);			
			$workOrderNumber   = str_pad($workOrderNumber + 1, 4, 0, STR_PAD_LEFT);			
			$workOrderNumber   = 'workOrder-'.$currentYear.$workOrderNumber;
		}
		else
		{
			$workOrderNumber  = 'workOrder-'.$currentYear.'0001';
		}

		$this->db->set('workOrderNumber',$workOrderNumber);	
		
		$this->db->insert('skyline_workorders',$data);
		$rows = $this->db->affected_rows();	
		$output['rows'] = $rows;
		return $output;
	}
	
	public function updatebookingworkorderStatus($requestsRef)
	{
		$this->db->set('workorderStatus',1);
		$this->db->where('requestsRef',$requestsRef);
		$this->db->update('skyline_bookingrequest');
		$db_error = $this->db->error();		
		if ($db_error['code'] == 0)
			return '1';
		else
			return '0';	
	}
	
	public function removepartDepartment($requestsRef)
	{
		$this->db->where('requestsRef',$requestsRef);
		$this->db->delete('skyline_departmentParts');
		$msg = $this->db->affected_rows();	
		return $msg;
	}
	
	public function skyline_insmisparts($data)
	{
		$this->db->insert('skyline_insmisparts',$data);
		$rows = $this->db->affected_rows();	
		$output['rows'] = $rows;
		return $output;		
	}

}
?>