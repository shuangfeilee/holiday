<?php
namespace mfunc;

/**
 * 节假日
 */
class Holiday
{
    /**
     * 配置文件路径
     * @var string
     */
    private $config;

    /**
     * 节假日名称
     * @var array
     */
    private $names = [
        'yd'    =>  '元旦',
        'cj'    =>  '春节',
        'qm'    =>  '清明',
        'ld'    =>  '劳动',
        'dw'    =>  '端午',
        'gq'    =>  '国庆',
        'zq'    =>  '中秋'
    ];

    /**
     * 析构方法
     * @param string|array $config 远程数据使用json文件,格式根据config数组格式转换
     */
    public function __construct ($config = '')
    {
        $this->config = $config ? : dirname(__DIR__ . '/config.php') . '/config.php';

        if (!is_array($this->config)) {
            // 使用在线数据
            if (preg_match('|^https?://|i', $this->config)) {
                $this->config = json_decode(file_get_contents($this->config), true);
            } 
            // 使用本地数据
            else {
                $this->config = include_once($this->config);
            }
        }
    }

    /**
     * 检测日期格式
     * @param $date string
     * @throws \Exception
     */
    private static function _chkDate ($date)
    {
        $len = mb_strlen($date);
        $err = '日期格式有误。格式为yyyy-mm-dd或yyyymmdd或mmdd或mm-dd';

        // yyyy-mm-dd格式数据
        if ($len == 10) {
            $arr = explode('-', $date);
            // 检测yyyy-mm-dd格式
            if (count($arr) != 3) throw new \Exception($err);

            $year   = $arr[0];
            $month  = $arr[1];
            $day    = $arr[2];
        }
        // yyyymmdd格式数据
        else if ($len == 8) {
            $year   = substr($date, 0, 4);
            $month  = substr($date, 4, 2);
            $day    = substr($date, -2);
        }
        // mmdd格式数据 省略年
        else if ($len == 4) {
            $year   = date('Y');
            $month  = substr($date, 0, 2);
            $day    = substr($date, -2);
            $date = $year . $date;
        }
        // mm-dd格式数据 省略年
        else if ($len == 5) {
            $year   = date('Y');
            $arr = explode('-', $date);
            // 检测mm-dd格式
            if (count($arr) != 2) throw new \Exception($err);

            $month  = $arr[0];
            $day    = $arr[1];
            $date = $year . '-' . $date;
        }
        // 不符合格式数据
        else {
            throw new \Exception($err);
        }
        
        // 检测年月日
        if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) throw new \Exception('年份，月份，日期必须为数字');
        // 检测月范围
        if (intval($month) < 1 || intval($month) > 12) throw new \Exception('月份错误，范围为1-12');
        // 检测日范围
        if (intval($day) < 1 || intval($day) > 31) throw new \Exception('日期错误，范围为1-31');
    }

    /**
     * 检测年格式
     * @param $year string
     * @throws \Exception
     */
    private static function _chkYear ($year)
    {
        $year = intval($year);
        $minYear = 1999;
        $maxYear   = 2100;

        if ($year < $minYear || $year > $maxYear) throw new \Exception("年份必须在{$minYear} - {$maxYear}之间");
    }

    /**
     * 获取日期信息
     * @param $date string
     * @throws \Exception
     * @return array
     * [
     *      'name'  => '', // 日期类型 工作日，周末，节假日名称，节假日调休
     *      'work'  => 0,  // 是否工作日 0否 1是
     *      'type'  => '', // 节假日类型 workday工作日, weekday周末, holiday节假日, txday节假日调休
     *      'dates' => '', // 节假日，工作日，调休日日期
     *      'week'  => '', // 星期
     * ]
     */
    public function isHoliday ($date = '')
    {
        $date = $date ? : date('Y-m-d');
        self::_chkDate($date);

        $year = substr($date, 0, 4);

        // 获取数据
        $holidays = $this->getHolidaysByYear($year);

        if (empty($holidays)) throw new \Exception("{$year}年数据不存在");

        $result = [];
        $weeks = ['日', '一', '二', '三', '四', '五', '六'];
        $week = date('w', strtotime($date));

        $name = '';
        foreach ($holidays as $k => $v) {
            $holidays = explode(',', $v['holiday']);
            $workdays = explode(',', $v['workday']);

            if (in_array($date, $holidays)) {
                $name .= $v['name'] . ',';
                $result['dates'] = $v['holiday'];
                $result['work'] = 0;
                $result['type'] = 'holiday';
            } elseif (in_array($date, $workdays)) {
                $name .= $v['name'] . ',';
                $result['dates'] = $v['workday'];
                $result['work'] = 1;
                $result['type'] = 'txday';
            }
        }

        if (!empty($result)) {
            $name = rtrim($name, ',');
            $result['work'] == 1 && $name .= '调休';
            $result['name'] = $name;
        } else {
            if ($week == 0 || $week == 6) {
                $result['name'] = '周末';
                $result['work'] = 0;
                $result['type'] = 'weekday';
                $result['dates'] = $date;
            } else {
                $result['name'] = '工作日';
                $result['work'] = 1;
                $result['type'] = 'workday';
                $result['dates'] = $date;
            }
        }
        !empty($result) && $result['week'] = '星期' . $weeks[$week];
        return $result;
    }

    /**
     * 获取年度所有节日信息
     * @param int $year
     * @throws \Exception
     * @return array
     */
    public function getHolidaysByYear ($year = '')
    {
        $year = $year ? : date('Y');
        self::_chkYear($year);

        $holidays = $this->config;
        if (empty($holidays[$year])) return;

        foreach ($holidays[$year] as $k => $v) {
            $holidays[$year][$k]['name'] = $this->names[$k];
        }
        return $holidays[$year];
    }
}