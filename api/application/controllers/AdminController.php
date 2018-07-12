<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:*");
class AdminController extends CI_Controller {

	 public function __construct()
	 {
	    parent::__construct();
			$this->load->Model('common');
			$this->load->Model('admin');
			$this->load->library('encrypt');
		}



	public function getFirms($sortField,$direction)
	{
		$output = $this->admin->getFirms($sortField,$direction);
		if($output)
		{
			$result["success"]   =  true;
			$result["data"]   =  $output;
		}
		else
		{
			$result["success"]   =  false;
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function DashboardInfo()
	{
		$output = $this->admin->DashboardInfo();
		if($output)
		{
			$result["success"]   =  true;
			$result["data"]   =  $output;
		}
		else
		{
			$result["success"]   =  false;
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function FirmDetails($firm)
	{
		$output = $this->admin->FirmDetails($firm);
		$output->clients = $this->common->getcompanyclientsCount($firm);
		if($output)
		{
			$result["success"]   =  true;
			$result["data"]   =  $output;
		}
		else
		{
			$result["success"]   =  false;
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function getFields($type='')
	{
		$isError = false;

		switch ($type)
		{
			case 'client':
			$table = 'dibcase_client';
				break;

			case 'claim':
			$table = 'dibcase_claim';
				break;

			case 'contacts':
			$table = 'dibcase_contacts';
				break;

			case 'conditions':
			$table = 'dibcase_medical_conditions';
				break;

			case 'medications':
			$table = 'dibcase_medications';
				break;

			case 'events':
			$table = 'dibcase_events';
				break;

			case 'tasks':
			$table = 'dibcase_tasks';
				break;

			default:
			$isError = true;
				break;
		}

		if(!$isError)
		{
			$output = $this->admin->getFields($table);
			foreach ($output as $key => $value)
			{
				if($type == 'client')
				{
					$output[$key] = str_replace("CL","CLIENT",$value);
					if($value == 'COM_REF' || $value == 'CL_ID' || $value == 'CL_REF')
					unset($output[$key]);
				}

				else if($type == 'claim')
				$output[$key] = str_replace("CLM","CLAIM",$value);

				else if($type == 'contacts')
				$output[$key] = str_replace("CON","CONTACTS",$value);

				else if($type == 'conditions')
				{
					$output[$key] = str_replace("MCOD","MEDICAL_CONDITION",$value);
					if($value == 'MCOD_ID' || $value == 'COM_REF')
					unset($output[$key]);
				}

				else if($type == 'medications')
				{
					$output[$key] = str_replace("MED","MEDICATION",$value);
					if($value == 'MED_ID' || $value == 'COM_REF' || $value == 'MED_ADDEDBY')
					unset($output[$key]);
				}

				else if($type == 'events')
				{
					if($value == 'idd' || $value == 'EVENT_REF' || $value == 'COM_REF' || $value == 'CLR' || $value == 'addedBy' || $value == 'GOOGLE_CAL_ID' || $value == 'EVENT_ALL_DAY' || $value == 'EVENT_CL_REF')
					unset($output[$key]);
				}

				else if($type == 'tasks')
				{
					$output[$key] = str_replace("TSK","TASK",$value);
					if($value == 'TSK_ID' || $value == 'REF_ID' || $value == 'REF_TYPE' || $value == 'TSK_MOD_ID' || $value == 'TSK_RESPONSIBLE' || $value == 'TSK_STAGE' || $value == 'CL_REF' || $value == 'COM_REF')
					unset($output[$key]);
				}
				$output = array_values($output);

			}
			$result["success"]   =  true;
			$result["data"]   =  $output;
		}
		else
		{
			$result["success"]   =  false;
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}





}
?>
