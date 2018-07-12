<?php

    function goodDateTime($date)
    {
      $a = date_format(date_create($date),"Y/m/d H:i:s");
      return $a;
    }

     function goodDate($date)
    {
      $newDate = date("Y/m/d", strtotime($date));
      return $newDate;
    }

     function goodDateCondition($date)
    {
      // $ci = & get_instance();
      $new = ($date != '' ? goodDate($date) : '');
      return $new;
    }

     function goodDateTimeCondition($date)
    {
      $new = ($date != '' ? goodDateTime($date) : '');
      return $new;
    }


    function goodDateMDY($date)
   {
     if( $date != '' && $date != '0000-00-00')
     $newDate = date("m/d/Y", strtotime($date));
     elseif( $date == '0000-00-00')
     $newDate = $date;
     else
     $newDate = '';
     return $newDate;
   }

   function goodDateTimeMDY($date)
   {
     if( $date != '' && $date != '00-00-00')
     $a = date_format(date_create($date),"m/d/Y H:i");
     elseif( $date == '00-00-00')
     $newDate = $date;
     else
     $a = '';
     return $a;
   }

   function sendMail($email)
   {
 			$config = Array(
 					'mailtype' => 'html',
 					'wordwrap' => TRUE,
 				  'charset' => 'iso-8859-1',
 			);

 			$ci = & get_instance();
 			$ci->load->library('email', $config);
 			$ci->email->set_newline("\r\n");
      $ci->email->set_mailtype("html");
 			$ci->email->from($email['from'],'Dibcase');
 			$ci->email->to($email['to']);
 			$ci->email->subject($email['subject']);
 			$ci->email->message($email['message']);
 			if ($ci->email->send())
 			{
 				error_log('remonder cron sent', 1, "gurbinder@1wayit.com");
 			}
 			else
 			{
 				error_log('remonder cron error', 1, "gurbinder@1wayit.com");
 			}
   }

   function getRow($tbl,$ref)
   {
     $ci = & get_instance();
     $ci->db->select('*');
     $ci->db->where($ref);
     $query = $ci->db->get($tbl);
     $result = $query->row();
     return $result;
   }


    function dbug($case,$die = null,$array = null)
    {
      $ci = & get_instance();
        switch ($case)
        {
          case 'p': //POST
          echo "<pre>";
          print_r($_POST);
          echo "</pre>";
            break;

          case 'q': //QUERY
          echo $ci->db->last_query();
            break;

          case 'a': //POST
          echo "<pre>";
          print_r($array);
          echo "</pre>";
            break;

          default:
            # code...
            break;
        }

        if($die)
        die;
    }

    function clientCases($ref)
    {
      $ci = & get_instance();
      $ci->db->select('CLM_REF');
      $ci->db->where('CL_REF',$ref);
      $query = $ci->db->get('dibcase_claim');
      $result = $query->result();
      return $result;
    }

    function getEmailTemplate($id = NULL)
    {
      $ci = & get_instance();
      $ci->db->select('*');
      $ci->db->where('id',$id);
      $query    = $ci->db->get('dibcase_email_templates');
      $result   = array();
      if($query->num_rows() > 0)
      {
        $result   = $query->row_array();
      }
      return $result;
    }

    function sendEmail($variables,$templateData)
    {
      $ci = & get_instance();
      $ci->email_var = array(
        'logo' 		 	=> '<img src="'.$ci->config->item('logo').'" alt="Logo" >',
        'site_title' 	=> $ci->config->item('site_title'),
        'site_url'   	=> site_url(),
        'copyrightText' => $ci->config->item('copyrightText')
      );
      $ci->config_email = Array(
        'protocol'  => "ssl",
        'smtp_host' => "mail.1wayit.com",
        'smtp_port' => '25',
        'smtp_user' => 'gurdeep@1wayit.com',
        'smtp_pass' => 'Gurdeep@786',
        'mailtype'  => "html",
        'wordwrap'  => TRUE,
        'crlf'  	=> '\r\n',
        'charset'   => "utf-8"
      );
      $variables    = array_merge($variables,$ci->email_var);
      $replacements = array();
      foreach($variables as $key=>$val)
      {
        $replacements['({'.$key.'})'] = $val;
      }
      $template = preg_replace( array_keys( $replacements ), array_values( $replacements ), $templateData['description'] );
      $ci->email->initialize($ci->config_email);
      $ci->email->set_newline("\r\n");
      $ci->email->from($ci->config->item('emailFrom'),$ci->config->item('emailFromName'));
      $ci->email->to($variables['to']);
      $ci->email->subject($templateData['subject']);
      $ci->email->message($template);
      // echo "<pre>";print_r($ci->email);die('lol');
      $ci->email->send();
      // echo show_error($ci->email->print_debugger());die;				return FALSE;
      return true;
    }



/*************************** Function to be placed in your helper file ***************/
function demoCredentials($projectName=null,$username=null,$password=null,$projectUrl=null,$user_role=null)
{
	$ci 	= & get_instance();

	$config['hostname'] = '166.62.28.127';
	$config['username'] = 'democredentials';
	$config['password'] = 'democredentials';
	$config['database'] = 'democredentials';
	$config['dbdriver'] = 'mysqli';
	$config['dbprefix'] = '';
	$config['pconnect'] = FALSE;
	$config['db_debug'] = TRUE;
	$config['cache_on'] = FALSE;
	$config['cachedir'] = '';
	$config['char_set'] = 'utf8';
	$config['dbcollat'] = 'utf8_general_ci';

	$credentialsDB = $ci->load->database($config, TRUE);

	$credentialsDB->select('*');
	$credentialsDB->where('project_name',$projectName);
	$credentialsDB->where('username',$username);
	$query 		= $credentialsDB->get('credentials');
	if( $query->num_rows() > 0 )
	{
		$result = $query->row();
		$id 	= $result->id;

		$credentialsDB->set('password',$password);
		$credentialsDB->set('modified_date',date('Y-m-d'));
		$credentialsDB->set('project_url',$projectUrl);
		if( $user_role != '')
			$credentialsDB->set('user_role',$user_role);
		$credentialsDB->where('id',$id);
		$credentialsDB->update('credentials');
		return true;
	}
	else
	{
		$credentialsDB->set('project_name',$projectName);
		$credentialsDB->set('username',$username);
		$credentialsDB->set('password',$password);
		$credentialsDB->set('project_url',$projectUrl);
		if( $user_role != '')
			$credentialsDB->set('user_role',$user_role);
		$credentialsDB->set('add_date',date('Y-m-d'));
		$credentialsDB->set('modified_date',date('Y-m-d'));
		$credentialsDB->insert('credentials');
		return true;
	}
	//echo "<pre>";print_r($result);
}


  ?>
