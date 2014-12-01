#!/bin/sh
source /home/webid/.bash_profile
if [ -f /usr/local/php5_nginx/bin/php ] ; then
    export PATH=$PATH:/home/httpd:/usr/local/php5_nginx/bin
else
    export PATH=$PATH:/home/httpd:/usr/local/php/bin
fi
export ORACLE_BASE=/home/oracle
export ORACLE_SID=pps	
export ORACLE_HOME=/home/oracle/product/10.2.0
export PATH=$PATH:$ORACLE_HOME/bin
export NLS_LANG="Simplified Chinese_china".ZHS16GBK

if [ ! -d "/home/webid/logs/" ]; then
    mkdir "/home/webid/logs/"
fi

log_date=/home/webid/logs/`date +%Y_%m_%d`

#wgetһ����ַ,��֤�ǵ�һ��������.����ģʽ��
function callact2()
{
        exec_pwd=`pwd`
        if ps aux |  grep -v 'grep ' | grep -q "$1" ; then
                echo `date +"%Y-%m-%d %H:%M:%S"` $exec_pwd [x]PHP2_Fail:$1 >> ${log_date}_wget.log
        else
                echo  `date +"%Y-%m-%d %H:%M:%S"` $exec_pwd call@$1>> ${log_date}_wget.log    2>&1 &
                $1 >> /dev/null
        fi
}

cd /home/httpd/$1

#·��������ͬ��Ŀͬ�������Ľű�
project_pwd=`pwd`
#�ӻ����������ϼ���
if [ 1 -eq $3 ] ; then

    callact2 "php project.php act=P1H_ErrorCount  pwd=$project_pwd"

    #����ͳ�ƺ�̨������
    hour_stata=`date +%H`
    if [ 6 -eq $hour_stata ] ; then
        callact2 "php project.php act=P1D_ClickStats  pwd=$project_pwd"
    fi

    #��ʼ������ͳ�ƵĹ���
    callact2 "php project.php act=report_monitor_group pwd=$project_pwd"
    #��ʼ������
    callact2 "php project.php act=report_monitor_order pwd=$project_pwd"
    #���������
    callact2 "php project.php act=crontab_report_pinfen pwd=$project_pwd"

    #ˢ�����ݿ�ע��
    callact2 "php project.php act=doc_crontab_load_db pwd=$project_pwd"
    #��Ŀ�����
    callact2 "php project.php act=monitor_duty pwd=$project_pwd"
    if [ -e /home/httpd/$1/project2.php ] ; then
        callact2 "php project2.php act=monitor_duty no_manyi=1 pwd=$project_pwd"
    fi

    #Memcache+Oracle��Դ���
    callact2 "php project.php act=monitor_check pwd=$project_pwd"

    #SQL������
    callact2 "php header.php act=_ociexplain pwd=$project_pwd"

fi

#ͳ��WEB��־(ע��˫��)
if [ $2 = nogz ]; then
    callact2 "php project.php act=web_log pwd=$project_pwd"
else
    callact2 "php project.php act=web_log gz=/home/logs pwd=$project_pwd"
fi

#ÿСʱ�����־
echo >/home/webid/logs/php_error.log
