<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:*");
class TaskController extends CI_Controller {

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

	public function submitTask()
	{
		$postData	=	json_decode($_POST['data'], true);

		if(!empty($postData))
		{
					$date  = date("d M Y H:i:s");
					$date1 = date("d M Y H:i:s");

					$emp['COM_REF'] 						=  $postData['COM_REF'];
					$emp['TSK_ADDEDBY'] 				=  $postData['addedBy'];
					$emp['TSK_CREATE_DATE'] 		=  date('Y-m-d H:i:s');
					if(isset($postData['notLinked']) && ($postData['notLinked'] == false))
					{
						$emp['CL_REF'] 						=  $postData['CL_REF'];
					}
					else
					{
						$emp['CL_REF'] 						=  '';
					}

					if(isset($postData['notLinkedClaim']) && ($postData['notLinkedClaim'] == false))
					{
						$emp['REF_ID'] 						=  $postData['claim'];
						$emp['REF_TYPE'] 					=  'claim';
					}
					else
					{
						$emp['REF_ID'] 						=  0;
						$emp['REF_TYPE'] 					=  '';
					}

					if(isset($postData['isPrivate']) && ($postData['isPrivate'] == true))
					{
						$emp['isPrivate'] 						=  1;
					}

					$emp['TSK_TITLE'] 					=  $postData['taskName'];
					$emp['TSK_DUE_DATE'] 				=  goodDateCondition($postData['dueDate']);
					$emp['TSK_START_DATE'] 			=  goodDateCondition($postData['TSK_START_DATE']);
					$emp['TSK_NOTE'] 						=  isset($postData['description']) ? $postData['description'] : '';

					// echo "<pre>";
					// print_r($emp);
					// print_r($postData);
					// echo "</pre>";
					// die;

					if(isset($postData['TSK_ID'])) // update
					{
						$tid = $postData['TSK_ID'];
						$emp['TSK_COMPLETE_DATE'] = goodDateCondition($postData['TSK_COMPLETE_DATE']);
						$emp['TSK_STATUS'] 					=  $postData['TSK_STATUS'];
						unset($emp['TSK_ADDEDBY']);
						unset($emp['TSK_CREATE_DATE']);
						$out = $this->common->update(array( 'TSK_ID' => $tid ),$emp,'dibcase_tasks');
						$this->common->delete('dibcase_activity',array( 'TSK_ID' => $tid ));
						$this->common->delete('dibcase_task_assigns',array( 'taskID' => $tid ));
						$this->common->delete('dibcase_tags',array( 'REF_ID' => $tid , 'TAG_REF_TYPE' => 'task' ));
						// print_r($out);
						// echo "<br>";
						if($out == 1)
						{
							$output[0] = 1;
							$output[1] = $tid;
						}
						// print_r($output);
						// die('here');
						$msg = 'Task updated successfully';
					}
					else //add
					{
						$output = $this->common->insert('dibcase_tasks',$emp);
						$msg = 'Task  added successfully';
					}

					if( $output[0] == 1  )
					{
						foreach ( $postData['checklist'] as $key => $value )
						{
							$act['ACT_TITLE'] 					=  $value['ACT_TITLE'];
							$act['TSK_ID'] 							=  $output[1];
							$act['ACT_ADDEDBY'] 				=  $postData['addedBy'];
							if(isset($postData['TSK_ID']))
							$act['ACT_STATUS'] 					=  ($value['ACT_STATUS'] == true ? 1 : 0); // Update Status in Case of Task Status
							$this->common->insert('dibcase_activity',$act);
						}

						foreach ( $postData['employees'] as $key => $value )
						{
							// $val = explode('_',$value); // empref_empname
							$assign['taskID'] 							=  $output[1];

							$assign['EmpId'] 								=  $key;
							$assign['EMP_NAME'] 						=  $value;
							$assign['COM_REF'] 							=  $postData['COM_REF'];

							// $assign['EmpId'] 								=  $val[0];
							// $assign['EMP_NAME'] 						=  $val[1];
							// $assign['COM_REF'] 							=  $postData['COM_REF'];
							$this->common->insert('dibcase_task_assigns',$assign);
						}

						$tags = array();

						if(!empty($postData['tags']))
						{
							foreach ( $postData['tags'] as $key => $value )
							{
								$tags[$key]['REF_ID'] 							=  $output[1];
								$tags[$key]['TAG_REF_TYPE'] 				=  'task';
								$tags[$key]['TAG_TITLE'] 						=  $value;
								$tags[$key]['COM_REF'] 							=  $postData['COM_REF'];
							}
							$this->common->insert_batch('dibcase_tags',$tags);
						}

						$this->common->updateTaskStatusTaskId($output[1]);
						$result["success"] 		= $msg;
					}
					else
					$result["error"] 		= 'Erro Occured , try again';

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
			$emp['EMP_COMPANY_EMAIL'] 	=  $postData['EMP_COMPANY_EMAIL'];
			$emp['EMP_PERSONAL_EMAIL'] 	=  $postData['EMP_PERSONAL_EMAIL'];
			$emp['EMP_ADDRESS'] 				=  $postData['EMP_ADDRESS'];
			$emp['EMP_STATUS'] 					=  $postData['EMP_STATUS'];
			$emp['EMP_ROLE'] 					  =  $postData['USER_ROLE'];
			$emp['EMP_DOB'] 					  =  goodDateCondition($postData['EMP_DOB']);

			$output = $this->common->update(array('EMP_REF' => $postData['EMP_REF']),$emp,'dibcase_employees');
			if($output == 1)
			{
				$user['USER_STATUS'] 	=  1;
				$user['USER_ROLE'] 		=  2;
				$output 				      =  $this->common->update(array('EMP_REF' => $postData['EMP_REF']),$emp,'dibcase_employees');
				$result["success"] 		=  'Employee updated successfully.';
			}

			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}
	}

