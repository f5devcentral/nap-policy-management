<?php


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


	if( !(isset($_POST['policy'])) || !(isset($_POST['uuid'])) ||  !(isset($_POST['comment'])))
	{
         header("HTTP/1.1 500 Variables Not Defined");					
			exit();
	}
	else
	{
      $policy = $_POST['policy'];
      $uuid = $_POST['uuid'];
      $comment = base64_decode($_POST['comment']);
	}

	// Read the Policy and Base64 Encode it.

   $file = "config_files/".$_POST["policy"]."/".$_POST["policy"];
   $policy_data = base64_encode(file_get_contents($file));

	// Read the JSON GIT file 
	$json = file_get_contents('/etc/fpm/git.json');
	$json_data = json_decode($json,true);

   
   $found_git = "false";
   foreach($json_data as $git)
   {
      # Get all details for the Git to be used.
      if($git["uuid"] == $uuid)
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
         <strong>Failed!</strong> Git not found on list. Click <a href="settings.php">here</a> to add it..
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>';						
      exit();
   }

    ##-----------------------   Update Policy   ----------------------------
	if ($type=="gitlab")
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


	   # create the payload to send to Gitlab
		$payload = '{"encoding":"base64", "branch": "'.$branch.'", "content": "'.$policy_data.'", "commit_message": "'.$comment.'"}';

		# run function that will upload the updated file.
		$result = update_policy_gitlab($git_fqdn, $project, $token, $id, $path, $policy, $branch, $payload);

      if ($result == "Success")
      {
         echo '
         <div class="alert alert-success alert-dismissible fade show" role="alert">
         Policy Updated Successfully!
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';
      }
      else
      {
         echo '
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <b>Failed!</b> '.$result.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';

      }

	}
	
	if ($type=="gitea")
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



		# create the payload to send to Gitlab
		$payload = '{"branch": "'.$branch.'", "content": "'.$policy_data.'", "sha": "'.$policy_content["msg"]["sha"].'", "message": "'.$comment.'"}';

		# run function that will upload the updated file.
		$result = update_policy_gitea($git_fqdn, $project, $token, $path, $policy, $branch, $payload);


      if ($result == "Success")
      {
         echo '
         <div class="alert alert-success alert-dismissible fade show" role="alert">
         Policy Updated Successfully!
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';
      }
      else
      {
         echo '
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <b>Failed!</b> '.$result.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';

      }

	}

  
   $file= "config_files/".$policy."/sync";
   unlink($file);
   sleep (1);
?>

