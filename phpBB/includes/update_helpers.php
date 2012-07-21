<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

/**
* phpBB Update Helpers
*/
class phpbb_update_helpers
{
	/**
	* Determine the new timezone for a given phpBB 3.0 timezone and
	* "Daylight Saving Time" option
	*
	*	@param	$timezone	float	Users timezone in 3.0
	*	@param	$dst		int		Users daylight saving time
	*	@return		string		Users new php Timezone which is used since 3.1
	*/
	function convert_phpbb30_timezone($timezone, $dst)
	{
		$offset = $timezone + $dst;

		switch ($timezone)
		{
			case '-12':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 12] Baker Island Time'
			case '-11':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 11] Niue Time, Samoa Standard Time'
			case '-10':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time'
			case '-9.5':
				return 'Pacific/Marquesas';			//'[UTC - 9:30] Marquesas Islands Time'
			case '-9':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 9] Alaska Standard Time, Gambier Island Time'
			case '-8':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 8] Pacific Standard Time'
			case '-7':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 7] Mountain Standard Time'
			case '-6':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 6] Central Standard Time'
			case '-5':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 5] Eastern Standard Time'
			case '-4.5':
				return 'America/Caracas';			//'[UTC - 4:30] Venezuelan Standard Time'
			case '-4':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 4] Atlantic Standard Time'
			case '-3.5':
				return 'America/St_Johns';			//'[UTC - 3:30] Newfoundland Standard Time'
			case '-3':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 3] Amazon Standard Time, Central Greenland Time'
			case '-2':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 2] Fernando de Noronha Time, South Georgia &amp; the South Sandwich Islands Time'
			case '-1':
				return 'Etc/GMT+' . abs($offset);	//'[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time'
			case '0':
				return (!$dst) ? 'UTC' : 'Etc/GMT-1';	//'[UTC] Western European Time, Greenwich Mean Time'
			case '1':
				return 'Etc/GMT-' . $offset;		//'[UTC + 1] Central European Time, West African Time'
			case '2':
				return 'Etc/GMT-' . $offset;		//'[UTC + 2] Eastern European Time, Central African Time'
			case '3':
				return 'Etc/GMT-' . $offset;		//'[UTC + 3] Moscow Standard Time, Eastern African Time'
			case '3.5':
				return 'Asia/Tehran';				//'[UTC + 3:30] Iran Standard Time'
			case '4':
				return 'Etc/GMT-' . $offset;		//'[UTC + 4] Gulf Standard Time, Samara Standard Time'
			case '4.5':
				return 'Asia/Kabul';				//'[UTC + 4:30] Afghanistan Time'
			case '5':
				return 'Etc/GMT-' . $offset;		//'[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time'
			case '5.5':
				return 'Asia/Kolkata';				//'[UTC + 5:30] Indian Standard Time, Sri Lanka Time'
			case '5.75':
				return 'Asia/Kathmandu';			//'[UTC + 5:45] Nepal Time'
			case '6':
				return 'Etc/GMT-' . $offset;		//'[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time'
			case '6.5':
				return 'Indian/Cocos';				//'[UTC + 6:30] Cocos Islands Time, Myanmar Time'
			case '7':
				return 'Etc/GMT-' . $offset;		//'[UTC + 7] Indochina Time, Krasnoyarsk Standard Time'
			case '8':
				return 'Etc/GMT-' . $offset;		//'[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time'
			case '8.75':
				return 'Australia/Eucla';			//'[UTC + 8:45] Southeastern Western Australia Standard Time'
			case '9':
				return 'Etc/GMT-' . $offset;		//'[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time'
			case '9.5':
				return 'Australia/ACT';				//'[UTC + 9:30] Australian Central Standard Time'
			case '10':
				return 'Etc/GMT-' . $offset;		//'[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time'
			case '10.5':
				return 'Australia/Lord_Howe';		//'[UTC + 10:30] Lord Howe Standard Time'
			case '11':
				return 'Etc/GMT-' . $offset;		//'[UTC + 11] Solomon Island Time, Magadan Standard Time'
			case '11.5':
				return 'Pacific/Norfolk';			//'[UTC + 11:30] Norfolk Island Time'
			case '12':
				return 'Etc/GMT-12';				//'[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time'
			case '12.75':
				return 'Pacific/Chatham';			//'[UTC + 12:45] Chatham Islands Time'
			case '13':
				return 'Pacific/Tongatapu';			//'[UTC + 13] Tonga Time, Phoenix Islands Time'
			case '14':
				return 'Pacific/Kiritimati';		//'[UTC + 14] Line Island Time'
			default:
				return 'UTC';
		}
	}
}
