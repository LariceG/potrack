<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
class UserController extends CI_Controller {

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


	 public function __construct()
	 {
		  parent::__construct();
		  $this->load->Model('common');
			$this->load->library('zip');
			$this->load->library('encrypt');
		  //$this->load->library('pagination');
    }

		public function updateClient()
		{
			$json 	= 	file_get_contents("php://input");
	    $postData 	= 	json_decode($json, true);
			if(!empty($postData))
			{
				$cl = $postData['cl'];
				unset($postData['cl']);
				$res = $this->common->update( array( 'clientId' => $cl ) , $postData  , 'client');
				if($res == 1)
				{
					$result["success"] 	= 'Client Updated';
				}
				else
				{
					$result["error"] 	= 'something wrong';
				}
			}
			header('Content-Type: application/json');
			echo json_encode($result);
			exit;
		}


	public function createNewEmployee()
	{
			$postData	=	json_decode($_POST['data'], true);
			if(!empty($postData))
			{
				$postData= array_change_key_case($postData,CASE_UPPER);
				// print_r( $postData );
				$date  = date("d M Y H:i:s");
				$date1 = date("d M Y H:i:s");
				$postData['COM_REF'] 				 = strtotime($date) . rand(0,9999);
				$user['USER_NAME'] 					 = $postData['USER_NAME'];
				$user['USER_PASSWORD'] 			 = $postData['ACCOUNT']['user_password'];
				$user['timeZone'] 					 = $postData['TIMEZONE'];
				$postData['COM_SIGNUP_DATE'] = date("d M Y H:i:s");
				unset($postData['ACCOUNT']);
				unset($postData['USER_NAME']);
				unset($postData['USER_PASSWORD']);
				unset($postData['RPASSOWRD']);
				unset($postData['TIMEZONE']);
				$password=$user['USER_PASSWORD'];
				$salt						= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
				$salt 					= 	base64_encode($salt);
				$salt 					= 	str_replace('+', '.', $salt);
				$hash 					= 	crypt($user['USER_PASSWORD'], '$2y$10$'.$salt.'$');
				$user['USER_PASSWORD']	=	$hash;

				$emailexist 				  = $this->common->checkexist('dibcase_company',array('COM_EMAIL' => $postData['COM_EMAIL']));
				$usernameexist 				= $this->common->checkexist('dibcase_users',array('USER_NAME' => $user['USER_NAME']));
				if( $emailexist == 0 && $usernameexist == 0 )
				{
					$output 				= $this->common->insert('dibcase_company',$postData);
					if($output[0] == 1)
					{
							$emp['COM_REF'] 	= $postData['COM_REF'];
							$emp['EMP_REF'] 	=  strtotime($date) . rand(0,9999);
							$emp['EMP_NAME'] 	= $postData['COM_NAME'].' admin';
							$emp['EMP_COMPANY_EMAIL'] 	= $postData['COM_EMAIL'];
							$output = $this->common->insert('dibcase_employees',$emp);
							if($output[0] == 1)
							{
									$date = date("d M Y H:i:s");
									$date1 = date("d M Y H:i:s");
									$user['USER_REF'] = strtotime($date) . rand(0,9999);
									$user['EMP_REF'] 		=  $emp['EMP_REF'];
									$user['USER_STATUS'] 	=  2;
									$user['USER_VERIFIED'] 	= 0 ;

									$user['USER_ROLE'] 		=  1;
									$user['passwordTimeStamp'] 		=  date('Y-m-d H:i:s', strtotime("+30 days"));
									$output 				= $this->common->insert('dibcase_users',$user);
									if($output[0] == 1)
									{
										$email_verify_token = $this->encrypt->encode($postData['COM_EMAIL']);
										$emailTemplate 		= getEmailTemplate(1);
										$urll 				= str_replace('api','#',site_url());
										$verification_link  = $urll.'verify-account/'.$email_verify_token;
										$receiver_name 		= $postData['COM_NAME'].' Admin';
										$variables = array( 'receiver_name' 	 => trim($receiver_name),
															'verification_link'  => $verification_link,
															'to'				 => $postData['COM_EMAIL'],
															'email'				 => $postData['COM_EMAIL'],
															'password'			 => $password,
														);

										sendEmail($variables,$emailTemplate);
									}
									// $result["success"] 		= 'Company  added successfully.';
									$result["data"] = $this->common->login($user['USER_NAME']);
									$result["success"] 			= true;
									$result["redirect"] 		= true;
									$result["success_msg"] 	= 'Registration Successful. Please check your email to verify Registration';
							}
						}
						else
						{
								$result["error"] 	= 'Data not insert.Please fill detail properly.';
						}
				}


			else
			{
				if( $emailexist != 0 && $usernameexist != 0 )
				{
					$result["error"] 	= 'Email & Username Already Exist';
					$result["emailexist"] 	= ' ';
					$result["usernameexist"] 	= ' ';
				}
				else if( $emailexist != 0)
				{
					$result["error"] 	= 'Email Already Exist';
					$result["emailexist"] 	= ' ';
				}
				else if($usernameexist !=0 )
				{
					$result["error"] 	= 'Username Already Exist';
					$result["usernameexist"] 	= ' ';
			}
		}
}
			header('Content-Type: application/json');
			echo json_encode($result);
			exit;

	}


