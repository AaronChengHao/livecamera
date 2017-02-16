<?php
/**
 * assist class
 * @Author: Aaron
 * @Date:   2017-02-14 11:45:26
 * @Last Modified by:   Aaron
 * @Last Modified time: 2017-02-15 11:04:14
 */

class assist
{
	/**
	 * formatData
	 *
	 * @param  string  $type draw || quit || ...
	 * @param  integer $fd   socket_descriptor
	 * @param  string  $data datapackage
	 * @param  string  $user extendmessage
	 * @return array         formatData after
	 */
	public static function formatData($type = '' , $fd = 1, $data = '', $user = '')
	{
		$format = [
			'action' => $type,
			'fd'   => $fd,
			'data' => $data,
			'user' => $user,
		];
		return $format;
	}
}
