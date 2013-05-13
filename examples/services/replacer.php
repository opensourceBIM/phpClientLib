<?php 
abstract class Replacer {
	public abstract function getTitle($revision);
	public abstract function getFilename();
	
	public function newRevision($poid, $roid, $soid, $serviceIdentifier, $profileIdentifier, $token=null, $apiUrl=null) {
		$bimServerApi = new BimServerApi($apiUrl, $token);
		$service = $bimServerApi->getService($soid);
		$revision = $bimServerApi->getRevision($roid);
		$user = $bimServerApi->getUserByUoid($revision["userId"]);
		$start = time() * 1000;
		$end = null;
		if ($revision["comment"] == "architect(startmodel).ifc") {
			if ($profileIdentifier == "default") {
				$title = $this->getTitle($revision["id"]);
				$topicId = $bimServerApi->registerProgressOnRevisionTopic("RUNNING_SERVICE", $revision["projectId"], $revision["oid"], $title);
				$bimServerApi->updateProgressTopic($topicId, "STARTED", $title, $start, $end, -1);
	
				$deserializer = $bimServerApi->getSuggestedDeserializerForExtension("ifc");
	
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->SetFrom('demo@bimserver.org', 'Demo');
	
				$mail->AddAddress($user["username"], $user["name"]);
				$mail->AddCC("ruben@logic-labs.nl", "Ruben de Laat");
				$mail->AddCC("demo@bimserver.org", "Demo");
	
				$mail->Subject = $title;
				$mail->AltBody = $title;
				$mail->MsgHTML($title);
	
				$filename = $this->getFilename();
	
				$mail->AddAttachment(getcwd() . "/files/" . $filename);
	
				if(!$mail->Send()) {
					error_log("Mailer Error: " . $mail->ErrorInfo);
				}
	
				$end = time() * 1000;
				$targetPoid = $poid;
				if ($service["writeRevisionId"] != -1) {
					$targetPoid = $service["writeRevisionId"];
				}
				$bimServerApi->checkin($targetPoid, $title, $filename, $deserializer["oid"], getcwd() . "/files/" . $filename);
				$bimServerApi->updateProgressTopic($topicId, "FINISHED", $title, $start, $end, -1);
				$bimServerApi->unregisterProgressTopic($topicId);
			}
		}
	}
}
?>