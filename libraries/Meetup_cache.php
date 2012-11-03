<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Meetup_cache
 *
 * @package		mithra62:Meetup_cache
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/meetup/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/meetup/
 */
 
 /**
 * Meetup_cache - Cache Library
 *
 * Cache Library
 *
 * @package 	mithra62:Meetup_cache
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/libraries/Meetup_cache.php
 */
class Meetup_cache 
{

	/**
	 * How long should the cache live for
	 * @var int
	 */
	public $cache_lifetime = 1800;	

	/**
	 * The path to the cache directory
	 * @var bool
	 */
	public $cache_path = FALSE;
	
	public function __construct() 
	{
		$this->cache_path = APPPATH.'cache/m62_meetup' ;			
	}
	
	/**
	 * Returns the path to the cache directory
	 * @param string $key
	 */
	private function get_cache_path($key)
	{
		return $this->cache_path ."/". $key . ".meetup";	
	}
	
	/**
	 * Wrapper to create the cache file
	 * @param string $data
	 * @param string $key
	 * @return boolean
	 */
	public function create_cache_file($data, $key)
	{
		$filepath = $this->get_cache_path($key);
	
		if (! is_dir($this->cache_path))
		{
			mkdir($this->cache_path . "", 0777, TRUE);
		}
		
		if(! is_really_writable($this->cache_path))
		{
			return FALSE;
		}
	
		if ( ! $fp = fopen($filepath, FOPEN_WRITE_CREATE_DESTRUCTIVE))
		{
			return FALSE;
		}
	
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
		chmod($filepath, DIR_WRITE_MODE);
		
		return TRUE;
	}
	
	/**
	 * Wrapper to read the cache into memory
	 * @param string $key
	 */
	public function read_cache($key)
	{
		$cache = FALSE;
		$filepath = $this->get_cache_path($key);
	
		if (!file_exists($filepath))
		{
			return FALSE;
		}
		
		if ( ! $fp = fopen($filepath, FOPEN_READ))
		{
			return FALSE;
		}
	
		if( filemtime($filepath) + $this->cache_lifetime < time() )
		{
			@unlink($filepath);
			return FALSE;
		}
	
		flock($fp, LOCK_SH);
		$length = filesize($filepath);
		if($length > 0)
		{
			$cache = fread($fp, $length);
		}
		flock($fp, LOCK_UN);
		fclose($fp);	
		return $cache;
	}	
}

?>