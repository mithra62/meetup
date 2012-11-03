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
 * Meetup - JS methods
 *
 * JavaScript Class
 *
 * @package 	mithra62:Meetup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/libraries/Meetup_js.php
 */
class Meetup_js
{
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	public function get_accordian_css()
	{	
		return ' $("#my_accordion").accordion({autoHeight: false,header: "h3"}); ';
	}
}