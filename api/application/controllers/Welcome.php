<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->Model('common');
        $this->perPageNum = 20;
		$this->load->library('zip');
		date_default_timezone_set('Asia/Kolkata');

	}

	public $variable = "awesome";

	public function index()
	{
$datetime = new DateTime('12-09-2017 12:35'); // current time = server time
$datetime2 = new DateTime('12-09-2017 12:35'); // current time = server time
$otherTZ  = new DateTimeZone('Asia/Kolkata');
$otherTZ2  = new DateTimeZone('America/Los_Angeles');
$dd = $datetime2->setTimezone($otherTZ); // calculates with new TZ now

echo "<pre>";
print_r($datetime);
print_r($dd);
$dd2 = $datetime2->setTimezone($otherTZ2); // calculates with new TZ now
print_r($dd2);
echo "</pre>";



		$this->load->view('welcome_message');
	}

	public function getDepartments()
	{
		$output['data']	=  $this->common->getAllDepartments();
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}
	public function getDesignations()
	{
		$output['data']	=  $this->common->getAllDesignations();
		header('Content-Type: application/json');
		echo json_encode($output);exit;
	}
	public function forgotPassword()
	{
		$rest_json	=	file_get_contents("php://input");
		$postData	=	json_decode($rest_json, true);
		if(!empty($postData))
		{
			$response = $this->common->forgotPassword($postData);
			if( $response == 1 )
				$result["success"] 	=  'Please check your mail.';
			else if( $response == 2 )
				$result["error"] 	=  'Something happens wrong. Please try again.';
			else
				$result["error"] 	=  'The email you entered is not found in our database.';
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}

	public function changePassword()
	{
		$rest_json	=	file_get_contents("php://input");
		$postData	=	json_decode($rest_json, true);
		if(!empty($postData))
		{
			$response = $this->common->changePassword($postData);
			if( $response == 1 )
				$result["success"] 	=  'Password changed successfully.';
			else
				$result["error"] 	=  'Something happens wrong. Please try again.';
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}

	public function mm()
	{
		$templine = '';
		$lines = file(base_url()."script.sql");
		foreach ($lines as $line)
		{
			if (substr($line, 0, 2) == '--' || $line == '')
			continue;
			$templine .= $line;
			if (substr(trim($line), -1, 1) == ';')
			{
				$this->db->query($templine);
				$templine = '';
			}
		}
	}
}
