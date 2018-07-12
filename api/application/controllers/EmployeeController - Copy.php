<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmployeeController extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 public function __construct() {
        parent::__construct();
        $this->load->Model('common');
        $this->perPageNum = 20;
		$this->load->library('zip');
        //$this->load->library('pagination');
    }
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	/* employee create api */
	public function createNewEmployee()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
		
        if(!empty($postData))
		{
			$userType 	 = $this->common->getUserTypeByDesignationRef($postData['designation']);
			$userDetail['id'] 			= isset( $postData['id'] ) ? $postData['id'] : '';
			$userDetail['userType']		= $userType;
			$userDetail['emailId']		= $postData['emailId'];
			
			$userDetail['createdDate']  = $postData['createdDate'];
			$userDetail['status'] 		= $postData['status'];			
			
			
			$userProfile['firstName'] 		= $postData['firstName'];
			$userProfile['lastName'] 		= $postData['lastName'];
			$userProfile['mobile'] 			= $postData['mobile'];
			$userProfile['department'] 		= $postData['department'];
			$userProfile['designation'] 	= $postData['designation'];
			if( $userDetail['id'] <= 0 )
			{
				$password	  			=   str_pad(mt_rand(111111, 999999), 6, '0', STR_PAD_LEFT);
				$salt					= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
				$salt 					= 	base64_encode($salt);
				$salt 					= 	str_replace('+', '.', $salt);
				$hash 					= 	crypt($password, '$2y$10$'.$salt.'$');
				$userDetail['password']	=	$hash;
			}
			$output 				= $this->common->createEmployee($userDetail,$userProfile);
			if($output == 0)
			{
				$result["error"] 	= 'Data not insert.Please fill detail properly.';
			}
			if($output == 1)
			{
				$emailTemplate = $this->common->getEmailTemplate(3);
				$variables = array
					(
						'receiver_name' 	=> ucfirst($userProfile['firstName']).' '.ucfirst($userProfile['lastName']),
						'email'				=> $userDetail['emailId'],
						'password'			=> $userDetail['password'],
						'to'				=> $userDetail['emailId'],
					);
				$this->common->sendEmail($variables,$emailTemplate);
				$result["success"] 	= 'Employee created successfully.';
			}
			if($output == 2)
			{
				$result["error"] 	= 'Email already exist.';
			}
			if($output == 3)
			{
				$result["success"] 	= 'Employee updated successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	/* employee login api */
	
	public function employeeLogin()
	{
		$output		=	'';
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData)){
			$result = $this->common->loginEmployee($postData);
			if(count($result) > 0)
			{
				if (password_verify($postData['password'], $result[0]->password)) 
				{
					unset($result[0]->password);		
					if( $result[0]->department != '' )
					{
						$result[0]->department = $this->common->getDepartmentsArray($result[0]->department);
					}
					else
					{
						$result[0]->department = array();
					}
					$output['successfully']	=	'Login successfully';
					$output['data']			=	$result;
				}
				else
				{
					$output['error']		=	'Please Enter Correct Password.';
				}					
			}
			else
			{
				$output['error']		=	'Please Enter Correct Username.';
			}
		}
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}
	
	/* add new customer api */
	
	public function createCustomer()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
         if(!empty($postData)){			
			$output 						=  $this->common->addCustomer($postData);
			
			if($output[0] == 0){
				$result["error"] 			=  'Data not save.Please fill detail properly.';
			}
			if($output[0] == 1){
				$result["success"] 			=  'Customer created successfully.';
				$result["customerNumber"] 	=  $output[1];
				$result["id"] 				=  $output[2];
			}
			if($output[0] == 2){
				$result["error"] 			=  'Email already exist.';
			}
			if($output[0] == 3){
				$result["success"] 			=  'Customer updated successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	/* add new car api */
	
	public function createCar()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
         if(!empty($postData)){			
			$output 					=  $this->common->addCar($postData);
			if($output[0] == 0){
				$result["error"] 		=  'Data not save.Please fill detail properly.';
			}
			if($output[0] == 1){
				$result["success"] 		=  'Car created successfully.';
				$result["carNumber"] 	=  $output[1];
				$result["id"] 			=  $output[2];
			}
			if($output[0] == 2){
				$result["error"] 		=  'License plate already exist.';
			}
			if($output[0] == 3){
				$result["success"] 			=  'Car updated successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	/* search customer api */
	
	public function searchCustomers()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
         if(!empty($postData)){		
			$output['data']		=  $this->common->searchCustomer($postData);			
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
	
	/* get latest customer api */	
	
	public function latestCustomers(){
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
         if(!empty($postData)){			
			$output['data']		=  $this->common->latestCustomer($postData);
						
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
	
	/* get cars by customer id api */
	
		public function selectCars(){
			$rest_json	=	file_get_contents("php://input");
			$postData	=	json_decode($rest_json, true);
			 if(!empty($postData)){			
				$output['data']		=  $this->common->customerCars($postData);			
				header('Content-Type: application/json');
				echo json_encode($output);exit;
			}
	}
	
	
	/* part request rejected api */
	
	public function partRequestRejected()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
		
		if(!empty($postData))
		{
			$output 				=  $this->common->addRejectedRequest($postData);
			$data['type']	= 'bookingstatus';
			$data['status']	= 2;
			$data['requestsRef'] = $postData['requestRef'];
			$this->common->updatebookingStatus($data);
			$this->common->updateCarRequestStatus($data);
			
			$log = array
				(
					'requestsRef'		=> $postData['requestRef'],
					'type' 				=> 'service',
					'action' 			=> 'booking_canceled',
				);
				$this->common->log($log);
			if($output == 0){
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			if($output == 1){
				$result["success"] 	=  'Part request rejected description submitted successfully.';
			}			
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
		
	/* make new car request api */
	public function makeNewRequest()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output						= $this->common->requestForCar($postData);	

			$log = array
				(
					'type' 				=> 'service',
					'action' 			=> 'carrequest',
					'datetime'			=> $postData['createDate'],
					'addedBy'			=> $postData['addedBy']
				);
			$this->common->log($log);
			
			if($output['rows'] == 0)
			{
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			
			if($output['rows'] == 1)
			{
				$result["success"] 	=  'Car request created successfully.';
				$result['refId'] 	=	$output['refId'];
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	
	/*
     * Get Employee All Employee Listing with Searching
     */
     
     public function getAllEmployee(){
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
		$data['page'] = ($postData["page"]) ? $postData["page"] : 0;
        $data['search'] = ($postData["search"]) ? $postData["search"] : '';
		if(!empty($data))
		{			
			$output['data']	=  $this->common->getAllEmployee($this->perPageNum, $data['page'], $data['search']);			
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
    
	/*****  *****/
	
	public function bookingRequest()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
		
        if(!empty($postData))
		{
			$output					=  $this->common->checkEmployeeAvailablity($postData['bookingdata']);	
			if(!$output)
			{
				$result["error"] 	=  'Selected employee is not available at the selected booking time.';
			}
			
			if($output == 1)
			{
				$output = '';
				$output					=  $this->common->createBookingRequest($postData);
				$log = array
					(
						'requestsRef'		=> $postData['bookingdata']['requestsRef'],
						'type' 				=> 'service',
						'action' 			=> 'bookingrequest',
						'datetime'			=> $postData['bookingdata']['createDate']
					);
				$this->common->log($log);
				if($output['msg'] == 0)
				{
					$result["error"] 	=  'Data not save.Please fill detail properly.';
				}
				if($output['msg'] == 1)
				{
					$result["success"] 	=  $output['bookingNumber'];
				}
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	public function getBookingsByDepartment()
	{
		$rest_json	 = file_get_contents("php://input");
        $postData	 = json_decode($rest_json, true);
		$department  = $postData['department'];
		$searchkey   = $postData['searchkey'];
       /* if(!empty($department))
		{*/			
			if( $department == 'qBZfItOgyS2u02vU' )
				$output['data']	=  $this->common->getBookingsByDepartment($department,$searchkey);	
			else	
				$output['data']	=  $this->common->calandarBookings($department,'',$searchkey);			
			$output['departmentEmployees']	=  $this->common->getDepartmentEmployees($department);			
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		//}
	}
	
	public function calandarBookings()
	{
		$rest_json	 = file_get_contents("php://input");
        $postData	 = json_decode($rest_json, true);
		$department  = $postData['department'];
		$employeeRef = $postData['employeeRef'];
		$searchkey   = $postData['searchkey'];
        if(!empty($department))
		{
			$calandarBookings	=  $this->common->calandarBookings($department,$employeeRef,$searchkey);
			$collisionSchedules	=  $this->common->collisionSchedules($employeeRef,$searchkey,'');
			$collisionWork	=  $this->common->collisionWork($department,$employeeRef,$searchkey);
			$output['data'] = array_merge($calandarBookings,$collisionSchedules);
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
	
	public function addBookingWorklog()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);		
        if(!empty($postData))
		{
			$output = '';
			$output	=  $this->common->addBookingWorklog($postData);
			if($output == 0)
			{
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			if($output == 1)
			{
				if( $postData['type'] == 'start' )
					$result["success"] 	=  'Booking time started successfully.';
				else if( $postData['type'] == 'pause' )
					$result["success"] 	=  'Booking time paused successfully.';
				else
					$result["success"] 	=  'Booking time end successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	/***** getting booking worklog data by request/booking ref ****/
	public function bookingWorkLog()
	{
		$rest_json	  = file_get_contents("php://input");
        $postData	  = json_decode($rest_json, true);
		$requestsRef  = $postData['requestsRef'];
        if(!empty($requestsRef))
		{			
			$output['data']	=  $this->common->getBookingWorklogByBookingRef($requestsRef);					
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
		else
		{
			$output["error"] 	=  'Booking reference is missing.';
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
	
	public function getAllPendingBookings()
	{
		$rest_json	  	= file_get_contents("php://input");
        $postData	  	= json_decode($rest_json, true);
		$searchkey    	= $postData['searchkey'];
		$output['data']	= $this->common->getPendingBookings($searchkey);			
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}
	
	/* make new collision api */
	public function addCollision()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			// print_r ($postData );
			// die;
			$toebillattach = $postData['toebillattach'];
			$insCopyattach = $postData['insCopyattach'];
			unset($postData['toebillattach']);
			unset($postData['insCopyattach']);
			
			$base_64 = base64_decode( $toebillattach );  
			$image_file = 'toebill_'.date(strtotime(date('d-m-Y h:i:s'))).rand(1,100); 
			$toeBillName = $image_file.".jpg";
			$base_path = './uploads/';
			
			file_put_contents($base_path.$toeBillName, $base_64);
			$postData['toeBillName'] = $toeBillName;
			
			$base_64 = base64_decode( $insCopyattach );  
			$image_file = 'inscopy_'.date(strtotime(date('d-m-Y h:i:s'))).rand(1,100); 
			$insCopyattach = $image_file.".jpg";
			$base_path = './uploads/';
			
			file_put_contents($base_path.$insCopyattach, $base_64);
			$postData['insCopyname'] = $insCopyattach;

			$postData['date'] = date('d-m-Y');
			$postData['time'] = date('h:i');
			$output					=  $this->common->addCollision($postData);	
			if($output['rows'] == 0)
			{
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			if($output['rows'] == 1)
			{
				$result["success"] 		=  'Collision Detail Saved successfully.';
				$result["collisionRef"] = $output["collisionRef"];
				$result["insCopyname"]	= $postData["insCopyname"];
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	
	public function addCollisionStep2()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$base_64 = base64_decode( $postData['image'] );  
			$image_file = $postData['collisionRef'].'_img'.date(strtotime(date('d-m-Y h:i:s'))).rand(1,100); 
			$img = $image_file.".jpg";
			$base_path = './uploads/';
			file_put_contents($base_path.$img, $base_64);
			$postData['image'] = $img;
			
			$zip = new ZipArchive();
			if ( file_exists($postData['collisionRef'].'.zip') )
			{
				$zip->open($base_path.$postData['collisionRef'].'.zip');				
			}
			else
			{
				$zip->open($base_path.$postData['collisionRef'].'.zip', ZipArchive::CREATE);
			}
			
			$ret = $zip->addFile('uploads/'.$img,$img);
			$zip->close();
			// if($ret) unlink('./uploads/'.$img);
			
			if( isset($postData['notes'])  &&  !empty($postData['notes']) )
			{
				$data['note'] = $postData['notes'];
				$data['addedBy'] = $postData['addedBy'];
				unset($postData['notes']);
				unset($postData['addedBy']);
				$data['type'] = 'collision';
				$data['reference'] = $postData['collisionRef'];
				$data['date'] = date('d-m-Y');
				$data['time'] = date('h:i');
				$this->common->addNote($data);
			}
			
			$output =  $this->common->addCollisionStep2($postData);
			if($output['rows'] == 0)
			{
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			if($output['rows'] == 1)
			{
				$result["success"] 		=  'Collision Images Uploaded successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}

	
	public function listCarRentCompanies()
	{
		$output['data']	=  $this->common->listCarRentCompanies();			
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}
	
	/* make new collision api */
	public function addrentedcar()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output					=  $this->common->addrentedcar($postData);	
			if($output == 0)
			{
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			if($output == 1)
			{
				$result["success"] 	=  'Details Saved successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	public function deleteBooking()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output = $this->common->deleteBooking($postData);	
			if($output == 0)
			{
				$result["error"] 	=  'Unknown error occured';
			}
			else
			{
				$result["success"] 	=  'Booking Deleted successfully.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
		
	}
	
	public function customerHistory()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output = $this->common->customerHistory($postData);
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
		else
		{
			$output = array('error'=>"Customer Reference is missing");
			header('Content-Type: application/json');
			echo json_encode($output);exit;			
		}
	}
	
	public function updatebookingRequest()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$log = array
				(
					'requestsRef'		=> $postData['bookingNumber'],
					'type' 				=> 'service',
					'datetime'			=> $postData['startDate'],
				);
				
			if( $postData['status'] == 0 )
				$log['action'] = 'booking_schedule';
			else if( $postData['status'] == 1 )
				$log['action'] = 'booking_added';
			else if( $postData['status'] == 2 )
				$log['action'] = 'booking_canceled';
			
			$this->common->log($log);
			
			$response = $this->common->updatebookingRequest($postData);
			if( $response == 1 )
				$result["success"] 	=  'Booking has been Updated successfully';
			else if( $response == 2 )
				$result["error"] 	=  'Something happens wrong. Please try again.';
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	public function completedBookings()
	{
		$response = $this->common->completedBookings();
		if(!empty($response))
		{
			$output['data']	=  $response;		
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
	
	public function updatebookingStatus()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$type = $postData['type'];
			$response = $this->common->updatebookingStatus($postData);
			if(!empty($response))
			{
				if( $response == 1 )
				$result["success"] 	=  'Booking '.$type.' Status has been Updated successfully';
				else if( $response == 2 )
				$result["error"] 	=  'Something happens wrong. Please try again.';
				header('Content-Type: application/json');
				echo json_encode($result);exit;
			}
		}
	}
	
	public function allCarRequests()
	{
		$response = $this->common->allCarRequests();
		if(!empty($response))
		{
			header('Content-Type: application/json');
			echo json_encode($response);exit;
		}
	}
	
	public function uploadBookingImage()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$refKey 	 = $this->common->generateRefKey();
			$base_64 = base64_decode( $postData['image'] );  
			$image_file = "carrequest_".$refKey; 
			$pic = $image_file.".jpg";
			$base_path = './uploads/';
			
			file_put_contents($base_path.$pic, $base_64);
			$postData['image'] = $pic;
			$output['msg']	=  $this->common->uploadBookingImage($postData);
			
			if($output['msg'] == 0)
			{
				$result["error"] 	=  'Data not save.Please fill detail properly.';
			}
			if($output['msg'] == 1)
			{
				$result["success"] 	= 'Booking Image has been Uploaded successfully';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	public function bookingImages()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output = $this->common->bookingImages($postData);
			header('Content-Type: application/json');
			echo json_encode($output);exit;
		}
	}
	
	public function addNote()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$postData['date'] = date('d-m-Y');
			$postData['time'] = date('h:i');
			$output['lastid'] = $this->common->addNote($postData);
			if($output['lastid'] != 0)
			{
				$result["success"] 	=  'Note has been saved successfully.';
			}
			if($output['lastid'] == 0)
			{
				$result["error"] 	= 'Data not save.Please fill detail properly.';
			}			
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	public function getNotes()
	{
		$data['notes']= $this->common->getNotes();
        if(!empty($data))
		{
			header('Content-Type: application/json');
			echo json_encode($data);exit;
		}
	}
	
	public function customerFilter()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$postData['addedBy'] = 0;
			$data['customers']= $this->common->searchCustomer($postData);
			$data['employees']= $this->common->getAllEmployee('','',$postData['searchtext']);
			header('Content-Type: application/json');
			echo json_encode($data);exit;
		}
	}
	
	public function employeeCarRequests()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data['employeeCarRequests']= $this->common->employeeCarRequests($postData);
			header('Content-Type: application/json');
			echo json_encode($data);exit;
		}
	}
	
	public function carAudit()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data= $this->common->carAudit($postData['carRef']);
			header('Content-Type: application/json');
			echo json_encode($data);exit;
		}
	}
	
	public function partHistory()
	{
		$data= $this->common->partHistory();
		header('Content-Type: application/json');
		echo json_encode($data);exit;
	}
	
	public function uploadInvoicePDF()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$base_64 = base64_decode( $postData['pdf'] );  
			$image_file = 'Invoice_'.$postData['requestsRef']; 
			$pdfname = $image_file.".pdf";
			$base_path = './uploads/';
			$postData['pdfname'] = $pdfname;
			unset( $postData['pdf'] );
			$bytes = file_put_contents($base_path.$pdfname, $base_64);
			$rows= $this->common->addInvoicePDF($postData);
			if( $rows !=0 && $bytes !=0 )
			{
				$data['success']	= "Pdf has been uploaded successfully";
				$data['link'] 		= base_url().'uploads/'.$pdfname;
			}
			else
			{
				$data['error']	= "Error in uploading , Try Again";
			}
				header('Content-Type: application/json');
				echo json_encode($data);exit;
		}
	}
	
	public function partsByBooking()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data= $this->common->partsByBooking($postData['requestsRef']);
			if(!empty($data))
			{
				header('Content-Type: application/json');
				echo json_encode($data);exit;
			}
		}
	}
	
	public function generateWorkOrder()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$base_64 = base64_decode( $postData['signature_file'] );  
			$image_file = 'Signature_'.$postData['requestsRef']; 
			$signature_file = $image_file.".png";
			$base_path = './uploads/';
			unset( $postData['signature_file'] );
			$postData['signature_file'] = $signature_file;
			$bytes = file_put_contents($base_path.$signature_file, $base_64);

			$parts	=	$postData['deparmentParts'];
			unset( $postData['deparmentParts'] );
			$output = $this->common->generateWorkOrder($postData);
			$updatestatus = $this->common->updatebookingworkorderStatus($postData['requestsRef']);
			
			$rows 	= array();
			foreach( $parts as $key => $part )
			{
				for ($i = 0; $i<count($part); $i++) 
				{
					$rows[] = array(
						'supplierName' 	=> $part[$i]['supplierName'],
						'partName' 		=> $part[$i]['partName'],
						'purchasePrice' => $part[$i]['purchasePrice'],
						'SalePrice' 	=> $part[$i]['SalePrice'],
						'requestsRef' 	=> $postData['requestsRef'],
						'quantity' 		=> $part[$i]['quantity'],
						'optionNo' 		=> $key
					);
				}
			}
			
			$this->common->removepartDepartment($postData['requestsRef']);
			$this->common->addNewpartDepartment($rows);
			
			if($output['rows'] == 1 && $updatestatus)
			{
				$result["success"] 	=  'Work order has been Created successfully.';
			}
			
			if($output['rows'] == 0)
			{
				$result["error"] 	= 'Data not save.Please fill detail properly.';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}
	
	public function skyline_insurancemisparts()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output = $this->common->skyline_insurancemisparts($postData);
			if($output['rows'] == 1)
			{
				$result["success"] 	=  'Data Saved successfully.';
			}
			
			if($output['rows'] == 0)
			{
				$result["error"] 	= 'Data not save.Please fill detail properly.';
			}
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}
	
	public function collisionList()
	{
		$collisionList['collisionList'] = $this->common->collisionList();
		if(!empty($collisionList))
		{
			header('Content-Type: application/json');
			echo json_encode($collisionList);exit;
		}
	}
	
	public function collisionDetails()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$collisionDetails = $this->common->collisionDetails($postData['collisionRef']);
			if(!empty($collisionDetails))
			{
				$result 	= $collisionDetails;
			}
			else
			{
				$result 	= array();
			}
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}
	
	public function addSchedule()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data['note'] = $postData['notes'];
			$data['addedby'] = $postData['addedby'];
			unset($postData['notes']);
			$data['type'] = 'collision-schedule';
			$data['reference'] = $postData['collisionRef'];
			$data['date'] = date('d-m-Y');
			$data['time'] = date('h:i');
			$noteID = $this->common->addNote($data);
			$this->common->update( array('collisionRef' => $postData['collisionRef']) , array ( 'collisionStatus' => 1) ,'skyline_collision');
			$postData['noteID'] = $noteID;
			$output = $this->common->addSchedule($postData);

			if($output['rows'] == 1)
			{
				$result["success"] 	=  'Data Saved successfully.';
			}
			
			else if($output['rows'] == 0)
			{
				$result["error"] 	= 'Data not save.Please fill detail properly.';
			}
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;		
	}
	
	//$where,$data,$table;
	public function collisionFinalVisit()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data['note'] = $postData['notes'];
			unset($postData['notes']);
			$output['rows'] = $this->common->update( array('collisionRef' => $postData['collisionRef']),array('collisionStatus' => 2),'skyline_collision');
			$finalschedule = $this->common->getfinalschedule($postData['collisionRef']);
			if($output['rows'] == 1)
			$output['rows'] = $this->common->update( array('scheduleNo' => $finalschedule, 'collisionRef' => $postData['collisionRef'] ),array('scheduleStatus' => 1),'skyline_schedule');
		
			$data['addedby'] = $postData['addedby'];
			$data['type'] = 'collision-visit';
			$data['reference'] = $postData['collisionRef'];
			$data['date'] = date('d-m-Y');
			$data['time'] = date('h:i');
			$noteID = $this->common->addNote($data);
			if($output['rows'] == 1)
			{
				$result["success"] 	=  'Collision updated successfully.';
			}
			
			else if($output['rows'] == 0)
			{
				$result["error"] 	= 'Something happens wrong. Please try again.';
			}
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}
	
	public function searchall()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$postData['addedBy'] = 0;
			$data['Customers']= $this->common->searchCustomer($postData);
			$data['Employees']= $this->common->getAllEmployee('','',$postData['searchtext']);
			$data['Collision']= $this->common->searchCollisions($postData['searchtext']);
			$data['Requests']= $this->common->searchRequests($postData['searchtext'],'request'); // car detail
			$data['Services']= $this->common->searchRequests($postData['searchtext'],'service');
			$data['PartDepartment']= $this->common->searchRequests($postData['searchtext'],'partDepartment');
			foreach( $data as $key => $val)
			{
				foreach( $val as  $vl)
				{
					$vl->Customfield = $key;
				}
			}
		}
		else
		{
				$data["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($data);exit;
	}
	
	public function  uploadCollisionEstimate()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$base_64 = base64_decode( $postData['pdf'] );  
			$image_file = 'Estimate-'.$postData['collisionRef'].'_'.date(strtotime(date('d-m-Y h:i:s'))).rand(1,100); 
			$pdf = $image_file.".pdf";
			$base_path = './uploads/';
			
			file_put_contents($base_path.$pdf, $base_64);
			$postData['pdf'] = $pdf;
			$data['note'] = $postData['notes'];
			unset($postData['notes']);
			$output['last']= $this->common->insert('skyline_collisionestimates',$postData);
			
			$data['addedby'] 	= $postData['addedby'];
			$data['type'] 		= 'collision-estimate';
			$data['reference'] 	= $postData['collisionRef'];
			$data['date'] 		= date('d-m-Y');
			$data['time'] 		= date('h:i');
			$noteID = $this->common->addNote($data);
			$this->common->update( array('collisionRef' => $postData['collisionRef']),array('collisionStatus' => 3),'skyline_collision');
			
			if($output['last'] != 0)
			{
				$result["success"] 	=  'Collision updated successfully.';
			}
			
			else if($output['rows'] == 0)
			{
				$result["error"] 	= 'Something happens wrong. Please try again.';
			}			
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;		
	}
	
	public function collisionWorkassign()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data['note'] = $postData['notes'];
			unset($postData['notes']);
			
			$lastUniqueRef = $this->common->lastUniqueRef('collisionWorkNum', array('collisionRef' => $postData['collisionRef']),'skyline_collisionworkassign','colWork');
			$postData['collisionWorkNum'] = $lastUniqueRef;
			
			$finalschedule = $this->common->insert('skyline_collisionworkassign',$postData);
			$output['rows'] = $this->common->update( array('collisionRef' => $postData['collisionRef']),array('collisionStatus' => 4),'skyline_collision');
		
			$data['addedby'] = $postData['addedby'];
			$data['type'] = 'collision-work';
			$data['reference'] = $postData['collisionRef'];
			$data['date'] = date('d-m-Y');
			$data['time'] = date('h:i');
			$noteID = $this->common->addNote($data);
			if($output['rows'] == 1)
			{
				$result["success"] 	=  'Collision updated successfully.';
			}
			
			else if($output['rows'] == 0)
			{
				$result["error"] 	= 'Something happens wrong. Please try again.';
			}
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}
	
	public function  generateCollisionWorkOrder()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$base_64 = base64_decode( $postData['pdf'] );  
			$image_file = 'Work-'.$postData['collisionRef'].'_'.date(strtotime(date('d-m-Y h:i:s'))).rand(1,100); 
			$pdf = $image_file.".pdf";
			$base_path = './uploads/';
			
			file_put_contents($base_path.$pdf, $base_64);
			$postData['pdf'] = $pdf;

			$lastUniqueRef = $this->common->lastUniqueRef('CWorkOrderNo', array('collisionRef' => $postData['collisionRef']),'skyline_collisionworkorders','colWorkOrder');
			$postData['CWorkOrderNo'] = $lastUniqueRef;
			$postData['date'] 		= date('Y-m-d');
			$postData['time'] 		= date('h:i');

			$output['last']= $this->common->insert('skyline_collisionworkorders',$postData);
			
			$this->common->update( array('collisionRef' => $postData['collisionRef']),array('collisionStatus' => 3),'skyline_collision');
			
			if($output['last'] != 0)
			{
				$result["success"] 	=  'Collision Work Order has been Created successfully.';
			}
			
			else if($output['rows'] == 0)
			{
				$result["error"] 	= 'Something happens wrong. Please try again.';
			}			
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;		
	}
	
	public function  getCollisionWorkOrder()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$data= $this->common->getCollisionWorkOrder($postData['collisionRef']);
			if(!empty($data))
			{
				$result["data"] 	= $data;
			}
			else
			{
				$result["data"] 	= array();				
			}
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;		
	}
	
	public function  changeCollisionStatus()
	{
		$rest_json	=	file_get_contents("php://input");
        $postData	=	json_decode($rest_json, true);
        if(!empty($postData))
		{
			$output = $this->common->update( array('collisionRef' => $postData['collisionRef']),array('collisionStatus' => 1),'skyline_collision');
			
			if($output == 1)
			{
				$result["success"] 	=  'Collision Status has been updated successfully.';
			}
			
			else if($output == 0)
			{
				$result["error"] 	= 'Something happens wrong. Please try again.';
			}			
		}
		else
		{
				$result["error"] 	= 'No Input Data';
		}
			header('Content-Type: application/json');
			echo json_encode($result);exit;		
	}
	
	
	/* make new collision api */
	// public function addCollisionWithoutInsurance()
	// {
		// $rest_json	=	file_get_contents("php://input");
        // $postData	=	json_decode($rest_json, true);
        // if(!empty($postData))
		// {

			// $postData['date'] = date('d-m-Y');
			// $postData['time'] = date('h:i');
			// $output					=  $this->common->addCollisionWithoutInsurance($postData);	
			// if($output['rows'] == 0)
			// {
				// $result["error"] 	=  'Data not save.Please fill detail properly.';
			// }
			// if($output['rows'] == 1)
			// {
				// $result["success"] 		=  'Collision Detail Saved successfully.';
				// $result["collisionWIRef"] = $output["collisionWIRef"];
			// }
			// header('Content-Type: application/json');
			// echo json_encode($result);exit;
		// }
	// }	
}
?>