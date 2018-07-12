<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers:*");
class EventController extends CI_Controller {

	 public function __construct()
	 {
	    parent::__construct();
	    $this->load->Model('employee');
			$this->load->Model('common');
			$this->load->Model('event');
			$this->load->library('encrypt');
		}

	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function submitEvent()
	{
		$postData	=	json_decode($_POST['data'], true);
		if(!empty($postData))
		{
					$date = date("d M Y H:i:s");
					$date1 = date("d M Y H:i:s");

					$reminders  =  $postData['reminders'];
					$tags 			=  $postData['tags'];
					$attendees  =  $postData['attendees'];
					$cref  			=  $postData['cref'];
					$uref  			=  $postData['uref'];
					$postData['addedBy']  =  $postData['uref'];
					$postData['COM_REF'] = $postData['cref'];
					$postData['CLR'] = 'blue';
					unset($postData['reminders']);
					unset($postData['tags']);
					unset($postData['attendees']);
					unset($postData['cref']);
					unset($postData['uref']);


					$postData['EVENT_START_DATE'] 				=  goodDateCondition($postData['EVENT_START_DATE']);
					$postData['EVENT_END_DATE'] 					=  goodDateCondition($postData['EVENT_END_DATE']);
					if(isset($postData['EVENT_START_TIME']))
					{
						$postData['EVENT_START_TIME'] 				=  date("H:i" , strtotime($postData['EVENT_START_TIME']));
						$postData['EVENT_END_TIME'] 					=  date("H:i" , strtotime($postData['EVENT_END_TIME']));

						$sT = new DateTime($postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME']);
						$En = new DateTime($postData['EVENT_END_DATE'].' '.$postData['EVENT_END_TIME']);
						$otherTZ  = new DateTimeZone('UTC');
						$sTO = $sT->setTimezone($otherTZ);
						$EnO = $En->setTimezone($otherTZ);
						// echo $postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME'];
						// echo "<br>";
						// echo $postData['EVENT_END_DATE'].' '.$postData['EVENT_START_TIME'];
						// print_r($sTO);
						// print_r($EnO);
						$postData['EVENT_START_TIME'] 				=  $sTO->format('H:i');
						$postData['EVENT_END_TIME'] 					=  $EnO->format('H:i');
						$postData['EVENT_START_DATE'] 				=  $sTO->format('Y-m-d');
						$postData['EVENT_END_DATE'] 					=  $EnO->format('Y-m-d');
						// echo "<br>";

						// echo $postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME'];
						// echo "<br>";
						// echo $postData['EVENT_END_DATE'].' '.$postData['EVENT_START_TIME'];

					}
					else
					{
						$postData['EVENT_ALL_DAY'] 						=  1;
					}

					if($postData['EVENT_TYPE'] == 'Add_new')
					{
						$postData['EV_CUS_TYPE'] = $postData['EV_CUS_TYPE'];
					}



					if(isset($postData['EVENT_REF'])) //update
					{
						if(!isset($postData['EVENT_CL_REF']))
						{
							$postData['EVENT_CL_REF'] = '';
						}

						$ref = $postData['EVENT_REF'];
						unset($postData['EVENT_REF']);
						$GOOGLE_CAL_ID = $postData['GOOGLE_CAL_ID'];
						unset($postData['GOOGLE_CAL_ID']);


						$out = $this->common->update(array( 'EVENT_REF' => $ref ),$postData,'dibcase_events');
						// echo $this->db->last_query(); die;
						$postData['EVENT_REF'] = $ref;
						$output[0] = 1;

						$this->common->delete('dibcase_event_attendee',array( 'EVENT_REF' => $ref ));
						$this->common->delete('dibcase_tags',array( 'REF_ID' => $ref ));
						$this->common->delete('dibcase_reminders',array( 'REF_ID' => $ref , 'REF_TYPE' => 'event' ));
					}
					else //insert
					{
						$nowUtc = new DateTime( 'now',  new DateTimeZone( 'UTC' ) );

						$postData['EVENT_CREATE'] 						=  $nowUtc->format('Y-m-d H:i:s');
						$postData['EVENT_REF'] 								=  strtotime($date) . rand(0,9999);
						$ref = $postData['EVENT_REF'];
						$output = $this->common->insert('dibcase_events',$postData);
						unset($postData['EVENT_REF']);
					}

					if( $output[0] == 1 )
					{
						if(!empty($attendees))
						{
							foreach ($attendees as $key => $value)
							{
								unset($attendees[$key]['ATNDY_NAME']);
								if(isset($attendees[$key]['eaid']))
								unset($attendees[$key]['eaid']);
								$attendees[$key]['EVENT_REF'] = $ref;
							}
							$this->common->insert_batch('dibcase_event_attendee',$attendees);
						}
							$credentials = $this->event->userApiCredentials($uref);
							if($credentials)
							{
									if( $client = $this->getClient($uref) )
									{
										$atd = [];
										if(!empty($attendees))
										{
											foreach ($attendees as $key => $value)
											{
												$em = $this->common->emailByEmpRef($value['ATNDY_REF']);
												$atd[] = array('email'=> $em);
											}
										}

										$service = new Google_Service_Calendar($client);
										if(!isset($postData['EVENT_START_TIME']))
										{
												$postData['EVENT_START_TIME'] = '00:00';
												$postData['EVENT_END_TIME'] = '00:00';
										}

										$strt = explode(' ',$postData['EVENT_START_TIME']);
										$end  = explode(' ',$postData['EVENT_END_TIME']);
										$postData['EVENT_START_TIME'] = $strt[0];
										$postData['EVENT_END_TIME'] = $end[0];
										$calendarId = 'primary';
										$event = new Google_Service_Calendar_Event(array(
										  'summary' => $postData['EVENT_TITLE'],
										  'location' => $postData['EVENT_LOCATION'],
										  'description' => $postData['EVENT_NOTES'],
										  'start' => array(
										    'dateTime' => str_replace('/','-',$postData['EVENT_START_DATE']).'T'.$postData['EVENT_START_TIME'].':00',
										    'timeZone' => 'Asia/Kolkata',
										  ),
										  'end' => array(
												'dateTime' => str_replace('/','-',$postData['EVENT_END_DATE']).'T'.$postData['EVENT_END_TIME'].':00',
										    'timeZone' => 'Asia/Kolkata',
										  ),
										  'recurrence' => array(
										    'RRULE:FREQ=DAILY;COUNT=2'
										  ),
										  'attendees' => $atd,
										));

										if(isset($postData['EVENT_REF']))
										{
											if($GOOGLE_CAL_ID != '')
											$event = $service->events->update('primary',$GOOGLE_CAL_ID, $event);
											else
											$event = $service->events->insert($calendarId, $event);
										}
										else
										{
											$event = $service->events->insert($calendarId, $event);
											//update google event id to database
											$this->common->update(array('EVENT_REF'=>$ref),array('GOOGLE_CAL_ID'=>$event->id),'dibcase_events');
										}
									}
							}

						if(!empty($tags))
						{
							$tagsArray = array();
							foreach ($tags as $k => $v)
							{
								$tagsArray[$k]['TAG_TITLE'] = $v;
								$tagsArray[$k]['COM_REF'] = $cref;
								$tagsArray[$k]['TAG_REF_TYPE'] = 'events';
								$tagsArray[$k]['REF_ID'] = $ref;
							}
							$this->common->insert_batch('dibcase_tags',$tagsArray);
						}

						if(!empty($reminders))
						{
							$remindersArray = array();
							foreach ( $reminders as $kk => $vv )
							{
								if( $vv['CountType'] == 'days')
								{
									$date = date_create($postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME']);
									date_sub($date, date_interval_create_from_date_string($vv['Count'].' days'));
									$remindersArray[$kk]['dateCheck'] = date_format($date, 'Y-m-d H:i:s');
									$remindersArray[$kk]['addedBy'] = $uref;
									$remindersArray[$kk]['Count'] = $vv['Count'];
									$remindersArray[$kk]['CountType'] = $vv['CountType'];
									$remindersArray[$kk]['REF_ID'] 		= $ref;
									$remindersArray[$kk]['REF_TYPE'] 	= 'event';
									$remindersArray[$kk]['reminderType'] = $vv['reminderType'];
								}
								else if( $vv['CountType'] == 'weeks')
								{
									$date = date_create($postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME']);
									date_sub($date, date_interval_create_from_date_string($vv['Count'].' weeks'));
									$remindersArray[$kk]['dateCheck'] = date_format($date, 'Y-m-d H:i:s');
									$remindersArray[$kk]['addedBy'] = $uref;
									$remindersArray[$kk]['Count'] = $vv['Count'];
									$remindersArray[$kk]['CountType'] = $vv['CountType'];
									$remindersArray[$kk]['REF_ID'] 		= $ref;
									$remindersArray[$kk]['REF_TYPE'] 	= 'event';
									$remindersArray[$kk]['reminderType'] = $vv['reminderType'];
								}
								else if( $vv['CountType'] == 'hours')
								{
									$remindersArray[$kk]['dateCheck'] = date('Y-m-d H:i', strtotime($postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME']) - $vv['Count'] * 3600);
									$remindersArray[$kk]['addedBy'] = $uref;
									$remindersArray[$kk]['Count'] = $vv['Count'];
									$remindersArray[$kk]['CountType'] = $vv['CountType'];
									$remindersArray[$kk]['REF_ID'] 		= $ref;
									$remindersArray[$kk]['REF_TYPE'] 	= 'event';
									$remindersArray[$kk]['reminderType'] = $vv['reminderType'];
								}
								else if( $vv['CountType'] == 'minutes')
								{
									$remindersArray[$kk]['dateCheck'] = date('Y-m-d H:i', strtotime($postData['EVENT_START_DATE'].' '.$postData['EVENT_START_TIME']) - $vv['Count'] * 60);
									$remindersArray[$kk]['addedBy'] = $uref;
									$remindersArray[$kk]['Count'] = $vv['Count'];
									$remindersArray[$kk]['CountType'] = $vv['CountType'];
									$remindersArray[$kk]['REF_ID'] 		= $ref;
									$remindersArray[$kk]['REF_TYPE'] 	= 'event';
									$remindersArray[$kk]['reminderType'] = $vv['reminderType'];
								}
							}
							// dbug('a','d',$remindersArray);
							$data = $this->common->insert_batch('dibcase_reminders',$remindersArray);
						}

						$result["success"] 		= 'Event  added successfully.';
						if($credentials)
						$result["googleEventLink"] 		=  $event->htmlLink;

					}
					else
					{
						$result["error"] 		= 'Something wrong , try again';
					}
			}
			else
			{
					$result["error"] 	= 'No input data';
					$result["usernameexist"] 	= ' ';
			}

			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}

		public function listEvent2()
		{
			$postData = (array) json_decode(base64_decode($_GET['aa'],true));
			if(!empty($postData))
			{
				if(isset($postData['showTask']) && $postData['showTask'] == 'true')
				{
					$task = $this->event->ListTask($postData['COM_REF'],$postData['USER_REF']);
					foreach ($task as $k => $v)
					{
							$id = $k+1;
							$task[$k]->start = $v->TSK_DUE_DATE.' 05:30';
							$task[$k]->end   = $v->TSK_DUE_DATE.' 23:59';
							$task[$k]->title = $v->TSK_TITLE;
							$task[$k]->id   = 'task_'.$v->TSK_ID;

							$primary = $this->event->employeeColor($v->TSK_ADDEDBY);
							// $a = array( 'primary' => $primary , 'secondary' => '#D1E8FF' );
							$task[$k]->background  = $primary ;
							$task[$k]->borderColor  = $primary ;
							$task[$k]->type   = 'task' ;
							$task[$k]->ref   = $v->TSK_ID ;
					}
				}

				$AppealDeadlines = array();
				if(isset($postData['AppealDeadlines']) && $postData['AppealDeadlines'] == 'true')
				{
					$AppealDeadlines = $this->event->AppealDeadlines($postData['COM_REF'],'all');
					foreach ($AppealDeadlines as $key => $value)
					{
						$value->title  = 'Appeal D '.$value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1);
						$value->start  = $value->start.' 05:30';
						$value->end    = $value->end.' 23:59';
						$value->id     = 'claim_'.$value->CLM_REF.'_'.$value->CL_REF;
						$value->type   = 'claim';
					}
					// print_r($AppealDeadlines);
				}
				// die;


				$data = $this->event->listEvent($postData);
				foreach ($data as $key => $value)
				{
					// if($key == 0)
					// continue;
						$id = $key+1;
						$primary = $this->event->employeeColor($value->addedBy);

						// echo $data[$key]->start;
						// echo "<br>";
						// echo $data[$key]->end;
						// echo "<br>";
						// echo $data[$key]->startTime;
						// echo "<br>";
						// echo $data[$key]->endTime;
						// echo "<br>";

						$userRow = $this->common->getrow('dibcase_users', array( 'USER_REF' => $postData['USER_REF']));
	          if(!empty($userRow) && $userRow->timeZone != '')
						{
							$userTimeZone = $userRow->timeZone;
						}
						else
						{
							$userTimeZone = 'UTC';
						}
						// echo $userTimeZone;

						$startObj = new DateTime( $data[$key]->start.' '.$data[$key]->startTime,  new DateTimeZone('UTC') );
						$endObj   = new DateTime( $data[$key]->end.' '.$data[$key]->endTime,  new DateTimeZone('UTC') );
						// echo date_default_timezone_get();
						// print_r($startObj);
						// print_r($endObj);
						$sTO = $startObj->setTimezone(new DateTimeZone($userTimeZone));
						$eNO = $endObj->setTimezone(new DateTimeZone($userTimeZone));

// 						print_r($sTO);
// 						print_r($eNO);
// die;
						$data[$key]->start 						=  $sTO->format('Y-m-d');
						$data[$key]->end 						=  $eNO->format('Y-m-d');
						$data[$key]->startTime 						=  $sTO->format('H:i:s');
						$data[$key]->endTime 						=  $eNO->format('H:i:s');

						// echo $data[$key]->start;
						// echo "<br>";
						// echo $data[$key]->end;
						// echo "<br>";
						// echo $data[$key]->startTime;
						// echo "<br>";
						// echo $data[$key]->endTime;
						// echo "<br>";
						// die;

						// $a = array( 'primary' => $primary , 'secondary' => '#D1E8FF' );
						$data[$key]->background  	= $primary ;
						$data[$key]->borderColor  = $primary ;
						$data[$key]->type   			= 'event' ;
						$data[$key]->ref   				= $data[$key]->EVENT_REF;
						$data[$key]->id   				= 'event_'.$data[$key]->EVENT_REF;
						$start 										= $data[$key]->start.' '.$data[$key]->startTime;
						$end 											= $data[$key]->end.' '.$data[$key]->endTime;
						$data[$key]->start				= $data[$key]->start.' '.date('H:i', strtotime($start));
						$data[$key]->end					= $data[$key]->end.' '.date('H:i', strtotime($end));
						$data[$key]->title   			= '('.date('h:i a', strtotime($start)).') '.$data[$key]->title;
						unset($data[$key]->idd);
						unset($data[$key]->addedBy);
				}

					if(isset($postData['showTask']) && $postData['showTask'] == 'true')
					$data = array_merge($data,$task);
					$data = array_merge($data,$AppealDeadlines);

					$result["success"] 			=  true;
					$result["data"]    			=  $data;

			}
			else
			{
					$result["error"] 	= 'No input data';
					$result["usernameexist"] 	= ' ';
			}

			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}


		public function TodayAppointments()
		{
			$postData	=	json_decode($_POST['data'], true);
			if(!empty($postData))
			{
					$userRow = $this->common->getrow('dibcase_users', array( 'USER_REF' => $postData['USER_REF']));
					if(!empty($userRow) && $userRow->timeZone != '')
					{
					  $userTimeZone = $userRow->timeZone;
					}
					else
					{
					  $userTimeZone = 'UTC';
					}

					// $task = $this->common->getdueTasks( array( 'ref' => $postData['COM_REF'] , 'dueFilter' => 'today' ));
					// foreach ($task as $k => $v)
					// {
					// 		$id = $k+1;
					// 		$task[$k]->start = $v->TSK_DUE_DATE.' 05:30';
					// 		$task[$k]->end   = $v->TSK_DUE_DATE.' 23:59';
					// 		$task[$k]->title = $v->TSK_TITLE;
					// 		$task[$k]->type   = 'task' ;
					// 		$task[$k]->ref   = $v->TSK_ID ;
					// }
					//
					$AppealDeadlines = array();
					$AppealDeadlines = $this->event->AppealDeadlines($postData['COM_REF'],'today');
					foreach ($AppealDeadlines as $key => $value)
					{
						$value->title = 'Appeal D '.$value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1);
						$value->start = $value->start.' 05:30';
						$value->end   = $value->end.' 23:59';
						$value->type   = 'AppealDeadlines';
						$value->addedBy   = $value->CLM_ADDEDBY;
					}

					$data = $this->event->todayEvents($postData);
					foreach ($data as $key => $value)
					{
							$startObj = new DateTime( $data[$key]->start.' '.$data[$key]->startTime,  new DateTimeZone('UTC') );
							$endObj   = new DateTime( $data[$key]->end.' '.$data[$key]->endTime,  new DateTimeZone('UTC') );

							$sTO = $startObj->setTimezone(new DateTimeZone($userTimeZone));
							$eNO = $endObj->setTimezone(new DateTimeZone($userTimeZone));

							$data[$key]->start 						=  $sTO->format('Y-m-d');
							$data[$key]->end 						=  $eNO->format('Y-m-d');
							$data[$key]->startTime 						=  $sTO->format('H:i a');
							$data[$key]->endTime 						=  $eNO->format('H:i a');


							$id = $key+1;
							$primary 									= $this->event->employeeColor($value->addedBy);
							$data[$key]->type   			= 'event' ;
							$data[$key]->ref   				= $data[$key]->EVENT_REF;
							$start 										= $data[$key]->start.' '.$data[$key]->startTime;
							$end 											= $data[$key]->end.' '.$data[$key]->endTime;
							$data[$key]->start				= $data[$key]->start.' '.date('H:i', strtotime($start));
							$data[$key]->end					= $data[$key]->end.' '.date('H:i', strtotime($end));
							$data[$key]->attendees 		= $this->event->eventAttendees($data[$key]->EVENT_REF);
							unset($data[$key]->idd);
							unset($data[$key]->addedBy);
					}

						// $data = array_merge($data,$task);
						$data = array_merge($data,$AppealDeadlines);

						$result["success"] 			=  true;
						$result["data"]    			=  $data;
						// print_r($result["data"]); die;
			}
			else
			{
					$result["error"] 	= 'No input data';
					$result["usernameexist"] 	= ' ';
			}

			header('Content-Type: application/json');
			echo json_encode($result);exit;
		}

		public function listEvent()
		{

			$postData	=	json_decode($_POST['data'], true);
			if(!empty($postData))
			{
				if(isset($postData['showTask']) && $postData['showTask'] == 'true')
				{
					$task = $this->event->ListTask($postData['COM_REF'],$postData['USER_REF']);
					foreach ($task as $k => $v)
					{
							$v->title = $v->TSK_TITLE;
							$v->start = $v->TSK_DUE_DATE;
							$v->end   = $v->TSK_DUE_DATE;

							$primary = $this->event->employeeColor($v->TSK_ADDEDBY);
							$a = array( 'primary' => $primary , 'secondary' => '#D1E8FF' );
							$task[$k]->color  = $a ;
							$task[$k]->meta   = array( 'TSK_ID' => $v->TSK_ID  ) ;
							$task[$k]->type   = 'task' ;
					}
				}

				if(isset($postData['AppealDeadlines']) && $postData['AppealDeadlines'] == 'true')
				{
					$AppealDeadlines = $this->event->AppealDeadlines($postData['COM_REF'],'all');
				}

				$data = $this->event->listEvent($postData);
				foreach ($data as $key => $value)
				{
						$primary = $this->event->employeeColor($value->addedBy);
						$a = array( 'primary' => $primary , 'secondary' => '#D1E8FF' );
						$data[$key]->color  = $a ;
						$data[$key]->meta   = array( 'EVENT_REF' => $value->EVENT_REF  ) ;
						$data[$key]->type   = 'event' ;
						unset($data[$key]->addedBy);
						unset($data[$key]->idd);
				}
				if(!empty($data))
				{
					if(isset($postData['showTask']) && $postData['showTask'] == 'true')
					$data = array_merge($data,$task);
					$result["success"] 			=  true;
					$result["data"]    			=  $data;
				}
				else
				{
					$result["success"] 			=  true;
					$result["data"]    			=  array();
				}
			}
			else
			{
					$result["error"] 	= 'No input data';
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
			$response = $this->event->allEmployee($postData['COM_REF']);
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

	public function EventData()
	{
		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
			$attendees = $this->common->get('dibcase_event_attendee' , array( 'EVENT_REF' => $postData['EVENT_REF']) );
			$tags = $this->common->get('dibcase_tags' , array( 'REF_ID' => $postData['EVENT_REF'] , 'TAG_REF_TYPE' => 'events' ) );
			$Event = $this->common->getrow('dibcase_events' , array( 'EVENT_REF' => $postData['EVENT_REF']) );
			$response = array('attendees' => $attendees , 'tags' => $tags , 'Event' => $Event );
			if($Event->EVENT_CL_REF != '')
			{
				$cl = $this->common->clientDetails($Event->EVENT_CL_REF);
				$Event->CL = $cl;
			}

			$userRow = $this->common->getrow('dibcase_users', array( 'USER_REF' => $_POST['userRef']));
			if(!empty($userRow) && $userRow->timeZone != '')
			{
				$userTimeZone = $userRow->timeZone;
			}
			else
			{
				$userTimeZone = 'UTC';
			}
			// echo $userTimeZone;

			$startObj = new DateTime( $Event->EVENT_START_DATE.' '.$Event->EVENT_START_TIME,  new DateTimeZone('UTC') );
			$endObj   = new DateTime( $Event->EVENT_END_DATE.' '.$Event->EVENT_END_TIME,  new DateTimeZone('UTC') );

			$sTO = $startObj->setTimezone(new DateTimeZone($userTimeZone));
			$eNO = $endObj->setTimezone(new DateTimeZone($userTimeZone));

			$Event->EVENT_START_DATE 					=  $sTO->format('Y-m-d');
			$Event->EVENT_END_DATE 						=  $eNO->format('Y-m-d');
			$Event->EVENT_START_TIME 					=  $sTO->format('H:i:s');
			$Event->EVENT_END_TIME 						=  $eNO->format('H:i:s');

			if($response)
			{
				$result["success"]   =  TRUE;
				$result["data"]   =  $response;
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

	public function eventTagsSearch($key)
	{
		if($key != '')
		{
			$response = $this->event->eventTagsSearch($key);
			header('Content-Type: application/json');
			echo json_encode($response);exit;
		}
	}

	public function searchCLientTags($key)
	{
		if($key != '')
		{
			$response = $this->event->searchCLientTags($key);
			$clients = array();
			foreach ($response as $key => $value)
			{
				$clients[$key]['CLIENT_NAME'] = $value->CL_LASTNAME.','.$value->CL_FIRST_NAME.' '. substr( $value->CL_MIDDLE_NAME ,0,1).'. #'.substr( $value->CL_SSN,-4);
				$clients[$key]['CLIENT_REF']  = $value->CL_REF;
			}
			header('Content-Type: application/json');
			echo json_encode($clients);exit;
		}
	}


	public function calender()
	{
		if(isset($_GET['code']) && isset($_GET['state']))
		{
			require_once(APPPATH . "third_party/vendor/autoload.php");
			define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
			define('CLIENT_SECRET_PATH', APPPATH . '/third_party/vendor/client_secret.json');
			define('SCOPES', implode(' ', array(
				Google_Service_Calendar::CALENDAR)
			));

			$client = new Google_Client();
		  $client->setApplicationName(APPLICATION_NAME);
		  $client->setScopes(SCOPES);
		  $client->setAuthConfig(CLIENT_SECRET_PATH);
		  $client->setAccessType('offline');
			$client->setApprovalPrompt ("force");
			$accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
			$refresh_token = $accessToken['refresh_token'];

			$en = json_encode($accessToken);
			$state = strtr(base64_decode($_GET['state']), '+/=', '-_,');
			$state = json_decode($state);
			$update = $this->common->update(array('USER_REF'=> $state->ref ),array('USER_CREDENTIALS' => $en , 'refreshToken' => $refresh_token),'dibcase_users');
			if($update)
			{
				header('location:http://1wayit.com/dibcase_app/#/dashboard/calender');
			}
		}
	}


	public function syncCalender()
	{
		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
			$state['ref']    = $postData['ref'];
			$state  			   = json_encode($state);
			$state = strtr(base64_encode($state), '+/=', '-_,');

			require_once(APPPATH . "third_party/vendor/autoload.php");
			define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
			define('CLIENT_SECRET_PATH', APPPATH . '/third_party/vendor/client_secret.json');
			define('SCOPES', implode(' ', array(
			  Google_Service_Calendar::CALENDAR)
			));

		  $client = new Google_Client();
		  $client->setApplicationName(APPLICATION_NAME);
		  $client->setScopes(SCOPES);
		  $client->setAuthConfig(CLIENT_SECRET_PATH);
		  $client->setAccessType('offline');
			$client->setApprovalPrompt ("force");
			$authUrl = $client->createAuthUrl();
			$result["success"]   =  true;
 			$authUrl = str_replace("&state&","&state=".$state.'&', $authUrl);
			$result["url"]   =  $authUrl;
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


	public function getClient($user)
	{
		require_once(APPPATH . "third_party/vendor/autoload.php");
		define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
		define('CLIENT_SECRET_PATH', APPPATH . '/third_party/vendor/client_secret.json');
		define('SCOPES', implode(' ', array(
		  Google_Service_Calendar::CALENDAR)
		));


	  $client = new Google_Client();
	  $client->setApplicationName(APPLICATION_NAME);
	  $client->setScopes(SCOPES);
	  $client->setAuthConfig(CLIENT_SECRET_PATH);
	  $client->setAccessType('offline');
		$client->setApprovalPrompt ("force");

		$credentials = $this->event->userApiCredentials($user);
	  if ($credentials)
	  {
	    $accessToken = json_decode($credentials, true);
	  }
	  else
	  {
	    if(isset($_GET['code']))
	    {
	      $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
				// print_r($accessToken);
				$en = json_encode($accessToken);
				// print_r($en); die;
				// $en = base64_encode($accessToken);
				$this->common->update(array('USER_REF'=>$user),array('USER_CREDENTIALS' => $en),'dibcase_users');
	    }
	    else
	    {
	      $authUrl = $client->createAuthUrl();
				redirect($authUrl);
	    }
	  }
	  $client->setAccessToken($accessToken);

	  // Refresh the token if it's expired.
	  if ($client->isAccessTokenExpired())
	  {
	    $client->fetchAccessTokenWithRefreshToken($this->event->userRefreshToken($user));
			$this->common->update(array('USER_REF'=>$user),array('USER_CREDENTIALS' => json_encode($client->getAccessToken()) ),'dibcase_users');
	  }
	  return $client;
	}

	public function submitContact()
	{
		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
			$tags = $postData['tags'];
			unset($postData['tags']);

			if(isset($postData['IS_COMPANY']) && $postData['IS_COMPANY'] == true)
			{
				$postData['IS_COMPANY'] = 1;
			}
			else
			{
				$postData['IS_COMPANY'] = 0;
			}

			if(isset($postData['CON_REF'])) // udpate
			{
				$con = $postData['CON_REF'];
				unset($postData['CON_REF']);
				$out = $this->common->update(array( 'CON_REF'=> $con ),$postData,'dibcase_contacts');
				if($out)
				$output[0] = 1;
				$this->common->delete('dibcase_tags',array( 'REF_ID'=> $con ,'TAG_REF_TYPE'=> 'contact' ));
				$msg = 'Contact updated successfully';
			}
			else // insert
			{
				$date = date("d M Y H:i:s");
				$postData['CON_REF'] 								=  strtotime($date) . rand(0,9999);
				$con = $postData['CON_REF'];
				$output = $this->common->insert('dibcase_contacts',$postData);
				$msg = 'Contact added successfully';
			}

			if( $output[0] == 1 )
			{
				$tagsArray = array();
				foreach ($tags as $key => $value)
				{
					$tagsArray[] = array( 'TAG_TITLE' => $value , 'TAG_REF_TYPE' => 'contact' , 'COM_REF' => $postData['COM_REF'] , 'REF_ID' => $con);
				}
				if(!empty($tagsArray))
				$this->common->insert_batch('dibcase_tags',$tagsArray);
				$result["success"]   = $msg;
			}
			else
			{
				$result["error"]   =  'No input data';
			}
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}


	public function importContacts()
	{
		$replace = array(
			'CONTACT_SALUTATION' => 'CON_SAL',
			'CONTACT_FIRST_NAME' => 'CON_FNAME',
			'CONTACT_MIDDLE_NAME' => 'CON_MIDDLE',
			'CONTACT_LAST_NAME' => 'CON_LNAME',
			'CONTACT_EMAIL1' => 'CON_EMAIL1',
			'CONTACT_EMAIL1_TYPE' => 'CON_EMAIL1_TYPE',
			'CONTACT_EMAIL2' => 'CON_EMAIL2',
			'CONTACT_EMAIL2_TYPE' => 'CON_EMAIL2_TYPE',
			'CONTACT_PHONE1' => 'CON_PHONE1',
			'EXTENSION_PHONE1' => 'EXT_PHONE1',
			'CONTACT_PHONE1_TYPE' => 'CON_PHONE1_TYPE',
			'CONTACT_ZIP' => 'CON_ZIP',
			'CONTACT_STATE' => 'CON_STATE',
			'CONTACT_CITY' => 'CON_CITY',
			'CONTACT_ADDRESS1' => 'CON_ADDR1',
			'CONTACT_EMPLOYER' => 'CON_EMPLOYER',
			'CONTACT_NOTES' => 'CON_NOTES',
		);

		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
			foreach ($postData['data'] as $key => $value)
			{
				if(isset($value['IS_COMPANY']) && $value['IS_COMPANY'] == 'yes')
				{
					$postData['data'][$key]['IS_COMPANY'] = 1;
				}
				else
				{
					$postData['data'][$key]['IS_COMPANY'] = 0;
				}

				foreach ($value as $k => $val)
				{
					$key2 = $replace[$k];
					$postData['data'][$key][$key2] = $val;
					unset($postData['data'][$key][$k]);
				}

				$date = date("d M Y H:i:s");
				$postData['data'][$key]['CON_REF'] 								=  strtotime($date) . rand(0,9999);
				$postData['data'][$key]['COM_REF'] 								=  $postData['COM_REF'];
				$postData['data'][$key]['CON_ADDEDBY'] 						=  $postData['CON_ADDEDBY'];
			}

			// print_r($postData['data']);
			// die;
			$msg = 'Contact Imported successfully';
			$out = $this->common->insert_batch('dibcase_contacts',$postData['data']);
			if($out)
			$result["success"]   = $msg;
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

	public function exportContacts($com)
	{
		require_once APPPATH . '/third_party/PHPExcel/PHPExcel.php';

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Dibcase Contacts');
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
			'CONTACT_SALUTATION' => 'CON_SAL',
			'CONTACT_FIRST_NAME' => 'CON_FNAME',
			'CONTACT_MIDDLE_NAME' => 'CON_MIDDLE',
			'CONTACT_LAST_NAME' => 'CON_LNAME',
			'CONTACT_EMAIL1' => 'CON_EMAIL1',
			'CONTACT_EMAIL1_TYPE' => 'CON_EMAIL1_TYPE',
			'CONTACT_EMAIL2' => 'CON_EMAIL2',
			'CONTACT_EMAIL2_TYPE' => 'CON_EMAIL2_TYPE',
			'CONTACT_PHONE1' => 'CON_PHONE1',
			'EXTENSION_PHONE1' => 'EXT_PHONE1',
			'CONTACT_PHONE1_TYPE' => 'CON_PHONE1_TYPE',
			'CONTACT_PHONE2' => 'CON_PHONE2',
			'EXTENSION_PHONE2' => 'EXT_PHONE2',
			'CONTACT_PHONE2_TYPE' => 'CON_PHONE2_TYPE',
			'CONTACT_ZIP' => 'CON_ZIP',
			'CONTACT_STATE' => 'CON_STATE',
			'CONTACT_CITY' => 'CON_CITY',
			'CONTACT_ADDRESS1' => 'CON_ADDR1',
			'CONTACT_EMPLOYER' => 'CON_EMPLOYER',
			'CONTACT_NOTES' => 'CON_NOTES',

		);

		$i = 0;
		foreach ($fields as $key => $value)
		{
			$objPHPExcel->getActiveSheet()->setCellValue(range('A', 'Z')[$i].'1', $key);
			$i++;
		}

		$Contacts = $this->common->get('dibcase_contacts',array('COM_REF' => $com));

		$excelRow = 2;
		for ($i=0; $i < count($Contacts); $i++)
		{
			// print_r($Contacts[$i]);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$excelRow, $Contacts[$i]->CON_SAL);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$excelRow, $Contacts[$i]->CON_FNAME);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$excelRow, $Contacts[$i]->CON_MIDDLE);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$excelRow, $Contacts[$i]->CON_LNAME);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$excelRow, $Contacts[$i]->CON_EMAIL1);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$excelRow, $Contacts[$i]->CON_EMAIL1_TYPE);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$excelRow, $Contacts[$i]->CON_EMAIL2);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$excelRow, $Contacts[$i]->CON_EMAIL2_TYPE);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$excelRow, $Contacts[$i]->CON_PHONE1);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$excelRow, $Contacts[$i]->EXT_PHONE1);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$excelRow, $Contacts[$i]->CON_PHONE1_TYPE);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$excelRow, $Contacts[$i]->CON_PHONE2);
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$excelRow, $Contacts[$i]->EXT_PHONE2);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$excelRow, $Contacts[$i]->CON_PHONE2_TYPE);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$excelRow, $Contacts[$i]->CON_ZIP);
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$excelRow, $Contacts[$i]->CON_STATE);
			$objPHPExcel->getActiveSheet()->setCellValue('Q'.$excelRow, $Contacts[$i]->CON_CITY);
			$objPHPExcel->getActiveSheet()->setCellValue('R'.$excelRow, $Contacts[$i]->CON_ADDR1);
			$objPHPExcel->getActiveSheet()->setCellValue('S'.$excelRow, $Contacts[$i]->CON_EMPLOYER);
			$objPHPExcel->getActiveSheet()->setCellValue('T'.$excelRow, $Contacts[$i]->CON_NOTES);
			$excelRow++;
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="DibcaseContactsExcelExport.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');

	}


	public function getContacts()
	{
		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
			if(isset($postData['page']))
			{
			  $page      = $postData['page'];
			  $perPage   = $postData['perPage'];
			  if( $perPage <= 0 )
			   $perPage = 10;

			   if( $page <= 0 )
			   $page = 1;

			   $start = ($page-1) * $perPage;
				 $postData['start'] = $start;
			}

		  if(!isset($postData['searchText']))
		  $postData['searchText'] = null;

		  if(!isset($postData['searchAlphabet']))
		  $postData['searchAlphabet'] = null;

			$output = $this->event->getContacts($postData);

			foreach ($output[0] as $key => $value)
			{
				$tags = $this->common->get('dibcase_tags',array( 'REF_ID' => $value->CON_REF , 'TAG_REF_TYPE' => 'contact' ));
				$output[0][$key]->tags = $tags;
			}
			$result["success"]   =  true;
			$result["data"]   =  $output[0];
			$result["total_rows"]   =  $output[1];
			$result["query"]   =  $output[2];
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;

	}

	public function getContactDetails()
	{
		if(!empty($_POST))
		{
			$postData	=	json_decode($_POST['data'], true);
			$output = $this->common->getrow('dibcase_contacts',$postData);
			if(!empty($output))
			{
				$tags = $this->common->get('dibcase_tags',array( 'REF_ID' => $output->CON_REF , 'TAG_REF_TYPE' => 'contact' ));
				$output->tags = $tags;
			}
			$result["success"]   =  true;
			$result["data"]   =  $output;
		}
		else
		{
			$result["error"]   =  'No input data';
		}
		header('Content-Type: application/json');
		echo json_encode($result);exit;
	}

	public function contactTagSearch($com,$key)
	{
		// echo $key;
		// die('helooo.');
		if($key != '')
		{
			$response = $this->event->contactTagSearch($com,$key);
			// echo $last = $this->db->last_query(); die;
			$contacts = array();
			foreach ($response as $key => $value)
			{
				if($value->IS_COMPANY == 1)
				{
					$contacts[$key]['TITLE']    = $value->CON_SAL.' '.$value->CON_FNAME.' '.$value->CON_MIDDLE.' '.$value->CON_LNAME.' (Company) | '.$value->CON_EMPLOYER.' | '.$value->CON_JOBTITLE.' | '.$value->CON_CITY.' | '.$value->CON_STATE;
				}
				else
				{
					$contacts[$key]['TITLE']    = $value->CON_SAL.' '.$value->CON_FNAME.' '.$value->CON_MIDDLE.' '.$value->CON_LNAME.' | '.$value->CON_EMPLOYER.' | '.$value->CON_JOBTITLE.' | '.$value->CON_CITY.' | '.$value->CON_STATE;
				}

				$contacts[$key]['CON_REF']  = $value->CON_REF;
				$contacts[$key]['CON_JOBTITLE']  = $value->CON_JOBTITLE;
				$contacts[$key]['CON_CITY']  = $value->CON_CITY;
				$contacts[$key]['CON_STATE']  = $value->CON_STATE;
				$contacts[$key]['CON_EMPLOYER']  = $value->CON_EMPLOYER;

				$tags = $this->common->get('dibcase_tags',array( 'REF_ID' => $value->CON_REF , 'TAG_REF_TYPE' => 'contact' ));
				$contacts[$key]['tags'] = $tags;
				if(!empty($tags))
				{
					$contacts[$key]['choosedTag'] = $tags[0]->TAG_ID;
				}

			}
		}
		else
		{
			$contacts[$key]['tags'] = [];
		}
		header('Content-Type: application/json');
		echo json_encode($contacts);exit;
	}






}
?>
