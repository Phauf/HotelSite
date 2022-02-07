<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

use om\IcalParser;

$cal = new IcalParser();
$results = $cal->parseFile(
	'http://webbademo.tech/calendar.ics'
);

foreach ($cal->getSortedEvents() as $r) {
    var_dump($r['DTSTART']);

}
