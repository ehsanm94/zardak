<?php 
namespace Zardak\Damev;
class Day
{
	private $day;
	private $events;
	private $is_holiday;

	public function __construct($day) {
		$this->day = $day;
		$this->events = array();
		$this->is_holiday = false;
	}

	public function setEvent($event)
	{
		$this->events[] = $event;
	}

	public function setHoliday() {
		$this->is_holiday = true;
	}

	public function getIsHoliday() {
		return $this->is_holiday;
	}

	public function getEvents() {
		return $this->events;
	}

	public function getAsArray() {
		return array(
			'day'		=> $this->day,
			'is_holiday'	=> $this->is_holiday,
			'events'		=> $this->getEvents(),
		);
	}
}
?>