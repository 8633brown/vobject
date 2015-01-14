<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject;

const CRLF = "\r\n";

class XmlTest extends \PHPUnit_Framework_TestCase {

    function testRFC6321Example1() {

        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <calscale>
                 <text>GREGORIAN</text>
               </calscale>
               <prodid>
                <text>-//Example Inc.//Example Calendar//EN</text>
               </prodid>
               <version>
                 <text>2.0</text>
               </version>
              </properties>
              <components>
               <vevent>
                <properties>
                 <dtstamp>
                   <date-time>2008-02-05T19:12:24Z</date-time>
                 </dtstamp>
                 <dtstart>
                   <date>2008-10-06</date>
                 </dtstart>
                 <summary>
                  <text>Planning meeting</text>
                 </summary>
                 <uid>
                  <text>4088E990AD89CB3DBB484909</text>
                 </uid>
                </properties>
               </vevent>
              </components>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            // VERSION comes first because this is required by vCard 4.0.
            'VERSION:2.0' . CRLF .
            'CALSCALE:GREGORIAN' . CRLF .
            'PRODID:-//Example Inc.//Example Calendar//EN' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'DTSTAMP:20080205T191224Z' . CRLF .
            'DTSTART;VALUE=DATE:20081006' . CRLF .
            'SUMMARY:Planning meeting' . CRLF .
            'UID:4088E990AD89CB3DBB484909' . CRLF .
            'END:VEVENT' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /*
    function testRFC6321Example2() {

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
  <vcalendar>
    <properties>
      <prodid>
        <text>-//Example Inc.//Example Client//EN</text>
      </prodid>
      <version>
        <text>2.0</text>
      </version>
    </properties>
    <components>
      <vtimezone>
        <properties>
          <last-modified>
            <date-time>2004-01-10T03:28:45Z</date-time>
          </last-modified>
          <tzid><text>US/Eastern</text></tzid>
        </properties>
        <components>
          <daylight>
            <properties>
              <dtstart>
                <date-time>2000-04-04T02:00:00</date-time>
              </dtstart>
              <rrule>
                <recur>
                  <freq>YEARLY</freq>
                  <byday>1SU</byday>
                  <bymonth>4</bymonth>
                </recur>
              </rrule>
              <tzname>
                <text>EDT</text>
              </tzname>
              <tzoffsetfrom>
                <utc-offset>-05:00</utc-offset>
              </tzoffsetfrom>
              <tzoffsetto>
                <utc-offset>-04:00</utc-offset>
              </tzoffsetto>
            </properties>
          </daylight>
          <standard>
            <properties>
              <dtstart>
                <date-time>2000-10-26T02:00:00</date-time>
              </dtstart>
              <rrule>
                <recur>
                  <freq>YEARLY</freq>
                  <byday>-1SU</byday>
                  <bymonth>10</bymonth>
                </recur>
              </rrule>
              <tzname>
                <text>EST</text>
              </tzname>
              <tzoffsetfrom>
                <utc-offset>-04:00</utc-offset>
              </tzoffsetfrom>
              <tzoffsetto>
                <utc-offset>-05:00</utc-offset>
              </tzoffsetto>
            </properties>
          </standard>
        </components>
      </vtimezone>
      <vevent>
        <properties>
          <dtstamp>
            <date-time>2006-02-06T00:11:21Z</date-time>
          </dtstamp>
          <dtstart>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <date-time>2006-01-02T12:00:00</date-time>
          </dtstart>
          <duration>
            <duration>PT1H</duration>
          </duration>
          <rrule>
            <recur>
              <freq>DAILY</freq>
              <count>5</count>
            </recur>
          </rrule>
          <rdate>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <period>
              <start>2006-01-02T15:00:00</start>
              <duration>PT2H</duration>
            </period>
          </rdate>
          <summary>
            <text>Event #2</text>
          </summary>
          <description>
            <text>We are having a meeting all this week at 12
pm for one hour, with an additional meeting on the first day
2 hours long.&#x0a;Please bring your own lunch for the 12 pm
meetings.</text>
          </description>
          <uid>
            <text>00959BC664CA650E933C892C@example.com</text>
          </uid>
        </properties>
      </vevent>
      <vevent>
        <properties>
          <dtstamp>
            <date-time>2006-02-06T00:11:21Z</date-time>
          </dtstamp>
          <dtstart>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <date-time>2006-01-04T14:00:00</date-time>
          </dtstart>
          <duration>
            <duration>PT1H</duration>
          </duration>
          <recurrence-id>
            <parameters>
              <tzid><text>US/Eastern</text></tzid>
            </parameters>
            <date-time>2006-01-04T12:00:00</date-time>
          </recurrence-id>
          <summary>
            <text>Event #2 bis</text>
          </summary>
          <uid>
            <text>00959BC664CA650E933C892C@example.com</text>
          </uid>
        </properties>
      </vevent>
    </components>
  </vcalendar>
</icalendar>
XML;

        $component = VObject\Reader::readXML($xml);
        $this->assertEquals(
            'BEGIN:VCALENDAR' . CRLF .
            'VERSION:2.0' . CRLF .
            'PRODID:-//Example Inc.//Example Client//EN' . CRLF .
            'BEGIN:VTIMEZONE' . CRLF .
            'LAST-MODIFIED:20040110T032845Z' . CRLF .
            'TZID:US/Eastern' . CRLF .
            'BEGIN:DAYLIGHT' . CRLF .
            'DTSTART:20000404T020000' . CRLF .
            'RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4' . CRLF .
            'TZNAME:EDT' . CRLF .
            'TZOFFSETFROM:-0500' . CRLF .
            'TZOFFSETTO:-0400' . CRLF .
            'END:DAYLIGHT' . CRLF .
            'BEGIN:STANDARD' . CRLF .
            'DTSTART:20001026T020000' . CRLF .
            'RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10' . CRLF .
            'TZNAME:EST' . CRLF .
            'TZOFFSETFROM:-0400' . CRLF .
            'TZOFFSETTO:-0500' . CRLF .
            'END:STANDARD' . CRLF .
            'END:VTIMEZONE' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'DTSTAMP:20060206T001121Z' . CRLF .
            'DTSTART;TZID=US/Eastern:20060102T120000' . CRLF .
            'DURATION:PT1H' . CRLF .
            'RRULE:FREQ=DAILY;COUNT=5' . CRLF .
            'RDATE;TZID=US/Eastern;VALUE=PERIOD:20060102T150000/PT2H' . CRLF .
            'SUMMARY:Event #2' . CRLF .
            'DESCRIPTION:We are having a meeting all this week at 12\npm for one hour\,' . CRLF .
            ' with an additional meeting on the first day\n2 hours long.\nPlease bring y' . CRLF .
            ' our own lunch for the 12 pm\nmeetings.' . CRLF .
            'UID:00959BC664CA650E933C892C@example.com' . CRLF .
            'END:VEVENT' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'DTSTAMP:20060206T001121Z' . CRLF .
            'DTSTART;TZID=US/Eastern:20060104T140000' . CRLF .
            'DURATION:PT1H' . CRLF .
            'RECURRENCE-ID;TZID=US/Eastern:20060104T120000' . CRLF .
            'SUMMARY:Event #2 bis' . CRLF .
            'UID:00959BC664CA650E933C892C@example.com' . CRLF .
            'END:VEVENT' . CRLF .
            'END:VCALENDAR' . CRLF,
            VObject\Writer::write($component)
        );

    }
    */

    /**
     * iCalendar Stream.
     */
    function testRFC6321Section3_2() {

        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'END:VCALENDAR' . CRLF
        );
    }

