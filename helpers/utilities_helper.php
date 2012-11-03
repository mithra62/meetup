<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Meetup
 *
 * @package		mithra62:Meetup
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/meetup/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/meetup/
 */
 
 /**
 * Meetup - Helper Functions
 *
 * Helper Functions
 *
 * @package 	mithra62:Meetup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/helpers/utilities_helper.php
 */

if ( ! function_exists('m62_format_number'))
{


	/**
	 * Timestamp Format
	 * Wrapper that takes a string and converts it according to settings
	 * @param string $date
	 * @param string $format
	 */
	function m62_convert_timestamp($date, $format = FALSE)
	{
		$EE =& get_instance();
		$EE->load->helper('date');		
		return mdate($format, $date);		
	}
	
	/**
	 * Returns the status color based on $status
	 * @param string $status
	 * @param array $statuses
	 * @return boolean|array
	 */
	function m62_status_color($status, array $statuses = array())
	{
		if(!is_array($statuses))
		{
			return FALSE;
		}

		foreach($statuses AS $color)
		{
			if($status == $color['status'])
				return $color['highlight'];
		}
	}
	
	function m62_country_code($code)
	{
		include APPPATH .'config/countries.php';
		if(isset($countries[$code]))
		{
			return $countries[$code];
		}
		else
		{
			return $code;
		}
	}

}