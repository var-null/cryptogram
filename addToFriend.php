<?php
session_start();
header('Content-Type: text/html; utf-8; charset=UTF-8');

require_once(realpath(__DIR__) . '/db/db_connect.php');
require_once(realpath(__DIR__) . '/utils.php');

$sid = session_id();//for php5	
$__session = $_SESSION;
session_write_close();

$encrypted = $_POST['encrypted'];

$return_data = array();
$return_data['sid'] = 'error2';
$return_data['result'] = 'error2';	

require_once(realpath(__DIR__) . '/db_utils.php');

if($text && $encrypted != "")
{
	
	$encrypted = urldecode($encrypted);
	$encrypted = str_replace("@@p@@", "+", $encrypted);		

	
	$key = pack("H*", $__session['sync_login_key']);
	$iv = pack("H*", $sid);
	
	//Now we receive the encrypted from the post, we should decode it from base64,
	$encrypted = base64_decode($encrypted);
	$decode_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
	

	
	$data_mas = explode('@', $decode_str);
	
	$data_send_str = $data_mas[0];
	
	$data_send_str = urldecode($data_send_str);
	$data_send_str = str_replace("@@p@@", "+", $data_send_str);	



	if($data_send_str != "")
	{
			
		
			
		$data_send_mas = json_decode($data_send_str, true);
	
		$sid_b = $data_send_mas['sid'];
		$add_user_id = $data_send_mas['user_id'];
		$user_name_md5 = $data_send_mas['user_name_md5'];
		
		$return_data['add_user_id'] = $add_user_id;	
		$return_data['user_name_md5'] = $user_name_md5;	
		
		if($sid_b == $sid && $add_user_id > 0 && $user_name_md5 != "")
		{
			$return_data['sid'] = $sid;
		
			//������� ������ �� ��� ����������� �����
			//������� � ����� ���� ���� ���� � �� ��� ���� �� ���������.. ������������� ����� ��������
			
			$user_id = getZUserIdBySid($sid);
			
			$return_data['user_id'] = $user_id;	
						
			
			if($user_id > 0)
			{
				
			
				if(userIssetByIdAndNameMd5($add_user_id, $user_name_md5))
				{
				
				
					$link_id = getZLinkIdByUsers($user_id, $add_user_id);
				
					if($link_id > 0)
					{
						//����������� ���� ��� ��������.. �� ����� �� � �����..
					}
					else
					{
					
					
						$query_insert = "";
						$query_insert .= "insert into z_user_friends ";
						$query_insert .= "(user_id, user_ch_id) values ";
						$query_insert .= "('" . mysql_real_escape_string($user_id) . "', '" . mysql_real_escape_string($add_user_id) . "');";	
						
						$text->my_sql_query = $query_insert;
						$text->my_sql_execute();	

						$insert_id = $text->my_sql_insert_id();		
						
						if($insert_id > 0)
						{
							$return_data['result'] = 'add';		
							
						
						}	
					
					}
				
					
				
				}
			

			
			
			}
			

		
		}
	
	}	

}

echo json_encode($return_data);

?>