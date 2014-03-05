<?php
namespace CLDR;

class Date extends \DateTime
{
	const DEFAULT_ADULT_AGE = 18;
	const DEFAULT_DATE_TIME_FORMAT='Y-m-d H:i:s';
	
	private $_format;
	
	public function __construct($time = 'now', $timezone = null)
	{
		$time = str_replace(array('/', '\\'), '-', $time);
		
		if (isset($timezone)) {
			return parent::__construct($time, $timezone);
		} else {
			return parent::__construct($time);
		}
	}
	
	public function __toString()
	{
		return $this->format($this->getFormat());
	}
	
	public function getDay()
    {
    	return (int)$this->format('d');
    }
	
	public function getDaysOld($to = 'now')
	{
        return (int)$this->diff(new \DateTime($to))->format('%R%d');
    }
    
    public function setFormat($value)
	{
		$this->_format = $value;
		return $this;
	}
	
	public function getFormat()
	{
		if (!isset($this->_format)) {
			$this->_format = self::DEFAULT_DATE_TIME_FORMAT;
		}
		
		return $this->_format;
	}
	
	public function getHours()
	{
		return (int)$this->format('G');
	}
	
	public function getMinutes()
	{
		return (int)$this->format('i');
	}
    
    public function getMinutesOld($to = 'now')
	{
        return (int)$this->diff(new \DateTime($to))->format('%R%i');
    }
    
    public function getMonth()
    {
    	return (int)$this->format('m');
    }
    
    public function getMonthDay()
    {
    	return (int)$this->format('j');
    }
    
    public function getMonthsOld($to = 'now')
	{
        return (int)$this->diff(new \DateTime($to))->format('%R%m');
    }
    
    public function getSeconds()
	{
		return (int)$this->format('s');
	}
	
	public static function getTimeZonesPairs($country = null)
	{
		if (isset($country)) {
			$data = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY,
				strtoupper($country));
		} else {
			$data = \DateTimeZone::listIdentifiers();
		}
		
		$result = array();
		
		foreach ($data as $id => $zone) {
			$result[$zone] = $zone;
		}
		
		return $result;
	}
    
    public function getYear()
    {
    	return (int)$this->format('Y');
    }
    
    public function getYearDay()
    {
    	return (int)$this->format('z');
    }
	
	
	public function getYearsOld($to = 'now')
	{
        return (int)$this->diff(new \DateTime($to))->format('%R%y');
    }
	
	public function getWeekDay()
    {
    	return (int)$this->format('w');
    }
    
	public function addDays($value)
	{
		return $this->add(new \DateInterval('P'.$value.'D'));
	}
	
	public function addHours($value)
	{
		return $this->add(new \DateInterval('PT'.$value.'H'));
	}
	
	public function addMinutes($value)
	{
		return $this->add(new \DateInterval('PT'.$value.'M'));
	}
	
	public function addMonths($value)
	{
		return $this->add(new \DateInterval('P'.$value.'M'));
	}
	
	public function addSeconds($value)
	{
		return $this->add(new \DateInterval('PT'.$value.'S'));
	}
	
	public function addYears($value)
	{
		return $this->add(new \DateInterval('P'.$value.'Y'));
	}
	
	public function addWeeks($value)
	{
		return $this->add(new \DateInterval('P'.$value.'W'));
	}
	
	public static function createFromFormat($format, $time, $timezone = null)
	{
		if (isset($timezone)) {
			return new \CLDR\Date(
				parent::createFromFormat($format, $time, $timezone)->format('Y-m-d H:i:s'));
		} else {
			return new \CLDR\Date(
				parent::createFromFormat($format, $time)->format('Y-m-d H:i:s'));
		}
	}
	
	public function isAdult($adultAge = null)
	{
		if (!isset($adultAge)) {
			$adultAge = self::DEFAULT_ADULT_AGE;
		}
		
		return ($this->getYearsOld() >= $adultAge);
	}
	
	public static function timeToSec($time) { 
		$hours = substr($time, 0, -6); 
		$minutes = substr($time, -5, 2); 
		$seconds = substr($time, -2); 
	
		return (int)($hours * 3600 + $minutes * 60 + $seconds); 
	} 

	public static function secToTime($seconds) {
		$days = floor($seconds / 86400); 
		$hours = floor($seconds % 86400 / 3600); 
		$minutes = floor($seconds % 3600 / 60); 
		$seconds = $seconds % 60;
		
		if ($days == 0) {
			if ($hours == 0) {
				return sprintf('%02d:%02d', $minutes, $seconds); 
			}
			
			return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds); 
		}
		
		return sprintf('%d days %d:%02d:%02d', $days, $hours, $minutes, $seconds); 		
	}

	public function subDays($value)
	{
		return $this->sub(new \DateInterval('P'.$value.'D'));
	}
	
	public function subHours($value)
	{
		return $this->sub(new \DateInterval('PT'.$value.'H'));
	}
	
	public function subMinutes($value)
	{
		return $this->sub(new \DateInterval('PT'.$value.'M'));
	}
	
	public function subMonths($value)
	{
		return $this->sub(new \DateInterval('P'.$value.'M'));
	}
	
	public function subSeconds($value)
	{
		return $this->sub(new \DateInterval('PT'.$value.'S'));
	}
	
	public function subYears($value)
	{
		return $this->sub(new \DateInterval('P'.$value.'Y'));
	}
	
	public function subWeeks($value)
	{
		return $this->sub(new \DateInterval('P'.$value.'W'));
	}
	
	public function toSQLDate()
	{
		return $this->format('Y-m-d');
	}
	
	public function toSQLDateTime()
	{
		return $this->format('Y-m-d H:i:s');
	}
	
	public function toSQLTime()
	{
		return $this->format('H:i:s');
	}
	
	public function toUnixTime()
	{
		return $this->format('U');
	}
}
