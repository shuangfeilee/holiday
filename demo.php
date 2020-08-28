<?php
require_once __DIR__ . '/vendor/autoload.php';

use mfunc\Holiday;

try {
	$data = 'https://shuangfeilee.gitee.io/holiday/data.json';
	// $holiday = new Holiday($data);
	$holiday = new Holiday();
	$holidays = $holiday->getHolidaysByYear();
	$isholiday = $holiday->isholiday();
	echo '<pre>';
	print_r($holidays);
	print_r($isholiday);
} catch(\Exception $e) {
	echo $e->getMessage();
}