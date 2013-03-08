<?php
$SERVER = "172.18.186.186";
$USERNAME = "root";
$PWD = 	"root";
$DB = "ipom";

$conn = mysql_connect($SERVER, $USERNAME, $PWD);
mysql_select_db($DB);

mysql_query("set names utf8");

$what = @$_GET['what'] or $what='';
switch ($what) {
case 'info':
	$projID = @$_GET['projectID'] or $projID = 0;
	$data = getProjectInfo($projID);
	break;

// fall through
case 'projects':
default:
	$data = getProjects();
}

mysql_close($conn);

echo json_encode($data);

function getProjects() {
	$sql = "select * from project";
	$result = mysql_query($sql);

	$data = array();
	while ($row = mysql_fetch_array($result)) {
		$proj = array('projectID' => $row['projectID'],
					'projectName' => $row['projectName']);
		array_push($data, $proj);
	}

	return $data;
}

function getProjectInfo($projID) {
	$field_sql = 'select count(*) as total, province, date(time) as day from information left join media on media.mediaID=information.mediaID ';
	$group_sql = 'group by province, date(time)';
	$proj_sql = "(select p.projectID, p.projectName, k.keywordID from project as p left join keyword as k 
		on p.projectID=k.projectID where p.projectID={{pID}}) as p";
	$sql = $field_sql . $group_sql;

	if ($projID) {
		$sql = $field_sql . ' , ' . str_replace("{{pID}}", $projID, $proj_sql) 
			. ' where p.keywordID=information.keywordID ' . $group_sql;
	}

	$result = mysql_query($sql);

	$data = array();
	while ($row = mysql_fetch_array($result)) {
		$province = $row['province'];

		if (!$province) continue;
		if (isset($data[$province])) {
			$day = $row['day'];
			$data[$province][$day] = $row['total'];
		} else {
			$data[$province] = array();
		}
	}

	return $data;
}