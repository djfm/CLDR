<?php
namespace CLDR\I18n;

class Locale
{
	const DEFAULT_LOCALE = 'en';
	
	protected static $_filters = array(
		'language' => array('filter' => 'strtolower'),
		'script' => array('filter' => array('strtolower', 'ucfirst')),
		'territory' => array('filter' => 'strtoupper'),
		'variant' => array('filter' => 'strtoupper')
	);
	
	private static $_browserLocales;
	private static $_environmentLocale;
	private static $_locale;
	
	public function __toString()
    {
        return self::toString();
    }
    
    public static function getAddressFormat($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.'addressesData.php';

		return $resource['address'][self::getRegion($locale)]['addressFormat'];
	}
	
	public static function getBrowserLocales()
	{
		if (self::$_browserLocales !== null) {
            return self::$_browserLocales;
        }
        
		$regex  = '(?P<locale>[\w\-]+)+(?:;q=(?P<quality>[0-9]+\.[0-9]+))?';
		$result = array();
		
		$httpLanguages = getenv('HTTP_ACCEPT_LANGUAGE');
		
        if (empty($httpLanguages)) {
            if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
                $httpLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            } else {
                return $result;
            }
        }

		foreach (explode(',', $httpLanguages) as $language) {		
			if (preg_match("/{$regex}/", $language, $matches)) {
				$quality = isset($matches['quality']) ? $matches['quality'] : 1;
				$result[self::_canonicalize($matches['locale'])] = $quality;
			}
		}
		
