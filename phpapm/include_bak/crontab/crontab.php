<?php

if (!$_SERVER['HTTP_HOST'] || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || strpos($_SERVER['REMOTE_ADDR'], '10.') === 0) {

} else {
	if (is_file("index.php"))
		die(header("location: /index.php"));
	else
		die("No input file specified." . date('r'));
}
if ($_SERVER['argv'] && !$_SERVER['HTTP_HOST']) {
	$str_array = array();
	$str = join('&', $_SERVER['argv']);
	parse_str($str, $str_array);
	settype($str_array, 'array');
	settype($_GET, 'array');
	$_GET = $str_array + $_GET;
}

ini_set("display_errors", false);
$m = new m;
$_GET['act'] = $_GET['act'] ? $_GET['act'] : "index";
$m->$_GET['act']();
class  m
{
	/**
	 * @desc   WHAT?
	 * @author ����̩ mailto:resia@dev.ppstream.com
	 * @since  2013-08-22 15:16:43
	 * @throws ע��:��DB�쳣����
	 */
	function index()
	{
		//������������ݱ��:
		$dir = './crontab';
		exec("chmod 0755 {$dir}/* ");
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == '.' || $file == '..')
					continue;
				//ȫ·��
				$nfile = $dir . '/' . $file;
				if (!is_dir($nfile) && strpos($file, '.sh') !== false) {
					$data = file_get_contents($nfile);
					$data = str_replace("\r", NULL, $data);
					print_r("�޸��ļ�:{$nfile}");
					print_r("<br>\n");
					$fp = fopen($nfile, 'w');
					if ($fp) {
						fwrite($fp, $data);
						fclose($fp);
						chmod($nfile, 0755);
					}
				}
			}
			closedir($dh);
		}

		//������������ݱ��:
		$dir = './shell';
		if (is_dir($dir)) {
			exec("chmod 0755 {$dir}/*.sh ");
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if ($file == '.' || $file == '..')
						continue;
					//ȫ·��
					$nfile = $dir . '/' . $file;
					if (!is_dir($nfile) && strpos($file, '.sh') !== false) {
						$data = file_get_contents($nfile);
						$data = str_replace("\r", NULL, $data);
						print_r("�޸��ļ�:{$nfile}");
						print_r("<br>\n");
						$fp = fopen($nfile, 'w');
						if ($fp) {
							fwrite($fp, $data);
							fclose($fp);
							chmod($nfile, 0755);
						}
					}
				}
				closedir($dh);
			}
		}
	}

	/**
	 * @desc   WHAT?
	 * @author ����̩ mailto:resia@dev.ppstream.com
	 * @since  2013-08-23 18:23:20
	 * @throws ע��:��DB�쳣����
	 */
	function mini_css_js($dir)
	{
		ini_set("display_errors", true);
		echo date("\nY-m-d H:i:s\n");
		$arr = array();
		$conn_db = ocinlogon('PPS_OA_SVN', 'PPS_OA_SVN', 'PPS_gcwo');
		if ($_GET['model_id']) {
			$sql = "select *
  from PPS_OA.T_SVN_COMMITLOGS t
 where t.model_id = :model_id
   and t.commit_date > (select *
                          from (select t.datetime
                                  from PPS_OA.T_SVN_UPGRADE_LOG t
                                 where t.model_id = :model_id
                                   and t.out_ok = 0
                                   and t.status = 'OUTER_SUCC'
                                 order by t.datetime desc) x
                         where rownum <= 1)
   and (instr(t.commit_file, '.js') > 0 or instr(t.commit_file, '.css') > 0)
 order by t.commit_date desc ";
			$stmt = ociparse($conn_db, $sql);
			ocibindbyname($stmt, ':model_id', $_GET['model_id']);
			$ocierror = ociexecute($stmt);
			$arr = $_row = array();
			while (ocifetchinto($stmt, $_row, OCI_ASSOC + OCI_RETURN_LOBS + OCI_RETURN_NULLS)) {
				$arr[$_row['COMMIT_FILE']] = true;
			}
		}
		//����û���ύ��js���޸�
		if ($_GET['model_id'] && empty($arr)) {
			echo "����û���ύ��js���޸�";
			die;
		}
		$this->_mini_css_js($arr, $dir);
		die;

		//����а汾����.��ô���а汾����.
		$sql = "select t.curr_version,t.model_name
  from PPS_OA.T_SVN_COMMITLOGS t
 where t.model_id = :model_id
   and t.commit_date > (select *
                          from (select t.datetime
                                  from PPS_OA.T_SVN_UPGRADE_LOG t
                                 where t.model_id = :model_id
                                   and t.out_ok = 0
                                   and t.status = 'OUTER_SUCC'
                                 order by t.datetime desc) x
                         where rownum <= 1)
 order by t.commit_date desc";
		$stmt = ociparse($conn_db, $sql);
		ocibindbyname($stmt, ':model_id', $_GET['model_id']);
		$ocierror = ociexecute($stmt);
		$arr = $_row = array();
		ocifetchinto($stmt, $_row, OCI_ASSOC + OCI_RETURN_LOBS + OCI_RETURN_NULLS);
		if ($_row) {
			//��ͬ��tagĿ¼�Ĵ���.webid3����TAGĿ¼
			exec("rsync -vzrtopg --progress  --port 873   --exclude=.svn  --exclude=.settings   --exclude=crontab.php   /home/webid3/sh/{$_row['MODEL_NAME']}/ webid@10.1.20.42::disk/{$_row['MODEL_NAME']}");
		}
	}

	/**
	 * @desc   WHAT?
	 * @author ����̩ mailto:resia@dev.ppstream.com
	 * @since  2013-08-22 15:16:53
	 * @throws ע��:��DB�쳣����
	 */

	function _mini_css_js($arr, $dir)
	{
		static $i = 0;

		if ($i++ > 300)
			die("\$i++ >300 ");
		if (!$dir)
			$dir = $_GET['dir'];
		if (!$dir)
			$dir = '.';
		if (!$_GET['charset'])
			$charset = 'GB18030';
		// Open a known directory, and proceed to read its contents
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if ($file == '.' || $file == '..') continue;
					$real_file = $dir . '/' . $file;
					if (strpos($real_file, 'project/') !== false) continue;
					if (strpos($real_file, '.svn/') !== false) continue;
					if (is_dir($real_file)) {
						$this->_mini_css_js($arr, $real_file);
					} else if (ereg("\.css$", strtolower($file)) || ereg("\.js$", strtolower($file))) {
						if (strpos(strtolower($file), '.mini.') !== false) continue;
						if ($_GET['model_id']) {
							$continue = false;
							foreach ($arr as $k => $v) {
								if (strpos($k, $real_file) !== false) {
									$continue = true;
									break;
								}
							}
							if (!$continue) continue;
						}
						$mini = explode('.', $file);
						$mini_ex = array_pop($mini);
						$mini_str = $dir . '/' . join('.', $mini) . '.mini.' . $mini_ex;
						$exec = escapeshellcmd("java  -jar crontab/yuicompressor.jar  --charset {$charset} '{$real_file}' -o '$mini_str' ");
						echo $mini_str . "\n";
						echo exec($exec);
					}
				}
				closedir($dh);
			}
		}
	}

	/**
	 * @desc   WHAT?
	 * @author ����̩ mailto:resia@dev.ppstream.com
	 * @since  2013-08-24 15:18:58
	 * @throws ע��:��DB�쳣����
	 */
	function tags()
	{
		echo date("Y-m-d H:i:s") . " TAGS :\n";
		ini_set("display_errors", true);
		$conn_db = ocinlogon('PPS_OA_SVN', 'PPS_OA_SVN', 'PPS_gcwo');
		$sql = "select * from PPS_OA.T_SVN_MODELS t where t.model_id=:model_id";
		$stmt = ociparse($conn_db, $sql);
		ocibindbyname($stmt, ':model_id', $_GET['model_id']);
		$ocierror = ociexecute($stmt);
		$_row = array();
		ocifetchinto($stmt, $_row, OCI_ASSOC + OCI_RETURN_LOBS + OCI_RETURN_NULLS);

		//TAGĿ���ļ���
		$model_path = str_replace("/webid/", "/webid3/", dirname($_row['MODEL_PATH']));
		exec("mkdir -p {$model_path}");
		//���߲��԰汾�ļ���
		$model_path2 = str_replace("/webid/", "/webid4/", dirname($_row['MODEL_PATH']));
		exec("mkdir -p {$model_path2}");
		//ȡ��
		$svnroot = str_replace("/home/svn/", "http://10.1.20.56/", $_row['SVNROOT']);

		#svnǩ��,�������,�͸���.
		if (!is_dir("{$model_path}/{$_row['MNAME']}")) {
			exec("cd {$model_path}; svn co --username {$_row['MODEL_USERNAME']} --password {$_row['MODEL_PASSWORD']}  {$svnroot}/{$_row['MNAME']}/tags/ {$_row['MNAME']};");
		} else {
			exec("cd {$model_path}/{$_row['MNAME']};svn cleanup;  svn up   --username {$_row['MODEL_USERNAME']} --password {$_row['MODEL_PASSWORD']} ;");
		}
		#svn���б���,ɾ�����Ѿ������ڵĴ���
		$rsync = "rsync  -vzrtopg --delete  {$model_path}/{$_row['MNAME']}/ {$model_path2}/{$_row['MNAME']}/ ";
		echo $rsync . "\n";
		exec($rsync);

		if ($_GET['exec']) {
			$runexec = "cd {$model_path2}/{$_row['MNAME']}; {$_GET['exec']}";
			echo $runexec . "\n";
			exec($runexec);
		}
		//���������Ի�����
		if ($_GET['test_rsync']) {
			$MODEL_IGNORE_str = array_diff(explode(";", $_row['MODEL_IGNORE']), array("", NULL));
			$MODEL_IGNORE_str = " --exclude=" . join(" --exclude=", $MODEL_IGNORE_str);
			$rsync = "rsync  -vzrtopg {$MODEL_IGNORE_str} {$model_path2}/{$_row['MNAME']}/ {$_GET['test_rsync']}";
			echo $rsync . "\n";
			exec($rsync);
		}

		die("\n" . date("Y-m-d H:i:s") . ',file:' . __FILE__ . ',line:' . __LINE__ . "\n");
	}

}
