<?php
namespace Zardak\Damev;
include 'jdf.php';

class Damev
{	
	public static function getCalendar($month = null, $year = null, $events = null)
	{
		$month		= !$month ? self::getThisMonth() : intval($month);
		$year 		= !$year ? self::getThisYear() : intval($year);

		$timestamp 	= self::getTimestamp($year, $month);

		$month_days 	= self::getMonthDays($timestamp);
		$month_offset 	= self::getMonthOffset($timestamp);
		$month_name 	= self::getMonthName($timestamp);

		$calendar		= array();
		$calendar['month']	= $month_name;
		$calendar['year']	= $year;
		$calendar['offset']	= $month_offset;
		$calendar['today']	= self::getToday();
		$calendar['days']	= self::getDays($month_days, $month_offset, $events);
		$calendar['next']	= self::getNextMonth($year, $month);
		$calendar['prev']	= self::getPrevMonth($year, $month);

		return json_encode(array('calendar' => $calendar));
	}

	private static function getMonthDays($timestamp)
	{
		return intval(tr_num(jdate('t', $timestamp), 'en'));
	}

	private static function getMonthOffset($timestamp) 
	{
		return intval(tr_num(jdate('w', $timestamp), 'en'));
	}

	public static function getThisMonth()
	{
		return intval(tr_num(jdate('n'), 'en'));
	}

	public static function getThisYear()
	{
		return intval(tr_num(jdate('Y'), 'en'));
	}

	public static function getThisDay()
	{
		return intval(tr_num(jdate('j'), 'en'));
	}

	private static function getToday()
	{
		return array(
			'day' 	=> self::getThisDay(),
			'month'	=> self::getThisMonth(),
			'year'	=> self::getThisYear(),
		);
	}

	private static function getTimestamp($year, $month)
	{
		return jmktime(12, 0, 0, $month, 1, $year);
	}

	private static function getMonthName($timestamp)
	{
		return jdate('F', $timestamp);
	}

	private static function getNextMonth($year, $month)
	{
		$next = array();
		$next_month 	= $month + 1;
		$next['month'] 	= $next_month < 13 ? $next_month : 1;
		$next['year']	= $next_month < 13 ? $year : $year + 1;
		return $next;
	}

	private static function getPrevMonth($year, $month)
	{
		$prev = array();
		$prev_month 	= $month - 1 ;
		$prev['month'] 	= $prev_month > 0 ? $prev_month : 12;
		$prev['year'] 	= $prev_month > 0 ? $year : $year - 1;
		return $prev;
	}

	private static function getDays($month_days, $month_offset, $events)
	{
		$days 		= array();
		$first_friday 	= 7 - $month_offset;

		for ($day_index = 1; $day_index <= $month_days; $day_index++)
		{
			$day = new Day($day_index);

			if (isset($events[$day_index]) && !empty($events[$day_index]))
			{
				foreach ($events[$day_index] as $event) 
				{
					$day->setEvent($event->getEvent());
					if ($event->getIsHoliday())
					{
						$day->setHoliday();
					}
				}
			}

			// set fridays as holiday.
			if ($day_index % 7 == ($first_friday != 7 ? $first_friday : 0)) $day->setHoliday();

			$days[] = $day->getAsArray();
		}

		return $days;
	}
}
?>