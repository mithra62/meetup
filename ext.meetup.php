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
 * Meetup - Ext Class
 *
 * Extension class
 *
 * @package 	mithra62:Meetup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/ext.meetup.php
 */
class Meetup_ext 
{	
	public $settings = array();
	
	public $description	= '';
	
	public $settings_exist	= 'y';
	
	public $docs_url = ''; 
	
	public $required_by = array('module');	
		
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$path = dirname(realpath(__FILE__));
		include_once $path.'/config'.EXT;
		$this->description = $config['description'];
		$this->docs_url = $config['docs_url'];
		$this->class = $this->name = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->version = $config['version'];
		$this->mod_name = $config['mod_url_name'];
		$this->ext_class_name = $config['ext_class_name'];
	}
	
	public function settings_form()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=settings');
	}
	
	public function void()
	{
		
	}
	
	public function activate_extension() 
	{
		return TRUE;

	}
	
	public function update_extension($current = '')
	{
		return TRUE;
	}

	public function disable_extension()
	{
		return TRUE;

	}
}