		//$emailexist 	= $this->common->verifyEmail('dibcase_users',array('USER_REF' => $postData['USER_REF']));
		public function verifyEmail()
	{
		$postData	=	json_decode($_POST['data'], true);
		if( $postData['token'] == '')
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Something went wrong. Please try again';
		}
		else
		{
			$email  	 = $this->encrypt->decode($postData['token']);
			if( $email != '' )
			{
				$exist	 = $this->common->getrow('dibcase_company',array('COM_EMAIL'=>$email));
				if( !empty($exist) )
				{
					$st	 = $this->common->verifyEmail($email);
					if($st)
					{
						$result["success"] 				= true;
						$result["success_msg"] 		= 'Email verified successfully. You can now login.';
					}
					else
					{
						$result["success"] 			= false;
						$result["error_msg"] 		= 'Something wrong...';
					}
				}
				else
				{
					$result["success"] 			= false;
					$result["error_msg"] 		= 'Token is invalid.';
				}
			}
			else
			{
				$result["success"] 			= false;
				$result["error_msg"] 		= 'Token is invalid.';
			}
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function unik()
	{
		$idd  = $this->common->uniqueId('orderNumber','orderId','orders','PO');
		echo $idd;
	}

	public function addPo()
	{
		$json 	= 	file_get_contents("php://input");
    $postData 	= 	json_decode($json, true);
	  if(!empty($postData))
	  {
				if(!isset($postData['poId']))
				{
					if(!isset($postData['clientId']) )
			    {
						$rows = $this->common->checkexist('client',array('clientEmail'=>$postData['clientEmail']));
						if($rows == 0)
						{
							//print_r($postData);die;
				      $scrn['clientFirstName']                = $postData['clientFirstName'];
				      $scrn['clientLastName']                 = $postData['clientLastName'];
				      $scrn['clientCompany']  								= $postData['clientCompany'];
				      $scrn['clientTelephone']                = $postData['clientTelephone'];
				      $scrn['clientEmail']    								= $postData['clientEmail'];
				      $scrn['clientBillingAddress']           = $postData['clientBillingAddress'];
				      // $scrn['clientDeliveryAddress']  				= $postData['clientDeliveryAddress'];
				      $scrn['clientCity']                     = $postData['clientCity'];
				      $scrn['clientCountry']  								= $postData['clientCountry'];
				      $scrn['clientPostal']                   = $postData['clientPostal'];
							$scrn['clientPic']                   		= $postData['clientPic'];
							$scrn['clientSalesname']                = $postData['clientSalesname'];
							$scrn['clientTag']                   		= isset($postData['clientTag']) ? $postData['clientTag'] : '';


				      $output                                 = $this->common->insert('client',$scrn);
				      $clientId =$output[1];

		          $user['userName'] 			= $postData['clientEmail'];
		          $user['userPassword'] 	= md5(123456);
		          $user['fullName'] 			= $postData['clientFirstName'].$postData['clientLastName'];
		          $user['userEmail'] 			= $postData['clientEmail'];
		          $user['userAddedOn'] 		= date('Y-m-d h:i');
		          $user['userType'] 			= 4;
		          $user['userStatus'] 		= 1;
		          $user['isCLient'] 			= $clientId;
							$this->common->insert('users',$user);
						}
						else
						{
							$result["error"]                      =  'Email Already Exist';
							header('Content-Type: application/json');
							echo json_encode($result);exit;
						}
			    }
			    else
			    {
			      $clientId =$postData['clientId'];
					}
					$po['clientId']                   			= $clientId;
		      // $po['orderTelephone']                   = $postData['orderTelephone'];
		      // $po['orderPostal']                      = $postData['orderPostal'];
		      $po['orderDueDate']                     = $postData['orderDueDate'];
		      $po['orderDescription']                 = $postData['orderDescription'];
		      $po['orderDeliveryAddress'] =$postData['orderDeliveryAddress'];
					$po['orderTotal']                      = $postData['orderTotal'];
					// $po['orderSalesPerson']                      = $postData['orderSalesPerson'];
					$po['orderNumber']  = $this->common->uniqueId('orderNumber','orderId','orders','PO');
					$po['orderCreated'] 		= date('Y-m-d h:i');

		      $output                                 = $this->common->insert('orders',$po);
				}
				else  // update
				{
					// $po['orderTelephone']                   = $postData['orderTelephone'];
		      // $po['orderPostal']                      = $postData['orderPostal'];
		      $po['orderDueDate']                     = $postData['orderDueDate'];
		      $po['orderDescription']                 = $postData['orderDescription'];
		      $po['orderDeliveryAddress'] 						= $postData['orderDeliveryAddress'];
					$po['orderTotal']                      = $postData['orderTotal'];
					// $po['orderSalesPerson']                      = $postData['orderSalesPerson'];

		      $output                                 = $this->common->update(array('orderId' => $postData['poId']),$po,'orders');
					$this->common->delete('orderItems',array('poId' => $postData['poId']));
					$output = array();
					$output[0]  = 1;
					$output[1]  = $postData['poId'];
				}
				$items = $postData['items'];

				foreach ($items as $key => $value)
				{
					if(isset($items[$key]['itemId']))
					unset($items[$key]['itemId']);
					$items[$key]['poId'] = $output[1];
				}

				if(!empty($items))
				$this->common->insert_batch('orderItems',$items);

	      if($output[0] == 1)
	      {
	        $result["success"]                    =  'Client Added successfully.';
	      }
	      else
	      {
	        $result["error"]                      =  'Data not save.Please fill detail properly.';
	      }
	  }
		else
		{
			$result["error"]                      =  'Data not save.Please fill detail properly.';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function login()
	{
		$json 	= 	file_get_contents("php://input");
    $postData 	= 	json_decode($json, true);
    if(!empty($postData))
		{
			$result = $this->common->login($postData['username']);

			if(count($result) > 0)
			{
				if (md5($postData['password']) == $result->userPassword)
				{
					if($result->userStatus == 1)
					{
						unset($result->userPassword);
						$output['success']	=	'Login successful';
						$output['data']			=	$result;

					}
					else if($result->userStatus == 2)
					{
						$output['error']		=	'Your profile is inactive';
					}
					// else
					// {
					// 	if($result[0]->USER_VERIFIED == 0)
					// 	$output['error']		=	'Please Verify your email';
					// 	else {
					// 		$output['error']		=	'Your profile is inactive';
					// 	}
					// }
				}
				else
				{
					$output['error']		=	'Username or Password Incorrect';
				}
			}
			else
			{
				$output['error']		=	'Username or Password Incorrect';
			}
		}
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}

	public function getClients()
	{
		$cl 	= $this->common->getTable('client');
		$output['success']		=	true;
		if(!empty($cl))
		{
			$output['clients']		=	$cl;
		}
		else
		{
			$output['clients']		=	array();
		}
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}

	public function getClient($id)
	{
		$client 	= $this->common->getrow('client',array( 'clientId' => $id ));
		$output['success']		=	true;
		$output['data']		=	$client;
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}

	public function getPurchaseOrdes($page,$perPage,$type,$user = null)
	{
			if( $perPage <= 0 )
			 $perPage = 10;

			 if( $page <= 0 )
			 $page = 1;

			 $start = ($page-1) * $perPage;

			if($user != null)
			{
				$client 	= $this->common->getrow('users',array( 'userId' => $user ));
				// print_r($client);
				$client 	= $client->isCLient;
			}
			else
			{
				$client 	= '';
			}
			// print_r($client);
			// die;

			$result = $this->common->getPurchaseOrdes($start ,$perPage,$type,$client);
			if(!empty($result))
			{
				$output['success']		=	true;
				$output['data']			=	$result;
			}
			else
			{
				$output['data']			=	array();
			}
		header('Content-Type: application/json');
		echo json_encode($output);
		exit;
	}

	public function PurchaseOrderDetails($orderid)
	{
		$result = $this->common->PurchaseOrderDetails($orderid);
		$result->items = $this->common->get('orderItems',array('poId'=>$orderid));
		$logs = $this->common->get('orderLog',array('orderId'=>$orderid));
		$result->log = array();
		foreach ($logs as $key => $log)
		{
			$t = strtotime($log->logDate);
			$result->log[$log->orderStatus] = date('d/m/Y',$t);
		}

		if(!empty($result))
		{
			$output['success']		=	true;
			$output['data']			=	$result;
		}
		else
		{
			$output['data']			=	array();
		}
		header('Content-Type: application/json');
		echo json_encode($output);
		exit;
	}

	public function ChangePoStatus($orderId,$status)
	{
		$result = $this->common->update(array( 'orderId' => $orderId ), array( 'orderStatus' => $status ),'orders');
		if(!empty($result))
		{
			$output['success']		=	true;
			if($status == 1)
			$output['data']				=	'Order submitted for design';

      if($status == 2)
      $output['data']				=	'Design has been started';
      if($status == 3)
      $output['data']				=	'Design sent for review';
      if($status == 4)
      $output['data']				=	'Design Compelete';
      if($status == 5)
      $output['data']				=	'Submitted for production';
      if($status == 6)
      $output['data']				=	'Production Started';
      if($status == 7)
      $output['data']				=	'Production Done';
      if($status == 8)
      $output['data']				=	'Order ready for shipment';
      if($status == 9)
      $output['data']				=	'Order shipment started';
      if($status == 10)
      $output['data']				=	'Order shipment successfull';

      $log['orderId'] 				= $orderId;
      $log['logDate'] 				= date('Y-m-d H:i:s');
      $log['orderStatus'] 		= $status;
      $log['logDescription'] 	= $output['data'];

      $this->common->insert('orderLog',$log);
		}
		else
		{
			$output['data']			=	array();
		}
		header('Content-Type: application/json');
		echo json_encode($output);
		exit;
	}

	public function uploadFile()
 	{
 		if( $_FILES['file']['name'] == '' )
 		{
 			$result["success"] 	 = false;
 			$result["error_msg"] = 'Please select an File';
 		}

 		if( isset($_FILES) && $_FILES['file']['name'] != '' )
 		{
 			$fileName = '';
 			if (!is_dir('./assets/uploads/files/'))
 			{
 				mkdir('./assets/uploads/files/', 0777, TRUE);
 			}

 			$config['upload_path']   = './assets/uploads/files/';
 			$config['allowed_types'] = '*';
 			$this->load->library('upload', $config);
 			if ($this->upload->do_upload('file'))
 			{

 				$data_upload 	   		= $this->upload->data();
 				$fileName         		= $data_upload['file_name'];
 				$result["success"] 		= true;
 				if( file_exists(FCPATH."assets/uploads/files/".$fileName) )
 				{
 					$result["fileName"] 	= $fileName;
 				}
 				else
 					$result["fileName"] 	= '';
 				  $result["success_msg"] 	= 'File Upload';
 			}

 			if($this->upload->display_errors())
 			{
 				$error = $this->upload->display_errors();
 				$result["success"] 	 = false;
 				$result["error_msg"] = $error;
 			}
 		}

     header('Content-Type: application/json');
     echo json_encode($result);exit;
 	}




	public function uploadImage()
	{
		if( $_FILES['file']['name'] == '' )
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Please select an image.';
		}

		if( isset($_FILES) && $_FILES['file']['name'] != '' )
		{
			$fileName = '';
			if (!is_dir('./assets/uploads/profilePic/'))
			{
				mkdir('./assets/uploads/profilePic/', 0777, TRUE);
			}
			$config['upload_path']   = './assets/uploads/profilePic/';
			$config['allowed_types'] = 'gif|jpg|png|tiff|tif|jpeg|bmp|BMPf';
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('file'))
			{
				$data_upload 	   		= $this->upload->data();
				$fileName         		= $data_upload['file_name'];
				$result["success"] 		= true;
				if( file_exists(FCPATH."assets/uploads/profilePic/".$fileName) )
					$result["fileName"] 	= $fileName;
				else
					$result["fileName"] 	= '';

				$result["success_msg"] 	= 'Image changed successfully.';
			}
			if($this->upload->display_errors())
			{
				$error = $this->upload->display_errors();
				$result["success"] 	 = false;
				$result["error_msg"] = $error;
			}
		}
    header('Content-Type: application/json');
    echo json_encode($result);exit;
	}


	public function clientDetails($id)
	{
		$cl 	= $this->common->getrow('client',array( 'clientId' => $id ));
		$output['success']		=	true;
		if(!empty($cl))
		{
			$output['data']		=	$cl;
		}
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}

	public function dashboardInfo($type,$user)
	{
		$output['success']	=	true;
		$cl 								= array();

		if($type == 'portal')
		{
			if($user == 1)
			{
			  $cl['total'] 							= $this->common->getCount('orders');
			  $cl['complete'] 					= $this->common->getCount('orders',array( 'orderStatus >=' => 10));
			}
			if($user == 1 || $user == 2)
			{
			  $cl['design'] 						= $this->common->getCountOr('orders',array( 'orderStatus' => 3),array( 'orderStatus' => 2));
			}
			if($user == 2)
			{
			  $cl['designPending'] 			= $this->common->getCount('orders',array( 'orderStatus' => 1));
			  $cl['designSent'] 				= $this->common->getCount('orders',array( 'orderStatus' => 3));
			  $cl['designApproved'] 		= $this->common->getCount('orders',array( 'orderStatus >=' => 4));
			}
			if($user == 1 || $user == 3)
			{
			  $cl['prod'] 							= $this->common->getCount('orders',array( 'orderStatus' => 6));
			}
			if($user == 3)
			{
			  $cl['productionPending'] 	= $this->common->getCount('orders',array( 'orderStatus' => 5));
				$cl['productionDone'] 					= $this->common->getCountOr('orders',array( 'orderStatus' => 7),array( 'orderStatus' => 8),array( 'orderStatus' => 9));
			  $cl['delivered'] 					= $this->common->getCount('orders',array( 'orderStatus >=' => 10));
			}
		}

		if($type == 'client')
		{
			$client 	= $this->common->getrow('users',array( 'userId' => $user ));

			$total 	= $this->common->getCount('orders',array('clientId' => $client->isCLient ));

			$design 	= $this->common->getCountOr('orders',array( 'orderStatus' => 2 , 'clientId' => $client->isCLient),array( 'orderStatus' => 3));
			$prod 	= $this->common->getCount('orders',array( 'orderStatus' => 6 , 'clientId' => $client->isCLient));
			$complete 	= $this->common->getCount('orders',array( 'orderStatus >=' => 10 , 'clientId' => $client->isCLient));


			$cl['total'] 				= $total;
			$cl['design'] 			= $design;
			$cl['prod'] 				= $prod;
			$cl['complete'] 		= $complete;
		}

		$output['data']			=	$cl;
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}



	public function getcompanyclients()
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


			// $result = $this->common->get( 'dibcase_client', array ('COM_REF'=>$postData) );
			$result = $this->common->getcompanyclients($postData['COM_REF'],$start ,$perPage ,$postData['SORT'],$postData['direction'],$postData['filter'],$postData['searchText'],$postData['searchAlphabet']);
			if(!empty($result))
			{
				$output['success']		=	true;
				$output['data']			=	$result;
			}
			else
			{
				$output['data']			=	array();
			}
		}
		header('Content-Type: application/json');
		echo json_encode($output);
		exit;
	}

	public function getcompanyclientsCount()
	{
		$postData	=	json_decode($_POST['data'], true);
    if(!empty($postData))
		{
			$result = $this->common->getcompanyclientsCount($postData);
			$output['success']		=	true;
			if(!empty($result))
			{
				$output['data']			=	$result;
			}
			else
			{
				$output['data']			=	array();
			}
		}
			header('Content-Type: application/json');
			echo json_encode($output);
			exit;
	}


	public function forgotPassword()
	{
		$postData	=	json_decode($_POST['data'], true);
		if( $postData['email'] != '' )
		{
			$response = $this->common->forgotPassword($postData['email']);
			if( !$response['success'])
			{
				if(isset($response['inactive']))
				{
					$result["success"] 	 = false;
					$result["error_msg"] = 'Your account is currently inactive';
				}
				else
				{
					$result["success"] 	 = false;
					$result["error_msg"] = 'The email you entered is not found in our database. Please enter correct email.';
				}
			}
			else
			{
				$result["success"] 	 	= true;
				$result["success_msg"]	= 'Please check your email to get new password.';
			}
		}
		else
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Parameters missing. Please try again.';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


	public function changePassword()
	{
		$postData		= json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$user = $this->common->getrow("dibcase_users", array( 'USER_REF' => $postData['ref'] ));
			if(empty($user))
			{
				$result["success"] 	 = false;
				$result["error_msg"] = 'Something went wrong. Please try again';
			}
			else
			{
				if (password_verify($postData['current_password'], $user->USER_PASSWORD))
				{
					$password	  		=   $postData['password'];
					$salt						= 	mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
					$salt 					= 	base64_encode($salt);
					$salt 					= 	str_replace('+', '.', $salt);
					$hash 					= 	crypt($password, '$2y$10$'.$salt.'$');
					unset($postData['currentpassword']);

					$response	= $this->common->update(array('USER_REF'=>$postData['ref']),array('USER_PASSWORD' => $hash , 'passwordTimeStamp' => date('Y-m-d H:i:s', strtotime("+30 days"))),'dibcase_users');
					if( $response == 0 )
					{
						$result["success"] 		= false;
						$result["error_msg"] 	= 'Data not saved. Please try again.';
					}
					else
					{
						$projectUrl = str_replace('api','#',site_url());
						demoCredentials('Dibcase',$user->USER_NAME,$postData['password'],$projectUrl,'company admin');
						$result["success"] 				= true;
						$result["success_msg"] 		= 'Password changed successfully.';
					}
				}
				else
				{
						$result["success"] 		= false;
						$result["error_msg"] 	= 'Current password is wrong';
				}
			}
		}
		else
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Parameters missing. Please try again.';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


	public function deleteCompany()
	{
		$postData	=	json_decode($_POST['data'], true);
		if( $postData['COM_REF'] != '' )
		{
			$response = $this->common->deleteCompany($postData['COM_REF']);
			// sleep(5);
			if( $response )
			{
				$result["success"] 	 = true;
				$result["success_msg"] = 'Your account is deleted';
			}
			else
			{
				$result["success"] 	 	= true;
				$result["error_msg"]	= 'Something wrong';
			}
		}
		else
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Parameters missing. Please try again.';
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
