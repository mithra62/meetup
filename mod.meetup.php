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
 * Meetup Modules - Mod Class
 *
 * Module class
 *
 * @package 	mithra62:Meetup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/meetup/mod.meetup.php
 */
class Meetup 
{

	/**
	 * The data to return from the module
	 * @var stirng
	 */
	public $return_data	= '';
	
	/**
	 * The TMPL var for member_id
	 * @var int
	 */
	public $member_id = FALSE;
	
	/**
	 * The delimeter to split template vars up with
	 * @var unknown_type
	 */
	public $delim = ':';
	
	/**
	 * Flag to enable pagination stuff
	 * @var bool
	 */
	public $paginate = FALSE;
	
	/**
	 * Contains the links to return for pagination
	 * @var string
	 */
	public $pagination_links = '';
	public $page_next = '';
	public $page_previous = '';
	public $current_page = 1;	
	
	public $anchor_class = FALSE;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$path = dirname(realpath(__FILE__));
		include $path.'/config'.EXT;
		$this->class = $config['class_name'];
		$this->settings_table = $config['settings_table'];
		$this->version = $config['version'];

		$this->EE->load->library('pagination');
		$this->EE->load->library('meetup_lib');
		$this->EE->load->library('Meetup_api');
		$this->settings = $this->EE->meetup_lib->get_settings();
		$this->EE->load->helper('utilities');
		
		$this->EE->meetup_api->prefix = $this->prefix = $this->EE->TMPL->fetch_param('prefix', 'mu') . $this->delim;
		
		$this->per_page = $this->EE->TMPL->fetch_param('limit', '20');
		$this->offset = $this->EE->TMPL->fetch_param('offset', '0');
		$this->order = $this->EE->TMPL->fetch_param('order', FALSE);
		$this->group_id = $this->EE->TMPL->fetch_param('group_id');
		$this->photo_id = $this->EE->TMPL->fetch_param('photo_id');
		$this->group_urlname = $this->EE->TMPL->fetch_param('group_urlname');
		$this->member_id = $this->EE->TMPL->fetch_param('member_id');
		$this->organizer_id = $this->EE->TMPL->fetch_param('organizer_id');
		$this->service = $this->EE->TMPL->fetch_param('service');
		$this->event_id = $this->EE->TMPL->fetch_param('event_id');
		$this->venue_id = $this->EE->TMPL->fetch_param('venue_id');		
		$this->status = $this->EE->TMPL->fetch_param('status');	
		$this->api_key = $this->EE->TMPL->fetch_param('api_key');
		
		$this->anchor_class = $this->EE->TMPL->fetch_param('anchor_class');
		
		//setup offset
		if ($this->EE->uri->query_string != '' && preg_match("#^".$this->prefix."(\d+)|/".$this->prefix."(\d+)#", $this->EE->uri->query_string, $match))
		{
			$this->offset = floor(isset($match['2']) ? ($match['2']/$this->per_page) : '0');
		}		
		
