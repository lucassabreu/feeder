<?php

namespace Core\Util;

use DateTime as SplDateTime;
use Zend\Stdlib\JsonSerializable;

/**
 * Extension of <code>\DateTime</code> with util methods
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 * 
 * @property int $year Year of date-time
 * @property int $month Month of date-time
 * @property int $day Day of date-time
 * @property int $hours Hours of date-time
 * @property int $minutes Minutes of date-time
 * @property int $seconds Seconds of date-time
 */
class DateTime extends SplDateTime implements JsonSerializable {

    public static function convert(SplDateTime $date) {
        $nDate = new DateTime();
        //$nDate->setTimestamp($date->getTimestamp());
        //if ($date->getTimezone())
        //    $nDate->setTimezone($date->getTimezone());

        return $nDate;
    }

    public function getYear() {
        return intval($this->format('Y'));
    }

    public function getMonth() {
        return intval($this->format('m'));
    }

    public function getDay() {
        return intval($this->format('d'));
    }

    public function setYear($year) {
        return $this->setDate($year, $this->getMonth(), $this->getDay());
    }

    public function setMonth($month) {
        return $this->setDate($this->getYear(), $month, $this->getDay());
    }

    public function setDay($day) {
        return $this->setDate($this->getYear(), $this->getMonth(), $day);
    }

    public function getHours() {
        return intval($this->format('H'));
    }

    public function getMinutes() {
        return intval($this->format('i'));
    }

    public function getSeconds() {
        return intval($this->format('s'));
    }

    public function setHours($hours) {
        $this->setTime($hours, $this->getMinutes(), $this->getSeconds());
    }

    public function setMinutes($minutes) {
        $this->setTime($this->getHours(), $minutes, $this->getSeconds());
    }

    public function setSeconds($seconds) {
        $this->setTime($this->getHours(), $this->getMinutes(), $seconds);
    }

    public function __get($name) {
        $propertyName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        if (method_exists($this, "get$propertyName")) {
            $propertyName = "get$propertyName";
            return $this->{$propertyName}();
        } else {
            if (method_exists($this, "is$propertyName")) {
                $propertyName = "is$propertyName";
                return $this->{$propertyName}();
            } else {
                return null;
            }
        }
    }

    public function __set($name, $value) {
        $propertyName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        if (method_exists($this, "set$propertyName")) {
            $propertyName = "set$propertyName";
            return $this->{$propertyName}($value);
        } else {
            return null;
        }
    }

    public function __toString() {
        return $this->format('Y-m-d');
    }

    public function toJson() {
        return $this->format(self::ISO8601);
    }

    public function jsonSerialize() {
        return $this->toJson();
    }

}
