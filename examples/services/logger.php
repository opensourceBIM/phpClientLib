<?php
class LoggerService {
	public function getDefinition() {
		return array(
			array(
					"__type" => "SProfileDescriptor",
					"identifier" => "ribcasette",
					"name" => "RibCasette",
					"description" => "Vervang vloeren door RibCasettes",
					"publicProfile" => true
			),
			array(
					"__type" => "SProfileDescriptor",
					"identifier" => "vloeren",
					"name" => "Vloeren",
					"description" => "Vervang vloeren door andere vloeren",
					"publicProfile" => true
			)
		);
	}
	
	public function getIdentifier() {
		return "logger";
	}
	
	public function getPrivateProfiles($serviceName, $token) {
		return null;
	}

	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Log all calls in a database",
				"identifier" => "default",
				"publicProfile" => true
			)
		);
	}
	
	public function newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
		$start = time() * 1000;
		$title = "Logging";
		$bimServerApi = new BimServerApi($apiUrl, $token);
		$service = $bimServerApi->getService($soid);
		$revision = $bimServerApi->getRevision($roid);
		$user = $bimServerApi->getUserByUoid($revision["userId"]);
		include "../dbsettings.php";
		
		global $host, $username, $password, $database;

		mysql_connect($host, $username, $password);
		mysql_select_db($database);
	
		$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running PHP Quantizator");

		$end = time();
		$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running PHP Logger");
		$sql = "INSERT INTO incoming SET message='" . json_encode($logAction) . "'";
		mysql_query($sql);
		$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
		$bimServerApi->unregisterProgressTopic($topicId);
		
		mysql_close();
	}
}
?>