<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Meetup_api
 *
 * @package		mithra62:Meetup_api
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/meetup/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/meetup/
 */
 
 /**
 * Meetup_api - API Wrapper
 *
 * API Wrapper
 *
 * @package 	mithra62:Meetup_api
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/libraries/Meetup_api.php
 */
class Meetup_api
{
	/**
	 * Container for the various settings
	 * @var array
	 */
	public $settings = array();
	
	/**
	 * The main URL the API requests go to
	 * @var string
	 */
	public $api_url = 'https://api.meetup.com';
	
	/**
	 * Whether we should cache requests
	 * @var unknown_type
	 */
	public $cache = TRUE;
	
	/**
	 * The various paths mapped to the API we're using
	 * @var array
	 */
	public $endpoints = array(
			'groups' => '/2/groups',
			'group_comments' => '/comments',
			'members' => '/2/members',
			'events' => '/2/events',
			'rsvps' => '/2/rsvps',
			'photos' => '/2/photos',
			'venues' => '/2/venues',
			'event_comments' => '/2/event_comments',
			'event_ratings' => '/2/event_ratings',
			'photo_comments' => '/2/photo_comments'
	);
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('Meetup_cache');
		$this->settings = $this->EE->meetup_lib->get_settings();
	}
	
	/**
	 * Returns the groups
	 * @param array $where
	 */
	public function get_groups(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['groups']);
		$data = $this->_proc_data($url);
		return $data;
	}
	
	/**
	 * Returns the group comments
	 * @param array $where
	 */
	public function get_group_comments(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['groups']);
		$data = $this->_proc_data($url);
		return $data;
	}	
	
	/**
	 * Returns the members
	 * @param array $where
	 * @param int $limit
	 */
	public function get_members(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['members']);
		$data = $this->_proc_data($url);
		return $data;
	}
	
	/**
	 * Returns the events
	 * @param array $where
	 */
	public function get_events(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['events']);
		$data = $this->_proc_data($url);
		return $data;
	}	
	
	/**
	 * Returns the event comments
	 * @param array $where
	 */
	public function get_event_comments(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['event_comments']);
		$data = $this->_proc_data($url);
		return $data;
	}

	/**
	 * Returns the event ratings
	 * @param array $where
	 */
	public function get_event_ratings(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['event_ratings']);
		$data = $this->_proc_data($url);
		return $data;
	}
		
	/**
	 * Returns the photos
	 * @param array $where
	 */
	public function get_photos(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['photos']);
		$data = $this->_proc_data($url);
		return $data;
	}

	/**
	 * Returns the photo comments
	 * @param array $where
	 */
	public function get_photo_comments(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['photo_comments']);
		$data = $this->_proc_data($url);
		return $data;
	}	
	
	/**
	 * Returns the RSVPs
	 * @param array $where
	 */
	public function get_rsvps(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['rsvps']);
		$data = $this->_proc_data($url);
		return $data;
	}	

	/**
	 * Returns the Venues
	 * @param array $where
	 */
	public function get_venues(array $where = array())
	{
		$url = $this->_create_url($where, $this->endpoints['venues']);
		$data = $this->_proc_data($url);
		return $data;
	}
		
	/**
	 * Wrapper to craft the URL we're executing to
	 * @param array $where
	 * @param stirng $endpoint
	 */
	private function _create_url(array $where = array(), $endpoint)
	{
		$api_key = $this->settings['api_key'];
		if(isset($where['key']))
		{
			$api_key = $where['key'];
			unset($where['key']);
		}
				
		$query = array();
		foreach($where AS $key => $value)
		{
			if($value != '')
			{
				$query[] = $key.'='.$value;
			}
		}
		
		$url = $this->api_url.$endpoint.'?key='.$api_key.'&'.implode('&',$query);
		return $url;
	}
	
	/**
	 * Executes the API calls and returns the response ready for work
	 * @param string $url
	 */
	private function _proc_data($url)
	{
		$cache_key = md5($url);
		$data = $this->EE->meetup_cache->read_cache($cache_key);
		if(!$data)
		{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
			$response = urldecode(curl_exec($ch));
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$bad_statuses = array('404','500', '400', '0');
			if(in_array($http_status, $bad_statuses))
			{
				return FALSE;
			}
			
			$data = $response;
			$this->EE->meetup_cache->create_cache_file(serialize($data), $cache_key);			
		}
		else 
		{
			$data = unserialize($data);
		}
		
		$data = json_decode($data);
		$data = json_decode(json_encode($data), true); //I do this silly dance to convert an object to an array recursively
		if(isset($data['meta']['count']) && $data['meta']['count'] == '0')
		{
			return array();
		}
		
		return $data;
	}	
	
}