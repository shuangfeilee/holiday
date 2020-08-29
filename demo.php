<?php
require_once __DIR__ . '/vendor/autoload.php';

use mfunc\Holiday;

try {
	// 使用远程数据文件
	// $data = 'https://shuangfeilee.gitee.io/holiday/data.json';
	// $holiday = new Holiday($data);
	
	$holiday = new Holiday();
	
	// 根据年份获取当年节假日 默认当前年
	$holidays = $holiday->getHolidaysByYear();
	
	// 检测当前日期是否为节假日 默认当天 返回日期信息
	$result  = $holiday->isholiday();
	
	echo '<pre>';
	print_r($holidays);
	print_r($result);
} catch(\Exception $e) {
	echo $e->getMessage();
}
