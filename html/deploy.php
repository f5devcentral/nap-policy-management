<?php

	// Read the JSON GITLAB file 
	$json = file_get_contents('/etc/fpm/gitlab.json');
	$json_data = json_decode($json,true);

	if( !(isset($_POST['gitlab'])) )
	{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong>GitLab Destination not Set.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';					
			exit();
	}
	else
	{
		$found_git = "false";
		foreach($json_data as $git)
		{
			if($git["id"] == $_POST['gitlab'])
			{
				$found_git="true";
				$token = $git["token"];
				$gitlab = $git["fqdn"];
				$project = $git["project"];
				$branch = $git["branch"];
				$path = $git["path"];
			}
		}
		if ($found_git == "false")
		{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong> GitLab not found on list. Click <a href="settings.php">here</a> to add it..
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';						
			exit();
		}
	}


	if( !(isset($_POST['policy'])) )
	{
		echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>Error!</strong> Policy not set.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';				
		exit();
	}
	else
		$policy = $_POST['policy'].".json";

	if( !(isset($_POST['policy_data'])) )
	{
		echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>Error!</strong>Data are missing from the request..
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';			
		exit();
	}
	else
		$policy_data = $_POST['policy_data'];

	if( !(isset($_POST['support_id'])) )
		$support_id = "-";
	else
		$support_id = $_POST['support_id'];


	if( !(isset($_POST['comment'])) )
		$comment = "-";
	else
		$comment = base64_decode($_POST['comment']);
		


	function json_validate($string)
	{
			// decode the JSON data
			$result = json_decode($string);

			// switch and check possible JSON errors
			switch (json_last_error()) {
					case JSON_ERROR_NONE:
							$error = ''; // JSON is valid // No error has occurred
							break;
					case JSON_ERROR_DEPTH:
							$error = 'The maximum stack depth has been exceeded.';
							break;
					case JSON_ERROR_STATE_MISMATCH:
							$error = 'Invalid or malformed JSON.';
							break;
					case JSON_ERROR_CTRL_CHAR:
							$error = 'Control character error, possibly incorrectly encoded.';
							break;
					case JSON_ERROR_SYNTAX:
							$error = 'Syntax error, malformed JSON.';
							break;
					// PHP >= 5.3.3
					case JSON_ERROR_UTF8:
							$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
							break;
					// PHP >= 5.5.0
					case JSON_ERROR_RECURSION:
							$error = 'One or more recursive references in the value to be encoded.';
							break;
					// PHP >= 5.5.0
					case JSON_ERROR_INF_OR_NAN:
							$error = 'One or more NAN or INF values in the value to be encoded.';
							break;
					case JSON_ERROR_UNSUPPORTED_TYPE:
							$error = 'A value of a type that cannot be encoded was given.';
							break;
					default:
							$error = 'Unknown JSON error occured.';
							break;
			}

			if ($error !== '') {
					// throw the Exception or exit // or whatever :)
					exit($error);
			}

			// everything is OK
			return $result;
	}
	function verify_project($project, $token, $gitlab) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'PRIVATE-TOKEN: ' . $token
			);

			$url = $gitlab."/api/v4/projects";
			
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
			$curl_response = curl_exec($curl);
		
			if ($curl_response === false) {
				curl_close($curl);
				return -2;
			}
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if ($curl_response == 401) {
				curl_close($curl);
				return -3;
			} 

			curl_close($curl);
			$result = json_decode($curl_response, true);

			$project_found = False;

			foreach ($result as $repo)
			{
				if ($repo["path_with_namespace"] == $project)
				{
					$project_found = True;
					return $repo["id"];
				}	
			}
			if (!$project_found )
				return -1;
	}
	function get_policy($project, $token, $id, $gitlab, $path, $policy, $branch) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'PRIVATE-TOKEN: ' . $token
			);
	
			$url = $gitlab."/api/v4/projects/".$id."/repository/files/".urlencode($path."/".$policy)."?ref=".$branch;

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
			$curl_response = curl_exec($curl);

			if ($curl_response === false) {
				$info = curl_getinfo($curl);
				curl_close($curl);
				return -2;
			}

			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);

			if ($httpcode == 200)
			{
				$result = json_decode($curl_response, true);
				return $result["content"];
			}
			else
				return "failure";

	}
	function update_policy($project, $token, $id, $gitlab, $path, $policy, $branch, $payload) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'PRIVATE-TOKEN: ' . $token
			);
			$url = $gitlab."/api/v4/projects/".$id."/repository/files/".urlencode($path."/".$policy)."?ref=".$branch;

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
			$curl_response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			$result = json_decode($curl_response, true);

			if ($curl_response === false) {
				$info = curl_getinfo($curl);
				curl_close($curl);
				return -1;
			}
		
			curl_close($curl);
			return $result;
			
	
	}


	#### Verify that the Project exists and get ID
	$id = verify_project($project, $token, $gitlab);

	if ($id == -3)
	{
		echo '
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					Unable to authenticate user.
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>';
		exit();
	}
	if ($id == -2)
	{
		echo '
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					Unable to connect to <strong>"'.$gitlab.'"</strong>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>';
		exit();
	}

	if ($id == -1)
	{

		echo '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Project <b>"'.$project.'"</b> not found 
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';
		exit();
	}

	#### Verify that the Policy exists and get contents exists
	$policy_content = get_policy($project, $token,  $id, $gitlab, $path, $policy, $branch);

	if ($policy_content == -2)
	{
		echo '
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					Unable to connect to <b>"'.$gitlab.'"</b>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>';
		exit();
	}

	if ($policy_content == "failure")
	{

		echo '
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			Policy <b>"'.$policy.'"</b> in branch "'.$branch.'" was not found 
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
		exit();
	}

	if (!json_validate(base64_decode($policy_content)))
	{
		echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
  		<strong>Error!</strong>The policy is not the JSON format.
  		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';		
		exit();
	}
		
	file_put_contents("policy.json",base64_decode($policy_content));
	
	$run_python_script = 'python3 modify-nap.py ' . $policy_data ;
	$command = escapeshellcmd($run_python_script);
	$output = shell_exec($command);
	
	if(!(strpos($output, 'Success') !== false))
	{
		echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
  		'.$output.'
  		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
	}
	else
	{ 

		// Read the JSON file 
		$new_policy = base64_encode(file_get_contents('policy_mod.json'));

		$payload = '{"encoding":"base64", "branch": "'.$branch.'", "content": "'.$new_policy.'", "commit_message": "'.$comment.'"}';
		#$payload = json_decode($string,true);
		$result = update_policy($project, $token, $id, $gitlab, $path, $policy, $branch, $payload);

		echo '
		<div class="alert alert-success alert-dismissible fade show" role="alert">
  		'.$output.'
  		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
	}
		
	

?>