	public function ListTaskAsssigns()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$data = $this->common->ListTaskAsssigns($postData['ref'],$postData['AssignedTo'],$_POST['userRef']);
			foreach ( $data as $key => $value )
			{
				$a  = $this->common->ListTask($value->EmpId,$postData['ref'] , $postData['Completion'], $postData['Case'] , $postData['sort'] , $postData['direction'],$_POST['userRef']);
				// print_r($a);
				foreach ( $a['result'] as $kk => $vv )
				{
					$tags  = $this->common->getFields('dibcase_tags' , array( 'REF_ID' => $vv->TSK_ID , 'TAG_REF_TYPE' => 'task' ) , array('TAG_TITLE'));
					// $tagAr = array();
					// foreach ($tags as $k => $v)
					// {
					// 	$tagAr[] = $v->TAG_TITLE;
					// }
					$a['result'][$kk]->tags = $tags;
					$acts = $this->common->getAct('dibcase_activity', array( 'TSK_ID' => $vv->TSK_ID ) );
					if(!empty($acts))
					$a['result'][$kk]->acts = $acts;
				}
				$data[$key]->tasks = $a['result'];
			}
			if(!empty($data))
			{
				$result["success"] 			=  true;
				$result["successdsadsa"] 			=  true;
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

	public function ListTask()
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

			$data = $this->common->ListTask($postData['empref'],$postData['COM_REF'] , $postData['Completion'], $postData['Case']);
			foreach ($data['result'] as $key => $value)
			{
				$asgn = $this->common->get('dibcase_task_assigns' , array( 'taskID' => $value->TSK_ID) );
				$data['result'][$key]->Assigns = $asgn;
			}

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
				$result["error"] 	= 'No Input data';
				$result["usernameexist"] 	= ' ';
		}

		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}


	public function GetTask()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$data = $this->common->getrow('dibcase_tasks' , array( 'TSK_ID' => $postData['ref'] ) );
			if(!empty($data))
			{
				$asgn = $this->common->get('dibcase_task_assigns' , array( 'taskID' => $data->TSK_ID ) );
				foreach ($asgn as $key => $value)
				{
					$assigns[] = $value->EmpId;
				}

				// if($data->REF_ID != 0 && $data->CL_REF != '')
				// {
				// 	$clientClaimDetails = $this->common->clientClaimDetails($data->REF_ID,$data->CL_REF);
				// }

				$act   = $this->common->get('dibcase_activity' , array( 'TSK_ID' => $data->TSK_ID ) );
				$tags  = $this->common->getFields('dibcase_tags' , array( 'REF_ID' => $data->TSK_ID , 'TAG_REF_TYPE' => 'task' ) , array('TAG_TITLE'));
				$taskComments   =  $this->common->taskCOmments($data->TSK_ID);

				$tagAr = array();
				foreach ($tags as $k => $v)
				{
					$tagAr[] = $v->TAG_TITLE;
				}
				$data->Assigns = $assigns;
				$data->Activities = $act;
				$data->tags = $tagAr;
				$data->taskComments = $taskComments;

				if($data->CL_REF != '')
				{
					$cl = $this->common->clientDetails($data->CL_REF);
					$data->cl = $cl;
				}

				if($data->REF_ID != 0)
				{
					$clientClaimDetails = $this->common->clientClaimDetails($data->REF_ID,$data->CL_REF);
					$data->claim = $clientClaimDetails['claim'];
				}
				// $data->tags2 = $this->db->last_query();
				$result["success"] 	= true;
				$result['data'] = $data;
			}
			else
			{
				$result["success"] 	= true;
				$result['data'] = array();
			}
		}
		else
		{
				$result["error"] 	= 'No input Data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


	public function ClientTask()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$data = $this->common->ClientTask($postData['client']);
			if(!empty($data))
			{
				$out = array();
				foreach ($data as $key => $value)
				{
					$act  = $this->common->get('dibcase_activity' , array( 'TSK_ID' => $value->TSK_ID ) );
					$assigns  = $this->common->get('dibcase_task_assigns' , array( 'taskID' => $value->TSK_ID ) );
					$out[$key]['act'] = $act;
					$out[$key]['data'] = $value;
					$out[$key]['assigns'] = $assigns;


					$tags  = $this->common->getFields('dibcase_tags' , array( 'REF_ID' => $value->TSK_ID, 'TAG_REF_TYPE' => 'task' ) , array('TAG_TITLE'));
					$tagAr = array();
					foreach ($tags as $k => $v)
					{
						$tagAr[] 				= $v->TAG_TITLE;
					}
					$out[$key]['TaskTags']   =  $tagAr;
					$out[$key]["comments"]   =  $this->common->taskCOmments($value->TSK_ID);


					// $getTaskTags  = $this->common->getTaskTags(); //all tags
					// $value->taskTags = $getTaskTags;
					//
					// $getTaskTagsAssigns  = $this->common->getTaskTagsAssigns(array( 'TSK_ID' => $value->TSK_ID )); // tags assigned to task
					// if(!empty($getTaskTagsAssigns))
					// {
					// 	$TaskTagsAssigns =  array();
					// 	foreach ($getTaskTagsAssigns as $k => $v)
					// 	{
					// 		$TaskTagsAssigns[] = $v->tagId;
					// 	}
					// 	$value->TaskTagsAssigns = $TaskTagsAssigns;
					// }
				}
				$result["success"] 			=  true;
				$result["data"]    			=  $out;
			}
			else
			{
				$result["error"] 			=  'No Records Found';
			}
		}
		else
		{
				$result["error"] 	= 'No Input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function CustomTaskTag()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$ar = array(
				'REF_ID' 				=> $postData['TaskId'],
				'TAG_REF_TYPE'  => 'task',
				'TAG_TITLE' 		=> $postData['name'],
				'COM_REF' 			=> $postData['COM_REF'],
			);
			$data = $this->common->insert('dibcase_tags',$ar); // add tag to tag table
			if( $data[0] == 1)
			{

				// $ar = array('tagId' => $data[1] , 'TSK_ID' => $TSK_ID);
				// $data = $this->common->insert('dibcase_task_tags_assigns',$ar); // assign tag to task
				// if( $data[0] == 1)
				// {
				// 	$result["success"] 			=  'Tag added successfully';
				// 	$getTaskTagsAssigns  = $this->common->getTaskTagsAssigns(array( 'TSK_ID' => $TSK_ID )); // tags assigned to task
				// 	$getTaskTags  = $this->common->getTaskTags(); //all tags
				// 	foreach ($getTaskTagsAssigns as $k => $v)
				// 	{
				// 		$TaskTagsAssigns[] = $v->tagId;
				// 	}
				// 	$result["TaskTagsAssigns"] 	=  $TaskTagsAssigns;
				// 	$result["TaskTags"] 				=  $getTaskTags;
				// }
				// else
				// {
				// 	$result["error"] 			=  'Something Wrong';
				// }
				$tags  = $this->common->getFields('dibcase_tags' , array( 'REF_ID' => $postData['TaskId'], 'TAG_REF_TYPE' => 'task' ) , array('TAG_TITLE'));
				$tagAr = array();
				foreach ($tags as $k => $v)
				{
					$tagAr[] = $v->TAG_TITLE;
				}
					$result["success"] 				=  true;
					$result["TaskTags"] 				=  $tagAr;
			}
			else
			{
				$result["error"] 			=  'Something Wrong';
			}
		}
		else
		{
			$result["error"] 			=  'No Input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}

	public function updateAssignedTags()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$this->common->delete('dibcase_tags',array( 'TAG_TITLE' => $postData['tag'] , 'REF_ID' => $postData['taskID'] , 'TAG_REF_TYPE' => 'task' ));
			$result["success"] 			=  true;
		}
		else
		{
			$result["error"] 			=  'No Input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function updateAssignedEmps()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			if($postData['type'] == 'delete')
			{
				$this->common->delete('dibcase_task_assigns',array( 'taid' => $postData['assignId'] ));
			}
			if($postData['type'] == 'add')
			{
				unset($postData['type']);
				$this->common->insert('dibcase_task_assigns',$postData);
				$result["assigns"] 			=  $this->common->get('dibcase_task_assigns',array( 'taskID'=> $postData['taskID']));
			}
			$result["success"] 			=  true;
		}
		else
		{
			$result["error"] 			=  'No Input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


public function getdueTasks()
{
	$postData	=	json_decode($_POST['data'], true);
	if(!empty($postData))
	{
		$data = $this->common->getdueTasks($postData);
		// $last = $this->db->last_query();
		foreach ( $data as $key => $value )
		{
			$asgn = $this->common->get('dibcase_activity' , array( 'TSK_ID' => $value->TSK_ID) );
			$data[$key]->acts = $asgn;
			if($value->CL_REF != '')
			$data[$key]->cl = $this->common->clientDetails($value->CL_REF);
		}

		if(!empty($data))
		{
			$result["success"] 			=  true;
			$result["data"]    			=  $data;
			// $result["last"]    			=  $last;
		}
		else
		{
			$result["success"] 			=  true;
			$result["data"]    			=  array();
			// $result["last"]    			=  $last;
		}
	}
	else
	{
			$result["error"] 	= 'No Input data';
			$result["usernameexist"] 	= ' ';
	}

	header('Content-Type: application/json');
	echo json_encode($result);exit;
}

public function addReminder()
{
	$postData	=	json_decode($_POST['data'], true);
	if(!empty($postData))
	{
		foreach ($postData['reminders'] as $key => $value)
		{
			if( $value['CountType'] == 'days')
			{
				$date = date_create($value['dateCheck']);
				date_sub($date, date_interval_create_from_date_string($value['Count'].' days'));
				$postData['reminders'][$key]['dateCheck'] = date_format($date, 'Y-m-d');
				$postData['reminders'][$key]['addedBy'] 	= $postData['addedBy'];
				$postData['reminders'][$key]['REF_ID'] 		= $value['TSK_ID'];
				$postData['reminders'][$key]['REF_TYPE'] 	= 'task';
			}
			else if( $value['CountType'] == 'weeks')
			{
				$date = date_create($value['dateCheck']);
				date_sub($date, date_interval_create_from_date_string($value['Count'].' weeks'));
				$postData['reminders'][$key]['dateCheck'] = date_format($date, 'Y-m-d');
				$postData['reminders'][$key]['addedBy'] 	= $postData['addedBy'];
				$postData['reminders'][$key]['REF_ID'] 		= $value['TSK_ID'];
				$postData['reminders'][$key]['REF_TYPE'] 	= 'task';
			}
			unset($postData['reminders'][$key]['TSK_ID']);
		}
		$data = $this->common->insert_batch('dibcase_reminders',$postData['reminders']);
		if($data[0] == 1)
		{
			$result["success"] 	= true;
		}
		else
		{
			$result["error"] 	= 'No Input data';
		}
	}
	else
	{
			$result["error"] 	= 'No Input data';
	}
	header('Content-Type: application/json');
	echo json_encode($result);exit;
}

	public function emailReminderCron($CountType)
	{
		$reminderUsers = $this->common->emailReminderCHeck($CountType,'users','','');
		if(!empty($reminderUsers))
		{
			foreach ( $reminderUsers as $key => $value )
			{
				$email['to'] 			 = 	$this->common->emailByUserRef($value->addedBy);
				$email['from'] 		 = 	'admin@dibcase.com';
				$email['subject']  = 	'Reminder email';
				$data ['task'] 		 = 	$this->common->emailReminderCHeck($CountType,'reminders',$value->addedBy,'task');
				$data ['query'] 	= 	$this->db->last_query();
				$data ['events']   = 	$this->common->emailReminderCHeck($CountType,'reminders',$value->addedBy,'event');
				$email['message']  = 	$this->load->view('email/reminders.php',$data,true);
				if( !empty($data['task']) || !empty($data['events']) )
				{
					if(sendMail($email))
					return 1;
					else
					return 0;
				}
			}
		}
	}

	public function taskLabelPdf()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$data = $this->common->ListTaskAsssigns($postData['ref'],$postData['AssignedTo']);
			foreach ( $data as $key => $value )
			{
				$a  = $this->common->ListTask($value->EmpId,$postData['ref'] , $postData['Completion'], $postData['Case']);
				// print_r($a);
				foreach ( $a['result'] as $kk => $vv )
				{
					$tags = $this->common->TaskTagsAssigns($vv->TSK_ID);
					if(!empty($tags))
					$a['result'][$kk]->tags = $tags;

					$acts = $this->common->getAct('dibcase_activity', array( 'TSK_ID' => $vv->TSK_ID ) );
					if(!empty($acts))
					$a['result'][$kk]->acts = $acts;
				}
				$data[$key]->tasks = $a['result'];
			}
			$output['page'] 			= 	'Claims';
			$output['result'] 			= 	$data;

			// print_r($output['result']); die;
			$html1 						= 	$this->load->view('pdf/task', $output, TRUE);
			// echo $html1;
			$response = $this->generatePDF($html1, ucwords('Claims').rand(0,9999), '', 'F');
			if($response)
			{
				$res["success"]   =  TRUE;
				$res["link"]   =  $response;
			}
			else
			{
				$res['data']			=	array();
			}
		}
		else
		{
				$res["error"] 	= 'No inpu data';
		}
		header('Content-Type: application/json');
		echo json_encode($res);exit;
	}


	public function generatePDF($html1 = NULL, $name = 'PDF', $path = null, $action = 'F')
	{
		ob_start();
		ob_clean();
		ini_set('memory_limit', '-1');
		require_once(APPPATH . 'third_party/tcpdf/tcpdf.php');
		//require_once(APPPATH . "third_party/tcpdf/custom_footer.php");
		//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->SetPrintHeader(false);
		$pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set margins
		//$pdf->SetMargins(5, 72, 1);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('times', '', 12, '', 'false');
		$pdf->AddPage();
		$pgtotlnm 	= 	$pdf->getAliasNbPages();
		$html1 		= 	str_replace('{pgs}', $pgtotlnm, $html1);
		$pdf->writeHTML($html1, true, false, true, false, '');
		$p = FCPATH.'pdf/'.$name . '.pdf';
		$pdf->Output( $p, $action);
		return $p = base_url().'pdf/'.$name . '.pdf';

	}

}
?>
