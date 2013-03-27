<?
	include "../bimserverapi.php";
	include "phpmailer.inc.php";

	$data = file_get_contents('php://input');
	$bimServerApi = new BimServerApi("", null);
	
	class RemoteServiceHandler {
	
		public function newRevision($poid, $roid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
			error_log($serviceIdentifier . "." . $profileIdentifier);
			if ($serviceIdentifier == "PHP Quantizator") {
				$bimServerApi = new BimServerApi($apiUrl, $token);
				$revision = $bimServerApi->getRevision($roid);
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running PHP Quantizator");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", -1);
				$response = $bimServerApi->getRevisionSummary($roid);
				
				$html = "<table><tr><th>Type</th><th>Amount</th></tr>";
				foreach ($response["list"] as $container) {
					$html .= "<tr><td colspan=\"2\">" . $container["name"] . "</td></tr>";
					foreach ($container["types"] as $type) {
						$html .= "<tr><td>" . $type["name"] . "</td><td>" . $type["count"] . "</td></tr>";
					}
				}
				$html .= "</table>";
				
				$extendedDataSchema = $bimServerApi->getExtendedDataSchemaByNamespace("http://extend.bimserver.org/htmlsummary");
				
				$bimServerApi->addExtendedDataToRevision($roid, "HTML Summary", $html, $extendedDataSchema["oid"]);

				$bimServerApi->updateProgressTopic($topicId, "FINISHED", -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			} else if ($serviceIdentifier == "PHP Logger") {
				include "dbsettings.php";

				mysql_connect($host, $username, $password);
				mysql_select_db($database);
			
				$topicId = $bimServerApi->registerProgressTopic("RUNNING_SERVICE", "Running floor demonstration");
				$sql = "INSERT INTO incoming SET message='" . json_encode($logAction) . "'";
				mysql_query($sql);
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", -1);
				$bimServerApi->unregisterProgressTopic($topicId);
				
				mysql_close();
			} else if ($serviceIdentifier == "Floor Demo") {
				$bimServerApi = new BimServerApi($apiUrl, $token);
				$revision = $bimServerApi->getRevision($roid);
				$user = $bimServerApi->getUserByUoid($revision["userId"]);
				if ($revision["comment"] == "M1_project (start).ifc") {
					$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running floor demonstration");
					$bimServerApi->updateProgressTopic($topicId, "STARTED", -1);

					$deserializer = $bimServerApi->getSuggestedDeserializerForExtension("ifc");
					
					$mail = new PHPMailer(); // defaults to using php "mail()"
					$mail->SetFrom('demo@bimserver.org', 'Demo');
					
					$mail->AddAddress($user["username"], $user["name"]);
					$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
					$mail->AddCC("demo@bimserver.org", "Demo");

					$mail->Subject = "Floors added";
					$mail->AltBody = "Floors added";
					$mail->MsgHTML("Floors added");
					
					$mail->AddAttachment(getcwd() . "/files/floor.xls");
					$mail->AddAttachment(getcwd() . "/files/floor.ifc");

					if(!$mail->Send()) {
					  error_log("Mailer Error: " . $mail->ErrorInfo);
					}
					
					$bimServerApi->checkin($poid, "Added floors", "M1_project (result).ifc", $deserializer["oid"], getcwd() . "/files/M1_project (result).ifc");
					$bimServerApi->updateProgressTopic($topicId, "FINISHED", -1);
					$bimServerApi->unregisterProgressTopic($topicId);
				}
			}
			return json_decode("{}");
		}

		public function newExtendedData($roid, $edid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
			$bimServerApi = new BimServerApi($apiUrl, $token);
			$revision = $bimServerApi->getRevision($roid);
			$project = $bimServerApi->getProject($revision["projectId"]);
			$extendedData = $bimServerApi->getExtendedData($edid);
			$extendedDataSchema = $bimServerApi->getExtendedDataSchema($extendedData["schemaId"]);
			if ($extendedDataSchema["namespace"] == "http://www.buildingsmart-tech.org/specifications/bcf-releases") {
				$topicId = $bimServerApi->registerProgressTopic("RUNNING_SERVICE", "Running BCF Mailer");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", -1);
			
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->SetFrom('demo@bimserver.org', 'Demo');
				
				$mail->Subject = "New BCF generated";
				$mail->AltBody = "New BCF generated";
				$mail->MsgHTML("New BCF generated");
				
				$file = $bimServerApi->getFile($extendedData["fileId"]);
				file_put_contents($file["filename"], $file["data"]);
				
				$mail->AddAttachment($file["filename"], $file["filename"], "base64", $file["mime"]);

				foreach ($project["hasAuthorizedUsers"] as $userId) {
					$user = $bimServerApi->getUserByUoid($userId);
					$mail->AddAddress($user["username"], $user["name"]);
					$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
					$mail->AddCC("demo@bimserver.org", "Demo");
				}

				if(!$mail->Send()) {
				  error_log("Mailer Error: " . $mail->ErrorInfo);
				}
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			}
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
						"name" => "Kanaalplaten",
						"description" => "Vervangt vloer door kanaalplaten",
						"publicProfile" => true
					),
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p2",
						"name" => "Breedplaten",
						"description" => "Vervangt vloer door breedplaten",
						"publicProfile" => true
					)
				);
			} else if ($serviceName == "PHP BCF Mailer") {
				return array(
					array(
						"__type" => "SProfileDescriptor",
						"identifier" => "p1",
						"name" => "Default",
						"description" => "Default",
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

	echo $bimServerApi->processIncoming($data, new RemoteServiceHandler());
?>