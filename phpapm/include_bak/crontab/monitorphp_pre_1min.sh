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
log_date2=`date +%M`
mod=`expr $log_date2 % $2`
mod2=`expr $log_date2 % 10`

if [ 0 -eq $mod2  ] ; then
    cp  -f /home/httpd/$1/crontab/.bashrc  /home/webid/.bashrc
    cp  -f /home/httpd/$1/crontab/jc.php  /home/webid/jc.php
fi

#���TCPѹ��(�̶�ÿ��10���Ӹ���һ��)
if [ 0 -eq $mod2  ] ; then
    if ps aux |  grep -v 'grep ' | grep -q "ESTABLISHED" ; then
       null;
    else
        nohup netstat -na|grep ESTABLISHED >/dev/shm/cache_tcp &
    fi
    cat /proc/meminfo | head -2 | tail -1 | awk '{print $2/1024/1024}' >/dev/shm/cache_mem
fi


#ɾ����10��Сʱ֮ǰ�Ļ����ļ�
cache_log=/dev/shm/cache_logs_`date +%Y_%m_%d_%H --date="-2 hour"`
if [ -d $cache_log ]; then
    rm -rf $cache_log
fi

#��������Ŀ¼
cache_log=/dev/shm/cache_logs_`date +%Y_%m_%d_%H --date="+1 hour"`
if [ ! -d $cache_log ]; then
    mkdir $cache_log
fi

#wgetһ����ַ,��֤�ǵ�һ��������.����ģʽ��
function callact2()
{
        exec_pwd=`pwd`
        cmd=`echo $1 |sed -e 's/\(^ *\)//' -e 's/\( *$\)//' `
        if ps aux |  grep -v 'grep ' | grep -q "$cmd" ; then
                echo `date +"%Y-%m-%d %H:%M:%S"` ${exec_pwd}[x]PHP2_Fail:$cmd >> ${log_date}_wget.log
        else
                echo  `date +"%Y-%m-%d %H:%M:%S"` ${exec_pwd}call@$cmd>> ${log_date}_wget.log    2>&1 &
                $cmd >/dev/null
        fi
}


#wgetһ����ַ,��֤�ǵ�һ��������.
function callact()
{
        exec_pwd=`pwd`
        cmd=`echo $1 |sed -e 's/\(^ *\)//' -e 's/\( *$\)//' `
        if ps aux |  grep -v 'grep ' | grep -q "$cmd" ; then
                echo `date +"%Y-%m-%d %H:%M:%S"` ${exec_pwd} [x]PHP_Fail:$cmd >> ${log_date}_wget.log
        else
                echo  `date +"%Y-%m-%d %H:%M:%S"` ${exec_pwd} call@$cmd>> ${log_date}_wget.log    2>&1 &
                nohup $cmd >/dev/null    2>&1 &
        fi
}

cd /home/httpd/$1
#·��������ͬ��Ŀͬ�������Ľű�
project_pwd=`pwd`

#�������
for (( i=0;i<=3;i++ )); do
    callact "php project.php act=monitor_fix pwd=$project_pwd mod=$i total=4"
done
if [ -e /home/httpd/$1/project2.php ] ; then
    callact "php project2.php act=monitor_fix pwd=$project_pwd"
fi

#���������ݿ�
callact2 "php project.php act=monitor go=1 pwd=$project_pwd"
#KPI����
if [ -e /home/httpd/$1/project2.php ] ; then
    callact2 "php project2.php act=monitor go=1 pwd=$project_pwd"
fi


#ÿ��$2����,�Ϳ�ʼͳ������,ͳ�Ʒ���������
if [ 0 -eq $mod ] ; then
    callact2 "php project.php act=sysload pwd=$project_pwd"
    #�ӻ����������ϼ���
    if [ 1 -eq $3 ] ; then
        callact2 "php project.php act=monitor_config del=1 pwd=$project_pwd"
        #KPI����
        if [ -e /home/httpd/$1/project2.php ] ; then
            callact2 "php project2.php act=monitor_config del=1 pwd=$project_pwd"
        fi
   fi
fi




#�ӻ����������ϼ���
if [ 1 -eq $3 ] ; then
    #����Ƿ���Ҫ����
    callact "php project.php act=P1S_SendApi pwd=$project_pwd"

    #�ĵ��Ķ�ʱ���
    if [ -e /home/httpd/$1/crontab/monitorphp_doc.sh ]; then
        source /home/httpd/$1/crontab/monitorphp_doc.sh
    fi

fi;


#���ɶ�ʱ���Խű�.���Ը����ӿ��Ƿ�����
if [ 0 -eq $mod ] ; then
    callact2 "php project.php act=doc_sh  pwd=$project_pwd"
fi

#��ĿĿ¼���ļ����޸��˶��ٸ�
callact "php project.php act=file_change pwd=$project_pwd"
