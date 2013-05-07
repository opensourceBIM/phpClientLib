<?php
class KalkzandsteenService {
	public function getDefinition() {
		return null;
	}
	
	public function getPublicProfiles() {
		return array(
			array(
				"__type" => "SProfileDescriptor",
				"name" => "Default",
				"description" => "Voeg kalkzandsteen toe",
				"identifier" => "default",
				"publicProfile" => true
			)
		);
	}
	
	public function getIdentifier() {
		return "kalkzandsteen";
	}
	
	public function getPrivateProfiles($serviceName, $token) {
		return null;
	}
	
	public function newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
		$bimServerApi = new BimServerApi($apiUrl, $token);
		$service = $bimServerApi->getService($soid);
		$revision = $bimServerApi->getRevision($roid);
		$user = $bimServerApi->getUserByUoid($revision["userId"]);
		$start = time() * 1000;
		$end = null;
		if ($revision["comment"] == "_start.ifc") {
			if ($profileIdentifier == "ribcasette") {
				$title = "Vervangen van de vloeren door ribcasettes voor revisie " . $revision["id"];
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running floor demonstration");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
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
				
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"]; 
				}
				$bimServerApi->checkin($targetPoid, "Added floors", "M1_project (result)_floor.ifc", $deserializer["oid"], getcwd() . "/files/M1_project (result)_floor.ifc");
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			} else if ($profileIdentifier == "vloeren") {
				$title = "Vervangen van de vloeren door andere vloeren voor revisie " . $revision["id"];
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], "Running floor demonstration");
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
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
				
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"]; 
				}
				$bimServerApi->checkin($targetPoid, "Added floors", "M1_project (result)_floor.ifc", $deserializer["oid"], getcwd() . "/files/M1_project (result)_floor.ifc");
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			}
		}
	}
}
?>