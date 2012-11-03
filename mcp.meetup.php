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
 * Meetup - CP Class
 *
 * Control Panel class
 *
 * @package 	mithra62:Meetup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/mcp.meetup.php
 */
class Meetup_mcp 
{
	public $url_base = '';
	
	/**
	 * The amount of pagination items per page
	 * @var int
	 */
	public $perpage = 10;
	
	/**
	 * The delimiter for the datatables jquery
	 * @var stirng
	 */
	public $pipe_length = 1;
	
	/**
	 * The name of the module; used for links and whatnots
	 * @var string
	 */
	private $mod_name = '';
		
	public function __construct()
	{
		$this->EE =& get_instance();
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->class = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->version = $config['version'];
				
		$this->mod_name = $config['mod_url_name'];
		
		//load EE stuff
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		$this->EE->load->helper('utilities');
		$this->EE->load->library('Meetup_lib');
		$this->EE->load->library('Meetup_js');

		$this->settings = $this->EE->meetup_lib->get_settings();		

		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
		$this->url_base = BASE.AMP.$this->query_base;
		$this->EE->meetup_lib->set_url_base($this->url_base);
		
		$this->EE->cp->set_variable('url_base', $this->url_base);
		$this->EE->cp->set_variable('query_base', $this->query_base);
		
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name, $this->EE->lang->line('meetup_module_name'));
		$this->EE->cp->set_right_nav($this->EE->meetup_lib->get_right_menu());	
		
		$this->errors = $this->EE->meetup_lib->error_check();
		
		$this->EE->cp->set_variable('errors', $this->errors);
		$this->EE->cp->set_variable('settings', $this->settings);
		
		$this->EE->cp->set_variable('theme_folder_url', $this->EE->config->item('theme_folder_url'));

		$ignore_methods = array();
		$method = $this->EE->input->get('method', TRUE);
		if($this->settings['disable_accordions'] === FALSE && !in_array($method, $ignore_methods))
		{
			$this->EE->javascript->output($this->EE->meetup_js->get_accordian_css());
		}		
	}
	
	public function index()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=settings');
	}

	public function settings()
	{
		if(isset($_POST['go_settings']))
		{				
			if($this->EE->meetup_settings->update_settings($_POST))
			{	
				$this->EE->logger->log_action($this->EE->lang->line('log_settings_updated'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('settings_updated'));
				$this->EE->functions->redirect($this->url_base.'settings');		
				exit;			
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('settings_update_fail'));
				$this->EE->functions->redirect($this->url_base.'settings');	
				exit;					
			}
		}
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('settings'));
		
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->compile();
		$vars['settings_disable'] = FALSE;
		if(isset($this->EE->config->config['meetup']))
		{
			$vars['settings_disable'] = 'disabled="disabled"';
		}	
			
		return $this->EE->load->view('settings', $vars, TRUE);
	}
}