<?php

   session_start();

   if (!isset($_SESSION['auth']))
   {
      header("Location: login.php"); 
      exit();
   }
   if (!$_SESSION["auth"])
   {
      header("Location: login.php"); 
      exit();
   }

	// Read the JSON GIT file 
	$json = file_get_contents('/etc/fpm/git.json');
	$json_data = json_decode($json,true);

	# If request doesn't contain the git variable return an error.
	# Git variable is meant to be a UUID.
	if( !(isset($_POST['git'])) )
	{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong>Git Destination not Set.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';					
			exit();
	}
	else
	{
		# Run through all the git entries and try to match the ID.
		$found_git = "false";
		foreach($json_data as $git)
		{
			# Get all details for the Git to be used.
			if($git["uuid"] == $_POST['git'])
			{
				$found_git="true";
				$token = $git["token"];
				$git_fqdn = $git["fqdn"];
				$project = $git["project"];
				$branch = $git["branch"];
				$format = $git["format"];
				$path = $git["path"];
				$id = $git["id"];
            $type = $git["type"];
				if ($path == ".")
					$path = "";
			}
		}
		# If the ID is not found return an error.
		if ($found_git == "false")
		{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong> Git not found on list. Click <a href="settings.php">here</a> to add it..
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';						
			exit();
		}
	}
	# If request doesn't contain the policy name as a variable, return an error.
	# We assume that the policy name is going to match with the file name and 
	# the file extension will be either .json or .yaml depending on the format.
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
		$policy = $_POST['policy'].".". strtolower($format);

	# The policy_data indicate what changed are required to be done on the policy.
	# Without this we return an error. 
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

	# Check if the support ID is sent as a parameter.
		if( !(isset($_POST['support_id'])) )
		$support_id = "none";
	else
		$support_id = $_POST['support_id'];

	# Check if the Git comment is sent as a parameter.
		if( !(isset($_POST['comment'])) )
		$comment = "none";
	else
		$comment = base64_decode($_POST['comment']);
		
	$comment = "(" . $support_id . ") - ". $comment;

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
	
	function get_id_gitlab($git_fqdn, $project, $token) {
      $headers = array(
         'Content-Type: application/json',
         'Accept: application/json, text/javascript, */*; ',
         'PRIVATE-TOKEN: ' . $token
         );
      $url = $git_fqdn."/api/v4/projects";            
	
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl,CURLOPT_TIMEOUT,4);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
   
		$curl_response = curl_exec($curl);
		
      #verify that the transaction was successful
      if (curl_errno($curl))
         return curl_error($curl);

      #Wrong Password
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if ($httpcode == 401) 
      {
         curl_close($curl);
         return "Authentication failure.";
      } 
		curl_close($curl);

      $result = json_decode($curl_response, true); ## Save response to a JSON variable

      foreach ($result as $repo)
      {
         if ($repo["path_with_namespace"] == $project)
            return $repo["id"];        ##  Return the Repo ID
      }

      return "Project/Repo Not found (".$project.").";
	}

	# This function will download the policy from Gitlab in Base64 format.
	function get_policy_gitlab($git_fqdn, $project, $token, $id, $path, $policy, $branch) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'PRIVATE-TOKEN: ' . $token
			);
			if ($path=="")
				$url = $git_fqdn."/api/v4/projects/".$id."/repository/files/".urlencode($policy)."?ref=".$branch;
			else
				$url = $git_fqdn."/api/v4/projects/".$id."/repository/files/".urlencode($path."/".$policy)."?ref=".$branch;

         $curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
			$curl_response = curl_exec($curl);
         $result  = array("status" => 0, "msg" => "-");
         
         if (curl_errno($curl))
         {
            $result["status"]=0;
            $result["msg"]=curl_error($curl);
            return $result;
         }

			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
        
			if ($httpcode == 200)
			{
				$policy = json_decode($curl_response, true);
            $result["status"]=1;
            $result["msg"]=$policy;
            return $result;
			}
			else
         {
            $result["status"]=0;
            $result["msg"]= $httpcode . " HTTP code received while getting the policy '".$policy. "' from " .$project."/".$path;
            return $result;
         }

	}

   # This function will upload the policy file to Gitlab in Base64 format.
	function update_policy_gitlab($git_fqdn, $project, $token, $id, $path, $policy, $branch, $payload) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'PRIVATE-TOKEN: ' . $token
			);
			if ($path == "")
				$url = $git_fqdn."/api/v4/projects/".$id."/repository/files/".urlencode($policy)."?ref=".$branch;
			else
				$url = $git_fqdn."/api/v4/projects/".$id."/repository/files/".urlencode($path."/".$policy)."?ref=".$branch;
         
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

		
         #verify that the transaction was successful
         if (curl_errno($curl))
            return curl_error($curl);
		
			curl_close($curl);
         if ($httpcode==200)
            return "Success";
         else
            return "Failed!! ". $httpcode . " HTTP code received while updating the policy '".$policy. "' from " .$project."/".$path;;
	}

	# Download the file from Gitea in Base64 format.
	function get_policy_gitea($git_fqdn, $project, $token, $id, $path, $policy, $branch) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'Authorization: token ' . $token
			);
			if ($path=="")
				$url = $git_fqdn."/api/v1/repos/".$project."/contents/".urlencode($policy)."?ref=".$branch;
			else
				$url = $git_fqdn."/api/v1/repos/".$project."/contents/".urlencode($path."/".$policy)."?ref=".$branch;

         $curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
			$curl_response = curl_exec($curl);
         $result  = array("status" => 0, "msg" => "-");
         
         if (curl_errno($curl))
         {
            $result["status"]=0;
            $result["msg"]=curl_error($curl);
            return $result;
         }

			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
        
			if ($httpcode == 200)
			{
				$policy = json_decode($curl_response, true);
            $result["status"]=1;
            $result["msg"]=$policy;
            return $result;
			}
			else
         {
            $result["status"]=0;
            $result["msg"]= $httpcode . " HTTP code received while getting the policy '".$policy. "' from " .$project."/".$path;
            return $result;
         }

	}

   # This function will upload the policy file to Gitea in Base64 format.
	function update_policy_gitea($git_fqdn, $project, $token, $path, $policy, $branch, $payload) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'Authorization: token ' . $token
			);
			if ($path == "")
				$url = $git_fqdn."/api/v1/repos/".$project."/contents/".urlencode($policy)."?ref=".$branch;
			else
				$url = $git_fqdn."/api/v1/repos/".$project."/contents/".urlencode($path."/".$policy)."?ref=".$branch;
         
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

         #verify that the transaction was successful
         if (curl_errno($curl))
            return curl_error($curl);
		
			curl_close($curl);

         if ($httpcode==200)
            return "Success";
         else
            return "Failed!! ". $httpcode . " HTTP code received while updating the policy '".$policy. "' from " .$project."/".$path;;
	
	}

   ##-----------------------  Download Policy  ----------------------------
   if ($type == "gitlab")
   {

      #### Verify that the Project exists and get ID
      $id = get_id_gitlab($git_fqdn, $project, $token);

      #### If ID is not Integer, then give the error description
      if (!is_int($id))
      {
         echo '
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
               <b>Failed!</b> '.$id.'
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
         exit();
      }
     
	   #### Verify that the Policy exists and get contents exists
      $policy_content = get_policy_gitlab($git_fqdn, $project, $token, $id, $path, $policy, $branch);
     
      # if the result of the get_policy function is 0 then it is an issue
      if ($policy_content["status"] == 0)
      {
         echo '
               <div class="alert alert-warning alert-dismissible fade show" role="alert">
               <b>Failed!</b> '.$policy_content["msg"].'
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>';
         exit();
      }
      
      file_put_contents("policy",base64_decode($policy_content["msg"]["content"]));  # store the policy to a file

   }

   if ($type == "gitea")
   {

	   #### Verify that the Policy exists and get contents exists
      $policy_content = get_policy_gitea($git_fqdn, $project, $token, $id, $path, $policy, $branch);
     
      # if the result of the get_policy function is 0 then it is an issue
      if ($policy_content["status"] == 0)
      {
         echo '
               <div class="alert alert-warning alert-dismissible fade show" role="alert">
               <b>Failed!</b> '.$policy_content["msg"].'
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>';
         exit();
      }
      
      file_put_contents("policy",base64_decode($policy_content["msg"]["content"]));  # store the policy to a file

   }
     

   ##-----------------------  Python merge script  ----------------------------

	
	# Run the python script to make the policy changes
	$run_python_script = 'python3 modify-nap.py ' . strtolower($format) . ' ' . $policy_data ;
	$command = escapeshellcmd($run_python_script);
	$output = shell_exec($command);
	
	# if the output of the script includes Success word then the script was successful.
	if(!(strpos($output, 'Success') !== false))
	{
		echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
  		'.$output.'
  		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
   	# Delete Temp files
      unlink('policy');
      unlink('policy_mod');
      exit();
	}

   # the python script will have created a file called "policy_mod".
   $new_policy = base64_encode(file_get_contents('policy_mod'));

   ##-----------------------   Update Policy   ----------------------------
	if ($type=="gitlab")
	{ 
		# create the payload to send to Gitlab
		$payload = '{"encoding":"base64", "branch": "'.$branch.'", "content": "'.$new_policy.'", "commit_message": "'.$comment.'"}';

		# run function that will upload the updated file.
		$result = update_policy_gitlab($git_fqdn, $project, $token, $id, $path, $policy, $branch, $payload);

      if ($result == "Success")
      {
         echo '
         <div class="alert alert-success alert-dismissible fade show" role="alert">
         '.$output.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';
      }
      else
      {
         echo '
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
         '.$result.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';

      }

	}
	
	if ($type=="gitea")
	{ 
		# create the payload to send to Gitea
		$payload = '{"branch": "'.$branch.'", "content": "'.$new_policy.'", "sha": "'.$policy_content["msg"]["sha"].'", "commit_message": "'.$comment.'"}';

		# run function that will upload the updated file.
		$result = update_policy_gitea($git_fqdn, $project, $token, $path, $policy, $branch, $payload);


      if ($result == "Success")
      {
         echo '
         <div class="alert alert-success alert-dismissible fade show" role="alert">
         '.$output.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';
      }
      else
      {
         echo '
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
         '.$result.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';

      }

	}

   # Delete Temp files
   unlink('policy');
   unlink('policy_mod');      
   exit();

?>