		$this->setup_where();
	}
	
	public function groups()
	{
		if(!$this->group_urlname && !$this->group_id && !$this->organizer_id && !$this->member_id)
		{
			return 'Must have group_id or group_urlname or member_id or organizer_id!';
		}

		$groups = $this->EE->meetup_api->get_groups($this->where);
		if(!isset($groups['results']) || !is_array($groups['results']) || count($groups['results']) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
		
		$data = array();
		foreach($groups['results'] AS $key => $value)
		{
			$value['created'] = ($value['created'] / 1000);
			$value = $this->clean_topics($value);
			$data[$key] = $this->setup_data($value);
		}
		
		$output = $this->prep_output($data, $groups['meta']);
		return $output;		
	}
	
	public function members()
	{	
		if(!$this->group_urlname && !$this->group_id && !$this->member_id)
		{
			return 'Must have group_id or group_urlname or member_id or service!';
		}	
			
		$members = $this->EE->meetup_api->get_members($this->where);
		if(count($members) == '0')
		{
			return $this->EE->TMPL->no_results();
		}

		$data = array();
		foreach($members['results'] AS $key => $value)
		{
			$value = $this->clean_topics($value);
			$data[$key] = $this->setup_data($value);
			if(!isset($data[$key][$this->prefix.'topics']))
			{
				$data[$key][$this->prefix.'topics'] = array();
			}
		}

		$output = $this->prep_output($data, $members['meta']);
		return $output;
	}	
	
	public function events()
	{
		if(!$this->group_urlname && !$this->group_id && !$this->member_id && !$this->venue_id && !$this->event_id)
		{
			return 'Must have group_id or group_urlname or member_id or service or event_id or venue_id!';
		}

		$events = $this->EE->meetup_api->get_events($this->where);
		if(count($events) == '0')
		{
			return $this->EE->TMPL->no_results();
		}

		$data = array();
		foreach($events['results'] AS $key => $value)
		{
			$value = $this->clean_events($value);
			$data[$key] = $this->setup_data($value);
			if(!isset($data[$key][$this->prefix.'venue']))
			{
				$data[$key][$this->prefix.'venue'] = array();
			}			
		}

		$output = $this->prep_output($data, $events['meta']);
		return $output;
	}

	public function rsvps()
	{
		if(!$this->event_id)
		{
			return 'Must have event_id!';
		}
	
		$rsvps = $this->EE->meetup_api->get_rsvps($this->where);
		if(count($rsvps) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
		
		$data = array();
		foreach($rsvps['results'] AS $key => $value)
		{
			$data[$key] = $this->setup_data($value);
		}	

		$output = $this->prep_output($data, $rsvps['meta']);
		return $output;
	}
	
	public function event_comments()
	{
		if(!$this->event_id)
		{
			return 'Must have event_id!';
		}
		
		$comments = $this->EE->meetup_api->get_event_comments($this->where);
		if(count($comments) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
		
		$data = array();
		foreach($comments['results'] AS $key => $value)
		{
			if(isset($value['time']))
			{
				$value['time'] = ($value['time'] / 1000);		
			}
			$value['comment_time'] = $value['time'];
			$data[$key] = $this->setup_data($value);
		}

		$output = $this->prep_output($data, $comments['meta']);
		return $output;		
	}
	
	public function event_ratings()
	{
		if(!$this->event_id)
		{
			return 'Must have event_id!';
		}
	
		$ratings = $this->EE->meetup_api->get_event_ratings($this->where);
		if(count($ratings) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
	
		$data = array();
		foreach($ratings['results'] AS $key => $value)
		{
			if(isset($value['time']))
			{
				$value['time'] = ($value['time'] / 1000);
			}
			
			if(!isset($value['review']))
			{
				$value['review'] = '';
			}
			$value['rating_time'] = $value['time'];
			$data[$key] = $this->setup_data($value);
		}
	
		$output = $this->prep_output($data, $ratings['meta']);
		return $output;
	}	
	
	public function photos()
	{
		if(!$this->group_id && !$this->photo_id && !$this->event_id)
		{
			return 'Must have group_id or photo_id or event_id!';
		}
	
		$photos = $this->EE->meetup_api->get_photos($this->where);
		if(count($photos) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
	
		$data = array();
		foreach($photos['results'] AS $key => $value)
		{
			$value['photo_created'] = ($value['created'] / 1000);
			$value['photo_updated'] = ($value['updated'] / 1000);
			$data[$key] = $this->setup_data($value);
		}

		$output = $this->prep_output($data, $photos['meta']);
		return $output;
	}

	public function photo_comments()
	{
		if(!$this->photo_id)
		{
			return 'Must have photo_id!';
		}
	
		$comments = $this->EE->meetup_api->get_photo_comments($this->where);
		if(count($comments) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
	
		$data = array();
		foreach($comments['results'] AS $key => $value)
		{
			if(isset($value['time']))
			{
				$value['created'] = ($value['created'] / 1000);
			}
			$value['comment_time'] = $value['created'];
			$data[$key] = $this->setup_data($value);
		}
				
		$output = $this->prep_output($data, $comments['meta']);
		return $output;
	}	
	
	public function venues()
	{
		if(!$this->group_id && !$this->venue_id && !$this->event_id && !$this->group_urlname)
		{
			return 'Must have group_id or photo_id or event_id or group_urlname!';
		}
	
		$venues = $this->EE->meetup_api->get_venues($this->where);
		if(count($venues) == '0')
		{
			return $this->EE->TMPL->no_results();
		}
	
		$data = array();
		foreach($venues['results'] AS $key => $value)
		{
			
			$value['venue_name'] = $value['name'];
			$data[$key] = $this->setup_data($value);
		}

		$output = $this->prep_output($data, $venues['meta']);
		return $output;
	}

	/**
	 * Wrapper for all the TMPL and pagination stuff
	 * @param array $data
	 * @param array $meta
	 */
	private function prep_output($data, $meta)
	{
		//pagination stage 1
		if (preg_match("/".LD.$this->prefix."paginate".RD."(.+?)".LD.'\/'.$this->prefix."paginate".RD."/s", $this->EE->TMPL->tagdata, $match))
		{
			$this->paginate = TRUE;
			$this->paginate_data = $match['1'];
			$this->p_page = '';
			$this->EE->TMPL->tagdata = preg_replace("/".LD.$this->prefix."paginate".RD.".+?".LD.'\/'.$this->prefix."paginate".RD."/s", "", $this->EE->TMPL->tagdata);
			$this->basepath = $this->EE->functions->create_url($this->EE->uri->uri_string, 1);	
			$paginate_prefix = $this->prefix;
			if ($this->EE->uri->query_string != '' && preg_match("#^".$paginate_prefix."(\d+)|/".$paginate_prefix."(\d+)#", $this->EE->uri->query_string, $match))
			{
				$this->p_page = (isset($match['2'])) ? $match['2'] : $match['1'];
				$this->basepath = $this->EE->functions->remove_double_slashes(str_replace($match['0'], '', $this->basepath));
			}

			$this->total_rows = $meta['total_count'];
			$this->p_limit  = ( ! $this->EE->TMPL->fetch_param('limit'))  ? 50 : $this->EE->TMPL->fetch_param('limit');
			$this->p_page = ($this->p_page == '' OR ($this->p_limit > 1 AND $this->p_page == 1)) ? 0 : $this->p_page;
			
			if ($this->p_page > $this->total_rows)
			{
				$this->p_page = 0;
			}
			
			$this->current_page = floor(($this->p_page / $this->p_limit) + 1);
			
			$this->total_pages = intval(floor($this->total_rows / $this->p_limit));

			if ($this->total_rows % $this->p_limit)
			{
				$this->total_pages++;
			}
				
			if ($this->total_rows > $this->p_limit)
			{
				if (strpos($this->basepath, SELF) === FALSE && $this->EE->config->item('site_index') != '')
				{
					$this->basepath .= SELF;
				}
			
				$this->basepath = rtrim($this->basepath,'/').'/';
			
				$config['anchor_class'] = $this->anchor_class;
				$config['base_url'] = $this->basepath;
				$config['prefix'] = $paginate_prefix;
				$config['use_page_numbers'] = TRUE;
				$config['total_rows'] = $this->total_rows;
				$config['per_page'] = $this->p_limit;
				$config['cur_page'] = $this->p_page;
				$config['first_link'] = $this->EE->lang->line('pag_first_link');
				$config['last_link'] = $this->EE->lang->line('pag_last_link');

				$this->EE->pagination->initialize($config);
				$this->pagination_links = $this->EE->pagination->create_links();
			
				if ((($this->total_pages * $this->p_limit) - $this->p_limit) > $this->p_page)
				{
					$this->page_next = $this->basepath.$paginate_prefix.($this->p_page + $this->p_limit);
				}
			
				if (($this->p_page - $this->p_limit ) >= 0)
				{
					$this->page_previous = $this->basepath.$paginate_prefix.($this->p_page - $this->p_limit);
				}
			}
			else
			{
				$this->p_page = '';
			}			
			
		}		

		//setup primary vars
		$output = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $data);	
		
		//pagination stage 2
		if ($this->paginate == TRUE)
		{
			$this->paginate_data = str_replace(LD.$this->prefix.'current_page'.RD, $this->current_page, $this->paginate_data);
			$this->paginate_data = str_replace(LD.$this->prefix.'total_pages'.RD,	$this->total_pages, $this->paginate_data);
			$this->paginate_data = str_replace(LD.$this->prefix.'pagination_links'.RD, $this->pagination_links, $this->paginate_data);
				
			if (preg_match("/".LD."if ".$this->prefix."previous_page".RD."(.+?)".LD.'\/'."if".RD."/s", $this->paginate_data, $match))
			{
				if ($this->page_previous == '')
				{
					$this->paginate_data = preg_replace("/".LD."if ".$this->prefix."previous_page".RD.".+?".LD.'\/'."if".RD."/s", '', $this->paginate_data);
				}
				else
				{
					$match['1'] = preg_replace("/".LD.'path.*?'.RD."/", 	$this->page_previous, $match['1']);
					$match['1'] = preg_replace("/".LD.'auto_path'.RD."/",	$this->page_previous, $match['1']);
						
					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}
				
			if (preg_match("/".LD."if ".$this->prefix."next_page".RD."(.+?)".LD.'\/'."if".RD."/s", $this->paginate_data, $match))
			{
				if ($this->page_next == '')
				{
					$this->paginate_data = preg_replace("/".LD."if ".$this->prefix."next_page".RD.".+?".LD.'\/'."if".RD."/s", '', $this->paginate_data);
				}
				else
				{
					$match['1'] = preg_replace("/".LD.'path.*?'.RD."/", 	$this->page_next, $match['1']);
					$match['1'] = preg_replace("/".LD.'auto_path'.RD."/",	$this->page_next, $match['1']);
						
					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}
		
			$position = ( ! $this->EE->TMPL->fetch_param('paginate')) ? '' : $this->EE->TMPL->fetch_param('paginate');
				
			switch ($position)
			{
				case "top": 
					$output  = $this->paginate_data.$output;
				break;
				case "both":
					$output  = $this->paginate_data.$output.$this->paginate_data;
				break;
				default: 
					$output .= $this->paginate_data;
				break;
			}
		}
		
		//setup meta vars
				
		return $output;	
	}

	/**
	 * Cleans up the Topic data
	 * @param array $data
	 */
	private function clean_topics($data)
	{
		$data['total_topics'] = count($data['topics']);
		$topic_count = 0;
		foreach($data['topics'] AS $key => $value)
		{
			$topic_count++;
			$data['topics'][$key]['topic_count'] = $topic_count;
			$data['topics'][$key]['topic_id'] = $value['id'];
			$data['topics'][$key]['topic_name'] = $value['name'];
		}

		return $data;
	}
	
	/**
	 * Cleas up the Event data
	 * @param array $data
	 */
	private function clean_events($data)
	{
		$data['event_name'] = $data['name'];
		$data['event_id'] = $data['id'];
		$data['event_created'] = $data['created'];
		$data['event_status'] = $data['status'];
		$data['time'] = ($data['time'] / 1000);
		return $data;
	}	
	
	/**
	 * Preps the TMPL variables to contain the prefix
	 * @param array $data
	 * @return multitype:array NULL
	 */
	private function prep_prefix($data)
	{
		$return = array();
		foreach($data AS $key => $value)
		{
			if(is_array($value))
			{
				if(!is_numeric($key))
				{
					$key = $this->prefix.$key;
				}
				$return[$key] = $this->prep_prefix($value);
			}
			else 
			{
				$return[$this->prefix.$key] = $value;
			}
		}
		
		return $return;
	}
	
	/**
	 * Converts the API data to TMPL friendly
	 * @param array $data
	 * @return array
	 */
	private function setup_data(array $data)
	{
		$data = $this->EE->meetup_lib->flatten_array($data, $this->delim);
		$data = $this->prep_prefix($data);
		return $data;
	}
	
	private function setup_where()
	{
		$this->where['page'] = $this->per_page;
		$this->where['offset'] = $this->offset;
		$this->where['order'] = $this->order;
	
		if($this->group_urlname)
		{
			$this->where['group_urlname'] = $this->group_urlname;
		}
		elseif($this->group_id)
		{
			$this->where['group_id']= $this->group_id;
		}
	
		if($this->photo_id)
		{
			$this->where['photo_id'] = $this->photo_id;
		}		
		
		if($this->organizer_id)
		{
			$this->where['organizer_id'] = $this->organizer_id;
		}
	
		if($this->member_id)
		{
			$this->where['member_id'] = $this->member_id;
		}
	
		if($this->event_id)
		{
			$this->where['event_id'] = $this->event_id;
		}
	
		if($this->venue_id)
		{
			$this->where['venue_id'] = $this->venue_id;
		}
	
		if($this->service)
		{
			$this->where['service'] = $this->service;
		}
	
		if($this->status)
		{
			$this->where['status'] = $this->status;
		}
		
		if($this->api_key)
		{
			$this->where['key'] = $this->api_key;
		}
	}	
}