    /**
     * All components exist.
     */
    function testRFC6321Section3_3() {

        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <components>
               <vevent></vevent>
               <vtodo></vtodo>
               <vjournal></vjournal>
               <vfreebusy></vfreebusy>
               <vtimezone></vtimezone>
               <standard></standard>
               <daylight></daylight>
               <valarm></valarm>
              </components>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'BEGIN:VTIMEZONE' . CRLF .
            'END:VTIMEZONE' . CRLF .
            'BEGIN:VEVENT' . CRLF .
            'END:VEVENT' . CRLF .
            'BEGIN:VTODO' . CRLF .
            'END:VTODO' . CRLF .
            'BEGIN:VJOURNAL' . CRLF .
            'END:VJOURNAL' . CRLF .
            'BEGIN:VFREEBUSY' . CRLF .
            'END:VFREEBUSY' . CRLF .
            'BEGIN:STANDARD' . CRLF .
            'END:STANDARD' . CRLF .
            'BEGIN:DAYLIGHT' . CRLF .
            'END:DAYLIGHT' . CRLF .
            'BEGIN:VALARM' . CRLF .
            'END:VALARM' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Properties, Special Cases, GEO.
     */
    function testRFC6321Section3_4_1_2() {

        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <geo>
                <latitude>37.386013</latitude>
                <longitude>-122.082932</longitude>
               </geo>
              </properties>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'GEO:37.386013;-122.082932' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    /**
     * Properties, Special Cases, REQUEST-STATUS.
     */
    function testRFC6321Section3_4_1_3() {

        // Example 1 of RFC5545, Section 3.8.8.3.
        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <request-status>
                <code>2.0</code>
                <description>Success</description>
               </request-status>
              </properties>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:2.0;Success' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 2 of RFC5545, Section 3.8.8.3.
        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <request-status>
                <code>3.1</code>
                <description>Invalid property value</description>
                <data>DTSTART:96-Apr-01</data>
               </request-status>
              </properties>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:3.1;Invalid property value;DTSTART:96-Apr-01' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 3 of RFC5545, Section 3.8.8.3.
        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <request-status>
                <code>2.8</code>
                <description>Success, repeating event ignored. Scheduled as a single event.</description>
                <data>RRULE:FREQ=WEEKLY;INTERVAL=2</data>
               </request-status>
              </properties>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:2.8;Success\, repeating event ignored. Scheduled as a single' .  CRLF .
            '  event.;RRULE:FREQ=WEEKLY\;INTERVAL=2' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 4 of RFC5545, Section 3.8.8.3.
        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <request-status>
                <code>4.1</code>
                <description>Event conflict.  Date-time is busy.</description>
               </request-status>
              </properties>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:4.1;Event conflict.  Date-time is busy.' . CRLF .
            'END:VCALENDAR' . CRLF
        );

        // Example 5 of RFC5545, Section 3.8.8.3.
        $this->assertXCalEqualsToICal(
<<<XML
<?xml version="1.0" encoding="utf-8"?>
            <icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
             <vcalendar>
              <properties>
               <request-status>
                <code>3.7</code>
                <description>Invalid calendar user</description>
                <data>ATTENDEE:mailto:jsmith@example.com</data>
               </request-status>
              </properties>
             </vcalendar>
            </icalendar>
XML
,
            'BEGIN:VCALENDAR' . CRLF .
            'REQUEST-STATUS:3.7;Invalid calendar user;ATTENDEE:mailto:jsmith@example.com' . CRLF .
            'END:VCALENDAR' . CRLF
        );

    }

    protected function assertXCalEqualsToICal($xcal, $ical) {

        $component = VObject\Reader::readXML($xcal);
        $this->assertEquals($ical, VObject\Writer::write($component));
    }
}
