<?php

namespace rapidPHP\library;

use rapid\library\rapid;

class Calendar
{


    /**
     * 一分钟的秒数
     */
    const TIME_MINUTE = 60;

    /**
     * 一小时的秒数
     */
    const TIME_HOURS = 3600;

    /**
     * 一天的秒数
     */
    const TIME_DAY = 3600 * 24;

    /**
     * 一周的秒数
     */
    const TIME_WEEK = 3600 * 24 * 7;

    /**
     * 一月的秒数
     */
    const TIME_MONTH = 3600 * 24 * 30;

    /**
     * 一年（平年）的秒数
     */
    const TIME_YEAR = 3600 * 24 * 365;

    /**
     * 一年（闰年年）的秒数
     */
    const TIME_YEAR_INTERCALARY = 3600 * 24 * 366;

    /**
     * 时间=》对应名称
     */
    const TIME_NAMES = [
        0 => '秒',
        self::TIME_MINUTE => '分钟',
        self::TIME_HOURS => '小时',
        self::TIME_DAY => '天',
        self::TIME_WEEK => '周',
        self::TIME_MONTH => '月',
        self::TIME_YEAR => '年',
    ];

    /**
     * @var Calendar
     */
    private static $instance;

    /**
     * @param string $zone
     * @return Calendar
     */
    public static function getInstance($zone = 'PRC')
    {
        return self::$instance instanceof self ? self::$instance : self::$instance = new self($zone);
    }

    /**
     * Calendar constructor.
     * @param $zone
     */
    public function __construct($zone)
    {
        self::setZone($zone);
    }

    /**
     * 设置时区
     * @param string $zone
     */
    public static function setZone(string $zone = 'PRC')
    {
        date_default_timezone_set($zone);
    }

    /**
     * 格式化日期，可以传入时间戳，或者时间，如果是时间则会转换成时间戳，在进行转换日期格式化
     * @param $datetime
     * @param string $format
     * @return false|string
     */
    public function format($datetime, string $format = 'Y-m-d H:i:s')
    {
        if (is_string($datetime)) $datetime = $this->dateToTime($datetime);

        if (is_int(strpos($format, 'AY'))) {
            if ($this->getDate($datetime, 'Y') == $this->getDate(time(), 'Y')) {
                $format = preg_replace("#AY(\(.*\)|)#i", '', $format);
            } else {
                $format = preg_replace("#AY(\((.*)\)|)#i", 'Y$2', $format);
            }
        }

        return date($format, $datetime);
    }


    /**
     * 获取时间戳时间或者本地时间
     * @param int|null $time
     * @param string $format
     * @return false|string
     */
    public function getDate(?int $time = null, string $format = 'Y-m-d H:i:s')
    {
        return date($format, $time ? $time : time());
    }

    /**
     * 日期到时间戳
     * @param $date
     * @param string $now
     * @return false|int
     */
    public function dateToTime($date, string $now = '')
    {
        if (!empty($now)) {
            return strtotime($date, $now);
        } else {
            return strtotime($date);
        }
    }


    /**
     * 获取时间是星期几
     * @param int|null $time
     * @param string[] $weeks
     * @return array|string|null
     */
    public function getDateWeekName(?int $time = null, $weeks = ['周天', '周一', '周二', '周三', '周四', '周五', '周六'])
    {
        if (is_null($time)) $time = time();

        return B()->getData($weeks, $this->getDate($time, 'w'));
    }


    /**
     * 格式化秒自动到 分或者时 或者 天 等....
     * @param int $second
     * @param string[] $unitString 可以自己定义
     * @return string
     */
    public function formatSecond($second = 0, $unitString = self::TIME_NAMES)
    {
        ksort($unitString);

        if ($second < self::TIME_MINUTE) return $second . $unitString[0];

        krsort($unitString);

        foreach ($unitString as $time => $unitName) {
            if ($time <= 0) continue;

            $result = floor($second / $time);

            if ($result >= 1) return $result . $unitName;
        }

        return null;
    }

    /**
     * 获取指定时间内，有多少闰年，不包含当前年
     * @param $fromTime
     * @param $toTime
     * @return false|float
     */
    public function getTimeIntercalary($fromTime, $toTime)
    {
        return (int)abs(($fromTime - $toTime) / 4) + (($toTime % 4 == 0 ? 0 : -1));
    }

    /**
     * 获取结束时间到开始时间相差多少天，当结束时间小于开始时间
     * @param string|int $formDatetime 如果是字符串则按照日期进行计算，会转换成时间戳
     * @param string|int $toDatetime 如果是字符串则按照日期进行计算，会转换成时间戳 默认为今天日期
     * @param string $format 默认是效验天数，如果要精确的天数的 时间分
     * @return float|int
     */
    public function getTimeToTimeDay(&$formDatetime, &$toDatetime = '', $format = 'Y-m-d')
    {
        if (is_string($formDatetime)) $formDatetime = $this->dateToTime($this->format($formDatetime, $format));

        if (empty($toDatetime)) $toDatetime = time();

        if (is_string($toDatetime)) $toDatetime = $this->dateToTime($this->format($toDatetime, $format));

        return ($toDatetime - $formDatetime) / 60 / 60 / 24;
    }

