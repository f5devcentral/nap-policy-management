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

   #--------   Validate that all the POST parameters are received. Otherwise give back an error
   $error="none";
	if( !(isset($_POST['git_fqdn'])) )
      $error="Git FQDN variable missing";
	else
		$git_fqdn = $_POST['git_fqdn'];

   if( !(isset($_POST['project_name'])) || $_POST['project_name']=="")
      $error="ProjectName variable missing";
	else
		$project_name = $_POST['project_name'];
	
	if( !(isset($_POST['token'])) || $_POST['token']=="" )
      $error = "Token variable missing";
	else
		$token = $_POST['token'];
	
	if( !(isset($_POST['branch'])) || $_POST['branch']=="" )
      $error = "Branch variable missing";
	else
		$branch = $_POST['branch'];
	
   if( !(isset($_POST['format'])))
      $error = "Format variable missing";
	else
		$format = $_POST['format'];
	

	if( !(isset($_POST['path'])) )
      $error = "Path variable missing";
	else
	{
		$path = $_POST['path'];
		if ($path == "")
			$path= ".";
	}

	if( !(isset($_POST['type'])) )
      $error = "Type variable missing";
  
	else
	{
		$type = $_POST['type'];
	}

   if($error!="none")
   {
      echo '
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Failed!</strong> '.$error.'.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>';
      exit();
   }
   #--------------------------------------------------------------------------------------------------------
	
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
      if ($httpcode == 200) 
      {
         foreach ($result as $repo)
         {
            if ($repo["path_with_namespace"] == $project)
               return $repo["id"];        ##  Return the Repo ID
         }
      }
      else
         return "Project/Repo Not found (".$project."). Response received from ".$git_fqdn. " was '" . $httpcode ."'";
	}

	function verify_path_gitlab($git_fqdn, $project, $token, $id, $path, $branch) {
      $headers = array(
         'Content-Type: application/json',
         'Accept: application/json, text/javascript, */*; ',
         'PRIVATE-TOKEN: ' . $token
         );
      $url = $git_fqdn."/api/v4/projects/".$id."/repository/tree?ref=".$branch;
      
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl,CURLOPT_TIMEOUT,5);
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

			
		if ($httpcode == 200)
		{
			$result = json_decode($curl_response, true);
         # Check if Folder exists and return 1.
         foreach ($result as $temp_path)
         {
            if ($temp_path["path"] == $path && $temp_path["type"] == "tree")
               return 1;
         }
			return "Folder (".$path.") not found";
      }
      else
         return "Response received from ".$git_fqdn. " was '" . $httpcode ."'";

	}

	function verify_repo_gitea ($git_fqdn, $project, $token) {
      $headers = array(
         'Content-Type: application/json',
         'Accept: application/json, text/javascript, */*; ',
         'Authorization: token ' . $token
         );
      $url = $git_fqdn."/api/v1/repos/".$project;            
	
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

      if ($httpcode == 200)
		{
         $result = json_decode($curl_response, true); ## Save response to a JSON variable
         if ($result["full_name"]==$project)
         {
            return $result["id"];        ##  Return the Repo ID
         }
      }
      else
         return "Project/Repo Not found (".$project."). <br> Response received from ".$git_fqdn. " was '" . $httpcode ."'";
	}

   function verify_path_gitea($git_fqdn, $project, $token, $path, $branch) {
      $headers = array(
         'Content-Type: application/json',
         'Accept: application/json, text/javascript, */*; ',
         'Authorization: token ' . $token
         );
      
      ##Verify the repo
      $url = $git_fqdn."/api/v1/repos/".$project."/contents?ref=".$branch;
      
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl,CURLOPT_TIMEOUT,5);
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


		if ($httpcode == 200)
		{
         if ($path==".")
         {
            return 1;
         }
         else
         {
            $result = json_decode($curl_response, true);
            # Check if Folder exists and return 1.
            foreach ($result as $temp_path)
            {
               if ($temp_path["path"] == $path && $temp_path["type"] == "dir")
                  return 1;
            }
         }

			return "Folder (".$path.") not found";
      }
      else
         return "Response received from ".$git_fqdn. " was '" . $httpcode ."'";

	}

   if ($type=="gitlab")
   {
      #### Verify that the Project exists and get ID
      $id = get_id_gitlab($git_fqdn, $project_name, $token);
      
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


      $response = 1;
      if ($path != ".") 
         $response = verify_path_gitlab($git_fqdn, $project_name, $token,  $id, $path, $branch);
   
      if ($response != 1)
      {
         echo '
               <div class="alert alert-warning alert-dismissible fade show" role="alert">
                  <b>Failed!</b> '.$response.'
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>';
         exit();
      }
   
      echo '{"id":'.$id.', "uuid":"'.md5($git_fqdn.$project_name.$path.$token.$branch.$format).'"}';


   }
 
   if ($type=="gitea")
   {

      #### Verify that the Project exists and get ID
      $id = verify_repo_gitea($git_fqdn, $project_name, $token,  $path, $branch);
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


      $verify = verify_path_gitea($git_fqdn, $project_name, $token,  $path, $branch);

      if ($verify != 1)
      {
         echo '
               <div class="alert alert-warning alert-dismissible fade show" role="alert">
                  <b>Failed!</b> '.$verify.'
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>';
         exit();
      }
   
      echo '{"id":'.$id.', "uuid":"'.md5($git_fqdn.$project_name.$path.$token.$branch.$format).'"}';

   }



	

?>
