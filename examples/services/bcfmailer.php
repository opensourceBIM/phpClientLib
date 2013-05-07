<?php 
class BcfMailerService {

	public function getIdentifier() {
		return "bcfmailer";
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
}
?>