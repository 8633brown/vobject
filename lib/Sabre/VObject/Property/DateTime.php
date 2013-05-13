<?php

namespace Sabre\VObject\Property;

use
    Sabre\VObject\Property,
    Sabre\VObject\Parser\MimeDir,
    Sabre\VObject\DateTimeParser,
    Sabre\VObject\TimeZoneUtil;

/**
 * DateTime property
 *
 * This object represents DATE-TIME values, as defined here:
 *
 * http://tools.ietf.org/html/rfc5545#section-3.3.5
 *
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class DateTime extends Property {

    /**
     * In case this is a multi-value property. This string will be used as a
     * delimiter.
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Sets a raw value coming from a mimedir (iCalendar/vCard) file.
     *
     * This has been 'unfolded', so only 1 line will be passed. Unescaping is
     * not yet done, but parameters are not included.
     *
     * @param string $val
     * @return void
     */
    public function setRawMimeDirValue($val) {

        $this->setValue(explode($this->delimiter, $val));

    }

    /**
     * Returns a raw mime-dir representation of the value.
     *
     * @return string
     */
    public function getRawMimeDirValue() {

        return implode($this->delimiter, $this->getParts());

    }

    /**
     * Returns a date-time value.
     *
     * Note that if this property contained more than 1 date-time, only the
     * first will be returned. To get an array with multiple values, call
     * getDateTimes.
     *
     * @return \DateTime
     */
    public function getDateTime() {

        $dt = $this->getDateTimes();
        return $dt[0];

    }

    /**
     * Returns multiple date-time values.
     *
     * @return \DateTime[]
     */
    public function getDateTimes() {

        // Finding the timezone.
        $tz = $this['TZID'];

        if ($tz) {
            $tz = TimeZoneUtil::getTimeZone((string)$tz, $this->root);
        }

        $dts = array();
        foreach($this->getParts() as $part) {
            $dts[] = DateTimeParser::parseDateTime($part, $tz);
        }
        return $dts;

    }

    /**
     * Sets the property as a DateTime object.
     *
     * @param \DateTime $dt
     * @return void
     */
    public function setDateTime(\DateTime $dt) {

        $this->setDateTimes(array($dt));

    }

    /**
     * Sets the property as multiple date-time objects.
     *
     * The first value will be used as a reference for the timezones, and all
     * the otehr values will be adjusted for that timezone
     *
     * @param \DateTime[] $dt
     * @return void
     */
    public function setDateTimes(array $dt) {

        $values = array();
        $tz = null;

        foreach($dt as $d) {

            if (is_null($tz)) {
                $tz = $d->getTimeZone();
                if ($tz->getName() !== 'UTC') {
                    $this->offsetSet('TZID', $tz->getName());
                    $this->offsetSet('VALUE','DATE-TIME');
                } else {
                    $this->offsetUnset('TZID');
                }
            } else {
                $d->setTimeZone($tz);
            }

            if ($tz->getName()==='UTC') {
                $values[] = $d->format('Ymd\\THis\\Z');
            } else {
                $values[] = $d->format('Ymd\\THis');
            }

        }

        $this->setParts($values);

    }
}
