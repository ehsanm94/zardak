<?php 
namespace Zardak\Damev;

class Event 
{
	const OFFICIAL_EVENT 	= 'official_event';
	const CLASS_EVENT	= 'class_event';
	const HW_EVENT 	= 'hw_event';

	private $type;
	private $name;
	private $is_holiday;
	private $url;

	public function __construct($type, $name, $is_holiday = false, $url = '')
	{
		$this->type 		= $type;
		$this->name 		= $name;
		$this->is_holiday 	= $is_holiday;
		$this->url 		= $url;
	}

	public function getEvent()
	{
		return array(
			'type'	=> $this->type,
			'name' 	=> $this->name,
			'url' 	=> $this->url,
		);
	}

	public function getIsHoliday()
	{
		return $this->is_holiday;
	}
}
?>