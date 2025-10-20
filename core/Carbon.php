<?php

namespace Core;

/**
 * Date/Time Utilities (Carbon-like)
 */
class Carbon extends \DateTime
{
    const ATOM = 'Y-m-d\TH:i:sP';
    const COOKIE = 'l, d-M-Y H:i:s T';
    const ISO8601 = 'Y-m-d\TH:i:sO';
    const RFC822 = 'D, d M y H:i:s O';
    const RFC850 = 'l, d-M-y H:i:s T';
    const RFC1036 = 'D, d M y H:i:s O';
    const RFC1123 = 'D, d M Y H:i:s O';
    const RFC2822 = 'D, d M Y H:i:s O';
    const RFC3339 = 'Y-m-d\TH:i:sP';
    const RSS = 'D, d M Y H:i:s O';
    const W3C = 'Y-m-d\TH:i:sP';
    
    /**
     * Create new Carbon instance
     */
    public function __construct($time = null, $timezone = null)
    {
        parent::__construct($time ?: 'now', $timezone ? new \DateTimeZone($timezone) : null);
    }
    
    /**
     * Create Carbon from format
     */
    public static function createFromFormat($format, $time, $timezone = null)
    {
        $date = parent::createFromFormat($format, $time, $timezone ? new \DateTimeZone($timezone) : null);
        
        if (!$date) {
            throw new \InvalidArgumentException("Could not parse date: {$time}");
        }
        
        $carbon = new self();
        $carbon->setTimestamp($date->getTimestamp());
        
        if ($timezone) {
            $carbon->setTimezone(new \DateTimeZone($timezone));
        }
        
        return $carbon;
    }
    
    /**
     * Create Carbon instance for now
     */
    public static function now($timezone = null)
    {
        return new self('now', $timezone);
    }
    
    /**
     * Create Carbon instance for today
     */
    public static function today($timezone = null)
    {
        return new self('today', $timezone);
    }
    
    /**
     * Create Carbon instance for tomorrow
     */
    public static function tomorrow($timezone = null)
    {
        return new self('tomorrow', $timezone);
    }
    
    /**
     * Create Carbon instance for yesterday
     */
    public static function yesterday($timezone = null)
    {
        return new self('yesterday', $timezone);
    }
    
    /**
     * Parse date string
     */
    public static function parse($time, $timezone = null)
    {
        return new self($time, $timezone);
    }
    
    /**
     * Create from timestamp
     */
    public static function createFromTimestamp($timestamp, $timezone = null)
    {
        $carbon = new self();
        $carbon->setTimestamp($timestamp);
        
        if ($timezone) {
            $carbon->setTimezone(new \DateTimeZone($timezone));
        }
        
        return $carbon;
    }
    
