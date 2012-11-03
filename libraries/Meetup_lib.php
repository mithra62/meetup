<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Meetup_lib
 *
 * @package		mithra62:Meetup_lib
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/meetup/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/meetup/
 */

 /**
 * Meetup_lib - Generic library
 *
 * Contains the commonly used functions 
 *
 * @package 	mithra62:Meetup_lib
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/libraries/Meetup_lib.php
 */
class Meetup_lib
{
	/**
	 * The default URL to use within the CP
	 */
	private $url_base = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('encrypt');
		$this->EE->load->model('Meetup_settings_model', 'meetup_settings');
		$this->settings = $this->get_settings();
	}
	
	/**
	 * Returns the array needed for the CP menu
	 */
	public function get_right_menu()
	{
		return array(
				'settings'	=> $this->url_base.'settings'
		);
	}	
	
	/**
	 * Wrapper to handle CP URL creation
	 * @param string $method
	 */
	public function _create_url($method)
	{
		return $this->url_base.$method;
	}

	/**
	 * Creates the value for $url_base
	 * @param string $url_base
	 */
	public function set_url_base($url_base)
	{
		$this->url_base = $url_base;
	}
	
	public function perpage_select_options()
	{
		return array(
			   '10' => '10 '.lang('results'),
			   '25' => '25 '.lang('results'),
			   '75' => '75 '.lang('results'),
			   '100' => '100 '.lang('results'),
			   '150' => '150 '.lang('results')
		);		
	}
	
	public function date_select_options()
	{
		return array(
			   '' => lang('date_range'),
			   '1' => lang('past_day'),
			   '7' => lang('past_week'),
			   '31' => lang('past_month'),
			   '182' => lang('past_six_months'),
			   '365' => lang('past_year'),
			   'custom_date' => lang('any_date')
		);				
	}
	
	/**
	 * Wrapper that runs all the tests to ensure system stability
	 * @return array;
	 */
	public function error_check()
	{
		$errors = array();
		return $errors;
	}	
	
	/**
	 * Returns an array for configuring the EE pagination mechanism 
	 * @param string $method
	 * @param int 	$total_rows
	 * @param int	 $perpage
	 */
	public function pagination_config($method, $total_rows, $perpage)
	{
		// Pass the relevant data to the paginate class
		$config['base_url'] = $this->_create_url($method);
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $perpage;
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'rownum';
		$config['full_tag_open'] = '<p id="paginationLinks">';
		$config['full_tag_close'] = '</p>';
		$config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		return $config;
	}

	/**
	 * Half ass attempt at license verification.
	 * @param string $license
	 */
	public function valid_license($license)
	{
		//return TRUE; //if you want to disable the check uncomment this line. You should pay me though eric@mithra62.com :) 
		return preg_match("/^([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})$/", $license);
	}	
	
	/**
	 * Returns the setting array and caches it if none exists
	 */
	public function get_settings()
	{
		if ( ! isset($this->EE->session->cache[__CLASS__]['settings']))
		{
			$settings = $this->EE->meetup_settings->get_settings();
			foreach($settings AS $key => $value)
			{
				if(in_array($key, $this->EE->meetup_settings->_encrypted))
				{
					$settings[$key] = $this->EE->encrypt->decode($settings[$key]);
				}
			}
			
			$this->EE->session->cache[__CLASS__]['settings'] = $settings;
		}
	
		return $this->EE->session->cache[__CLASS__]['settings'];
	}	
	
	/**
	 * Forces an array to download as a csv file
	 * @param array $arr
	 * @param bool $keys_as_headers
	 * @param bool $file_name
	 */
	public function downloadArray(array $arr, $keys_as_headers = TRUE, $file_name = FALSE)
	{
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$file_name\"");
		$cols = '';
		$rows = '';			
		if(is_array($arr) && count($arr) >= 1)
		{
			$rows = array();
			$cols = array_keys($arr['0']);
			foreach($arr AS $key => $value)
			{
				foreach($value AS $k => $v)
				{
					$value[$k] = $this->escape_csv_value($v, "\t");
				}
								
				$rows[] = implode("\t", $value);
			}
						
			echo implode("\t", $cols)."\n";
			echo implode("\n", $rows);
		}
		
		
		exit;

	}	
	
	public function escape_csv_value($value, $delim = ',') 
	{
		$value = str_replace('"', '""', $value);
		if(preg_match('/'.$delim.'/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value))
		{ 
			return '"'.$value.'"'; 
		} 
		else 
		{
			return $value; 
		}
	}	
	
	public function is_installed_module($module_name)
	{
		$data = $this->EE->db->select('module_name')->from('modules')->like('module_name', $module_name)->get();
		if($data->num_rows == '1')
		{
			return TRUE;
		}
	}
	
	public function get_template_options()
	{
		if ( ! isset($this->EE->session->cache[__CLASS__]['template_options']))
		{
			$query = $this->EE->template_model->get_templates();
			$this->EE->session->cache[__CLASS__]['template_options'][] = '';
			
			if ($query->num_rows() > 0)
			{
				foreach ($query->result() as $template)
				{
					$this->EE->session->cache[__CLASS__]['template_options'][$template->template_id] = $template->group_name.'/'.$template->template_name;
				}
			}			
		}
		
		return $this->EE->session->cache[__CLASS__]['template_options'];
	}
	
	public function flatten_array(array $data, $delim = '_', $depth = 1)
	{
		$return = array();
		foreach($data AS $key => $value)
		{
			if(is_array($value))
			{
				foreach($value AS $k => $v)
				{
					if(!is_numeric($k))
					{
						$return[$key.$delim.$k] = $v;
					}
					else
					{
						$return[$key] = $value;
						break;
					}
				}
			}
			else
			{
				$return[$key] = $value;
			}
			//$return[$delim.$key]
		}
		return $return;
	}
}