		arsort($result);
		$result = array_keys($result);	
		self::$_browserLocales = $result;		
		return $result;
	}
	
	public static function getCountryByCode($code, $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';

		return $resource['countries'][$code];
	}
	
	public static function getCountriesPairs($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';

		return $resource['countries'];
	}
	
	public static function getCurrenciesPairs($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';
		
		$supplemental = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.'supplementalData.php';
		
		$data = array();		
		$currencies = array_unique($supplemental['currencies']);
		
		foreach ($currencies as $country => $currency) {
			$data[$currency] = ucwords($resource['currency'][$currency]['name']);
		}
		
		asort($data);
		
		return $data;
	}

	public static function getCurrencyByCountry($country)
	{
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.'supplementalData.php';

		return $resource['currencies'][$country];
	}
	
	public static function getCurrencyFormatByLocale($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['currencyFormat'];
	}
	
	public static function getCurrencyIso($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		

		return self::getCurrencyByCountry(self::getRegion($locale));
	}
	
	public static function getCurrencySymbol($locale = null, $currency = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		

		if (!isset($currency)) {
			$currency = self::getCurrencyByCountry(self::getRegion($locale));
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return  $resource['currency'][$currency]['symbol'];
	}
	
	public static function getDateFormat($type = 'medium', $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['dateFormats'][$type];
	}
	
	public static function getDateTimeFormat($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['dateTimeFormat'];
	}
	
	public static function getDecimalFormat($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['decimalFormat'];
	}
	
	public static function getEnvironmentLocale()
	{
		if (self::$_environmentLocale !== null) {
            return self::$_environmentLocale;
        }
        
		$regex = '(?P<locale>[\w\_]+)(\.|@|$)+';
		$result = array();
		
		$value = setlocale(LC_ALL, 0);

		if ($value != 'C' && $value != 'POSIX' && preg_match("/{$regex}/", $value, $matches)) {
			$result = (array) $matches['locale'];
			
			// TODO: Add region handle
		}
		
		self::$_environmentLocale = $result;
		return $result;
	}
	
	public static function getHttpCharset()
	{
		$regex  = '(?P<charset>[\w\-*]+)+(?:;q=(?P<quality>[0-9]+\.[0-9]+))?';
		$result = array();
		
		$httpCharsets = getenv('HTTP_ACCEPT_CHARSET');
		
        if (empty($httpCharsets)) {
            if (array_key_exists('HTTP_ACCEPT_CHARSET', $_SERVER)) {
                $httpCharsets = $_SERVER['HTTP_ACCEPT_CHARSET'];
            } else {
                return $result;
            }
        }

		foreach (explode(',', $httpCharsets) as $charset) {		
			if (preg_match("/{$regex}/", $charset, $matches)) {
				$quality = isset($matches['quality']) ? $matches['quality'] : 1;
				$result[$matches['charset']] = $quality;
			}
		}
		
		arsort($result);
		return array_keys($result);
	}
	
	public static function getLanguage()
    {
        $locale = explode('_', self::getLocale());
        return $locale[0];
    }
	
	public static function setLocale($value)
	{
		if (!is_string($value)) {
			throw new \InvalidArgumentException("Invalid type for setLocale function");
		}
		
		self::$_locale = self::_canonicalize($value);
	}
	
	public static function getLocale()
	{
		if (!isset(self::$_locale)) {
			self::$_locale = self::getPreferedLocale();
		}
		
		return self::$_locale;
	}
	
	public static function getMonthName($month, $type = 'wide', $standAlone = false, $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';
		
		if ($standAlone) {
			return isset($resource['monthNamesSA'][$type][$month]) ? $resource['monthNamesSA'][$type][$month] : $resource['monthNames'][$type][$month];
		} else {
			return isset($resource['monthNames'][$type][$month]) ? $resource['monthNames'][$type][$month] : $resource['monthNamesSA'][$type][$month];
		}
	}
	
	public static function getMonthNames($type = 'wide', $standAlone = false, $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';
		
		if ($standAlone) {
			return isset($resource['monthNamesSA'][$type]) ? $resource['monthNamesSA'][$type] : $resource['monthNames'][$type];
		} else {
			return isset($resource['monthNames'][$type]) ? $resource['monthNames'][$type] : $resource['monthNamesSA'][$type];
		}
	}
	
	public static function getNumberSymbol($name, $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';

		return $resource['symbols'][$name];
	}
	
	public static function getPercentFormat($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['percentFormat'];
	}
	
	public static function getPostalCodeRegex($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		

		return self::getPostalCodeRegexByCountry(self::getRegion($locale));		
	}
	
	public static function getPostalCodeRegexByCountry($country)
	{
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.'postalCodeData.php';

		if (isset($resource['postalCodes'][$country])) {
			return $resource['postalCodes'][$country];
		}
		
		return null;
	}
	
	public static function getPreferedLocale($locale = null)
	{
		if ($locale instanceof self) {
            $locale = $locale->toString();
        }

		if ($locale === 'browser') {
			$locale = self::getBrowserLocales();
		}

		if ($locale === 'environment') {
			$locale = self::getEnvironmentLocale();
		}

		if (($locale === 'auto') or ($locale === null)) {
			$locale = self::getBrowserLocales();
			$locale += self::getEnvironmentLocale();			
		}

		if (is_array($locale) === true) {
			reset($locale);
			$locale = current($locale);
		}

        if ($locale === null || trim($locale) == '') {
            $locale = self::DEFAULT_LOCALE;
        }

        $locale = self::_canonicalize($locale);
        return (string) $locale;
	}

    public static function getRegion($locale = null)
    {
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
        $locale = explode('_', strtoupper($locale));
        
        if (isset($locale[1]) === true) {
            return $locale[1];
        }

        return $locale[0];
    }
	
	public static function getScientificFormat($locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['scientificFormat'];
	}
	
	public static function getTimeFormat($type = 'medium', $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}		
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
			.DIRECTORY_SEPARATOR.$locale.'.php';
		return $resource['timeFormats'][$type];
	}
	
	public static function getWeekDayName($day, $type='wide', $standAlone = false, $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';
		
		if ($standAlone) {
			return isset($resource['weekDayNamesSA'][$type][$day]) ? $resource['weekDayNamesSA'][$type][$day] : $resource['weekDayNames'][$type][$day];
		} else {
			return isset($resource['weekDayNames'][$type][$day]) ? $resource['weekDayNames'][$type][$day] : $resource['weekDayNamesSA'][$type][$day];
		}
	}
	
	public static function getWeekDayNames($type='wide', $standAlone = false, $locale = null)
	{
		if (!isset($locale)) {
			$locale = self::getLocale();
		}
		
		$resource = require __DIR__.DIRECTORY_SEPARATOR.'Data'
		.DIRECTORY_SEPARATOR.$locale.'.php';
		
		if ($standAlone) {
			return isset($resource['weekDayNamesSA'][$type]) ? $resource['weekDayNamesSA'][$type] : $resource['weekDayNames'][$type];
		} else {
			return isset($resource['weekDayNames'][$type]) ? $resource['weekDayNames'][$type] : $resource['weekDayNamesSA'][$type];
		}
	}
	
	private static function _canonicalize($locale)
	{
		if (empty($locale) || $locale == '') {
			return null;
		}
		
		$regex  = '(?P<language>[a-z]{2,3})(?:[_-](?P<script>[a-z]{4}))?(?:[_-](?P<territory>[a-z]{2}))?(?:[_-](?P<variant>[a-z]{5,}))?';

		if (!preg_match("/^{$regex}$/i", $locale, $matches)) {
			throw new \InvalidArgumentException('Locale "'.$locale.'" could not be parsed');
		}

		$tags = array_filter(array_intersect_key($matches, static::$_filters));

		foreach ($tags as $name => &$tag) {
			foreach ((array)static::$_filters[$name]['filter'] as $filter) {
				$tag = $filter($tag);
			}
		}
		
		$result = array();

		foreach (static::$_filters as $name => $value) {
			if (isset($tags[$name])) {
				$result[] = $tags[$name];
			}
		}

		if ($result) {
			return implode('_', $result);
		}
		
		return $result;
	}
	
	public static function toString()
    {
        return (string) self::getLocale();
    }
}