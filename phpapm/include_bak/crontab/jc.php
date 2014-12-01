<?php
define('VHOST', true);
ini_set('date.timezone', 'PRC');
ini_set("display_errors", false);
foreach ($_SERVER['argv'] as $k1 => $v1) {
    $str_array = array();
    parse_str($v1, $str_array);
    if (count($str_array) > 1) {
        $key = key($str_array);
        $current = current($str_array);
        array_shift($str_array);
        $str_array[$key] = $current . "&" . http_build_query($str_array);
        $_GET = $str_array + $_GET;
        unset($str_array);
    } else
        $_GET = $str_array + $_GET;
}

/**
 * @desc WHAT?
 * @author ����̩ mailto:xialintai@qiyi.com
 * @since  2013-11-06 14:54:48
 * @throws ע��:��DB�쳣����
 */
function _line($str = NULL)
{
    $function = debug_backtrace();
    echo "-------------------------------------------------------------------------\n{$str}";
}

function _line2($str)
{
    $function = debug_backtrace();
    echo $function[1]['function'] . "\t\t" . $str . "\n";
}

function _line3($str, $color = 'green')
{
    $function = debug_backtrace();
    if ($color == 'green')
        $color_1 = '32';
    else if ($color == 'blue')
        $color_1 = '34';
    else if ($color == 'w')
        $color_1 = '33';
    else
        $color_1 = '31';
    echo("\033[1;40;{$color_1}m" . $function[1]['function'] . "\t\t" . $str . "\n" . "\033[0m");
}


$m = new m;
$_GET['act'] = $_GET['act'] ? $_GET['act'] : "index";
$m->$_GET['act'] ();
class m
{
    /**
     * @desc WHAT?
     * @author ����̩ mailto:xialintai@qiyi.com
     * @since  2013-11-04 16:35:03
     * @throws ע��:��DB�쳣����
     */
    function  php_ini()
    {
        _line();
        _line3("��⵱ǰ����������php.ini�����Ƿ�����.");
        foreach (glob("/usr/local/php*", GLOB_ONLYDIR) as $dir) {
            $d2 = $dir . "/etc/php.ini";
            _line3($d2);
            $d = file_get_contents($d2);
            preg_match("/eaccelerator\.cache_dir=\"(.*)\"/i", $d, $da);
            if (!is_dir($da[1]))
                _line3("\t[���ô���]{$da[1]}", 'red'); else _line2("\t[Y]{$da[1]}");
            foreach (array('error_reporting', 'error_log', 'log_errors', 'auto_append_file') as $vv) {
                preg_match("/[^;]{$vv}.*/i", $d, $da);
                $da[0] = trim($da[0]);
                if ($da[0])
                    _line2("\t [{$vv}]->{$da[0]}");
                else
                    _line3("\t [{$vv}]->{$da[0]}", 'red');
            }
            preg_match("/.*couchbase.so/i", $d, $da);
            if ($da[0]) {
                _line2("\t [couchbase.so]->{$da[0]}");
                preg_match("/couchbase.compressor.*/i", $d, $da);
                _line2("\t [couchbase.compressor]->{$da[0]}");
            }
        }
        _line("\n\n");
    }

    /**
     * @desc WHAT?
     * @author ����̩ mailto:xialintai@qiyi.com
     * @since  2013-11-04 16:41:11
     * @throws ע��:��DB�쳣����
     */
    function db_ini()
    {
        foreach (array("/home/oracle/product/10.2.0/network/admin/tnsnames.ora", "/opt/oracle/product/9.2.0/network/admin/tnsnames.ora") as $k) {
            if (is_file($k))
                _line3($k);
            else continue;
            foreach (file($k) as $r) {
                if (preg_match("/PPS(.)*=/i", $r, $a))
                    $name = str_replace(array("=", " ", "\n", "\t"), "", $a[0]);
                if (preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)/i", $r, $a))
                    $i = str_replace(array("=", " ", "\n", "\t"), "", $a[0]);
                if ($name && $i) {
                    //pps_ugc pps_core ppstream
                    if (is_resource(ocinlogon("ppstream", "ppstream", $name)))
                        _line2("Oracle ���ݿ�:{$name}\tIP:{$i}\tppstream/ppstream\tOK");
                    else if (is_resource(ocinlogon("pps_ugc", "pps_ugc", $name)))
                        _line2("Oracle ���ݿ�:{$name}\tIP:{$i}\tpps_ugc/pps_ugc\tOK");
                    elseif (is_resource(ocinlogon("pps_core", "pps_core", $name)))
                        _line2("Oracle ���ݿ�:{$name}\tIP:{$i}\tpps_core/pps_core\tOK");
                    elseif (is_resource(ocinlogon("pps_coremgr", "pps_coremgr", $name)))
                        _line2("Oracle ���ݿ�:{$name}\tIP:{$i}\tpps_coremgr/pps_coremgr\tOK");
                    else _line3("Oracle ���ݿ�:{$name}\tIP:{$i}\t[����]", 'red');
                    $name = "";
                    $i = "";
                }
            }
        }
        _line("\n\n");
    }

    /**
     * @desc �г���������
     * @author ����ޱ mailto:nyw@ppstream.com
     * @since  2013-11-04 18:23:03
     * @throws ע��:��DB�쳣����
     */

