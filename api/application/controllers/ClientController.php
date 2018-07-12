<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:*");
class ClientController extends CI_Controller {


 public function __construct()
 {
		parent::__construct();
		$this->load->Model('common');
		$this->load->library('zip');
		//$this->load->library('pagination');
  }

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function addclient()
	{
		//error_log('new client add request', 1, "gurbinder@1wayit.com");
		$postData	=	json_decode($_POST['data'], true);
    if(!empty($postData))
		{
			$exist = $this->common->checkexist('dibcase_client',array('CL_SSN' => $postData['CL_SSN'] , 'COM_REF' => $postData['COM_REF']));
			if( $exist == 0 )
			{
				$newDate = date("Y-m-d", strtotime($postData['CL_DOB']));

				$from = new DateTime($newDate);
				$to   = new DateTime('today');
				$postData['CL_AGE'] = $from->diff($to)->y;

				$postData['CL_DOB'] = goodDateCondition($postData['CL_DOB']);
				$postData['CL_STATUS'] = 1;
				$postData['CL_DATECREATED'] = date('Y-m-d H:i:s');

				$date = date("d M Y H:i:s");
				$postData['CL_REF'] = strtotime($date) . rand(0,9999);
				$phn['CL_REF'] = $postData['CL_REF'];

				$scrn['SOCIAL_SEC_DENI']					= $postData['SOCIAL_SEC_DENI'];
				$scrn['SOCIAL_SEC_DENI_DATE']			= goodDateCondition($postData['SOCIAL_SEC_DENI_DATE']);
				$scrn['SOCIAL_SEC_CLAIM_PENDING']	= $postData['SOCIAL_SEC_CLAIM_PENDING'];
				$scrn['SOCIAL_SEC_WHY_DISB']			= $postData['SOCIAL_SEC_WHY_DISB'];

				$scrn['INJ_DATE']			= goodDateCondition($postData['INJ_DATE']);
				$scrn['INJ_DESC']			= $postData['INJ_DESC'];
				$scrn['INJ_PRPTY_DMG']			= $postData['INJ_PRPTY_DMG'];

				$scrn['MED_VTRN']			= $postData['MED_VTRN'];
				$scrn['MED_SER_BRCH']			= $postData['MED_SER_BRCH'];
				$scrn['MED_SER_PERD']			= $postData['MED_SER_PERD'];
				$scrn['MED_CLR_DISCH']			= $postData['MED_CLR_DISCH'];
				$scrn['MED_NOTES']			= $postData['MED_NOTES'];

				$scrn['ACC_DATE']			= goodDateCondition($postData['ACC_DATE']);
				$scrn['ACC_RPT_DATE']			= goodDateCondition($postData['ACC_RPT_DATE']);
				$scrn['ACC_INJ_DESC']			= $postData['ACC_INJ_DESC'];
				$scrn['ACC_INC_DESC']			= $postData['ACC_INC_DESC'];

        $scrn['CLM_APPEAL_DEADLINE']			= goodDateCondition($postData['CLM_APPEAL_DEADLINE']);

				$log['type'] 			= 'client-add';
				$log['CL_REF'] 		= $postData['CL_REF'];
				$log['addedBy'] 	= $postData['CL_ADDEDBY'];
				$log['datetime'] 	= date('Y-m-d H:i:s');


				unset($postData['SOCIAL_SEC_DENI']);
				unset($postData['SOCIAL_SEC_DENI_DATE']);
				unset($postData['SOCIAL_SEC_CLAIM_PENDING']);
				unset($postData['SOCIAL_SEC_WHY_DISB']);
				unset($postData['INJ_DATE']);
				unset($postData['INJ_DESC']);
				unset($postData['INJ_PRPTY_DMG']);
				unset($postData['MED_VTRN']);
				unset($postData['MED_SER_BRCH']);
				unset($postData['MED_SER_PERD']);
				unset($postData['MED_CLR_DISCH']);
				unset($postData['MED_NOTES']);
				unset($postData['ACC_DATE']);
				unset($postData['ACC_RPT_DATE']);
				unset($postData['ACC_INJ_DESC']);
				unset($postData['ACC_INC_DESC']);
        unset($postData['CLM_APPEAL_DEADLINE']);

				$phn = $postData['phones'];
				$emailrows = $postData['emails'];
				if(!empty($postData['emails']))
				{
					foreach( $postData['emails'] as $key=> $em)
					{
            if($em['EMAIL'] != '')
						$emailrows[$key]['CL_REF'] = $postData['CL_REF'];
            else
            unset($emailrows[$key]);
					}
				}
        // print_r($emailrows);
        // die;

				if(!empty($postData['phones']))
				{
					foreach( $postData['phones'] as $key=>$ph)
					{
						if( $ph['PHN_REL'] == 'Client' )
						$phn[$key]['PHN_REL_NAME']			= 'Client';
						$phn[$key]['CL_REF']			= $postData['CL_REF'];
					}
				}

				unset($postData['phones']);
				unset($postData['emails']);
				if(isset($postData['CL_SRC']) && $postData['CL_SRC'] == 'Other')
				{
					$postData['CL_SRC'] = $postData['CL_SRC_CUS'];
					unset($postData['CL_SRC_CUS']);
				}
				$output 				= $this->common->insert('dibcase_client',$postData);
				if($output[0] == 1)
				{
					$result["success"] 			=  'Client Added successfully.';
					$this->common->insert_batch('dibcase_phones',$phn);
					$this->common->insert('dibcase_client_log',$log);
					if (array_filter($scrn))
					{
						$scrn['CL_REF'] = $postData['CL_REF'];
						$date = date("d M Y H:i:s");
						$scrn['CLM_REF'] = strtotime($date) . rand(0,9999);
            $scrn['CLM_ADDEDBY'] 	= $postData['CL_ADDEDBY'];

            $nowUtc = new DateTime( 'now',  new DateTimeZone( 'UTC' ) );
						$scrn['CLM_CREATED'] 						=  $nowUtc->format('Y-m-d H:i:s');

						$this->common->insert('dibcase_claim',$scrn);
					}
          if(!empty($emailrows))
					$this->common->insert_batch('dibcase_cleintemails',$emailrows);
				}
				else
				{
					error_log($output, 1, "gurbinder@1wayit.com");
					$result["error"] 			=  'Data not save.Please fill detail properly.';
				}
			}
			else
			{
					$result["error"] 			=  'SSN number already exist';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}

	}

	public function clientdata()
	{
		$postData	=	json_decode($_POST['data'], true);
    if(!empty($postData))
		{
			$data = $this->common->getrow('dibcase_client',array('CL_REF' => $_POST['data']));
			if( !empty($data) )
			{
				($data->CL_DOB == '0000-00-00' ? $data->CL_DOB = '' : $data->CL_DOB);
				if( $data->CL_PIC != '')
				{
					if( file_exists(FCPATH."assets/uploads/profilePic/".$data->CL_PIC) )
						$data->CL_PIC	= site_url().'assets/uploads/profilePic/'.$data->CL_PIC;
				}
				else
				$data->CL_PIC	= site_url().'assets/uploads/profilePic/demo.png';
				$phones = $this->common->get('dibcase_phones',array('CL_REF' => $_POST['data']));
				$emails = $this->common->get('dibcase_cleintemails',array('CL_REF' => $_POST['data']));
				$claims = $this->common->get('dibcase_claim',array('CL_REF' => $_POST['data']));
        $devNotes = $this->common->get('dibcase_develop_notes',array('CL_REF' => $_POST['data']));
        $billingHoursSum = $this->common->billingHoursSum('dibcase_billing_hours','all',array('CL_REF' => $_POST['data']));
				$result["success"] 			        =  true;
				$result["data"]['basic'] 			  =  $data;
				$result["data"]['phones'] 			=  $phones;
				$result["data"]['emails'] 			=  $emails;
				$result["data"]['claims'] 			=  $claims;
        $result["data"]['devNotes'] 		=  $devNotes;
        $result["data"]['billingHours'] 		=  ($billingHoursSum[0]->bhMinutes != null ? $billingHoursSum[0]->bhMinutes : 0);
			}
			else
			{
				$result["error"] 			=  'No Records Found';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function delete($id,$type)
	{
    $postData = array();
    $postData['id']   = $id;
    $postData['type'] = $type;
    if(!empty($postData))
		{
      $isError = false;
			if($postData['type'] == 'po')
			{
				$msg   =  'Order Deleted Successfully';
        $rows = $this->common->delete('orders',array('orderId' => $postData['id']));
				$rows = $this->common->delete('orderItems',array('poId' => $postData['id']));
				$rows = $this->common->delete('orderLog',array('orderId' => $postData['id']));
				$rows = $this->common->delete('poComments',array('pomsgPOld' => $postData['id']));
			}

			elseif($postData['type'] == 'claim')
			{
				$msg   =  'Claim Deleted Successfully';
				$rows = $this->common->delete('dibcase_claim',array('CLM_REF' => $postData['id']));
			}
      elseif($postData['type'] == 'employee')
			{
				$msg   =  'Employee Deleted Successfully';
				$rows = $this->common->delete('dibcase_employees',array('EMP_REF' => $postData['id']));
			}
      elseif($postData['type'] == 'task')
			{
				$msg   =  'Task Deleted Successfully';
				$rows = $this->common->delete('dibcase_tasks',array('TSK_ID' => $postData['id']));
        $rows = $this->common->delete('dibcase_task_assigns',array('taskID' => $postData['id']));
        $rows = $this->common->delete('dibcase_task_tags_assigns',array('TSK_ID' => $postData['id']));
			}
      elseif($postData['type'] == 'event')
			{
				$msg   =  'Event Deleted Successfully';
				$rows = $this->common->delete('dibcase_events',array('EVENT_REF' => $postData['id']));
        $rows = $this->common->delete('dibcase_tags',array('REF_ID' => $postData['id'] , 'TAG_REF_TYPE' => 'event'));
        $rows = $this->common->delete('dibcase_event_attendee',array('EVENT_REF' => $postData['id']));
        $rows = $this->common->delete('dibcase_reminders',array('REF_ID' => $postData['id'] , 'REF_TYPE' => 'event'));
			}

      elseif($postData['type'] == 'contact')
			{
				$msg   =  'Contact Deleted Successfully';
				$rows = $this->common->delete('dibcase_contacts',array('CON_REF' => $postData['id']));
        $rows = $this->common->delete('dibcase_tags',array('REF_ID' => $postData['id'] , 'TAG_REF_TYPE' => 'contact'));
			}
      elseif($postData['type'] == 'taskAct')
			{
				$msg   =  'Activity Deleted Successfully';
				$rows = $this->common->delete('dibcase_activity',array('ACT_ID' => $postData['id']));
			}
      elseif($postData['type'] == 'template')
			{
				$msg   =  'Template Deleted Successfully';
				$rows = $this->common->delete('dibcase_doc_templates',array('tempId' => $postData['id']));
			}

      else
      {
        $isError = true;
      }

      if(!$isError)
      {
        if($rows != 0)
  			{
  				$result["success"]   =  $msg;
  			}
  			else
  			{
  				$result["error"]   =  'Error Occured try again';
  			}
      }
      else
      {
        $result["error"]   =  'Error Occured try again';
      }

		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function deleteMultiple()
	{
		$postData	=	json_decode($_POST['data'], true);
    if(!empty($postData))
		{
			$selected = $postData['selected'];
			// foreach ($postData['selected'] as $key => $value)
			// {
			// 	$selected[] = $key;
			// }
			if($postData['type'] == 'clients')
			{
				$msg   =  'Clients Deleted Successfully';
				$rows = $this->common->deleteMultiple('dibcase_client','CL_REF',$selected);
			}
			elseif($postData['type'] == 'claims')
			{
				$rows = $this->common->deleteMultiple('dibcase_claim','CLM_REF',$selected);
				$msg   =  'Claims Deleted Successfully';
			}
      elseif($postData['type'] == 'employees')
			{
        $rows = $this->common->deleteMultiple('dibcase_employees','EMP_REF',$selected);
        $rows = $this->common->deleteMultiple('dibcase_users','EMP_REF',$selected);
				$msg   =  'Employees Deleted Successfully';
			}
      elseif($postData['type'] == 'task')
			{
        $rows = $this->common->deleteMultiple('dibcase_tasks','TSK_ID',$selected);
        $rows = $this->common->deleteMultiple('dibcase_task_assigns','taskID',$selected);
        $rows = $this->common->deleteMultiple('dibcase_activity','TSK_ID',$selected);
				$msg   =  'Task Deleted Successfully';
			}
			if($rows != 0)
			{
				$result["success"]   =  $msg;
			}
			else
			{
				$result["error"]   =  'Error Occured try again';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function uploadProfileImage()
	{
		$id = $_POST['id'];
		if( $id == 0 )
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Something happens wrong. Please try again.';
		}
		if( $_FILES['file']['name'] == '' )
		{
			$result["success"] 	 = false;
			$result["error_msg"] = 'Please select an image.';
		}

		if( isset($_FILES) && $_FILES['file']['name'] != '' && $id > 0 )
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
				$response 				= $this->common->updateProfileImage($id,$fileName,$_POST['type']);
				if( !$response )
				{
					$result["success"] 	 = false;
					$result["error_msg"] = 'Something happens wrong. Please try again.';
				}
				else
				{
					$result["success"] 		= true;
					if( file_exists(FCPATH."assets/uploads/profilePic/".$fileName) )
						$result["fileName"] 	= site_url().'assets/uploads/profilePic/'.$fileName;
					else
						$result["fileName"] 	= '';

					$result["success_msg"] 	= 'Image changed successfully.';
				}
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

	public function update()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$isErrr = false;
			switch ($postData['type'])
			{
				case 'client':
					$table = 'dibcase_client';
					$where = array('CL_REF' => $postData['ref']);
					$data  = $postData['data'];
					//dob
					if(isset($data['CL_DOB']))
					{
						$newDate = date("Y-m-d", strtotime($data['CL_DOB']));
						$from = new DateTime($newDate);
						$to   = new DateTime('today');
						$data['CL_AGE'] = $from->diff($to)->y;
						$data['CL_DOB'] = goodDateCondition($data['CL_DOB']);
						//dob
					}

					$log  = array(
					  'addedBy' => $data['addedBy'] ,
					  'CL_REF' => $postData['ref'],
						'type' => 'client-update',
					  'datetime' => date('Y-m-d H:i:s')
					);
					unset($data['addedBy']);
					$this->common->insert('dibcase_client_log',$log);
					break;

          case 'clientsForm':
  					$table = 'dibcase_client';
  					$where = array('CL_REF' => $postData['ref']);
            $data  = $postData['data'];
            $CL_STATUS  = $postData['data']['CL_STATUS'];
            if(isset($postData['data']['CL_CLOSED_DATE']))
  					{
              $closed = goodDateCondition($postData['data']['CL_CLOSED_DATE']);
            }
            break;

          case 'clientNote':
  					$table = 'dibcase_client_notes';
  					$where = array('NOTE_ID' => $postData['ref']);
            $data  = $postData['data'];
            $CL_REF  = $postData['CL_REF'];
            $time  = $data['NOTE_TIME'];
            unset($data['NOTE_TIME']);
            break;

          case 'taskActivity':
  					$table = 'dibcase_activity';
  					$where = array('ACT_ID' => $postData['ref']);
  					$data  = array('ACT_STATUS' => $postData['data']);
            break;

          case 'task':
  					$table = 'dibcase_tasks';
  					$where = array('TSK_ID' => $postData['ref']);
            $data['TSK_STATUS'] = $postData['data'];
            if($postData['data'] == 3)
            $data['TSK_COMPLETE_DATE'] = date('Y-m-d');
            break;


          case 'taskActTitle':
  					$table = 'dibcase_activity';
  					$where = array('ACT_ID' => $postData['ref']);
  					$data  = array('ACT_TITLE' => $postData['new']);
            break;

          case 'taskTitle':
  					$table = 'dibcase_tasks';
  					$where = array('TSK_ID' => $postData['ref']);
  					$data  = $postData['data'];
            break;

          case 'CalendarEventTimeChange':
            if($postData['subtype'] == 'task')
            {
              $table = 'dibcase_tasks';
              $where = array('TSK_ID' => $postData['ref']);
              $data  = array('TSK_DUE_DATE'     =>  goodDateCondition($postData['from']));
            }
            else if($postData['subtype'] == 'event')
            {
              $table = 'dibcase_events';
              $where = array('EVENT_REF'        =>  $postData['ref']);
              $data['EVENT_START_DATE']  =   goodDateCondition($postData['from']);
              $data['EVENT_END_DATE']  =   goodDateCondition($postData['to']);
            }
            break;

            case 'taskDates':
    					$table = 'dibcase_tasks';
    					$where = array('TSK_ID' => $postData['ref']);
    					$data  = array($postData['dataName'] => goodDateCondition($postData['data']) );
              break;

            case 'companySettings':
    					$table = 'dibcase_company';
    					$where = array('COM_REF' => $postData['ref']);
              $data  = $postData['data'];
              break;

            case 'updateEmpProfile':
    					$table = 'dibcase_employees';
    					$where = array('EMP_REF' => $postData['ref']);
              $data  = $postData['data'];
              break;

            case 'updateEmpStatus':
    					$table = 'dibcase_employees';
    					$where = array('EMP_REF' => $postData['ref']);
              $data  = $postData['data'];
              break;

            case 'UserStatus':
    					$table = 'dibcase_users';
    					$where = array('USER_REF' => $postData['ref']);
              $data  = $postData['data'];
              break;

            case 'clientOwner':
    					$table = 'dibcase_client';
    					$where = array('CL_REF' => $postData['ref']);
              $data  = $postData['data'];
              break;






  				default:
  				$isErrr = true;
  					break;
			}


			if(!$isErrr) // if no error
			{
        if($postData['type'] == 'clientsForm')
				$response = $this->common->updateClientsForm($where,$data,$table,$CL_STATUS,$closed);
        else if($postData['type'] == 'taskActivity')
        {
          $response = $this->common->update($where,$data,$table);
          $this->common->updateTaskStatus($where['ACT_ID']);
        }
        else
        $response = $this->common->update($where,$data,$table);

        if($postData['type'] == 'clientNote') // aditional query for client note update
        {
          $exist    = $this->common->checkexist('dibcase_billing_hours' ,array('bhRef'=>$where['NOTE_ID'] , 'bhRefType'=>'clientGeneralNotes'));
          if($exist != 0)
          $response = $this->common->update(array('bhRef'=>$where['NOTE_ID'] , 'bhRefType'=>'clientGeneralNotes'),array( 'bhMinutes' => $time , 'CL_REF' => $CL_REF ),'dibcase_billing_hours');
          else
          $response = $this->common->insert('dibcase_billing_hours',array( 'CL_REF' => $CL_REF , 'bhRef'=>$where['NOTE_ID'] , 'bhRefType'=>'clientGeneralNotes' , 'bhMinutes' => $time));

          $billingHoursSum = $this->common->billingHoursSum('dibcase_billing_hours','all',array('CL_REF' => $CL_REF));
          $result['TotalBilling'] = ($billingHoursSum[0]->bhMinutes != null ? $billingHoursSum[0]->bhMinutes : 0);
        }

				if($response)
				$result["success"]   =  'Updated Successfully';
				else
				$result["error"]   =  'Unknown Error Occured';
        // $result["query"]   =  $this->db->last_query();
			}
			else
			{
				$result["error"]   =  'Unknown Error Occured';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


	public function insert()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$isErrr = false;
      $batchInsert  = false;
			$msg = 'Data Saved Successfully';
			switch ($postData['type'])
			{
				case 'clnotes':
					$table = 'dibcase_client_notes';
					$data  = $postData['notes'];
					$data['CL_REF']  		= $postData['ref'];
					$data['NOTE_DATE']  = date('Y-m-d H:i:s');
          $data['addedBy']  		= $postData['addedBy'];
					$log  = array(
						'addedBy' => $postData['addedBy'] ,
						'type' => 'client-notes' ,
						'CL_REF' => $postData['ref'],
						'datetime' => date('Y-m-d H:i:s')
					);
          $time['bhMinutes']  =  $data['NOTE_TIME'];
          $time['bhRefType']  =  'clientGeneralNotes';
          $time['CL_REF']     =  $postData['ref'];
          unset($data['NOTE_TIME']);
					$msg = 'Note added Successfully';
					break;

				case 'clcall':
					$table = 'dibcase_client_call';
					$data  = $postData;
					$data['CALL_DATE']  = date('Y-m-d H:i:s');
					$log  = array(
						'addedBy' => $postData['addedBy'],
						'type' => 'client-call',
						'CL_REF' => $postData['CL_REF'],
						'datetime' => date('Y-m-d H:i:s')
					);
					unset($data['addedBy']);
					unset($data['type']);
					$msg = 'Call Details Saved Successfully';
					break;

        case 'taskCOmment':
					$table = 'dibcase_task_comments';
					$data  = $postData;
          unset($data['type']);
					$msg = 'Comment added Successfully';
					break;

        case 'taskChecklist':
					$table = 'dibcase_activity';
					$data  = $postData['data'];
          unset($data['type']);
					$msg = 'Checklist added Successfully';
					break;


  			default:
  				$isErrr = true;
  				break;
			}

			if(!$isErrr)
			{
        if($batchInsert)
        $response = $this->common->insert_batch($table,$data);
        else
        $response = $this->common->insert($table,$data);
				if(isset($log))
				{
					$log['reference'] = $response[1];
					$this->common->insert('dibcase_client_log',$log);
				}

        if($postData['type'] == 'clnotes')
        {
          $time['bhRef']  = $response[1];
          $this->common->insert('dibcase_billing_hours',$time);
          $billingHoursSum = $this->common->billingHoursSum('dibcase_billing_hours','all',array('CL_REF' => $time['CL_REF']));
           $response[] = ($billingHoursSum[0]->bhMinutes != null ? $billingHoursSum[0]->bhMinutes : 0);
        }

				if($response)
				$result["success"]   =  $msg;
				else
				$result["error"]   =  'Unknown Error Occured';
        if($postData['type'] == 'taskCOmment')
        {
          $result["comments"] = $this->common->taskCOmments($postData['taskID']);
        }
        $result["response"]   =  $response;
			}
			else
			{
				$result["error"]   =  'Unknown Error Occured';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function get()
	{
    // $a = $this->input->request_headers();
    // print_r($a);
    // die;
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$isErrr = false;
			switch ($postData['type'])
			{
				case 'clientrecent':
					$where['CL_REF']  		= $postData['ref'];
					break;

				case 'clientnotes':
					$table = 'dibcase_client_notes';
					$where['CL_REF']  		= $postData['ref'];
					break;

				case 'social-scrn':
					$where['CLM_REF']  		= $postData['ref'];
					break;

        case 'clients':
          $table = 'dibcase_client';
					$where['COM_REF']  		= $postData['ref'];
					break;

        case 'claimsByClient':
          $table = 'dibcase_claim';
          $where['CL_REF']  		= $postData['ref'];
					break;

        case 'companyData':
          $table = 'dibcase_company';
          $where['COM_REF']  		= $postData['ref'];
					break;

        case 'clientOwner':
          $table = 'dibcase_client';
          $where['CL_REF']  		= $postData['ref'];
					break;



				default:
					$isErrr = true;
					break;
			}
			if(!$isErrr)
			{
				if( $postData['type'] == 'clientrecent' )
				$response = $this->common->getClientRecent($where);

        else if( $postData['type'] == 'clientnotes' )
				// $response = $this->common->getSorted($table,$where,'NOTE_ID','desc');
        $response = $this->common->getClientNotes($where);

				else if( $postData['type'] == 'social-scrn' )
				$response = $this->common->getClientClaim($where);

        else if( $postData['type'] == 'claimsByClient' )
				$response = $this->common->allCLaimList(null,$where['CL_REF']);

				else
				$response = $this->common->get($table,$where);
				if($response)
				{
					$result["data"]   =  $response;
					$result["success"]   =  true;
				}
				else
				$result["error"]   =  'No data';
			}
			else
			{
				$result["error"]   =  'Unknown Error Occured';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}


	public function getTable()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
			$isErrr = false;
			switch ($postData['type'])
			{
				case 'states':
					$table = 'dibcase_states';
					break;

				case 'claimlevels':
					$table = 'dibcase_claimlevels';
					break;

        case 'claims':
					$table = 'dibcase_claim';
					break;

        case 'mcods':
  				$table = 'dibcase_medical_conditions';
  				break;

        case 'medics':
  				$table = 'dibcase_medications';
  				break;

				default:
					$isErrr = true;
					break;
			}
			if(!$isErrr)
			{
				$response = $this->common->getTable($table);
				if($response)
				{
					$result["data"]   =  $response;
					$result["success"]   =  true;
				}
				else
				$result["error"]   =  'No data';
			}
			else
			{
				$result["error"]   =  'Unknown Error Occured';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function sendClientMail()
	{
		if(!empty($_POST))
		{
				$postData	=	json_decode($_POST['data'], true);
				$addedBy = $postData['addedBy'];
				unset($postData['addedBy']);
				$res = $this->sendEmail($postData);
				if($res)
				{
					$log  = array(
						'addedBy' => $addedBy,
						'type' => 'client-email' ,
						'CL_REF' => $postData['ref'],
						'datetime' => date('Y-m-d H:i:s')
					);
					$this->common->insert('dibcase_client_log',$log);
					$result["success"]   =  'Mail Sent';
				}
				else
				{
					$result["error"]   =  'Error in sending mail';
				}
			}
			else
			{
				$result["error"]   =  'No input data';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}


public function sendEmail($email)
{
				$config = Array(
						'mailtype' => 'html',
						'wordwrap' => TRUE,
					'charset' => 'iso-8859-1',
				);

				$ci = & get_instance();
				$ci->load->library('email', $config);
				$ci->email->set_newline("\r\n");
				$ci->email->from($email['fromm'],'Dibcase');
				$ci->email->to($email['too']);
				$ci->email->subject('Dibcase General Email');
				$ci->email->message($email['emailbody']);
				if ($ci->email->send())
				{
						return TRUE;
				}
				else {
						//echo show_error($ci->email->print_debugger());die;
						return FALSE;
				}
				return true;
		}

		public function sendBulkMail()
		{
			if(!empty($_POST))
			{
					$postData	=	json_decode($_POST['data'], true);
					$addedBy = $postData['addedBy'];
					$postData['emailbody'] = $postData['message'];
					unset($postData['message']);
					unset($postData['addedBy']);
					$res = $this->sendEmail($postData);
					if($res)
					{
						foreach ($postData as $key => $value) {
							$log  = array(
								'addedBy' => $addedBy,
								'type' => 'client-email',
								'CL_REF' => $key,
								'datetime' => date('Y-m-d H:i:s')
							);
							$this->common->insert('dibcase_client_log',$log);

						}
						$result["success"]   =  'Mail Sent';
					}
					else
					{
						$result["error"]   =  'Error in sending mail';
					}
				}
				else
				{
					$result["error"]   =  'No input data';
				}
				header('Content-Type: application/json');
				echo json_encode($result);exit;
		}

	public function clientClaims()
	{
			if(!empty($_POST))
			{
				$postData	=	json_decode($_POST['data'], true);
				$response = $this->common->get('dibcase_claim',array('CL_REF' => $postData));
				if($response)
				{
					$result["success"]   =  TRUE;
					$result["data"]   =  $response;
				}
				else
				{
					$result["error"]   =  'No Records';
				}
			}
			else
			{
				$result["error"]   =  'No input data';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}

	public function updateClaimForm()
	{
		if(!empty($_POST))
		{
				$postData	=	json_decode($_POST['data'], true);
        $contacts = $postData['contacts'];
        unset($postData['contacts']);
        // echo "<pre>";
        // print_r($postData);
        // echo "</pre>";
				$postData['SOCIAL_SEC_DENI_DATE']				      = goodDateCondition($postData['SOCIAL_SEC_DENI_DATE']);
				$postData['CLM_APPEAL_DEADLINE']							= goodDateCondition($postData['CLM_APPEAL_DEADLINE']);
				$postData['CLM_CLOSE_DATE']										= goodDateCondition($postData['CLM_CLOSE_DATE']);
				$postData['CLM_DATE_RETAINED']								= goodDateCondition($postData['CLM_DATE_RETAINED']);
				$postData['CLM_SSA_PFL']											= goodDateCondition($postData['CLM_SSA_PFL']);
				$postData['CLM_SSA_INITIAL_FILE_DATE']				= goodDateCondition($postData['CLM_SSA_INITIAL_FILE_DATE']);
				$postData['CLM_SSA_INITIAL_DENIAL_DATE']			= goodDateCondition($postData['CLM_SSA_INITIAL_DENIAL_DATE']);
				$postData['CLM_RECON_FILE_DATE']							= goodDateCondition($postData['CLM_RECON_FILE_DATE']);
				$postData['CLM_RECON_DENIAL_DATE']						= goodDateCondition($postData['CLM_RECON_DENIAL_DATE']);
				$postData['CLM_SSA_ONSET_DATE']								= goodDateCondition($postData['CLM_SSA_ONSET_DATE']);
				$postData['CLM_SSA_DLI']											= goodDateCondition($postData['CLM_SSA_DLI']);
				$postData['CLM_SSA_DATE_LAST_WORKED']					= goodDateCondition($postData['CLM_SSA_DATE_LAST_WORKED']);

				$postData['CLM_STATUS_DATE']									= goodDateCondition($postData['CLM_STATUS_DATE']);
				$postData['CLM_HEARING_SCHEDULED']						= goodDateCondition($postData['CLM_HEARING_SCHEDULED']);
				$postData['CLM_SSA_HEARING_FILE_DATE']				= goodDateCondition($postData['CLM_SSA_HEARING_FILE_DATE']);
				$postData['AC_REQ_DATE']				              = goodDateCondition($postData['AC_REQ_DATE']);
				$postData['AC_STATUS_DATE']				            = goodDateCondition($postData['AC_STATUS_DATE']);

				$postData['CLM_FED_TRANSCRIPT_DATE_DATE']				= goodDateCondition($postData['CLM_FED_TRANSCRIPT_DATE_DATE']);
				$postData['CLM_FED_APPEAL_DATE']				= goodDateCondition($postData['CLM_FED_APPEAL_DATE']);
				$postData['CLM_FED_DECISION_DATE']				= goodDateCondition($postData['CLM_FED_DECISION_DATE']);
				$postData['CLM_FED_OBJ_DECISION_DATE']				= goodDateCondition($postData['CLM_FED_OBJ_DECISION_DATE']);
				$postData['CLM_FED_FILE_DATE']				= goodDateCondition($postData['CLM_FED_FILE_DATE']);

				$postData['CLM_CIRC_APPEAL_DATE']				= goodDateCondition($postData['CLM_CIRC_APPEAL_DATE']);
				$postData['CLM_CIRC_COURT_DATE']				= goodDateCondition($postData['CLM_CIRC_COURT_DATE']);
				$postData['CLM_CIR_COURT_DEC_DATE']				= goodDateCondition($postData['CLM_CIR_COURT_DEC_DATE']);

				$postData['CLM_HEARING_TIME']				= goodDateTimeCondition($postData['CLM_HEARING_TIME']);

        if(isset($postData['CLM_MED_CONDITIONS']))
        {
          $MedCond = $postData['CLM_MED_CONDITIONS'];
          unset($postData['CLM_MED_CONDITIONS']);
        }

        if(isset($postData['CLM_MEDICATIONS']))
        {
          $Medic = $postData['CLM_MEDICATIONS'];
          unset($postData['CLM_MEDICATIONS']);
        }

        if(isset($postData['CLM_MED_PROVIDERS']))
        {
          $MediP = $postData['CLM_MED_PROVIDERS'];
          unset($postData['CLM_MED_PROVIDERS']);
        }


				if(isset($postData['clmRef'])) // already screened - update claim
				{
          if(isset($postData['CL_REF']))
          {
            unset($postData['CL_REF']);
          }
					$clmRef = $postData['clmRef'];
					unset($postData['clmRef']);
					$response = $this->common->update( array('CLM_REF' => $clmRef) , $postData , 'dibcase_claim');
					if($response)
					$result["success"]   =  'Successfully Updated';
					else
					$result["error"]   =  'Error Occured , try again ';

          // $result["query"]   =  $this->db->last_query();

          $this->common->delete('dibcase_claim_med_conditions', array( 'CLM_REF' => $clmRef ));
          $this->common->delete('dibcase_claim_medications', array( 'CLM_REF' => $clmRef ));
				}
				else // not screened (new claim) - add claim
				{
					$date = date("d M Y H:i:s");
					$postData['CLM_REF'] = strtotime($date) . rand(0,9999);
          $clmRef = $postData['CLM_REF'];

          $nowUtc = new DateTime( 'now',  new DateTimeZone( 'UTC' ) );
          $postData['CLM_CREATED'] 						=  $nowUtc->format('Y-m-d H:i:s');

					$response = $this->common->insert('dibcase_claim', $postData);
					if($response[0] == 1)
					{
						$result["success"]   =  'Successfully Added';
					}
					else
					{
						$result["error"]   =  'Error Occured , try again ';
					}
				}

        if(!empty($MedCond))
        {
          foreach ($MedCond as $key => $value)
          {
            if(!isset($value['MCOD_ID']))
            {
              $data  = $this->common->getrow('dibcase_medical_conditions',array('MCOD_TITLE' => $value['MCOD_TITLE']));
              if(empty($data))
              {
                $response = $this->common->insert('dibcase_medical_conditions', array('MCOD_TITLE' => $value['MCOD_TITLE']));
                if($response[0] == 1)
                {
                  $MedCond[$key]['MCOD_ID']  = $response[1];
                }
              }
              else
              {
                  $MedCond[$key]['MCOD_ID'] =  $data->MCOD_ID;
              }
            }

            if(isset($value['MCOD_TITLE']))
            {
              unset($MedCond[$key]['MCOD_TITLE']);
            }
            $MedCond[$key]['CLM_REF'] = $clmRef;
          }
          $this->common->insert_batch('dibcase_claim_med_conditions',$MedCond);
        }

        if(!empty($Medic))
        {
          foreach ($Medic as $key => $value)
          {
            // print_r($value);
            if(!isset($value['MED_ID']))
            {
              $data  = $this->common->getrow('dibcase_medications',array('MED_TITLE' => $value['MED_TITLE']));
              if(empty($data))
              {
                $response = $this->common->insert('dibcase_medications', array('MED_TITLE' => $value['MED_TITLE']));
                if($response[0] == 1)
                {
                  $Medic[$key]['MED_ID']  = $response[1];
                }
              }
              else
              {
                  $Medic[$key]['MED_ID'] =  $data->MED_ID;
              }
            }

            if(isset($value['MED_TITLE']))
            {
              unset($Medic[$key]['MED_TITLE']);
            }

            $Medic[$key]['CLM_REF'] = $clmRef;


            if (!is_numeric($value['MCOD']))
            {
              $data  = $this->common->getrow('dibcase_medical_conditions',array('MCOD_TITLE' => $value['MCOD']));
              // print_r($data);
              $Medic[$key]['MCOD']  = $data->MCOD_ID;
              // print_r($Medic);
            }
            // echo "later";
            // print_r($value);
          }
          // print_r($Medic);
          $this->common->insert_batch('dibcase_claim_medications',$Medic);
        }
        // die;

        $this->common->delete('dibcase_claim_contacts', array( 'CLM_REF' => $clmRef ));

        if(!empty($contacts))
        {
          // print_r($contacts);
          $conArray = array();
          foreach ($contacts as $key => $value)
          {
            $conArray[$key]['choosedTag'] = isset($value['choosedTag']) ? $value['choosedTag'] : '';
            $conArray[$key]['CON_ID'] = $value['id'];
            $conArray[$key]['NOTES'] = $value['NOTES'];
            $conArray[$key]['CLM_REF'] = $clmRef;
          }
          // print_r($conArray);
          $this->common->insert_batch('dibcase_claim_contacts', $conArray);
        }

        $this->common->delete('dibcase_medical_providers', array( 'CLM_REF' => $clmRef ));
        if(!empty($MediP))
        {
          // print_r($contacts);
          $MedicalProviders = array();
          foreach ($MediP as $key => $value)
          {
            $MedicalProviders[$key]['CON_REF']       = $value['object']['CON_REF'];
            $MedicalProviders[$key]['CLM_REF']      = $clmRef;
            $MedicalProviders[$key]['firstVisit']   = $value['firstVisit'];
            $MedicalProviders[$key]['lastVisit']    = $value['lastVisit'];
            $MedicalProviders[$key]['Notes']    = $value['Notes'];
          }
          // print_r($MediP);
          // print_r($MedicalProviders);
          $this->common->insert_batch('dibcase_medical_providers', $MedicalProviders);
        }
			}
			else
			{
				$result["error"]   =  'No input data';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}

	public function SaveLayout()
	{
		if(!empty($_POST))
		{
				$postData	=	json_decode($_POST['data'], true);
				$userref = $postData['USER_REF'];
				$type = $postData['type'];
				$postData['layoutName'] = trim($postData['layoutName']);
				$this->common->delete('dibcase_client_list_template', array('USER_REF'=>$userref , 'templateSlug' => strtolower(str_replace(' ','-',$postData['layoutName']))));
				foreach ($postData['layout'][0] as $key => $value) {
					$data = array(
						'field' => $key,
						'value' => ($value ? 'true' : 'false'),
						'type' => $type,
						'templateName' => $postData['layoutName'],
						'USER_REF' => $userref,
						'templateSlug' =>  strtolower(str_replace(' ','-',$postData['layoutName']))
					);
					$response = $this->common->insert('dibcase_client_list_template', $data);
				}
				if($response[0] == 1)
				{
					$result["success"]   =  'Successfully Added';
				}
				else
				{
					$result["error"]   =  'Error Occured , try again ';
				}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function getLayout()
	{
			if(!empty($_POST))
			{
				$postData	=	json_decode($_POST['data'], true);

				if(!isset($postData['templateSlug']))
				{
					$response = $this->common->dibcase_client_template_slugs($postData);
					if($response)
					{
						$result["success"]   =  TRUE;
						$result["data"]   =  $response;
					}
					else
					{
						$result["error"]   =  'No Records';
					}
				}
				else
				{
					if($postData['templateSlug'] == '')
					{
						unset($postData['templateSlug']);
						$postData['templateSlug'] = $this->common->recentSlug($postData);
					}
					$response = $this->common->get('dibcase_client_list_template',$postData);
					if($response)
					{
						foreach ($response as $key => $value)
						{
							$a[$value->field] = $value->value;
						}
						$result["success"]   =  TRUE;
						$result["data"]   =  array(json_encode($a));
					}
					else
					{
						$result["error"]   =  'No Records';
					}
				}
			}
			else
			{
				$result["error"]   =  'No input data';
			}
			header('Content-Type: application/json');
			echo json_encode($result);exit;
	}


	public function ClaimList()
	{
		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
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

			if(!isset($postData['CaseManager']))
			$postData['CaseManager'] = null;

			if(!isset($postData['Representative']))
			$postData['Representative'] = null;
      $arrayName = array();


			$response = $this->common->ClaimList($postData['cl'],$start ,$perPage ,$postData['SORT'],$postData['direction'],$postData['filter'],$postData['searchText'],$postData['searchAlphabet'],$postData['CaseManager'],$postData['Representative'],$postData['isAppeals']);
      foreach ($response['result'] as $key => $value)
      {
        if(!isset($arrayName[$value->CL_REF]))
        {
          $arrayName[$value->CL_REF] = 1;
        }
        else
        {
          $arrayName[$value->CL_REF] = $arrayName[$value->CL_REF] + 1;
        }
        $response['result'][$key]->index = $arrayName[$value->CL_REF];
      }

			if($response)
			{
				$result["success"]   =  TRUE;
				$result["data"]   =  $response;
        $result["last_query"]   =  $this->db->last_query();
			}
			else
			{
				$output['data']			=	array();
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}


public function allCLaimList()
{
  if(!empty($_POST))
  {
    $postData	=	json_decode($_POST['data'], true);
    $response = $this->common->allCLaimList($postData['COM_REF']);
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



public function headerSearch()
{
  if(!empty($_POST))
  {
    $postData	=	json_decode($_POST['data'], true);
    $data['clients'] = array();
    $data['contacts'] = array();
    foreach (explode(' ',$postData['key']) as $key => $value)
    {
      $cl = $this->common->headerSearch('dibcase_client',$value,$postData['COM_REF']);
      $cn = $this->common->headerSearch('dibcase_contacts',$value,$postData['COM_REF']);
      $data['clients'] = array_merge($data['clients'],$cl);
      $data['contacts'] = array_merge($data['contacts'],$cn);
    }
    $result["data"]   =  $data;
  }
  else
  {
    $result["error"]   =  'No input data';
  }
  header('Content-Type: application/json');
  echo json_encode($result);exit;
}



public function saveSettings()
{
  if(!empty($_POST))
  {
    $postData	=	json_decode($_POST['data'], true);
    $post = $postData;
    unset($post['value']);
    $rows = $this->common->get('dibcase_settings',$post);
    if(empty($rows))
    {
      $row = $this->common->insert('dibcase_settings',$postData);
      if($row[0] == 1)
      {
        $result["success"]   =  'Setting Saved';
      }
      else
        $result["error"]   =  'Error Occured , try again';
    }
    else
    {
      $v = $postData['value'];
      unset($postData['value']);
      $row = $this->common->update($postData,array( 'value' => $v ),'dibcase_settings');
      $result["success"]   =  'Setting Updated';
    }
  }
  else
  {
    $result["error"]   =  'No input data';
  }
  header('Content-Type: application/json');
  echo json_encode($result);exit;
}

public function getSettings()
{
  if(!empty($_POST))
  {
    $postData	=	json_decode($_POST['data'], true);
    $rows = $this->common->getrow('dibcase_settings',$postData);
    $result["success"]   =  true;
    $result["data"]   =  $rows;
  }
  else
  {
    $result["error"]   =  'No input data';
  }
  header('Content-Type: application/json');
  echo json_encode($result);exit;
}

public function getMultipleSettings()
{
  if(!empty($_POST))
  {
    $postData	=	json_decode($_POST['data'], true);
    $rows = $this->common->get('dibcase_settings',$postData);
    $result["success"]   =  true;
    $result["data"]   =  $rows;
  }
  else
  {
    $result["error"]   =  'No input data';
  }
  header('Content-Type: application/json');
  echo json_encode($result);exit;
}




public function client_label_pdf()
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


		$result = $this->common->getcompanyclients($postData['COM_REF'],$start ,$perPage ,$postData['SORT'],$postData['direction'],$postData['filter'],$postData['searchText'],$postData['searchAlphabet']);
	}

	$output['page'] 			= 	'Clients';
	$output['result'] 			= 	$result;
	$output['ClientListCustom'] 			= 	$postData['ClientListCustom'];

	$html1 						= 	$this->load->view('pdf/client', $output, TRUE);
	$response = $this->generatePDF($html1, ucwords('Clients').rand(0,9999), '', 'F');
	if($response)
	{
		$res["success"]   =  TRUE;
		$res["link"]   =  $response;
	}
	else
	{
		$res['data']			=	array();
	}
	header('Content-Type: application/json');
	echo json_encode($res);exit;
}

public function claim_label_pdf()
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

	  if(!isset($postData['CaseManager']))
	  $postData['CaseManager'] = null;

	  if(!isset($postData['Representative']))
	  $postData['Representative'] = null;

		$result = $this->common->ClaimList($postData['cl'],$start ,$perPage ,$postData['SORT'],$postData['direction'],$postData['filter'],$postData['searchText'],$postData['searchAlphabet'],$postData['CaseManager'],$postData['Representative'],$postData['isAppeals']);
	}

	$output['page'] 			       = 	'Claims';
	$output['result'] 			     = 	$result;
	$output['ClaimListCustom'] 	 = 	$postData['ClaimListCustom'];

	// print_r($output['result']); die;
	$html1 						= 	$this->load->view('pdf/claim', $output, TRUE);
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
	header('Content-Type: application/json');
	echo json_encode($res);exit;
}

public function clDevelopNotes()
{
  $_POST['data'] =  urldecode($_POST['data']);
  $postData	=	json_decode($_POST['data'], true);
	if(!empty($postData))
	{
    $rows = $this->common->get('dibcase_develop_notes',array('CL_REF' => $postData['CL_REF']));
    if(!empty($rows))
    {
      $out = $this->common->update(array('CL_REF' => $postData['CL_REF']),array('NOTES' => $postData['NOTES']),'dibcase_develop_notes');
    }
    else
    {
      $res = $this->common->insert('dibcase_develop_notes',$postData);
      $out = $res[0];
    }
    if($out != 0)
    {
      $result["success"]   =  'Successfully Updated';
    }
    else
    {
      $result["error"]   =  'Try Again Later';
    }
    header('Content-Type: application/json');
  	echo json_encode($result);exit;
  }
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


public function downloadExcel()
{
        $claimLevels = $this->common->getTable('dibcase_claimlevels');
        sort($claimLevels);
        // echo $claimLevels[0]->CL_FIRST_NAME;
        // die;
        require_once(APPPATH . 'third_party/PHPExcel.php');
        $name = "Update Claims";

        $setStyle = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 12,
                'bold' => TRUE,
                'color' => array(
                    'rgb' => 'FFFFFF'
                ),
            ),
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => '2685E1',
                ),
            ),
        );

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('UpdateClaim');

        for ($x = 0; $x < count($claimLevels); $x++) {
            $objPHPExcel->getActiveSheet()->setCellValue('D' . ($x + 1), $claimLevels[$x]->CLVL_TITLE);
        }
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle('Update Claim');
        //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->applyFromArray($setStyle);
        $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($setStyle);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($setStyle);
        //$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Date');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'CLM NOTES');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'CLM CLAIM TYPE');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'CLAIM LEVELS');


        for ($x = 1; $x < 300; $x++)
        {
            $objValidation = $objPHPExcel->getActiveSheet()->getCell('D' . ($x + 1))->getDataValidation();
            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(false);
            $objValidation->setShowInputMessage(true);
            $objValidation->setShowErrorMessage(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setFormula1('UpdateClaim!$D$1:$D$' . (count($claimLevels)));
        }
        $objPHPExcel->getSheetByName('UpdateClaim')->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);



    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
}

public function ClientAutocompleteSearch($com,$key)
{
  if($key != '')
  {
    $response = $this->common->ClientAutocompleteSearch($com,$key);
    $clients = array();
    foreach ($response as $key => $value)
    {
      $clients[$key]['CL_NAME'] = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4);
      $clients[$key]['CL_REF']  = $value->CL_REF;
    }
    header('Content-Type: application/json');
    echo json_encode($clients);exit;
  }
}

public function ClaimAutocompleteSearch($com,$cl,$key)
{
  if($key != '')
  {
    $response = $this->common->ClaimAutocompleteSearch($com,$cl,$key);
    $clients = array();
    $arrayName = array();
    foreach ($response as $key => $value)
    {
      if(!isset($arrayName[$value->CL_REF]))
      {
        $arrayName[$value->CL_REF] = 1;
      }
      else
      {
        $arrayName[$value->CL_REF] = $arrayName[$value->CL_REF] + 1;
      }
      $clients[$key]['index'] = $arrayName[$value->CL_REF];
      $clients[$key]['CL_NAME'] = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4).' (Claim '.$clients[$key]['index'].' of '.$value->claimCount.' )';
      $clients[$key]['CLM_REF']  = $value->CLM_REF;
      $clients[$key]['claimCount']  = $value->claimCount;
    }
    header('Content-Type: application/json');
    echo json_encode($clients);exit;
  }
}



public function templatedummy()
{
  $this->load->library('parser');
  $_POST['data'] =  urldecode($_POST['data']);
  $postData	=	json_decode($_POST['data'], true);
  $data = array(
              'Name' => 'Gurbinder singh',
              'Ssn' => '4324 423432 4324324'
              );

  $replacements = array();

  foreach($data as $key=>$val)
  {
    $replacements['({'.$key.'})'] = $val;
  }
  $template2 = preg_replace( array_keys( $replacements ), array_values( $replacements ), $postData['data'] );

  $response = $this->generatePDF($template2, ucwords('Dummy').rand(0,9999), '', 'F');
	if($response)
	{
		$res["success"]   =  TRUE;
		$res["link"]   =  $response;
	}
	else
	{
		$res['data']			=	array();
	}
	header('Content-Type: application/json');
	echo json_encode($res);exit;
}

public function mcodSearch($com,$key)
{
  if($key != '')
  {
    $response = $this->common->mcodSearch($com,$key);
    // $clients = array();
    // foreach ($response as $key => $value)
    // {
    //   $clients[$key]['CL_NAME'] = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4);
    //   $clients[$key]['CL_REF']  = $value->CL_REF;
    // }
    header('Content-Type: application/json');
    echo json_encode($response);exit;
  }
}

public function medicSearch($com,$key)
{
  if($key != '')
  {
    $response = $this->common->medicSearch($com,$key);
    // $clients = array();
    // foreach ($response as $key => $value)
    // {
    //   $clients[$key]['CL_NAME'] = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4);
    //   $clients[$key]['CL_REF']  = $value->CL_REF;
    // }
    header('Content-Type: application/json');
    echo json_encode($response);exit;
  }
}

  public function saveTemplate()
  {
    if(!empty($_POST))
    {
      $_POST['data'] =  urldecode($_POST['data']);
      $postData	=	json_decode($_POST['data'], true);
      if(isset($postData['tempId']))
      {
        $id = $postData['tempId'];
        unset($postData['tempId']);
        $res = $this->common->update(array('tempId' => $id),$postData,'dibcase_doc_templates');
      }
      else
      {
        $res = $this->common->insert('dibcase_doc_templates',$postData);
      }
      if($res[0] == 1)
      {
        $result["success"]   =  'Successfully Added';
      }
      else
      {
        $result["error"]   =  'Error Occured , try again ';
      }
    }
    else
    {
      $result["error"]   =  'No input data';
    }
    header('Content-Type: application/json');
    echo json_encode($result);exit;
  }

  public function getTemplates($type,$ref)
  {
    if($type && $ref)
    {
      $where = array('ref'=>$ref , 'refType'=>$type);
      $rows = $this->common->get('dibcase_doc_templates', $where);
      $result["success"]   =  true;
      $result["data"]   =  $rows;
    }
    else
    {
      $result["error"]   =  'No input data';
    }
    header('Content-Type: application/json');
    echo json_encode($result);exit;
  }

  public function getTemplate($id)
  {
    if($id)
    {
      $rows = $this->common->getrow('dibcase_doc_templates',array('tempId'=>$id));
      $result["success"]  =  true;
      $result["data"]     =  $rows;
    }
    else
    {
      $result["error"]   =  'No input data';
    }
    header('Content-Type: application/json');
    echo json_encode($result);exit;
  }

  public function importClients()
  {
    $replace = array(
      'SSN'                   => 'CL_SSN',
      'SALUTATION'            => 'CL_SAL',
      'FIRST_NAME'            => 'CL_FIRST_NAME',
      'MIDDLE_NAME'           => 'CL_MIDDLE_NAME',
      'LAST_NAME'             => 'CL_LASTNAME',
      'DATE_OF_BIRTH'         => 'CL_DOB',
      'ADDRESS1'              => 'CL_ADDRESS',
      'ADDRESS2'              => 'CL_ADDRESS2',
      'CITY'                  => 'CL_CITY',
      'STATE'                 => 'CL_STATE',
      'ZIP_CODE'              => 'CL_ZIP',
      'STATUS'                => 'CL_STATUS',
      'PHONE1'                => 'PHN_NUMBER1',
      'PHONE1_RELATION'       => 'PHN_REL1',
      'PHONE2'                => 'PHN_NUMBER2',
      'PHONE2_RELATION'       => 'PHN_REL2',
      'EMAIL1'                => 'EMAIL1',
      'EMAIL1_RELATION'       => 'EMAIL_REL1',
      'EMAIL2'                => 'EMAIL2',
      'EMAIL2_RELATION'       => 'EMAIL_REL2',
    );

    if(!empty($_POST))
    {
      $postData	=	json_decode($_POST['data'], true);
      $phones = array();
      $emails = array();

      $error = false;
      $Ok = 0;
      $NotOk = 0;

      foreach ($postData['data'] as $key => $value)
      {

        foreach ($value as $k => $val)
        {

          $key2 = $replace[$k];
          $postData['data'][$key][$key2] = $val;
          unset($postData['data'][$key][$k]);

          $postData['data'][$key]['CL_ADDEDBY'] = $postData['CL_ADDEDBY'];
          $postData['data'][$key]['COM_REF']    = $postData['COM_REF'];
          $date = date("d M Y H:i:s");
          $postData['data'][$key]['CL_REF'] 								=  strtotime($date) . rand(0,9999);


          for ($i=1; $i < 3; $i++) // inseting phones and emails to another array
          {
            if(isset($value['PHONE'.$i]))
            {
              $phones[$i]['CL_REF'] = $postData['data'][$key]['CL_REF'];
              $phones[$i]['PHN_NUMBER'] = $value['PHONE'.$i];
              unset($postData['data'][$key]['PHN_NUMBER'.$i]);
              //print_r($postData['data'][$key]);
              //print_r($postData['data'][$key]['PHONE'.$i]);
            }
            if(isset($value['PHONE'.$i.'_RELATION']))
            {
              $phones[$i]['PHN_REL'] = $value['PHONE'.$i.'_RELATION'];
              unset($postData['data'][$key]['PHN_REL'.$i]);
            }
            if(isset($value['EMAIL'.$i]))
            {
              $emails[$i]['CL_REF'] = $postData['data'][$key]['CL_REF'];
              $emails[$i]['EMAIL'] = $value['EMAIL'.$i];
              unset($postData['data'][$key]['EMAIL'.$i]);
            }
            if(isset($value['EMAIL'.$i.'_RELATION']))
            {
              $emails[$i]['EMAIL_REL'] = $value['EMAIL'.$i.'_RELATION'];
              unset($postData['data'][$key]['EMAIL_REL'.$i]);
            }
          }

        }

        $exist = $this->common->checkexist('dibcase_client',array('CL_SSN' => $postData['data'][$key]['CL_SSN']));
        if($exist == 0)
        {
          $out = $this->common->insert('dibcase_client',$postData['data'][$key]);
          if($out[0] == 1)
          {
            // print_r($phones);
            // print_r($emails);
            if(!empty($emails))
            $out = $this->common->insert_batch('dibcase_cleintemails',$emails);
            if(!empty($phones))
            $out = $this->common->insert_batch('dibcase_phones',$phones);
            // die;
          }
          else
          {
            $error = true;
          }
          $Ok++;
        }
        else
        {
          $NotOk++;
        }
      }

      $msg = 'Clients Imported successfully';
      if(!$error)
      {
        $result["success"]   = $msg;
        if($NotOk != 0)
        $result["success"]   = $Ok." Clients imported ,".$NotOk." unable to import, SSN number already exist";
      }
      else
      $result["error"]     = true;
    }
    else
    {
      $result["error"]   =  'No input data';
    }
    header('Content-Type: application/json');
    echo json_encode($result);exit;
  }

  public function exportClients($com)
  {
    require_once APPPATH . '/third_party/PHPExcel/PHPExcel.php';

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Dibcase Clients');
		$objPHPExcel->createSheet();

		// $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
		// $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

		foreach (range('A', 'Z') as $key => $value)
		{
			$objPHPExcel->getActiveSheet()->getColumnDimension($value)->setWidth(30);
		}

		$fields = array(
      'SSN'                   => 'CL_SSN',
      'SALUTATION'            => 'CL_SAL',
      'FIRST_NAME'            => 'CL_FIRST_NAME',
      'MIDDLE_NAME'           => 'CL_MIDDLE_NAME',
      'LAST_NAME'             => 'CL_LASTNAME',
      'DATE_OF_BIRTH'         => 'CL_DOB',
      'ADDRESS1'              => 'CL_ADDRESS',
      'ADDRESS2'              => 'CL_ADDRESS2',
      'CITY'                  => 'CL_CITY',
      'STATE'                 => 'CL_STATE',
      'ZIP_CODE'              => 'CL_ZIP',
      'STATUS'                => 'CL_STATUS',
      'PHONE1'                => 'PHN_NUMBER1',
      'PHONE1_RELATION'       => 'PHN_REL1',
      'PHONE2'                => 'PHN_NUMBER2',
      'PHONE2_RELATION'       => 'PHN_REL2',
      'EMAIL1'                => 'EMAIL1',
      'EMAIL1_RELATION'       => 'EMAIL_REL1',
      'EMAIL2'                => 'EMAIL2',
      'EMAIL2_RELATION'       => 'EMAIL_REL2',

		);

		$i = 0;
		foreach ($fields as $key => $value)
		{
			$objPHPExcel->getActiveSheet()->setCellValue(range('A', 'Z')[$i].'1', $key);
			$i++;
		}

		$Clients = $this->common->get('dibcase_client',array('COM_REF' => $com));
    foreach ($Clients as $key => $value)
    {
      $phones = $this->common->get('dibcase_phones',array('CL_REF' => $value->CL_REF));
      if(!empty($phones))
      {
        for ($i=0; $i < count($phones); $i++)
        {
          $ii = $i+1;
          $newKey = 'PHONE'.$ii;
          $newKey1 = 'PHONE'.$ii.'_RELATION';
          $Clients[$key]->$newKey  = $phones[$i]->PHN_NUMBER;
          $Clients[$key]->$newKey1 = $phones[$i]->PHN_REL;
          if($i == 1)
          continue;
        }
      }

      $emails = $this->common->get('dibcase_cleintemails',array('CL_REF' => $value->CL_REF));
      if(!empty($emails))
      {
        for ($i=0; $i < count($emails); $i++)
        {
          $ii = $i+1;
          $newKey = 'EMAIL'.$ii;
          $newKey1 = 'EMAIL'.$ii.'_RELATION';
          $Clients[$key]->$newKey  = $emails[$i]->EMAIL;
          $Clients[$key]->$newKey1 = $emails[$i]->EMAIL_REL;
          if($i == 1)
          continue;
        }
      }
    }
    // echo "<pre>";
    // print_r($Clients);

		$excelRow = 2;
		for ($i=0; $i < count($Clients); $i++)
		{
			// print_r($Clients[$i]);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$excelRow, $Clients[$i]->CL_SSN);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$excelRow, $Clients[$i]->CL_SAL);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$excelRow, $Clients[$i]->CL_FIRST_NAME);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$excelRow, $Clients[$i]->CL_MIDDLE_NAME);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$excelRow, $Clients[$i]->CL_LASTNAME);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$excelRow, $Clients[$i]->CL_DOB);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$excelRow, $Clients[$i]->CL_ADDRESS);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$excelRow, $Clients[$i]->CL_ADDRESS2);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$excelRow, $Clients[$i]->CL_CITY);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$excelRow, $Clients[$i]->CL_STATE);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$excelRow, $Clients[$i]->CL_ZIP);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$excelRow, $Clients[$i]->CL_STATUS);
      if(isset($Clients[$i]->PHONE1))
      {
        $objPHPExcel->getActiveSheet()->setCellValue('M'.$excelRow, $Clients[$i]->PHONE1);
        $objPHPExcel->getActiveSheet()->setCellValue('N'.$excelRow, $Clients[$i]->PHONE1_RELATION);
      }
      if(isset($Clients[$i]->PHONE2))
      {
        $objPHPExcel->getActiveSheet()->setCellValue('O'.$excelRow, $Clients[$i]->PHONE2);
        $objPHPExcel->getActiveSheet()->setCellValue('P'.$excelRow, $Clients[$i]->PHONE2_RELATION);
      }
      if(isset($Clients[$i]->EMAIL1))
      {
        $objPHPExcel->getActiveSheet()->setCellValue('Q'.$excelRow, $Clients[$i]->EMAIL1);
        $objPHPExcel->getActiveSheet()->setCellValue('R'.$excelRow, $Clients[$i]->EMAIL1_RELATION);
      }
      if(isset($Clients[$i]->EMAIL2))
      {
        $objPHPExcel->getActiveSheet()->setCellValue('S'.$excelRow, $Clients[$i]->EMAIL2);
        $objPHPExcel->getActiveSheet()->setCellValue('T'.$excelRow, $Clients[$i]->EMAIL2_RELATION);
      }

			$excelRow++;
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="DibcaseContactsExcelExport.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');

  }

  public function pomsginsert()
  {
    $json 	= 	file_get_contents("php://input");
    $postData 	= 	json_decode($json, true);
	  if(!empty($postData))
	  {
      $postData['pomsgDate'] = date('Y-m-d');
      $files = $postData['files'];
      unset($postData['files']);
      $res = $this->common->insert('poComments',$postData);
      if(!empty($files))
      {
        $filesArray = array();
        foreach ($files as $key => $value)
        {
          $filesArray[$key]['commentId'] = $res[1];
          $filesArray[$key]['filename']  = $value;
        }
      }
      if(!empty($filesArray))
      {
        $this->common->insert_batch( 'poCommentFiles', $filesArray );
      }
      if($res[0] == 1)
      {
        $result["success"]   =  'Successfully Added';
      }
      else
      {
        $result["error"]   =  'Error Occured , try again ';
      }
    }
    else
    {
      $result["error"]   =  'No input data';
    }
    header('Content-Type: application/json');
    echo json_encode($result);exit;
  }

  public function getPomsg($id)
  {
	  if($id != '')
	  {
      $res = $this->common->getPomsg($id);
       foreach ($res as $key => $value)
       {
         $res[$key]->files = $this->common->get('poCommentFiles',array('commentId' => $value->pomsgId));
       }
      $result["data"]   =  $res;
      $result["success"]   =  true;
    }
    else
    {
      $result["error"]   =  'No input data';
    }
    header('Content-Type: application/json');
    echo json_encode($result);exit;
  }

}


?>
