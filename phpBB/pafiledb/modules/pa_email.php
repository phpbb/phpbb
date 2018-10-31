<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pa_email.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

/**
 * Enter description here...
 *
 */
class pafiledb_email extends pafiledb_public
{
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 */
	function main( $action  = false )
	{
		global $template, $user, $config, $phpEx, $pafiledb_config, $db;
		global $phpbb_root_path, $mx_root_path, $module_root_path, $is_block, $tplEx;

		if ( isset( $_REQUEST['file_id'] ) )
		{
			$file_id = intval( $_REQUEST['file_id'] );
		}
		else
		{
			mx_message_die(GENERAL_MESSAGE, $user->lang['File_not_exist']);
		}
		
		$priority = request_var('mail_priority_flag', MAIL_NORMAL_PRIORITY);

		$sql = 'SELECT file_catid, file_name
			FROM ' . PA_FILES_TABLE . "
			WHERE file_id = $file_id";

		if ( !( $result = $db->sql_query( $sql ) ) )
		{
			mx_message_die( GENERAL_ERROR, 'Couldnt Query file info', '', __LINE__, __FILE__, $sql );
		}

		if ( !$file_data = $db->sql_fetchrow( $result ) )
		{
			mx_message_die( GENERAL_MESSAGE, $user->lang['File_not_exist'] );
		}

		$db->sql_freeresult( $result );

		if ( ( !$this->auth_user[$file_data['file_catid']]['auth_email'] ) )
		{
			if ( !$user->data['is_registered'] )
			{
				// mx_redirect(mx_append_sid($mx_root_path . "login.$phpEx?redirect=".$this->this_mxurl("action=email&file_id=" . $file_id), true));
			}

			$message = sprintf( $user->lang['Sorry_auth_email'], $this->auth_user[$file_data['file_catid']]['auth_email_type'] );
			mx_message_die( GENERAL_MESSAGE, $message );
		}

		if ( isset( $_POST['submit'] ) )
		{
			//
			// session id check
			//
			if ( !isset( $_POST['sid'] ) || $_POST['sid'] != $user->data['session_id'] )
			{
				mx_message_die(GENERAL_ERROR, 'Invalid_session');
			}

			$error = false;

			if ( !empty( $_POST['femail'] ) && preg_match( '/^[a-z0-9\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is', $_POST['femail'] ) )
			{
				$user_email = trim(stripslashes($_POST['femail']));
			}
			else
			{
				$error = true;
				$error_msg = ( !empty( $error_msg ) ) ? $error_msg . '<br />' . $user->lang['Email_invalid'] : $user->lang['Email_invalid'];
			}

			$username = trim( stripslashes( $_POST['fname'] ) );
			$sender_name = trim( strip_tags( stripslashes( $_POST['sname'] ) ) );

			if ( !$user->data['is_registered'] || ( $user->data['is_registered'] && $sender_name != $user->data['username'] ) )
			{
				include( $phpbb_root_path . 'includes/functions_user.' . $phpEx );

				$result = validate_username( $username );
				if ( $result['error'] )
				{
					$error = true;
					$error_msg .= ( !empty( $error_msg ) ) ? '<br />' . $result['error_msg'] : $result['error_msg'];
				}
			}
			else
			{
				$sender_name = $user->data['username'];
			}

			if ( !$user->data['is_registered'] )
			{
				if ( !empty( $_POST['semail'] ) && preg_match( '/^[a-z0-9\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is', $_POST['semail'] ) )
				{
					$sender_email = trim( stripslashes( $_POST['semail'] ) );
				}
				else
				{
					$error = true;
					$error_msg = ( !empty( $error_msg ) ) ? $error_msg . '<br />' . $user->lang['Email_invalid'] : $user->lang['Email_invalid'];
				}
			}
			else
			{
				$sender_email = $user->data['user_email'];
			}

			if ( !empty( $_POST['subject'] ) )
			{
				$subject = trim( stripslashes( $_POST['subject'] ) );
			}
			else
			{
				$error = true;
				$error_msg = ( !empty( $error_msg ) ) ? $error_msg . '<br />' . $user->lang['Empty_subject_email'] : $user->lang['Empty_subject_email'];
			}

			if ( !empty( $_POST['message'] ) )
			{
				$message = trim( stripslashes( $_POST['message'] ) );
			}
			else
			{
				$error = true;
				$error_msg = ( !empty( $error_msg ) ) ? $error_msg . '<br />' . $user->lang['Empty_message_email'] : $user->lang['Empty_message_email'];
			}

			if ( !$error )
			{
			
				// Send the messages
				include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
				include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
				$messenger = new messenger($config['smtp_delivery']);

				$errored = false;
				
				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $sender_name);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
				
				$messenger->template('profile_send_email', $user_lang);
				$messenger->to($user_email, $username); 
				$messenger->subject(htmlspecialchars_decode($subject));
				$messenger->set_mail_priority($priority);

				$messenger->assign_vars( array(
					'SITENAME' => $config['sitename'],
					'CONTACT_EMAIL' => $config['board_contact'],
					'BOARD_CONTACT' => $config['board_contact'],
					'BOARD_EMAIL' => $config['board_email'],
					'FROM_USERNAME' => $sender_name,
					'TO_USERNAME' => $username,
					'MESSAGE' => htmlspecialchars_decode($message))
				);
				
				if (!($messenger->send()))
				{
					$errored = true;
				}

				$messenger->save_queue(); 

				$message = $user->lang['Econf'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $file_id ) ) . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_forum'], '<a href="' . mx_append_sid( $mx_root_path . 'index.' . $phpEx ) . '">', '</a>' );
				mx_message_die( GENERAL_MESSAGE, $message );
			}