    /**
     * 是否闰年
     * @param $date
     * @return bool
     */
    public function isIntercalaryYear($date = null)
    {
        if (empty($date)) {
            $date = $this->getDate(time(), 'Y');
        } else {
            $date = $this->format($date, 'Y');
        }

        return ((int)$date) % 4 === 0;
    }


    /**
     * 效验时间
     * @param $default
     * @param $to
     * @param string $format
     * @return bool
     */
    public function hsTime($default, $format, $to = null)
    {
        if (empty($to)) {
            $to = $this->getDate(time(), $format);
        } else {
            $to = $this->format($to, $format);
        }

        $default = $this->format($default, $format);

        return $default == $to;
    }

    /**
     * 效验年
     * @param $default
     * @param $to
     * @return bool
     */
    public function hsYear($default, $to = null)
    {
        return $this->hsTime($default, $to, 'Y');
    }

    const HS_MODE_C = 'c';

    const HS_MODE_F = 'f';

    private function getHsMode($mode, $c, $f)
    {
        switch ($mode) {
            case self::HS_MODE_C:
                return $c;
                break;
            case self::HS_MODE_F:
                return $f;
                break;
        }
        return $f;
    }

    /**
     * 效验月
     * @param $default
     * @param $to
     * @param string $mode
     * @return bool
     */
    public function hsMonth($default, $to = null, $mode = self::HS_MODE_F)
    {
        return $this->hsTime($default, $to, $this->getHsMode($mode, 'm', 'Ym'));
    }

    /**
     * 效验周
     * @param $default
     * @param $to
     * @param string $mode
     * @return bool
     */
    public function hsWeek($default, $to = null, $mode = self::HS_MODE_F)
    {
        return $this->hsTime($default, $to, $this->getHsMode($mode, 'w', 'Ymdw'));
    }

    /**
     * 效验天
     * @param $default
     * @param $to
     * @param string $mode
     * @return bool
     */
    public function hsDay($default, $to = null, $mode = self::HS_MODE_F)
    {
        return $this->hsTime($default, $to, $this->getHsMode($mode, 'd', 'Ymd'));
    }


    /**
     * 效验时
     * @param $default
     * @param $to
     * @param string $mode
     * @return bool
     */
    public function hsHours($default, $to = null, $mode = self::HS_MODE_F)
    {
        return $this->hsTime($default, $to, $this->getHsMode($mode, 'H', 'YmdH'));
    }

    /**
     * 效验分
     * @param $default
     * @param $to
     * @param string $mode
     * @return bool
     */
    public function hsMinute($default, $to = null, $mode = self::HS_MODE_F)
    {
        return $this->hsTime($default, $to, $this->getHsMode($mode, 'i', 'YmdHi'));
    }

    /**
     * 效验秒
     * @param $default
     * @param $to
     * @param string $mode
     * @return bool
     */
    public function hsSecond($default, $to = null, $mode = self::HS_MODE_F)
    {
        return $this->hsTime($default, $to, $this->getHsMode($mode, 's', 'YmdHis'));
    }

    /**
     * 是否超出指定时间
     * @param $date
     * @param $limit
     * @return bool
     */
    public function isPassTime($date, $limit)
    {
        $date = $this->dateToTime($date);

        return $date >= (time() + $limit);
    }

    /**
     * 是否超过一年
     * @param $date
     * @return bool
     */
    public function isPassYear($date)
    {
        $limit = $this->isIntercalaryYear() ? Calendar::TIME_YEAR_INTERCALARY : Calendar::TIME_YEAR;

        return $this->isPassTime($date, $limit);
    }

    /**
     * 是否超过一月
     * @param $date
     * @return bool
     */
    public function isPassMonth($date)
    {
        return $this->isPassTime($date, Calendar::TIME_MONTH);
    }


    /**
     * 是否超过一周
     * @param $date
     * @return bool
     */
    public function isPassWeek($date)
    {
        return $this->isPassTime($date, Calendar::TIME_WEEK);
    }

    /**
     * 是否超过一天
     * @param $date
     * @return bool
     */
    public function isPassDay($date)
    {
        return $this->isPassTime($date, Calendar::TIME_DAY);
    }

    /**
     * 是否超过一小时
     * @param $date
     * @return bool
     */
    public function isPassHours($date)
    {
        return $this->isPassTime($date, Calendar::TIME_HOURS);
    }

    /**
     * 是否超过一分钟
     * @param $date
     * @return bool
     */
    public function isPassMinute($date)
    {
        return $this->isPassTime($date, Calendar::TIME_MINUTE);
    }

    /**
     * 是否超过多少秒
     * @param $date
     * @param $second
     * @return bool
     */
    public function isPassSecond($date, $second)
    {
        return $this->isPassTime($date, $second);
    }
}