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


$policies = [];
$asm_go = 0;
$dir = getcwd() . '/config_files/';
$scan = scandir($dir);

foreach($scan as $file)
{
    if (is_dir($dir.$file) and !($file=="." || $file==".."))
    {
		array_push ($policies, $file);
    }
}

$policies_count = sizeof($policies);
$config_files = [];
if ($policies_count >0 )
{

	foreach($policies as $file)
	{

		if(file_exists("config_files/".$file."/config.json"))
		{
			$string = file_get_contents("config_files/".$file."/config.json");
			$config = json_decode($string, true);

			$name =$config['name'];
			$gitlab =$config['gitlab'];
			$token =$config['token'];
			$user =$config['user'];
			$owasp =$config['owasp'];
			$warnings =$config['warnings'];
			$mode =$config['mode'];

		}
		else
		{
			$name = "-";
			$gitlab = "-";
			$token = "-";
			$user = "-";
			$owasp = "-";
			$warnings = "-";		
			$file = "-";
			$mode = "-";
		}

		array_push ($config_files, json_decode('{"name":"'.$name.'", "gitlab":"'.$gitlab.'", "token":"'.$token.'", "user":"'.$user.'", "owasp":"'.$owasp.'", "warnings":"'.$warnings.'", "mode":"'.$mode.'", "owasp":"'.$owasp.'", "file":"'.$file.'"}', true));
	}
}
else 
{
array_push ($config_files, json_decode('{"name":"No Policies found", "gitlab":"-", "token":"-", "user":"-", "owasp":"-", "warnings":"-",  "mode":"-", "owasp":"-", "file":"-"}', true));
}

?>
<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="">
      <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
      <meta name="generator" content="Hugo 0.84.0">
      <title>NAP Policy Review</title>
      <link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <!-- Bootstrap core CSS -->
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="css/flags16.css" rel="stylesheet">
      <link href="css/flags32.css" rel="stylesheet">

      <!-- Custom styles for this template -->
      <link href="dashboard.css" rel="stylesheet">

   </head>
   <body>
      <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
         <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#"><img src="images/app-protect.svg" width=32/> &nbsp; NGINX App Protect</a>
         <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
         <span class="navbar-toggler-icon"></span>
         </button>
         <div class="navbar-nav">
            <div class="nav-item text-nowrap">
               <a class="nav-link px-3" href="https://docs.nginx.com/nginx-app-protect/">Sign out</a>
            </div>
         </div>
      </header>
      <div class="container-fluid">
         <div class="row">
            <nav id="sidebarMenu" class="col-md-1 col-lg-1 d-md-block bg-light sidebar collapse">
               <div class="position-sticky pt-3">
                  <ul class="nav flex-column">
                     <li class="nav-item" style="background-color:#d2d8dc">
                        <a class="nav-link active" aria-current="page" href="#">
                        <span data-feather="home"></span>
                        Policies
                        </a>
                     </li>
                     <li class="nav-item" >
                        <a class="nav-link" href="violation.php">
                        <span data-feather="file"></span>
                        Violations
                        </a>
                     </li>
                  </ul>
               </div>
            </nav>
            <main class="col-md-11 ms-sm-auto col-lg-09 px-md-4">


									<div class="row">
										<div class="col-8">
												<div class="panel">
													<div class="title"> Parameters </div>
													<div class="line"></div>
													<div class="content">
														<table id="overall" class="table table-striped table-bordered" style="width:100%; font-size:12px">
															<thead>
																<tr>
																<th>Policies</th>
																<th style="width: 250px; text-align:left;">Git Repository</th>
																<th style="width: 80px; text-align:center;">Mode</th>
																<th style="width: 100px; text-align:center;;">Warnings</th>
																<th style="width: 100px; text-align:center;">OWASP TOP 10</th>
																</tr>
															</thead>
															<tbody style="text-align: center; vertical-align: middle;">
														
																
																<?php 	
																	foreach($config_files as $key)
																	{	
																			$result = $key["owasp"];	
																			$mode = $key["mode"];	
																			if ($mode =="blocking")
																				$mode_value = '<span  style="font-size:14px; color: green;"><b>Blocking</b></span>';
																			if ($mode =="transparent")
																				$mode_value = '<span  style="font-size:14px; color: red;"><b>Transparent</b<</span>';

																			if ($result <5)
																				$owasp_score = '<span class="badge" style="font-size:16px; padding:7px 15px; background-color:red">'.$result.'/10</span>';

																			if ($result >=6 && $result <8)
																				$owasp_score = '<span class="badge" style="font-size:16px; padding:7px 15px;background-color:orange ">'.$result.'/10</span>';

																			if ($result >=8)
																				$owasp_score = '<span class="badge" style="font-size:16px; padding:7px 15px; background-color:green;">'.$result.'/10</span>';

																			if ($result =="-")
																				$owasp_score = '<span class="badge" style="font-size:16px; padding:4px 10px 10px 8px; background-color:gray">-</span>';

																		echo '
																	<tr >
																		<td style="text-align: left; font-weight: bold; "><a href="policy.php?policy='.$key['name'].'">'.$key['name'].'</a></td>
																		<td style="text-align: left;">'.$key['gitlab'].'</td>
																		<td>'.$mode_value.'</td>
																		<td><b>'.$key['warnings'].'</b></td>
																		<td>'.$owasp_score.'</td>
																	</tr>';
																	}
																	?>
															</tbody>
														</table>
													</div>
												</div>
										</div>
												
										<div class="col-4">
												<div class="panel">
													<div class="title"> Import Policies </div>
													<div class="line"></div>
													<div class="content">

													
													<form  class="row g-3" action="import.php" method="post" autocomplete="off">


														<div class="col-md-8 violation_form vars">
															<label class="form-label">Repository Address</label>
															<input type="text" class="form-control" id="repo_address" placeholder="https://git.f5demo.cloud/nap-policies">
														</div>
														<div class="col-md-4 ip_form vars">
															<label class="form-label">Delete existing data</label>
																<select class="custom-select d-block w-100 form-control" name="delete_policies" required="">
																	<option value="yes">Yes</option>
																	<option value="no" selected="">No</option>
																</select>
														</div>
														<div class="col-md-6 signature_form vars">
															<label class="form-label">Git Username</label>
															<input type="text" class="form-control" id="git_user" placeholder="Username">
														</div>
														<div class="col-md-6 metachar_form vars">
															<label class="form-label">Git Token</label>
															<input type="password" class="form-control" id="git_token" placeholder="Password">
														</div>	
															<div class="row">
																<div class="col-md-9 mb-9" style="text-align:left">
																	<button class="btn btn-success" type="submit"> Import</button>
																</div>
																
															</div>	

														</form>

													</div>
												</div>

												<div class="panel">
													<div class="title"> Delete All Policies  </div>
													<div class="line"></div>
													<div class="content">

															<div class="row">
																<div class="col-md-9 mb-9" style="text-align:left">
																	<button class="btn btn-danger" id="delete" onclick="return confirm('This will delete the existing audit files')"> Delete</button>
																</div>
																
															</div>	

													</div>
												</div>											

										</div>

									</div>






            </main>
         </div>
      </div>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
      <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
      <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
   </body>

</html>

<script>
      $(document).ready(function () {
        $('#general').DataTable(
			{
				"searching": false,
				"info": false,
				"paging":false,
				"ordering":false,
			//	"order": [],
			//	"pageLength": 25
			}
		);
      });
      
   </script>

<script>
	$(document).ready(function() {
		var table = $('#overall').DataTable( {
				"autoWidth": false,
				"processing": true,
				"order": [[0, 'desc']]
		} );	
	} );
</script>
