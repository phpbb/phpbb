<?php
/**
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: admin_custom_manage.php,v 1.2 2008/10/26 08:36:06 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

if ( !defined( 'IN_PORTAL' ) || !defined( 'IN_ADMIN' ) )
{
	die( "Hacking attempt" );
}

class pafiledb_custom_manage extends pafiledb_admin
{
	var $tpl_name;
	var $page_title;
	
	/**
	* Constructor
	* Init bbcode cache entries if bitfield is specified
	*/
	function pafiledb_custom_manage($u_action = '')
	{
		global $config, $phpbb_root_path;
		
		if ($u_action)
		{
			$this->u_action = $u_action;
		}		
	}

	function main( $mode )
	{
		global $db, $template, $template, $user, $phpEx, $pafiledb_functions, $pafiledb_cache, $pafiledb_config, $phpbb_root_path, $mx_request_vars;

		//
		// Init
		//
		$custom_field = new custom_field();
		$custom_field->init();

		$action = ( isset( $_REQUEST['action'] ) ) ? htmlspecialchars( $_REQUEST['action'] ) : 'select';
		$field_id = ( isset( $_REQUEST['field_id'] ) ) ? intval( $_REQUEST['field_id'] ) : 0;
		$field_type = ( isset( $_REQUEST['field_type'] ) ) ? intval( $_REQUEST['field_type'] ) : $custom_field->field_rowset[$field_id]['field_type'];
		$field_ids = ( isset( $_REQUEST['field_ids'] ) ) ? $_REQUEST['field_ids'] : '';
		$submit = ( isset( $_POST['submit'] ) ) ? true : false;

		switch ( $action )
		{
			case 'addfield':
				$template_file = 'acp_pafiledb_field_add.html';
				$this->tpl_name = 'acp_pafiledb_catauth_manage';
				break;
			case 'editfield':
				$template_file = 'acp_pafiledb_field_add.html';
				$this->tpl_name = 'acp_pafiledb_field_add';
				break;
			case 'edit':
				$template_file = 'acp_pafiledb_select_field.html';
				$this->tpl_name = 'acp_pafiledb_select_field';				
				break;
			case 'add':
				$template_file = 'acp_pafiledb_select_field_type.html';
				$this->tpl_name = 'acp_pafiledb_select_field_type';	
				break;
			case 'delete':
				$template_file = 'acp_pafiledb_field_delete.html';
				$this->tpl_name = 'acp_pafiledb_field_delete';					
				break;
			case 'select':
				$template_file = 'acp_pafiledb_field.html';
				$this->tpl_name = 'acp_pafiledb_field';				
				break;
		}

		if ( $submit )
		{
			if ( $action == 'do_add' && !$field_id )
			{
				$custom_field->update_add_field( $field_type );

				$message = $user->lang['Fieldadded'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . $this->u_actionage . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_admin_index'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx") . '">', '</a>' );
				trigger_error($message . adm_back_link($this->u_action));
			}
			elseif ( $action == 'do_add' && $field_id )
			{
				$custom_field->update_add_field( $field_type, $field_id );

				$message = $user->lang['Fieldedited'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . $this->u_actionage . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_admin_index'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx") . '">', '</a>' );
				trigger_error($message . adm_back_link($this->u_action));
			}
			elseif ( $action == 'do_delete' )
			{
				foreach( $field_ids as $key => $value )
				{
					$custom_field->delete_field( $key );
				}

				$message = $user->lang['Fieldsdel'] . '<br /><br />' . sprintf( $user->lang['Click_return'], '<a href="' . $this->u_actionage . '">', '</a>' ) . '<br /><br />' . sprintf( $user->lang['Click_return_admin_index'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx") . '">', '</a>' );
				trigger_error($message . adm_back_link($this->u_action));
			}
		}

		$template->set_filenames( array( 'admin' => $template_file ) );

		switch ( $action )
		{
			case 'add':
			case 'addfield':
				$l_title = $user->lang['Afieldtitle'];
				break;
			case 'edit':
				$l_title = $user->lang['Efieldtitle'];
				break;
			case 'editfield':
				$l_title = $user->lang['Efieldtitle'];
				break;
			case 'delete':
				$l_title = $user->lang['Dfieldtitle'];
				break;
			case 'select':
				$l_title = $user->lang['Mfieldtitle'];
				break;
		}

		if ( $action == 'add' )
		{
			$s_hidden_fields = '<input type="hidden" name="action" value="addfield">';
		}
		elseif ( $action == 'addfield' || $action == 'editfield')
		{
			$s_hidden_fields = '<input type="hidden" name="field_type" value="' . $field_type . '">';
			$s_hidden_fields .= '<input type="hidden" name="field_id" value="' . $field_id . '">';
			$s_hidden_fields .= '<input type="hidden" name="action" value="do_add">';
		}
		elseif ( $action == 'edit' )
		{
			$s_hidden_fields = '<input type="hidden" name="action" value="editfield">';
		}
		elseif ( $action == 'delete' )
		{
			$s_hidden_fields = '<input type="hidden" name="action" value="do_delete">';
		}

		$template->assign_vars( array(
			'L_FIELD_TITLE' => $l_title,
			'L_FIELD_EXPLAIN' => $user->lang['Fieldexplain'],
			'L_SELECT_TITLE' => $user->lang['Fieldselecttitle'],

			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'S_FIELD_ACTION' => $this->u_action
		));

		if ( $action == 'addfield' || $action == 'editfield')
		{
			if ( $field_id )
			{
				$data = $custom_field->get_field_data( $field_id );
			}

			$template->assign_vars( array(
				'L_FIELD_NAME' => $user->lang['Fieldname'],
				'L_FIELD_NAME_INFO' => $user->lang['Fieldnameinfo'],
				'L_FIELD_DESC' => $user->lang['Fielddesc'],
				'L_FIELD_DESC_INFO' => $user->lang['Fielddescinfo'],
				'L_FIELD_DATA' => $user->lang['Field_data'],
				'L_FIELD_DATA_INFO' => $user->lang['Field_data_info'],
				'L_FIELD_REGEX' => $user->lang['Field_regex'],
				'L_FIELD_REGEX_INFO' => sprintf( $user->lang['Field_regex_info'], '<a href="http://www.php.net/manual/en/function.preg-match.php" target="_blank">', '</a>' ),
				'L_FIELD_ORDER' => $user->lang['Field_order'],

				'DATA' => ( $field_type != INPUT && $field_type != TEXTAREA ) ? true : false,
				'REGEX' => ( $field_type == INPUT || $field_type == TEXTAREA ) ? true : false,
				'ORDER' => ( $field_id ) ? true : false,

				'FIELD_NAME' => $data['custom_name'],
				'FIELD_DESC' => $data['custom_description'],
				'FIELD_DATA' => $data['data'],
				'FIELD_REGEX' => $data['regex'],
				'FIELD_ORDER' => $data['field_order']
			));
		}
		elseif ( $action == 'add' )
		{
			$field_types = array( INPUT => $user->lang['Field_Input'], TEXTAREA => $user->lang['Field_Textarea'], RADIO => $user->lang['Field_Radio'], SELECT => $user->lang['Field_Select'], SELECT_MULTIPLE => $user->lang['Field_Select_multiple'], CHECKBOX => $user->lang['Field_Checkbox'] );

			$field_type_list = '<select name="field_type">';
			foreach( $field_types as $key => $value )
			{
				$field_type_list .= '<option value="' . $key . '">' . $value . '</option>';
			}
			$field_type_list .= '</select>';

			$template->assign_vars( array( 'S_SELECT_FIELD_TYPE' => $field_type_list ) );
		}
		elseif ( $action == 'edit' || $action == 'delete' || $action == 'select' )
		{
			foreach( $custom_field->field_rowset as $field_id => $field_data )
			{
				$template->assign_block_vars( 'field_row', array(
					'FIELD_ID' => $field_id,
					'FIELD_NAME' => $field_data['custom_name'],
					'FIELD_DESC' => $field_data['custom_description']
				));
			}
		}

		// Output
		$this->_pafiledb();
		$pafiledb_cache->unload();
	}
}
?>