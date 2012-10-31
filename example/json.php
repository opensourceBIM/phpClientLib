<?
	include "header.php";
	include "../bimserverapi.php";

	$data = file_get_contents('php://input');
	$bimServerApi = new BimServerApi("");
	
	class NotificationHandler {
	
		public function newLogAction($uuid, $logAction, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
			if ($serviceIdentifier == "PHP Quantizator") {
				if ($logAction["__type"] == "SNewRevisionAdded") {
					$bimServerApi = new BimServerApi($apiUrl);
					$roid = $logAction["revisionId"];
					$response = $bimServerApi->getDataObjects($token, $roid);
					
					$map = array();
					foreach ($response as $dataobject) {
						if (array_key_exists("type", $dataobject)) {
							$type = $dataobject["type"];
							if (array_key_exists($type, $map)) {
								$map[$type] = $map[$type] + 1;
							} else {
								$map[$type] = 1;
							}
						}
					}
					
					$html = "<table><tr><th>Type</th><th>Amount</th></tr>";
					foreach ($map as $type => $amount) {
						$html .= "<tr><td>" . $type . "</td><td>" . $amount . "</td></tr>";
					}
					$html .= "</table>";
					
					$extendedDataSchema = $bimServerApi->getExtendedDataSchemaByNamespace($token, "http://extend.bimserver.org/htmlsummary");
					
					$bimServerApi->addExtendedDataToRevision($token, $roid, "HTML Summary", $html, $extendedDataSchema["oid"]);
				}
				return json_decode("{}");
			} else if ($serviceIdentifier == "PHP Logger") {
				$sql = "INSERT INTO incoming SET message='" . json_encode($logAction) . "'";
				mysql_query($sql);
			} else if ($serviceIdentifier == "Floor Demo") {
				if ($logAction["__type"] == "SNewRevisionAdded") {
					$bimServerApi = new BimServerApi($apiUrl);
					$revision = $bimServerApi->getRevision($token, $logAction["revisionId"]);
					if ($revision["comment"] == "M1_project (start).ifc") {
						$poid = $logAction["projectId"];
						
						$deserializer = $bimServerApi->getSuggestedDeserializerForExtension($token, "ifc");
						
						$bimServerApi->checkin($token, $poid, "Added floors", "M1_project (result).ifc", $deserializer["oid"], getcwd() . "/ifcfiles/M1_project (result).ifc");
					}
				}			
			}
		}
		
		public function progress($topicId, $longActionState) {
		}
		
		public function getPublicProfiles($serviceName) {
			if ($serviceName == "PHP Quantizator") {
				return array(
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p1",
						"name" => "Count all objects",
						"description" => "Will count all objects of all types",
						"publicProfile" => true
					),
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p2",
						"name" => "Count all IfcRoot objects",
						"description" => "Will count all objects of type IfcRoot and below",
						"publicProfile" => true
					)
				);
			} else if ($serviceName == "PHP Logger") {
				return array(
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p1",
						"name" => "Log all notifications",
						"description" => "Will log all notifications",
						"publicProfile" => true
					),
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p2",
						"name" => "Log only server start/stop notifications",
						"description" => "Will log all start/stop notifications",
						"publicProfile" => true
					)
				);
			} else if ($serviceName == "Floor Demo") {
				return array(
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p1",
						"name" => "Add floors",
						"description" => "Add floors",
						"publicProfile" => true
					)
				);
			} else {
				return array();
			}
		}
		
		public function getPrivateProfiles($serviceName, $token) {
			if ($token == "12345") {
				if ($serviceName == "PHP Quantizator") {
					return array(
						array(
							"__type" => "SProfileDescriptor",
							"identifier" => "p3",
							"name" => "Secret private profile",
							"description" => "Will count all objects of all types",
							"publicProfile" => false
						)
					);
				} else if ($serviceName == "PHP Logger") {
					return array();
				}		
			}
			return array();
		}
		
		public function getService($name) {
			if ($name == "PHP Quantizator") {
				$result = array(
					"__type" => "SServiceDescriptor",
					"providerName" => "Localhost",
					"name" => "PHP Quantizator",
					"identifier" => "PHP Quantizator",
					"description" => "This service will count all objects and write an HTML report as Extended Data",
					"url" => "http://localhost/php/json",
					"notificationProtocol" => "JSON",
					"categories" => array(
						"Local Test",
						"Quantizator",
						"PHP"
					),
					"trigger" => "NEW_REVISION",
					"rights" => array(
						"readRevision" => true,
						"writeExtendedData" => "http://extend.bimserver.org/htmlsummary",
					)
				);
				return $result;
			} else if ($name == "PHP Logger") {
				$result = array(
					"__type" => "SServiceDescriptor",
					"providerName" => "Localhost",
					"name" => "PHP Logger",
					"identifier" => "PHP Logger",
					"description" => "This service will log all JSON representations of notifications in a database, content of the database is available at http://bimservertest.logic-labs.nl",
					"url" => "http://localhost/php/json",
					"notificationProtocol" => "JSON",
					"categories" => array(
						"Local Test",
						"Logger",
						"PHP"
					),
					"trigger" => "NEW_REVISION",
					"rights" => array(
					)
				);
				return $result;
			}
		}
	}

	$notificationHandler = new NotificationHandler();
	echo $bimServerApi->processIncoming($data, $notificationHandler);
?>