    /**
     * Create from date components
     */
    public static function create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $timezone = null)
    {
        $year = $year ?: date('Y');
        $month = $month ?: date('n');
        $day = $day ?: date('j');
        $hour = $hour ?: date('G');
        $minute = $minute ?: date('i');
        $second = $second ?: date('s');
        
        $dateString = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
        
        return new self($dateString, $timezone);
    }
    
    /**
     * Add time
     */
    public function addYears($years)
    {
        return $this->add(new \DateInterval("P{$years}Y"));
    }
    
    public function addMonths($months)
    {
        return $this->add(new \DateInterval("P{$months}M"));
    }
    
    public function addDays($days)
    {
        return $this->add(new \DateInterval("P{$days}D"));
    }
    
    public function addHours($hours)
    {
        return $this->add(new \DateInterval("PT{$hours}H"));
    }
    
    public function addMinutes($minutes)
    {
        return $this->add(new \DateInterval("PT{$minutes}M"));
    }
    
    public function addSeconds($seconds)
    {
        return $this->add(new \DateInterval("PT{$seconds}S"));
    }
    
    /**
     * Subtract time
     */
    public function subYears($years)
    {
        return $this->sub(new \DateInterval("P{$years}Y"));
    }
    
    public function subMonths($months)
    {
        return $this->sub(new \DateInterval("P{$months}M"));
    }
    
    public function subDays($days)
    {
        return $this->sub(new \DateInterval("P{$days}D"));
    }
    
    public function subHours($hours)
    {
        return $this->sub(new \DateInterval("PT{$hours}H"));
    }
    
    public function subMinutes($minutes)
    {
        return $this->sub(new \DateInterval("PT{$minutes}M"));
    }
    
    public function subSeconds($seconds)
    {
        return $this->sub(new \DateInterval("PT{$seconds}S"));
    }
    
    /**
     * Start/End of periods
     */
    public function startOfDay()
    {
        return $this->setTime(0, 0, 0);
    }
    
    public function endOfDay()
    {
        return $this->setTime(23, 59, 59);
    }
    
    public function startOfWeek()
    {
        $daysFromMonday = ($this->format('N') - 1);
        return $this->subDays($daysFromMonday)->startOfDay();
    }
    
    public function endOfWeek()
    {
        $daysToSunday = (7 - $this->format('N'));
        return $this->addDays($daysToSunday)->endOfDay();
    }
    
    public function startOfMonth()
    {
        return $this->setDate($this->format('Y'), $this->format('n'), 1)->startOfDay();
    }
    
    public function endOfMonth()
    {
        return $this->setDate($this->format('Y'), $this->format('n'), $this->format('t'))->endOfDay();
    }
    
    public function startOfYear()
    {
        return $this->setDate($this->format('Y'), 1, 1)->startOfDay();
    }
    
    public function endOfYear()
    {
        return $this->setDate($this->format('Y'), 12, 31)->endOfDay();
    }
    
    /**
     * Comparison methods
     */
    public function isToday()
    {
        return $this->format('Y-m-d') === date('Y-m-d');
    }
    
    public function isTomorrow()
    {
        return $this->format('Y-m-d') === date('Y-m-d', strtotime('tomorrow'));
    }
    
    public function isYesterday()
    {
        return $this->format('Y-m-d') === date('Y-m-d', strtotime('yesterday'));
    }
    
    public function isPast()
    {
        return $this->getTimestamp() < time();
    }
    
    public function isFuture()
    {
        return $this->getTimestamp() > time();
    }
    
    public function isWeekend()
    {
        return in_array($this->format('N'), [6, 7]); // Saturday, Sunday
    }
    
    public function isWeekday()
    {
        return !$this->isWeekend();
    }
    
    /**
     * Difference methods
     */
    public function diffInYears($date = null)
    {
        $date = $date ?: new self();
        return (int) $this->diff($date)->format('%y');
    }
    
    public function diffInMonths($date = null)
    {
        $date = $date ?: new self();
        $diff = $this->diff($date);
        return ($diff->y * 12) + $diff->m;
    }
    
    public function diffInDays($date = null)
    {
        $date = $date ?: new self();
        return (int) $this->diff($date)->format('%a');
    }
    
    public function diffInHours($date = null)
    {
        $date = $date ?: new self();
        return (int) ($this->getTimestamp() - $date->getTimestamp()) / 3600;
    }
    
    public function diffInMinutes($date = null)
    {
        $date = $date ?: new self();
        return (int) ($this->getTimestamp() - $date->getTimestamp()) / 60;
    }
    
    public function diffInSeconds($date = null)
    {
        $date = $date ?: new self();
        return $this->getTimestamp() - $date->getTimestamp();
    }
    
    /**
     * Human readable differences
     */
    public function diffForHumans($date = null)
    {
        $date = $date ?: new self();
        $diff = abs($this->getTimestamp() - $date->getTimestamp());
        
        $isPast = $this->getTimestamp() < $date->getTimestamp();
        $suffix = $isPast ? 'ago' : 'from now';
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ' . $suffix;
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $suffix;
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ' . $suffix;
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ' . $suffix;
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ' . $suffix;
        }
    }
    
    /**
     * Formatting methods
     */
    public function toDateString()
    {
        return $this->format('Y-m-d');
    }
    
    public function toTimeString()
    {
        return $this->format('H:i:s');
    }
    
    public function toDateTimeString()
    {
        return $this->format('Y-m-d H:i:s');
    }
    
    public function toISOString()
    {
        return $this->format(self::ISO8601);
    }
    
    public function toArray()
    {
        return [
            'year' => (int) $this->format('Y'),
            'month' => (int) $this->format('n'),
            'day' => (int) $this->format('j'),
            'hour' => (int) $this->format('G'),
            'minute' => (int) $this->format('i'),
            'second' => (int) $this->format('s'),
            'dayOfWeek' => (int) $this->format('w'),
            'dayOfYear' => (int) $this->format('z'),
            'weekOfYear' => (int) $this->format('W'),
            'daysInMonth' => (int) $this->format('t'),
            'timestamp' => $this->getTimestamp(),
            'formatted' => $this->toDateTimeString(),
            'timezone' => $this->getTimezone()->getName()
        ];
    }
    
    /**
     * Clone instance
     */
    public function copy()
    {
        return clone $this;
    }
    
    /**
     * String representation
     */
    public function __toString()
    {
        return $this->toDateTimeString();
    }
    
    /**
     * Static helper methods
     */
    public static function maxValue()
    {
        return self::createFromTimestamp(PHP_INT_MAX);
    }
    
    public static function minValue()
    {
        return self::createFromTimestamp(~PHP_INT_MAX);
    }
}
