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


	if( !(isset($_POST['gitlab_fqdn'])) )
	{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong>GitLab FQDN not Set.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';					
			exit();
	}
	else
	{
			$gitlab_fqdn = $_POST['gitlab_fqdn'];
	}

	if( !(isset($_POST['project_name'])) )
	{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong> Project_name not Set.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';					
			exit();
	}
	else
		$project_name = $_POST['project_name'];
	
	if( !(isset($_POST['token'])) )
	{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong> GitLab Token not Set.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';					
			exit();
	}
	else
		$project_name = $_POST['project_name'];
	
	if( !(isset($_POST['branch'])) )
	{
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Error!</strong> GitLab Branch was not Set.
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';					
			exit();
	}
	else
		$branch = $_POST['branch'];
	

	if( !(isset($_POST['path'])) )
	{
		echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<strong>Error!</strong> GitLab Path was not Set.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';					
		exit();
	}
	else
	{
		$path = $_POST['path'];
		if ($path == "")
			$path= "-";
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
			curl_setopt($curl,CURLOPT_TIMEOUT,4);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
			$curl_response = curl_exec($curl);
		
			if ($curl_response === false) {
				$info = curl_getinfo($curl);
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
	function get_path($project, $token, $id, $gitlab, $path, $branch) {
		$headers = array(
			'Content-Type: application/json',
			'Accept: application/json, text/javascript, */*; ',
			'PRIVATE-TOKEN: ' . $token
			);
	
			$url = $gitlab."/api/v4/projects/".$id."/repository/tree?ref=".$branch;
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl,CURLOPT_TIMEOUT,5);
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
				$found = "Folder Not Found";
				foreach ($result as $temp_path)
				{
					if ($temp_path["path"] == $path)
						$found = "Folder Found";
				}
				return $found;
			}
			else
				return "failure";

	}


	#### Verify that the Project exists and get ID
	$id = verify_project($project_name, $token, $gitlab_fqdn);

	if ($id == -2)
	{
		echo '
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					Unable to connect to <strong>"'.$gitlab_fqdn.'"</strong>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>';
		exit();
	}
	if ($id == -3)
	{
		echo '
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					Unable to authenticate user.
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>';
		exit();
	}
	if ($id == -1)
	{

		echo '
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Project <b>"'.$project_name.'"</b> not found 
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>';
		exit();
	}

	if ($path =="-") 
		$policy_content == "Folder Found";
	else
		$policy_content = get_path($project_name, $token,  $id, $gitlab_fqdn, $path, $branch);

	if ($policy_content == -2)
	{
		echo '
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					Unable to connect to <b>"'.$gitlab_fqdn.'"</b>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>';
		exit();
	}

	if ($policy_content == "failure")
	{

		echo '
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<b>Failed!</b> Path <b>"'.$path.'"</b> in branch "'.$branch.'" was not found 
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
		exit();
	}
	if ($policy_content == "Folder Found")
	{
		echo '
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<b>Success!</b> GitLab configuration is valid.
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
		exit();
		
	}
	else
	{
		echo '
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<b>Failed!</b> Folder <b>"'.$path.'"</b> in branch "'.$branch.'" was not found 
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
		exit();
		
}
	

?>