    function host_ini()
    {
        _line();
        $out = NULL;
        _line3("������80�˿��������еķ���: [netstat -tlnp | grep :80]");
        exec('netstat -tlnp | grep :80', $out);
        foreach ($out as $vv)
            _line3($vv, 'red');
        if (strrpos($out['0'], 'nginx')) {
            _line3("nginx ��������  [ps aux | grep ngi | grep conf] ");
            exec('ps aux | grep ngi | grep conf', $c);
            exec('grep server_name /usr/local/nginx/conf/nginx.conf', $ngi);
            _line3('������������: grep server_name /usr/local/nginx/conf/nginx.conf');
            foreach ($ngi as $vv)
                _line2($vv);
        } else {
            _line3("apache��������  [/usr/local/apache/bin/httpd -S]");
            system('/usr/local/apache/bin/httpd -S');
        }

        exec('netstat -na|grep ESTABLISHED > /dev/shm/can_delete_tcp.txt');
        _line3("�ܶ˿�: cat /dev/shm/can_delete_tcp.txt |awk '($4!~/:9000/){print}'");
        $out = null;
        exec("cat /dev/shm/can_delete_tcp.txt |awk '($4!~/:9000/){print}'  | wc -l", $out);
        if ($out[0] > 200) {
            _line3('��ERROR���ܶ˿�:' . $out[0], 'red');
        } else {
            _line3('��OK���ܶ˿�:' . $out[0]);
        }
        _line3("80�˿�: cat /dev/shm/can_delete_tcp.txt | awk '($4~/:80/){print}'");
        $out = null;
        exec("cat /dev/shm/can_delete_tcp.txt | awk '($4~/:80/){print}' | wc -l", $out);
        if ($out[0] > 100) {
            _line3('��ERROR��80�˿�:' . $out[0], 'red');
        } else {
            _line3('��OK��80�˿�:' . $out[0]);
        }

        _line("\n\n");
    }

    /**
     * @desc ������״̬
     * @author ����ޱ mailto:nyw@ppstream.com
     * @since  2013-11-05 11:19:39
     * @throws ע��:��DB�쳣����
     */
    function web_ini()
    {
        _line();
        _line3("ʣ���ڴ� [free -m]");
        $out = NULL;
        exec('free -m', $out);
        $arr = explode('      ', $out[2]);
        $free = sprintf("%.3f", $arr['2'] / 1024);
        if ($free < 4) {
            _line3('��ERROR��������ʣ���ڴ�:' . $free . "G", 'red');
        } else {
            _line2('��OK��������ʣ���ڴ�:' . $free . "G");
        }
        _line3("ϵͳ���� [uptime]");
        system('uptime');
        _line("\n\n");
    }

    /**
     * @desc WHAT?
     * @author ����ޱ mailto:nyw@ppstream.com
     * @since  2013-11-05 17:09:52
     * @throws ע��:��DB�쳣����
     */

    function config_ini()
    {
        _line3("��ʱ���� [crontab -l]");
        //��ʱ��������
        exec("crontab -l", $out);
        $last_contab = NULL;
        foreach ($out as $vv) {
            if (!trim($vv)) continue;
            $vv_arr = explode(" ", $vv);
            unset($vv_arr[0], $vv_arr[1], $vv_arr[2], $vv_arr[3], $vv_arr[4]);
            $vv_arr_5 = explode("/", join("", $vv_arr));
            if ($last_contab != "@[{$vv_arr_5[3]}]") {
                echo("\n");
                $last_contab = "@[{$vv_arr_5[3]}]";
                _line3($last_contab);
            }
            if (strpos($vv, 'monitorphp') !== false) {
                if (substr(trim($vv), -1, 1) == 1)
                    _line3($vv." [Դվ]", 'blue');
                else
                    _line3($vv." [��վ]", 'w');
            } else
                _line2($vv);
        }
        _line("\n\n");
        _line3("������Ϣ [cat /proc/sys/kernel/msgmnb]");

        //���в鿴
        $out = NULL;
        exec("cat /proc/sys/kernel/msgmnb", $out);
        $ipcs = $out[0] / 1024 / 1024;
        if ($ipcs < 100) {
            _line3('��ERROR�� ���д�С:' . $ipcs . "M", 'red');
        } else {
            _line2('��OK�����д�С:' . $ipcs . "M");
        }
        $out = NULL;
        exec("ipcs -q", $out);
        foreach ($out as $k => $vv)
            if ($k > 2) {
                $arr = explode('  ', $vv);
                if (($arr['8'] / 1024 / 1024) > 80) {
                    _line3($vv, 'red');
                } else {
                    _line2($vv);
                }
            } else {
                _line2($vv);
            }
        _line("\n\n");
    }

    /**
     * @desc web��־����
     * @author ����ޱ mailto:nyw@ppstream.com
     * @since  2013-11-06 10:48:03
     * @throws ע��:��DB�쳣����
     */

    function log_ini()
    {
        //_line3("�鿴��������php������־.[tail -n 20 /home/webid/logs/php_error.log | grep -v jc.php ", 'red');
        exec('tail -n 20 /home/webid/logs/php_error.log | grep -v jc.php ', $out);
        foreach ($out as $vv) {
            _line2($vv);
        }
        //���������������Сʱ����״̬��
        //_line3("�鿴����������Сʱ��־.[head /home/webid/logs/php_error.log]", 'red');

        _line("\n\n");
    }

}