			if ( $error )
			{
				mx_message_die( GENERAL_MESSAGE, $error_msg );
			}
		}

		$template->assign_vars( array(
			'USER_LOGGED' => ( !$user->data['is_registered'] ) ? true : false,
			'S_EMAIL_ACTION' => mx_append_sid( $this->this_mxurl() ),
			'S_HIDDEN_FIELDS' => '<input type="hidden" name="sid" value="' . $user->data['session_id'] . '" />',

			'L_INDEX' => "<<",
			'L_EMAIL' => $user->lang['Semail'],
			'L_EMAIL' => $user->lang['Emailfile'],
			'L_EMAILINFO' => $user->lang['Emailinfo'],
			'L_YNAME' => $user->lang['Yname'],
			'L_YEMAIL' => $user->lang['Yemail'],
			'L_FNAME' => $user->lang['Fname'],
			'L_FEMAIL' => $user->lang['Femail'],
			'L_ETEXT' => $user->lang['Etext'],
			'L_DEFAULTMAIL' => $user->lang['Defaultmail'],
			'L_SEMAIL' => $user->lang['Semail'],
			'L_ESUB' => $user->lang['Esub'],
			'L_EMPTY_SUBJECT_EMAIL' => $user->lang['Empty_subject_email'],
			'L_EMPTY_MESSAGE_EMAIL' => $user->lang['Empty_message_email'],

			'U_INDEX' => mx_append_sid( $mx_root_path . 'index.' . $phpEx ),
			'U_DOWNLOAD_HOME' => mx_append_sid( $this->this_mxurl() ),
			'U_FILE_NAME' => mx_append_sid( $this->this_mxurl( 'action=file&file_id=' . $file_id ) ),

			'FILE_NAME' => $file_data['file_name'],
			'SNAME' => $user->data['username'],
			'SEMAIL' => $user->data['user_email'],
			'DOWNLOAD' => $pafiledb_config['module_name'],
			'FILE_URL' => get_formated_url() . '/dload.' . $phpEx . '?action=file&file_id=' . $file_id,
			'ID' => $file_id )
		);

		// ===================================================
		// assign var for navigation
		// ===================================================
		$this->generate_navigation( $file_data['file_catid'] );

		$this->display( $user->lang['Download'], 'pa_email_body.'.$tplEx );
	}
}

?>