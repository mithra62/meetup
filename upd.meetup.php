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
 * Meetup - Upd Class
 *
 * Updater class
 *
 * @package 	mithra62:Meetup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/upd.meetup.php
 */
class Meetup_upd 
{     
    public $class = '';
    
    public $settings_table = '';  
     
    public function __construct() 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->class = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->version = $config['version'];	
		$this->ext_class_name = $config['ext_class_name'];	
    } 
    
	public function install() 
	{
		$this->EE->load->dbforge();
	
		$data = array(
			'module_name' => $this->class,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
	
		$this->EE->db->insert('modules', $data);
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->class."', 'void')";
		$this->EE->db->query($sql);
		
		$this->add_settings_table();
		
		$this->activate_extension();
		
		return TRUE;
	} 
	
	public function activate_extension()
	{
		$data = array();
		$data[] = array(
					'class'      => $this->ext_class_name,
					'method'    => 'void',
					'hook'  => 'export_it_api_start',
				
					'settings'    => '',
					'priority'    => 1,
					'version'    => $this->version,
					'enabled'    => 'y'
		);
	
		foreach($data AS $ex)
		{
			$this->EE->db->insert('extensions', $ex);	
		}		
	}

	public function uninstall()
	{
		$this->EE->load->dbforge();
	
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->class));
	
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', $this->class);
		$this->EE->db->delete('modules');
	
		$this->EE->db->where('class', $this->class);
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table($this->settings_table);
		
		$this->disable_extension();
	
		return TRUE;
	}
	
	public function disable_extension()
	{
		$this->EE->db->where('class', $this->ext_class_name);
		$this->EE->db->delete('extensions');
	}

	public function update($current = '')
	{
		
		if ($current == $this->version)
		{
			return FALSE;
		}
	}	
	
	private function add_settings_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
						'id'	=> array(
											'type'			=> 'int',
											'constraint'	=> 10,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'=> TRUE
										),
						'setting_key'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '30',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'setting_value'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'serialized' => array(
											'type' => 'int',
											'constraint' => 1,
											'null' => TRUE,
											'default' => '0'
						)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->settings_table, TRUE);		
	}
}