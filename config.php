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
$config['name'] = 'Meetup'; //CHANGE WHEN CREATING NEW ADD-ONS
$config['class_name'] = 'Meetup'; //CHANGE WHEN CREATING NEW ADD-ONS
$config['settings_table'] = 'meetup_settings'; //CHANGE WHEN CREATING NEW ADD-ONS
$config['description'] = 'Wrapper for using the Meetup.com API within an ExpressionEngine site'; //CHANGE WHEN CREATING NEW ADD-ONS

$config['mod_url_name'] = strtolower($config['class_name']);
$config['ext_class_name'] = $config['class_name'].'_ext';

$config['version'] = '1.0.1';
$config['nsm_addon_updater']['versions_xml'] = 'http://mithra62.com/meetup.xml';
$config['docs_url'] = 'http://mithra62.com/docs/meetup';