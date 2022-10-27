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


   if(!isset($_GET["policy"]))
   {
     Header("Location: index.php");
     exit();
   }	
   
// Read the JSON file 
   $json = file_get_contents('policy2.json');
     
   // Decode the JSON file
   $json_data = json_decode($json,true);
   

   //  Reading the FileTypes and splitting them to allowed and disallowed
   $filetypes_allowed = array();
   $filetypes_disallowed = array();
   
   foreach($json_data["policy"]["filetypes"] as $values)
   {
   
     if(array_key_exists('allowed', $values))
     {
       if ($values["allowed"])
         $filetypes_allowed[] = $values;
       else
         $filetypes_disallowed[] = $values;
   
     }
     else
     {
		 $filetypes_allowed[] = $values;
		}
	}
	
  /* 
   $config_files = "config_files/" . $_GET["policy"];
   
   $string = file_get_contents($config_files."/templates.txt");
   $template = json_decode($string, true);
*/
   



	$minimumAccuracyForAutoAddedSignatures = $json_data["policy"]["signature-settings"]["minimumAccuracyForAutoAddedSignatures"];

   if($json_data["policy"]["general"]["caseInsensitive"])
      $caseInsensitive = "<i class='fa fa-check-square-o fa-2x green'></i>";
   else
      $caseInsensitive = "<i class='fa fa-times fa-2x black' ></i>";

   if(array_key_exists("applicationLanguage", $json_data["policy"]))
      $applicationLanguage = $json_data["policy"]["applicationLanguage"];
   else
      $applicationLanguage = "utf-8";

  if($json_data["policy"]["general"]["trustXff"])
      $trustXff = "<i class='fa fa-check-square-o fa-2x green'></i>";
   else
      $trustXff = "<i class='fa fa-times fa-2x black' ></i>";

	if(array_key_exists("customXffHeaders", $json_data["policy"]["general"]))
      $customXffHeaders = $json_data["policy"]["customXffHeaders"];
   else
      $customXffHeaders = "Not Configured";  

  if($json_data["policy"]["general"]["maskCreditCardNumbersInRequest"])
      $maskCreditCardNumbersInRequest = "<i class='fa fa-check-square-o fa-2x green'></i>";
   else
      $maskCreditCardNumbersInRequest = "<i class='fa fa-times fa-2x black' ></i>";
	  

	if($json_data["policy"]["data-guard"]["enabled"])
		$dataguard = '<span class="green">Enabled</span>';
 	else
		$dataguard = '<span class="red">Disabled</span>';

	if(array_key_exists("maskData", $json_data["policy"]["data-guard"]))
		if($json_data["policy"]["data-guard"]["maskData"])
			$maskData = "<i class='fa fa-check-square-o fa-2x green'></i>";
 		else
			$maskData = "<i class='fa fa-times fa-2x black' ></i>";
	else
    	$maskData = "N/A";	  
	  
	if(array_key_exists("usSocialSecurityNumbers", $json_data["policy"]["data-guard"]))
		if($json_data["policy"]["data-guard"]["maskData"])
			$usSocialSecurityNumbers = "<i class='fa fa-check-square-o fa-2x green'></i>";
 		else
			$usSocialSecurityNumbers = "<i class='fa fa-times fa-2x black' ></i>";
	else
    	$usSocialSecurityNumbers = "N/A";	 	  

	
	if(array_key_exists("creditCardNumbers", $json_data["policy"]["data-guard"]))
		if($json_data["policy"]["data-guard"]["maskData"])
			$creditCardNumbers = "<i class='fa fa-check-square-o fa-2x green'></i>";
 		else
			$creditCardNumbers = "<i class='fa fa-times fa-2x black' ></i>";
	else
    	$creditCardNumbers = "N/A";	 	  

	if(array_key_exists("enforcementUrls", $json_data["policy"]["data-guard"]))
		if( sizeof($json_data["policy"]["data-guard"]["enforcementUrls"])==0)
			$enforcementUrls = "List Empty";
		else
			$enforcementUrls = implode($json_data["policy"]["data-guard"]["enforcementUrls"], "<br>");
	else
		$enforcementUrls = "Not Configured";	
    		 	

	if(array_key_exists("server-technologies", $json_data["policy"]))
		$server_technologies = "var server_technologies = " . json_encode($json_data["policy"]["server-technologies"])  . " ;";
   	else
	   $server_technologies = "var server_technologies = [] ;";

   if(array_key_exists("json-validation-files", $json_data["policy"]))
	   $json_validation_files = "var json_validation_files = " . json_encode($json_data["policy"]["json-validation-files"])  . " ;";
	else
		$json_validation_files = "var json_validation_files = [] ;";

	$string = json_encode($json_data["policy"]["general"]["allowedResponseCodes"]); 
	$string = str_replace('[','[{"name":"',$string);
	$string = str_replace(',','"}, {"name":"',$string);
	$string = str_replace(']','"}]', $string);
	$allowed_responses = "var allowed_responses = " . $string . " ;";
	$blocking_settings = "var blocking_settings = " . json_encode($json_data["policy"]["blocking-settings"]["violations"]) . " ;";
	$evasion = "var evasion = " .  json_encode($json_data["policy"]["blocking-settings"]["evasions"]) . " ;";
	$compliance = "var compliance = " . json_encode($json_data["policy"]["blocking-settings"]["http-protocols"]) . " ;";
	$file_types = "var file_types = " . json_encode($filetypes_allowed)  . " ;";
	$file_types_disallowed = "var file_types_disallowed = " . json_encode($filetypes_disallowed)  . " ;";
	$methods = "var methods = " . json_encode($json_data["policy"]["methods"]) . " ;";
	$cookies = "var cookies = " . json_encode($json_data["policy"]["cookies"])  . " ;";
	$sensitive_param = "var sensitive_param = " . json_encode($json_data["policy"]["sensitive-parameters"])  . " ;";
	$headers = "var headers = " . json_encode($json_data["policy"]["headers"])  . " ;";
	$signature_sets = "var signature_sets = " . json_encode($json_data["policy"]["signature-sets"])  . " ;";
	$url = "var url = " . json_encode($json_data["policy"]["urls"]). " ;";
	$bot_defense = "var bot_defense = " . json_encode($json_data["policy"]["bot-defense"]["mitigations"]["classes"])  . " ;";
	
	$json_profiles = "var json_profiles = " . json_encode($json_data["policy"]["json-profiles"]) . " ;"; 


   	$parameters = "var parameters = " . json_encode($json_data["policy"]["parameters"]) . " ;";
   
  
	if(array_key_exists("signature-requirements", $json_data["policy"]))
	{
		$signature_requirements = "var signature_requirements = " . json_encode($json_data["policy"]["signature-requirements"])  . " ;";
		$signature_requirements_display = False;
	}	
	else
	{
		$signature_requirements = "var signature_requirements = [] ;";
		$signature_requirements_display = True;
	}	
 
    
	if(array_key_exists("threat-campaigns", $json_data["policy"]))
	{
		$threat_campaigns = "var threat_campaigns = " . json_encode($json_data["policy"]["threat-campaigns"])  . " ;";
	}	
	else
	{
		$threat_campaigns = "var threat_campaigns = [] ;";
	}	
	
	if(array_key_exists("signatures", $json_data["policy"]))
	{
		$signatures = "var signatures = " . json_encode($json_data["policy"]["signatures"])  . " ;";
	}	
	else
	{
		$signatures = "var signatures = [] ;";
	}		

	
	if(array_key_exists("whitelist-ips", $json_data["policy"]))
	{
		$whitelist_ips = "var whitelist_ips = " . json_encode($json_data["policy"]["whitelist-ips"])  . " ;";
	}	
	else
	{
		$whitelist_ips = "var whitelist_ips = [] ;";
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
                     <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="policies.php"  style="background-color:#d2d8dc">
                        <span data-feather="home"></span>
                        Policies
                        </a>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link" href="violation.php">
                        <span data-feather="file"></span>
                        Violations
                        </a>
                     </li>
                  </ul>
               </div>
            </nav>
            <main class="col-md-11 ms-sm-auto col-lg-09 px-md-4">
               <div class="row align-items-center">
                  <div class="title"> NAP Policy: <b><?php echo $_GET['policy']; ?> </b></div>
               </div>


               <div class="row">

                  <div class="d-flex align-items-start">
                    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#taboverview" type="button" role="tab" aria-controls="taboverview" aria-selected="true">Overview</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabsignatures" type="button" role="tab" aria-controls="tabsignatures" aria-selected="false">Signatures</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabparameters" type="button" role="tab" aria-controls="tabparameters" aria-selected="false">Parameters</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabfiletypes" type="button" role="tab" aria-controls="tabfiletypes" aria-selected="false">File Types</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabcookies" type="button" role="tab" aria-controls="tabcookies" aria-selected="false">Headers<br>Cookies</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabmethods" type="button" role="tab" aria-controls="tabmethods" aria-selected="false">Methods<br>Codes</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#taburls" type="button" role="tab" aria-controls="taburls" aria-selected="false">URLs</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabevasion" type="button" role="tab" aria-controls="tabevasion" aria-selected="false">Evasions<br>Compliance</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabprofiles" type="button" role="tab" aria-controls="tabprofiles" aria-selected="false">JSON/XML Profiles</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabip" type="button" role="tab" aria-controls="tabip" aria-selected="false">IP Exceptions</button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabbotdefense" type="button" role="tab" aria-controls="tabbotdefense" aria-selected="false">Bot Defense</button>
                    </div>
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show active" id="taboverview" role="tabpanel" aria-labelledby="taboverview-tab">
                           
                        	<div class="row">

                           		<div class="col-5">
                                	<div class="panel">
                                    	<div class="title"> General Settings </div>
                                    	<div class="line"></div>
                                    	<div class="content">
																				<table id="general" class="test table-striped " style="width:100%">
																				<thead>
																						<tr>
																						<th>Settings</th>
																						<th>Value</th>
																						</tr>
																				</thead>
																				<tbody>
																						<tr>
																						<td>Enforcement Mode</td>
																						<td> <b><?php echo $json_data["policy"]["enforcementMode"]; ?><b></td>
																						</tr>
																						<tr>
																						<td>Template</td>
																						<td><?php echo $json_data["policy"]["template"]["name"]; ?></td>
																						</tr>
																						<tr>
																						<td>Description</td>
																						<td><?php echo $json_data["policy"]["description"]; ?></td>
																						</tr>                                            
																						<tr>
																						<td>Max Cookie Length</td>
																						<td><?php echo $json_data["policy"]["cookie-settings"]["maximumCookieHeaderLength"]; ?></td>
																						</tr>
																						<tr>
																						<td>Max Header Length</td>
																						<td><?php echo $json_data["policy"]["header-settings"]["maximumHttpHeaderLength"]; ?></td>
																						</tr>
																						<tr>
																						<td>Application Language</td>
																						<td><?php echo $applicationLanguage; ?></td>
																						</tr>
																						<tr>
																						<td>Trust XFF</td>
																						<td><?php echo $trustXff; ?></td>
																						</tr>
																						<tr>
																						<td>XFF Headers</td>
																						<td><?php echo $customXffHeaders; ?></td>
																						</tr>
																						<tr>
																						<td>Mask Credit Card</td>
																						<td><?php echo $maskCreditCardNumbersInRequest; ?></td>
																						</tr>
																						<tr>
																						<td>Case Insensitive</td>
																						<td><?php echo $caseInsensitive ?></td>
																						</tr>
																						<tr>
																						<td>Enforcer</td>
																						<td>test1<br>test2<br>t2323</td>
																						</tr>                                               

																						


																				</tbody>
																				</table>
                                    	</div>
                                 	</div>
                              	</div>

                            	<div class="col-7">
                                 	<div class="panel">
                                    	<div class="title"> Blocking Settings </div>
                                    	<div class="line"></div>
                                    	<div class="content">
                                       		<table id="blocking" class="table table-striped table-bordered" style="width:100%">
                                          		<thead>
																								<tr>
																									<th>Decription</th>
																									<th style="width: 45px; text-align: center;">Alarm</th>
																									<th style="width: 45px; text-align: center;">Block</th>
																								</tr>
                                          		</thead>
                                       		</table>
                                    	</div>
                                 	</div>
                              	</div>

                            	<div class="col-3" hidden>
                                 	<div class="panel">
                                    	<div class="title"> Blocking Settings </div>
                                    	<div class="line"></div>
                                    	<div class="content">
		 																		<?php echo '<pre>' . json_encode($json_data["policy"]["urls"], JSON_PRETTY_PRINT) . '</pre>'; ?>
                                    	</div>
                                 	</div>
                              	</div>

								  
								

                              
                           </div>

                        </div>
                        <div class="tab-pane fade" id="tabparameters" role="tabpanel" aria-labelledby="tabparameters-tab">

                           <div class="row">
                              <div class="col-9">
                                 <div class="panel">
                                    <div class="title"> Parameters </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="parameters" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                          <tr>
                                             <th style="width:10px;"></th>
                                             <th>Parameter Name</th>
                                             <th style="width:55px; text-align:center;">Type</th>
                                             <th style="width:65px; text-align:center;">Level</th>
                                             <th style="width:55px; text-align:center;">Location</th>
                                             <th style="width:70px; text-align:center;" data-toggle="tooltip" data-original-title="Is the parameter conifgured as Sensitive">Sensitive </th>
                                             <th style="width:70px; text-align:center;" data-toggle="tooltip" data-original-title="If Attack Signatures have been enabled">Signatures </th>
                                             <th style="width:70px; text-align:center;" data-toggle="tooltip" data-original-title="If checking on Meta-characters has been enabled">MetaChar </th>
                                             <th style="width:90px; text-align:center;" data-toggle="tooltip" data-original-title="How many Attack Signatures have been overriden">Sig. Overrides</th>
                                             <th style="width:90px; text-align:center;" data-toggle="tooltip" data-original-title="How many Meta-characters have been overriden">MC. Overrides </th>
                                          </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                                 
                              <div class="col-3">
                                 <div class="panel">
                                    <div class="title"> Sensitive Parameters </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="sensitive_param" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>Parameter Name</th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                           </div>


                        </div>
                        <div class="tab-pane fade" id="tabfiletypes" role="tabpanel" aria-labelledby="tabfiletypes-tab">
                           
                           <div class="row">
                              <div class="col-9">
                                 <div class="panel">
                                    <div class="title"> File Types Allowed </div>
                                    <div class="line"></div>
                                    <div class="content">
                                    	<table id="file_type" class="table table-striped table-bordered" style="width:100%">
                                        	<thead>
												<tr>
													<th rowspan="2">File Type</th>
													<th rowspan="2" style="width:60px; text-align:center;">Type </th>
													<th rowspan="2" style="width:60px; text-align:center;">Allowed </th>
													<th colspan="4" style="width:70px; text-align:center;">Enable Check <i class="fa fa-info-circle" data-toggle="tooltip" data-original-title="Allowed URI Length for each File Type"></i></th>
													<th colspan="4" style="width:70px; text-align:center;">Configure Length <i class="fa fa-info-circle" data-toggle="tooltip" data-original-title="Allowed URI Length for each File Type"></i></th>
													<th rowspan="2" style="width:80px; text-align:center;">Check Responses <i class="fa fa-info-circle" data-toggle="tooltip" data-original-title="Allowed Request Length for each File Type"></i></th>
												</tr>
												<tr>
													<th style="width:70px; text-align:center;">URI</th>
													<th style="width:70px; text-align:center;">QueryString </th>
													<th style="width:90px; text-align:center;">PostData </th>									
													<th style="width:70px; text-align:center;">Request </th>
													<th style="width:70px; text-align:center;">URI </th>
													<th style="width:80px; text-align:center;">QueryString </th>
													<th style="width:70px; text-align:center;">PostData </th>
													<th style="width:70px; text-align:center;">Request </th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-3">
                                 <div class="panel">
                                    <div class="title"> File Types Blocked</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="file_types_disallowed" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>File Type</th>
                                                <th style="width: 90px; text-align:center;">Allowed <i class="fa fa-info-circle" data-toggle="tooltip" data-original-title="Last time the entity was modified"></i></th>									
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                                                            
                           </div>

                        </div>
                        <div class="tab-pane fade" id="tabcookies" role="tabpanel" aria-labelledby="tabcookies-tab">
                           
                           <div class="row">
                              <div class="col-6">
                                 <div class="panel">
                                    <div class="title"> Cookies </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="cookies" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th style="width: 15px; text-align: center;"></th>
                                                <th>Name</th>
                                                <th style="width:45px; text-align:center;">Type</th>
                                                <th style="width:90px; text-align: center;" data-toggle="tooltip" data-original-title="Whether the cookie is on Enforced or Allowed mode">Enforcement </th>
                                                <th style="width:75px; text-align:center;" data-toggle="tooltip" data-original-title="If Attack Signatures have been enabled">Signatures</th>
                                                <th style="width:90px; text-align:center;" data-toggle="tooltip" data-original-title="How many Attack Signatures have been overriden">Sig. Overrides </th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-6">
                                 <div class="panel">
                                    <div class="title"> Headers</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="headers" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th style="width: 15px; text-align: center;"></th>
                                                <th>Name</th>
                                                <th style="width:45px; text-align:center;">Type</th>
                                                <th style="width:75px; text-align:center;" data-toggle="tooltip" data-original-title="If Attack Signatures have been enabled">Signatures</th>
                                                <th style="width:90px; text-align:center;">Sig Overrides</th>
                                             </tr>
                                          </thead>
                                       </table>    
                                    </div>
                                 </div>
                              </div>
                                                            
                           </div>

                        </div>
                        <div class="tab-pane fade" id="tabmethods" role="tabpanel" aria-labelledby="tabmethods-tab">
                           
                           <div class="row">
                              <div class="col-4">
                                 <div class="panel">
                                    <div class="title"> Response Pages </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="response_pages" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>Page Type</th>
                                                <th style="width:125px; text-align:center;">Action Type</th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-3">
                                 <div class="panel">
                                    <div class="title"> HTTP Methods</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="methods" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>Allowed HTTP Methods</th>									
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-3">
                                 <div class="panel">
                                    <div class="title"> HTTP Response Codes </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="response" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                          <tr>
                                             <th>HTTP Response Codes</th>
                                          </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>                              


                           </div>

                        </div>
                        <div class="tab-pane fade" id="tabsignatures" role="tabpanel" aria-labelledby="tabsignatures-tab">
                           
                           <div class="row">
                              <div class="col-6">
									<div class="panel">
										<div class="title"> Signature Sets </div>
										<div class="line"></div>
										<div class="content">
											<table id="signature_sets" class="table table-striped table-bordered" style="width:100%">
												<thead>
													<tr>
													<th style="width: 15px; text-align: center;"></th>
													<th>Signature Sets</th>
													<th style="width: 45px; text-align: center;">Alarm</th>
													<th style="width: 45px; text-align: center;">Block</th>
													<th style="width: 60px; text-align: center;">Type</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>
								</div>


							    <div class="col-6">

		 							<p class="title"> Accuracy for auto added signatures: <span class="green"><b>Medium</b></span></p>
                                	<div class="panel">
                                    	<div class="title">Individual Signatures  </div>
                                    	<div class="line"></div>
                                    	<div class="content">
											<table id="signatures" class="table table-striped table-bordered" style="width:100%">
												<thead>
													<tr>
													<th>Signature Name</th>
													<th>Signature ID</th>
													<th>Tag</th>
													<th style="width: 45px; text-align: center;">Enabled</th>
													</tr>
												</thead>
											</table>											
                                    	</div>
                                 	</div>

									 <div class="panel <?php if($signature_requirements_display) echo 'display_none1';  ?>">
										<div class="title"> Signature Requirements </div>
										<div class="line"></div>
										<div class="content">
											<table id="signature_requirements" class="table table-striped table-bordered" style="width:100%">
												<thead>
													<tr>
													<th>Tag</th>
													<th style="text-align: center;">Max Revision Date</th>
													<th style="text-align: center;">Min Revision Date</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>

								</div>						  
                                
								
								<div class="col-6 ">

								</div>

                            </div>

                        </div>
                        <div class="tab-pane fade" id="taburls" role="tabpanel" aria-labelledby="taburls-tab">
                           
                           <div class="row">
                              <div class="col-9">
                                 <div class="panel">
                                    <div class="title"> URLs </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="urls" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th style="width:10px;"></th>
                                                <th style="width:50px; text-align:center;" data-toggle="tooltip" data-original-title="HTTP Protocol used (HTTP/HTTPS)">Proto</th>
                                                <th style="width:50px; text-align:center;" data-toggle="tooltip" data-original-title="Allowed Methods">Method</th>
                                                <th>URL</th>
                                                <th style="width:50px; text-align:center;" data-toggle="tooltip" data-original-title="Is the URL allowed?">Allowed</th>
                                                <th style="width:75px; text-align:center;" data-toggle="tooltip" data-original-title="If Attack Signatures have been enabled">Signatures</th>
                                                <th style="width:75px; text-align:center;" data-toggle="tooltip" data-original-title="If Meta-character Check has been enabled">Metachar</th>
                                                <th style="width:90px; text-align:center;" data-toggle="tooltip" data-original-title="How many Attack Signatures have been overriden">Sig. Overrides</th>
                                                <th style="width:90px; text-align:center;" data-toggle="tooltip" data-original-title="How many Meta-characters have been overriden">MC Overrides</th>

                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                                                           
                           </div>

                        </div>  
                        <div class="tab-pane fade" id="tabevasion" role="tabpanel" aria-labelledby="tabevasion-tab">
                           
                           <div class="row">
                              <div class="col-4">
                                 <div class="panel">
                                    <div class="title"> Evasions </div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="evasion" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>Evasion Technique Name</th>
                                                <th style="width: 60Px; text-align: center;">Enabled</th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-4">
                                 <div class="panel">
                                    <div class="title"> HTTP Compliance</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="compliance" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>HTTP Protocol Compliance</th>
                                                <th style="width: 60px; text-align: center;">Enabled</th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

							  
                              <div class="col-4">
                                 <div class="panel">
                                    <div class="title"> Threat Campaigns</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="threat_campaigns" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>Name</th>
                                                <th style="width: 60px; text-align: center;">Enabled</th>
                                                <th>DisplayName</th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>
							  
							  
                           </div>

                        </div>
                        <div class="tab-pane fade" id="tabip" role="tabpanel" aria-labelledby="tabip-tab">
                           
                           <div class="row">

                              <div class="col-5">
                                 <div class="panel">
                                    <div class="title">Whitelist IPs</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="whitelist_ips" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th style="width:100px;">IP</th>
                                                <th style="width:120px;">Mask</th>
                                                <th style="width:60px; text-align:center;" data-toggle="tooltip" data-original-title="Never Block this IP address">Block </th>
                                                <th style="width:60px; text-align:center;" data-toggle="tooltip" data-original-title="Never Log for this IP address">Log </th>
                                                <th style="text-align:center;">Description </th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>

							  <div class="col-3">
                                	<div class="panel">
                                    	<div class="title"> Dataguard </div>
                                    	<div class="line"></div>
                                    	<div class="content">
											<table id="dataguard" class="table table-striped table-bordered" style="width:100%">
												<thead>
														<tr>
														<th>Settings</th>
														<th>Value</th>
														</tr>
												</thead>
												<tbody>
														<tr>
														<td>Status</td>
														<td> <b><?php echo $dataguard; ?><b></td>
														</tr>
														<tr>
														<td>Enforcement Mode</td>
														<td><?php echo $json_data["policy"]["data-guard"]["enforcementMode"]; ?></td>
														</tr>
														<tr>
														<td>maskData</td>
														<td><?php echo $maskData; ?></td>
														</tr>                                            
														<tr>
														<td>usSocialSecurityNumbers</td>
														<td><?php echo $usSocialSecurityNumbers; ?></td>
														</tr>
														<tr>
														<td>creditCardNumbers</td>
														<td><?php echo $creditCardNumbers; ?></td>
														</tr>
														<tr>
														<td>enforcementUrls</td>
														<td><?php echo $enforcementUrls; ?></td>
														</tr>
												</tbody>
											</table>
                                    	</div>
                                 	</div>
                              </div>

                              <div class="col-4">
                                 <div class="panel">
                                    <div class="title">Bot Defense</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="bot_defense" class="table table-striped table-bordered" style="width:100%">
                                          <thead>
                                             <tr>
                                                <th>Name</th>
                                                <th style="width:80px; text-align:center;">Action</th>
                                             </tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>							  
							  
                           </div>

                        </div>
                        <div class="tab-pane fade" id="tabprofiles" role="tabpanel" aria-labelledby="tabprofiles-tab">
                           
                           <div class="row">

                              <div class="col-9">
                                 <div class="panel">
                                    <div class="title">JSON Profiles</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="json_profiles" class="table table-striped table-bordered" style="width:100%">
									   		<thead>
												<tr>
													<th rowspan="2" style="width: 15px; text-align: center;"></th>
													<th rowspan="2">Name </th>
													<th colspan="4" style="width:60px; text-align:center;">Defense Attributes </th>
													<th rowspan="2" style="width:70px; text-align:center;">Parsing Warnings</th>
													<th colspan="2" style="width:70px; text-align:center;">Enable Check </th>
													<th colspan="2" style="width:70px; text-align:center;">Overrides </th>
												</tr>
												<tr>
													<th style="width:90px; text-align:center;">ArrayLength</th>
													<th style="width:90px; text-align:center;">StructureDepth</th>
													<th style="width:90px; text-align:center;">TotalLength</th>
													<th style="width:90px; text-align:center;">ValueLength </th>
													<th style="width:70px; text-align:center;">Signatures </th>
													<th style="width:70px; text-align:center;">MetaChar </th>
													<th style="width:70px; text-align:center;">Signatures </th>
													<th style="width:70px; text-align:center;">MetaChar </th>
												</tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
                              </div>



                              <div class="col-3">
                                 <div class="panel">
                                    <div class="title">JSON Validation Files</div>
                                    <div class="line"></div>
                                    <div class="content">
                                       <table id="json_validation_files" class="table table-striped table-bordered" style="width:100%">
									   		<thead>
												<tr>
													<th>FileName</th>
													<th style="width:90px; text-align:center;">Content</th>
												</tr>
                                          </thead>
                                       </table>
                                    </div>
                                 </div>
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
</html>

<!-- SENSITIVE PARAMS -->
<script>
	<?php echo $sensitive_param; ?>

	$(document).ready(function() {
		var table = $('#sensitive_param').DataTable( {
			"searching": false,
			"info": false,
			"data": sensitive_param,
			"columns": [
				{ "className": 'bold',"data": "name" }
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	
	} );
</script>


<!-- RESPONSE PAGES -->
<script>

	<?php echo $response_pages; ?>

	$(document).ready(function() {
		var table = $('#response_pages').DataTable( {
			"data": response_pages,
			"searching": false,
			"info": false,
			"columns": [
				{ "className": 'bold',"data": "responsePageType" },
				{ "className": 'bold',"data": "responseActionType" }
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	

	} );
</script>

<!-- Bot Defense -->
<script>

	<?php echo $bot_defense; ?>

	$(document).ready(function() {
		var table = $('#bot_defense').DataTable( {
			"data": bot_defense,
			"searching": false,
			"info": false,
			"createdRow": function( row, data, dataIndex ) {
  
			if ( data['action'] == "block" )
				$('td', row).eq(1).html("<span class='red'><b>Block</span>");
			if ( data['action'] == "alarm" ) 
				$('td', row).eq(1).html("<span class='orange'><b>Alarm</b></span>");
			if ( data['action'] == "detect" ) 
				$('td', row).eq(1).html("<span class='green'><b>Detect Only</b></span>");
			if ( data['action'] == "ignore" ) 
				$('td', row).eq(1).html("<span class='blue'><b>Ignore</b></span>");
			},			
			"columns": [
				{ "className": 'bold',"data": "name" },
				{ "className": 'bold',"data": "action" }
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	

	} );
</script>


<!-- Whitelist IPs -->
<script>

	<?php echo $whitelist_ips; ?>

	$(document).ready(function() {
		var table = $('#whitelist_ips').DataTable( {
			"data": whitelist_ips,
			//"searching": false,
			"info": false,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['blockRequests'] == "always" )
				  $('td', row).eq(2).html("<span class='red'>Always</span>");
				if ( data['neverLogRequests'] == true )
					$('td', row).eq(3).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(3).html("<i class='fa fa-minus-circle fa-2x red ' ></i>");	
				if ( data['description'] == "" )
					$('td', row).eq(4).html("-");

			},
			"columns": [
				{ "className": 'bold',"data": "ipAddress" },
				{ "className": 'bold',"data": "ipMask" },
				{ "className": 'attacks',"data": "blockRequests" },
				{ "className": 'attacks',"data": "neverLogRequests", "defaultContent":false},
				{ "className": 'attacks',"data": "description", "defaultContent":"None" }
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	

	} );
</script>


<!-- Threat campaigns -->
<script>

	<?php echo $threat_campaigns; ?>

	$(document).ready(function() {
		var table = $('#threat_campaigns').DataTable( {
			"data": threat_campaigns,
			"searching": false,
			"info": false,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['isEnabled'] == true )
				  $('td', row).eq(1).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(1).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
			},			
			"columns": [
				{ "className": 'bold',"data": "name", "defaultContent":"-" },
				{ "className": 'attacks',"data": "isEnabled", "defaultContent":"-" },
				{ "className": 'attacks',"data": "displayName", "defaultContent":"-" },
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	

	} );
</script>


<!-- signature-requirements -->
<script>

	<?php echo $signature_requirements; ?>
	
	$(document).ready(function() {
		var table = $('#signature_requirements').DataTable( {
			"data": signature_requirements,
			"searching": false,
			"paging":false,
			"info": false,
			"columns": [
			{ "className": 'bold',"data": "tag" },
			{ "className": 'attacks', "data": "maxRevisionDatetime", "defaultContent": "None"},
			{ "className": 'attacks', "data": "minRevisionDatetime", "defaultContent": "None"},
			],
			"autoWidth": false,
			"processing": true,
			"language": {"processing": "Waiting.... " },
			"order": [[1, 'desc']]
		} );	

	} );
</script>


<!-- Evasion -->
<script>

	<?php echo $evasion; ?>
	
	$(document).ready(function() {
		var table = $('#evasion').DataTable( {
			"data": evasion,
			"searching": false,
			"info": false,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['enabled'] == true )
				  $('td', row).eq(1).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(1).html("<i class='fa fa-minus-square-o fa-2x red' ></i>");
			},
			  "columns": [
				{ "className": 'bold',"data": "description" },
				{ "className": 'attacks', "data": "enabled"},
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " },
				"order": [[1, 'desc']]
		} );	

	} );
</script>

<!-- Compliance -->
<script>
	<?php echo $compliance; ?>

	$(document).ready(function() {
		var table = $('#compliance').DataTable( {
			"data": compliance,
			"searching": false,
			"info": false,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['enabled'] == true )
				  $('td', row).eq(1).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(1).html("<i class='fa fa-minus-square-o fa-2x red' ></i>");
			  },
			  "columns": [
				{ "className": 'bold',"data": "description" },
				{ "className": 'attacks', "data": "enabled"}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " },
				"order": [[1, 'desc']]
		} );	

	} );
</script>

<!-- Blocking settings -->
<script>

	<?php echo $blocking_settings; ?>

	$(document).ready(function() {
		var table = $('#blocking').DataTable( {
			"data": blocking_settings,
			"createdRow": function( row, data, dataIndex ) {
  
				if ( data['alarm'] == true )
				  $('td', row).eq(1).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(1).html("<i class='fa fa-minus-square fa-2x red' ></i>");
				if ( data['block'] == true )
				  $('td', row).eq(2).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(2).html("<i class='fa fa-minus-square fa-2x red' ></i>");
			  },
			  "columns": [
				{ "className": 'bold', "data":"description" },
				{ "className": 'attacks', "data":"alarm"},
				{ "className": 'attacks', "data":"block"}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " },
				"order": [[1, 'desc']]
		} );	
	} );
</script>	

<!-- Signatures -->
<script>
	<?php echo $signatures; ?>

	$(document).ready(function() {
		var table = $('#signatures').DataTable( {
			"data": signatures,
			"searching": false,
			"info": false,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['enabled'] == true )
					$('td', row).eq(3).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
					$('td', row).eq(3).html("<i class='fa fa-minus-square fa-2x red' ></i>");
			},			
			"columns": [
				{"className": 'bold', "data": "name","defaultContent": "N/A"},
				{"className": 'bold', "data": "signatureId","defaultContent": "N/A"},
				{"className": 'bold', "data": "tag","defaultContent": "N/A"},
				{"className": 'bold', "data": "enabled","defaultContent": false}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );
	} );
</script>	

<!-- Allowed Responses -->
<script>
	<?php echo $allowed_responses; ?>

	$(document).ready(function() {
		var table = $('#response').DataTable( {
			"data": allowed_responses,
			"searching": false,
			"info": false,
			"columns": [
				{"className": 'bold', "data": "name"}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );
	} );
</script>	


<!-- File Types -->
<script>

	<?php echo $file_types; ?>

	$(document).ready(function() {
		var table = $('#file_type').DataTable( {
			"data": file_types,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['allow'] == true )
				  $('td', row).eq(2).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(2).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
				if ( data['checkUrlLength'] == true )
				  $('td', row).eq(3).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(3).html("<i class='fa fa-minus-square-o  fa-2x black' ></i>");
				  if ( data['checkQueryStringLength'] == true )
				  $('td', row).eq(4).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(4).html("<i class='fa fa-minus-square-o  fa-2x black' ></i>");
				if ( data['checkPostDataLength'] == true )
				  $('td', row).eq(5).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(5).html("<i class='fa fa-minus-square-o  fa-2x black' ></i>");
				if ( data['checkRequestLength'] == true )
				  $('td', row).eq(6).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(6).html("<i class='fa  fa-minus-square-o fa-2x black' ></i>");
				if ( data['responseCheck'] == true )
				  $('td', row).eq(11).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
				  $('td', row).eq(11).html("<i class='fa fa-minus-square-o  fa-2x black' ></i>");	  
			  },
			  "columns": [
				{ "className": 'bold',"data": "name" },
				{ "className": 'attacks', "data": "type"},
				{ "className": 'attacks', "data": "allowed"},
				{ "className": 'attacks',"data": "checkUrlLength"},
				{ "className": 'attacks',"data": "checkQueryStringLength"},
				{ "className": 'attacks',"data": "checkPostDataLength"},
				{ "className": 'attacks',"data": "checkRequestLength"},
				{ "className": 'attacks',"data": "urlLength", "defaultContent": "N/A"},
				{ "className": 'attacks',"data": "queryStringLength",  "defaultContent": "N/A"},
				{ "className": 'attacks',"data": "postDataLength", "defaultContent": "N/A"},
				{ "className": 'attacks',"data": "requestLength", "defaultContent": "N/A"},
				{ "className": 'attacks',"data": "responseCheck"}
				],
				"autoWidth": false,
				"processing": true,
				"order": [[0, 'asc']]
		} );	

	} );
</script>

<!-- Urls -->
<script>

	function format_url ( d ) {
		var contentprofiles = "N/A";
		var clickjackingProtection ="<i class='fa fa-times fa-2x black' ></i>";
		var disallowFileUploadOfExecutables ="<i class='fa fa-times fa-2x black' ></i>";
		var wildcardOrder = "N/A";
		var methodsOverrideOnUrlCheck = "<i class='fa fa-times fa-2x black' ></i>";
		var type = "N/A";
		var mandatoryBody = "<i class='fa fa-times fa-2x black' ></i>";
		var metacharacters = "N/A";
		var signatures = "N/A";

		if ("urlContentProfiles" in d)
		{
			var urlContentProfiles = "";
			for(var i in d.urlContentProfiles){
				var key = i;
				var contentprofile = "N/A";
				var val = d.urlContentProfiles[i];
				if ("ContentProfiles" in val)
					contentprofile = val.ContentProfiles;
				
				urlContentProfiles = urlContentProfiles + '"HeaderName" : <b> "' +val.headerName + '"</b> , ' + '"HeaderValue" : <b> "' + val.headerValue + '"</b>, '+ '"HeaderValue" : <b> "' +val.headerValue + '"</b>, '+ '"HeaderOrder" : <b> "' +val.headerOrder + '"</b>, '+ '"Type" : <b> "' +val.type + '"</b>, "ContentProfile" : <b> "' + contentprofile +'", </b> <br>';
			}
		}

		if ("clickjackingProtection" in d)
			if (d.clickjackingProtection == true)
				clickjackingProtection = "<i class='fa fa-check-circle fa-2x green'></i>";

			
		if ("disallowFileUploadOfExecutables" in d)
			if (d.disallowFileUploadOfExecutables == true)
				disallowFileUploadOfExecutables = "<i class='fa fa-check-circle fa-2x green'></i>";

		if ("methodsOverrideOnUrlCheck" in d)
			if (d.methodsOverrideOnUrlCheck == true)
				methodsOverrideOnUrlCheck = "<i class='fa fa-check-circle fa-2x green'></i>";
					
		if ("mandatoryBody" in d)
			if (d.mandatoryBody == true)
				mandatoryBody = "<i class='fa fa-check-circle fa-2x green'></i>";
					
		
				
		if ("wildcardOrder" in d)
			wildcardOrder = d.wildcardOrder



		if ("signatureOverrides" in d)
		{
			var signatures = "";
			for(var j in d.signatureOverrides){
				var sub_key = j;
				var sub_val = d.signatureOverrides[j];
				if (sub_key == "tag")
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"Tag" : <b> "' + sub_val.tag + '"</b><br>';
				else
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"SignatureID" : <b> "' + sub_val.signatureId + '"</b><br>';
			}				
		}

		if ("metacharOverrides" in d)
		{
			var metacharacters = "";
			for(var j in d.metacharOverrides){
				var sub_key = j;
				var sub_val = d.metacharOverrides[j];
				metacharacters = metacharacters + '"MetaChar" : <b> "' + sub_val.metachar + '" </b> - ' + '"isAllowed" : <b> "' + sub_val.isAllowed + '"</b><br>';
			}				
		}		


		return '<table cellpadding="5" cellspacing="0" border="0" class="table table-bordered">'+
			'<tr>'+
				'<td style="width:250px"><b>URL Content Profiles:</b></td>'+
				'<td >'+urlContentProfiles+'</td>'+
			'</tr>'+ 
			'<tr>'+
				'<td style="width:250px"><b>Type:</b></td>'+
				'<td >'+type+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Clickjacking Protection:</b></td>'+
				'<td >'+clickjackingProtection+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>DisallowFileUploadOfExecutables:</b></td>'+
				'<td >'+disallowFileUploadOfExecutables+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Mandatory Body:</b></td>'+
				'<td >'+mandatoryBody+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Signature Overrides:</b></td>'+
				'<td >'+signatures+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Metachar Overrides:</b></td>'+
				'<td >'+metacharacters+'</td>'+
			'</tr></table>';
	}

	<?php echo $url; ?>

	$(document).ready(function() {
		var table = $('#urls').DataTable( {
			"data": url,
			"createdRow": function( row, data, dataIndex ) {
				if ("metacharOverrides" in data)
					$('td', row).eq(8).html(data.metacharOverrides.length);
				else
					$('td', row).eq(8).html("0");
				if ("signatureOverrides" in data)
					$('td', row).eq(7).html(data.signatureOverrides.length);
				else
					$('td', row).eq(7).html("0");
				if ( data['attackSignaturesCheck'] == true )
					$('td', row).eq(5).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
					$('td', row).eq(5).html("<i class='fa fa-times fa-2x black' ></i>");
				if ( data['metacharsOnUrlCheck'] == true )
				  $('td', row).eq(6).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(6).html("<i class='fa fa-times fa-2x black' ></i>");
				if ( data['isAllowed'] == true )
				  $('td', row).eq(4).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(4).html("<i class='fa fa fa-times fa-2x black' ></i>");
			  },
			"columns": [
				{ "className":'details-control',"orderable": false,"data": null,"defaultContent": ''},
				{ "className": 'attacks',"data": "protocol"},
				{ "className": 'attacks',"data": "method"},
				{ "className": 'bold',"data": "name" },
				{ "className": 'attacks',"data": "isAllowed"},
				{ "className": 'attacks',"data": "attackSignaturesCheck"},
				{ "className": 'attacks',"data": "metacharsOnUrlCheck"},
				{ "className": 'attacks',"data": null,"defaultContent": ''},
				{ "className": 'attacks',"data": null,"defaultContent": ''}
				],
				"autoWidth": false,
				"processing": true,
				"order": [[2, 'asc']]
		} );	
		
		$('#urls tbody').on('click', 'td.details-control', function () {
			var tr = $(this).closest('tr');
			var row = table.row( tr );
	
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child( format_url(row.data()) ).show();
				tr.addClass('shown');
			}
		} );
	} );
</script>

<!-- Parameters -->
<script>
function format_parameter ( d ) {

	var allowEmptyValue  = "<i class='fa fa-times fa-2x black' ></i>";
	var allowRepeatedParameterName = "<i class='fa fa-times fa-2x black' ></i>";
	var arrayUniqueItemsCheck = "<i class='fa fa-times fa-2x black' ></i>";
	var attackSignaturesCheck = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMaxItemsInArray = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMaxValue = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMaxValueLength = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMetachars = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMetachars = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMinItemsInArray = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMinValue = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMinValueLength = "<i class='fa fa-times fa-2x black' ></i>";
	var checkMultipleOfValue = "<i class='fa fa-times fa-2x black' ></i>";
	var decodeValueAsBase64 = "<i class='fa fa-times fa-2x black' ></i>";
	var disallowFileUploadOfExecutables = "<i class='fa fa-times fa-2x black' ></i>";
	var enableRegularExpression = "<i class='fa fa-times fa-2x black' ></i>";
	var exclusiveMin = "<i class='fa fa-times fa-2x black' ></i>";
	var exclusiveMax = "<i class='fa fa-times fa-2x black' ></i>";
	var explodeObjectSerialization = "<i class='fa fa-times fa-2x black' ></i>";
	var isCookie = "<i class='fa fa-times fa-2x black' ></i>";
	var isHeader = "<i class='fa fa-times fa-2x black' ></i>";
	var mandatory = "<i class='fa fa-times fa-2x black' ></i>";



	var objectSerializationStyle = "N/A"
	var arraySerializationFormat = "N/A"
	var dataType = "N/A"
	var hostNameRepresentation = "N/A"
	var maximumValue = "N/A"
	var minItemsInArray = "N/A"
	var minimumLength = "N/A"
	var multipleOf = "N/A"
	var staticValues = "N/A"
	var parameterEnumValues = "N/A"
	var wildcardOrder = "N/A"
	var contentProfile = "N/A"
	

	if ("allowEmptyValue" in d)
		if (d.allowEmptyValue == true)
			allowEmptyValue = "<i class='fa fa-check-circle fa-2x green'></i>";


	if ("allowRepeatedParameterName" in d)
		if (d.allowRepeatedParameterName == true)
			allowRepeatedParameterName = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("arrayUniqueItemsCheck" in d)
		if (d.arrayUniqueItemsCheck == true)
			arrayUniqueItemsCheck = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("attackSignaturesCheck" in d)
		if (d.attackSignaturesCheck == true)
			attackSignaturesCheck = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMaxItemsInArray" in d)
		if (d.checkMaxItemsInArray == true)
			checkMaxItemsInArray = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMaxValue" in d)
		if (d.checkMaxValue == true)
			checkMaxValue = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMaxValueLength" in d)
		if (d.checkMaxValueLength == true)
			checkMaxValueLength = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMetachars" in d)
		if (d.checkMetachars == true)
			checkMetachars = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMetachars" in d)
		if (d.checkMetachars == true)
			checkMetachars = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMinItemsInArray" in d)
		if (d.checkMinItemsInArray == true)
			checkMinItemsInArray = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMinValue" in d)
		if (d.checkMinValue == true)
			checkMinValue = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMinValueLength" in d)
		if (d.checkMinValueLength == true)
			checkMinValueLength = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("checkMultipleOfValue" in d)
		if (d.checkMultipleOfValue == true)
			checkMultipleOfValue = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("decodeValueAsBase64" in d)
		if (d.decodeValueAsBase64 == true)
			decodeValueAsBase64 = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("disallowFileUploadOfExecutables" in d)
		if (d.disallowFileUploadOfExecutables == true)
			disallowFileUploadOfExecutables = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("enableRegularExpression" in d)
		if (d.enableRegularExpression == true)
			enableRegularExpression = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("exclusiveMin" in d)
		if (d.exclusiveMin == true)
			exclusiveMin = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("exclusiveMax" in d)
		if (d.exclusiveMax == true)
			exclusiveMax = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("explodeObjectSerialization" in d)
		if (d.explodeObjectSerialization == true)
			explodeObjectSerialization = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("isCookie" in d)
		if (d.isCookie == true)
			isCookie = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("isHeader" in d)
		if (d.clicisHeaderkjackingProtection == true)
			isHeader = "<i class='fa fa-check-circle fa-2x green'></i>";

	if ("mandatory" in d)
		if (d.mandatory == true)
		mandatory = "<i class='fa fa-check-circle fa-2x green'></i>";







	if ("objectSerializationStyle" in d)
		objectSerializationStyle = d.objectSerializationStyle
	
	if ("arraySerializationFormat" in d)
		arraySerializationFormat = d.arraySerializationFormat


	if ("dataType" in d)
		dataType = d.dataType
	
	if ("hostNameRepresentation" in d)
		hostNameRepresentation = d.hostNameRepresentation


	if ("maximumValue" in d)
		maximumValue = d.maximumValue
	
	if ("minItemsInArray" in d)
		minItemsInArray = d.minItemsInArray

	if ("minimumLength" in d)
		minimumLength = d.minimumLength


	if ("multipleOf" in d)
		multipleOf = d.multipleOf
	
	if ("staticValues" in d)
		staticValues = d.staticValues


	if ("parameterEnumValues" in d)
		parameterEnumValues = d.parameterEnumValues
	
	if ("wildcardOrder" in d)
		wildcardOrder = d.wildcardOrder
	
	
	if ("contentProfile" in d)
		contentProfile = d.contentProfile.contentProfile.name
	
	var signatures = "N/A";
		if ("signatureOverrides" in d)
		{
			var signatures = "";
			for(var j in d.signatureOverrides){
				var sub_key = j;
				var sub_val = d.signatureOverrides[j];
				if (sub_key == "tag")
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"Tag" : <b> "' + sub_val.tag + '"</b><br>';
				else
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"SignatureID" : <b> "' + sub_val.signatureId + '"</b><br>';
			}				
		}
		var metacharacters = "N/A";

		if ("metacharOverrides" in d)
		{
			var metacharacters = "";
			for(var j in d.metacharOverrides){
				var sub_key = j;
				var sub_val = d.metacharOverrides[j];
				metacharacters = metacharacters + '"MetaChar" : <b> "' + sub_val.metachar + '" </b> - ' + '"isAllowed" : <b> "' + sub_val.isAllowed + '"</b><br>';
			}				
		}		


		return '<table cellpadding="5" cellspacing="0" border="0" class="table table-bordered">'+
			'<tr>'+
				'<td style="width:250px"><b>Signature Overrides:</b></td>'+
				'<td >'+signatures+'</td>'+
				'<td style="width:250px"><b>allowEmptyValue:</b></td>'+
				'<td >'+allowEmptyValue+'</td>'+
				'<td style="width:250px"><b>checkMinItemsInArray:</b></td>'+
				'<td >'+checkMinItemsInArray+'</td>'+
			'</tr>'+ 
			'<tr>'+
				'<td style="width:250px"><b>Metacharacters Overrides:</b></td>'+
				'<td >'+metacharacters+'</td>'+
				'<td style="width:250px"><b>wildcardOrder:</b></td>'+
				'<td >'+wildcardOrder+'</td>'+
				'<td style="width:250px"><b>checkMaxItemsInArray:</b></td>'+
				'<td >'+checkMaxItemsInArray+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>dataType :</b></td>'+
				'<td >'+dataType+'</td>'+
				'<td style="width:250px"><b>isHeader:</b></td>'+
				'<td >'+isHeader+'</td>'+
				'<td style="width:250px"><b>checkMaxValue:</b></td>'+
				'<td >'+checkMaxValue+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b> contentProfile:</b></td>'+
				'<td >'+contentProfile+'</td>'+
				'<td style="width:250px"><b> isCookie:</b></td>'+
				'<td >'+isCookie+'</td>'+
				'<td style="width:250px"><b>checkMaxValueLength:</b></td>'+
				'<td >'+checkMaxValueLength+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>decodeValueAsBase64:</b></td>'+
				'<td >'+decodeValueAsBase64+'</td>'+
				'<td style="width:250px"><b>mandatory:</b></td>'+
				'<td >'+mandatory+'</td>'+
				'<td style="width:250px"><b>checkMetachars:</b></td>'+
				'<td >'+checkMetachars+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>disallowFileUploadOfExecutables:</b></td>'+
				'<td >'+disallowFileUploadOfExecutables+'</td>'+
				'<td style="width:250px"><b>allowRepeatedParameterName:</b></td>'+
				'<td >'+allowRepeatedParameterName+'</td>'+
				'<td style="width:250px"><b>checkMinValue:</b></td>'+
				'<td >'+checkMinValue+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>hostNameRepresentation:</b></td>'+
				'<td >'+hostNameRepresentation+'</td>'+
				'<td style="width:250px"><b>maximumValue:</b></td>'+
				'<td >'+maximumValue+'</td>'+
				'<td style="width:250px"><b>checkMinValueLength:</b></td>'+
				'<td >'+checkMinValueLength+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>minimumLength:</b></td>'+
				'<td >'+minimumLength+'</td>'+
				'<td style="width:250px"><b>minItemsInArray:</b></td>'+
				'<td >'+minItemsInArray+'</td>'+
				'<td style="width:250px"><b>checkMultipleOfValue:</b></td>'+
				'<td >'+checkMultipleOfValue+'</td>'+
			'</tr>'+			
			'<tr>'+
				'<td style="width:250px"><b>multipleOf:</b></td>'+
				'<td >'+multipleOf+'</td>'+
				'<td style="width:250px"><b>parameterEnumValues:</b></td>'+
				'<td >'+parameterEnumValues+'</td>'+
				'<td style="width:250px"><b>objectSerializationStyle:</b></td>'+
				'<td >'+objectSerializationStyle+'</td>'+
			'</tr>'+	
			'<tr>'+
				'<td style="width:250px"><b>staticValues:</b></td>'+
				'<td >'+staticValues+'</td>'+
				'<td style="width:250px"><b>minItemsInArray:</b></td>'+
				'<td >'+minItemsInArray+'</td>'+
				'<td style="width:250px"><b>arraySerializationFormat:</b></td>'+
				'<td >'+arraySerializationFormat+'</td>'+
			'</tr>'+	
			'<tr>'+
				'<td style="width:250px"><b>arrayUniqueItemsCheck:</b></td>'+
				'<td >'+arrayUniqueItemsCheck+'</td>'+
				'<td style="width:250px"><b>enableRegularExpression:</b></td>'+
				'<td >'+enableRegularExpression+'</td>'+
				'<td style="width:250px"><b>exclusiveMin:</b></td>'+
				'<td >'+exclusiveMin+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>exclusiveMax:</b></td>'+
				'<td >'+exclusiveMax+'</td>'+
				'<td style="width:250px"><b>explodeObjectSerialization:</b></td>'+
				'<td >'+explodeObjectSerialization+'</td>'+
				'<td style="width:250px"></td>'+
				'<td </td>'+				
			'<tr></table>';
	}
	<?php echo $parameters; ?>

	$(document).ready(function() {
		var table = $('#parameters').DataTable( {
			"data": parameters,
			"createdRow": function( row, data, dataIndex ) {
				if ("metacharOverrides" in data)
					$('td', row).eq(9).html(data.metacharOverrides.length);
				else
					$('td', row).eq(9).html("0");
				if ("signatureOverrides" in data)
					$('td', row).eq(8).html(data.signatureOverrides.length);
				else
					$('td', row).eq(8).html("0");			

				if ( data['attackSignaturesCheck'] == true )
				  $('td', row).eq(6).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(6).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
				if ( data['metacharsOnParameterValueCheck'] == true)
				  $('td', row).eq(7).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(7).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
				if ( data['sensitiveParameter'] == true )
				  $('td', row).eq(5).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(5).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
			  },
			  "columns": [
				{"className":'details-control',"orderable":false,"data":null,"defaultContent": ''},
				{ "className": 'bold',"data": "name","defaultContent": '' },
				{ "className": 'attacks',"data": "valueType","defaultContent": ''},
				{ "className": 'attacks',"data": "level","defaultContent": ''},
				{ "className": 'attacks',"data": "parameterLocation","defaultContent": ''},
				{ "className": 'attacks',"data": "sensitiveParameter","defaultContent": ''},
				{ "className": 'attacks',"data": "attackSignaturesCheck","defaultContent": ''},
				{ "className": 'attacks',"data": "metacharsOnParameterValueCheck","defaultContent": ''},
				{ "className": 'attacks',"data": null,"defaultContent": 0},
				{ "className": 'attacks',"data": null,"defaultContent": 0}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " },
				"order": [[1, 'asc']]
		} );	

    $('#parameters tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );
 
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format_parameter(row.data()) ).show();
            tr.addClass('shown');
        }
    } );


	} );
</script>

<!-- Signature Sets -->
<script>

	function format_signature_sets ( d ) {
		var filter = "N/A";
		var systems = "N/A";
		var signatures = "N/A";
		var table_add = "";
		var line_add = "";
		if ("signatureSet" in d)
		{
			if ("filter" in d.signatureSet)
			{
				var filter = "";
				for(var j in d.signatureSet.filter){
					var sub_key = j;
					var sub_val = d.signatureSet.filter[j];
					if (sub_key == "attackType")
						filter = filter + '"'+sub_key+'" : <b> "' + sub_val.name + '"</b><br>';
					else
						filter = filter + '"'+sub_key+'" : <b> "' + sub_val + '"</b><br>';
				}				
			}
			if ("signatures" in d.signatureSet)
			{
				var signatures = "";
				for(var j in d.signatureSet.signatures){
					var sub_key = j;
					var sub_val = d.signatureSet.signatures[j];
					signatures = signatures + '"signatureId" : <b> "' + sub_val.signatureId + '"</b><br>';
				}				
			}
			if ("systems" in d.signatureSet)
			{
				var systems = "";
				for(var j in d.signatureSet.systems){
					var sub_key = j;
					var sub_val = d.signatureSet.systems[j];
					systems = systems + '"name" : <b> "' + sub_val.name + '"</b><br>';
				}				
			}
		}
	/*	for(var i in d.signatureSet){
			var key = i;
			var val = d.signatureSet[i];
			for(var j in val){
				var sub_key = j;
				var sub_val = val[j];
				console.log(sub_key);
			}
		}
	*/	
		return '<table cellpadding="5" cellspacing="0" border="0" class="table table-bordered">'+
			'<tr>'+
				'<td style="width:150px"><b>Filter:</b></td>'+
				'<td >'+filter+'</td>'+
			'</tr>'+ 
			'<tr>'+
				'<td style="width:150px"><b>Systems:</b></td>'+
				'<td >'+systems+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:150px"><b>Individual Signatures:</b></td>'+
				'<td >'+signatures+'</td>'+
			'</tr>'+
			table_add +
			'</table>';
	}


	<?php echo $signature_sets; ?>

	$(document).ready(function() {
		var table = $('#signature_sets').DataTable( {
			"data": signature_sets,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['alarm'] == true )
				  $('td', row).eq(2).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(2).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
				if ( data['block'] == true )
				  $('td', row).eq(3).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				  $('td', row).eq(3).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
			  },
			  "columns": [
				{"className":'details-control',"orderable":false,"data":null,"defaultContent": ''},		
				{"className":'bold',"data": "name" },
				{"className":'attacks',"data": "alarm"},
				{"className":'attacks',"data": "block"},
				{"className":'attacks',"data": "signatureSet.type", "defaultContent": "default"}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );
		$('#signature_sets tbody').on('click', 'td.details-control', function () {
			var tr = $(this).closest('tr');
			var row = table.row( tr );
	
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child( format_signature_sets(row.data()) ).show();
				tr.addClass('shown');
			}
		} );

	} );
</script>

<!--COOKIES -->
<script>

	function format_cookie ( d ) {
		var wildcardOrder = "N/A";
		var accessibleOnlyThroughTheHttpProtocol ="<i class='fa fa-times fa-2x black' ></i>";
		var decodeValueAsBase64 ="<i class='fa fa-times fa-2x black' ></i>";
		var insertSameSiteAttribute = "<i class='fa fa-times fa-2x black' ></i>";
		var securedOverHttpsConnection = "<i class='fa fa-times fa-2x black' ></i>";
		var signatures = "N/A";

		if ("securedOverHttpsConnection" in d)
			if (d.securedOverHttpsConnection == true)
				securedOverHttpsConnection = "<i class='fa fa-check-circle fa-2x green'></i>";

			
		if ("insertSameSiteAttribute" in d)
			if (d.insertSameSiteAttribute == true)
				insertSameSiteAttribute = "<i class='fa fa-check-circle fa-2x green'></i>";

		if ("decodeValueAsBase64" in d)
			if (d.decodeValueAsBase64 == true)
				decodeValueAsBase64 = "<i class='fa fa-check-circle fa-2x green'></i>";
					
		if ("accessibleOnlyThroughTheHttpProtocol" in d)
			if (d.accessibleOnlyThroughTheHttpProtocol == true)
				accessibleOnlyThroughTheHttpProtocol = "<i class='fa fa-check-circle fa-2x green'></i>";
					
						
		if ("wildcardOrder" in d)
			wildcardOrder = d.wildcardOrder



		if ("signatureOverrides" in d)
		{
			var signatures = "";
			for(var j in d.signatureOverrides){
				var sub_key = j;
				var sub_val = d.signatureOverrides[j];
				if (sub_key == "tag")
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"Tag" : <b> "' + sub_val.tag + '"</b><br>';
				else
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"SignatureID" : <b> "' + sub_val.signatureId + '"</b><br>';
			}				
		}

		return '<table cellpadding="5" cellspacing="0" border="0" class="table table-bordered">'+
			'<tr>'+
				'<td style="width:250px"><b>HTTPOnly:</b></td>'+
				'<td >'+accessibleOnlyThroughTheHttpProtocol+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>DecodeValue As Base64:</b></td>'+
				'<td >'+decodeValueAsBase64+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Insert SameSite:</b></td>'+
				'<td >'+insertSameSiteAttribute+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Secured over HTTPS:</b></td>'+
				'<td >'+securedOverHttpsConnection+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>Signature Overrides:</b></td>'+
				'<td >'+signatures+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>wildcardOrder:</b></td>'+
				'<td >'+wildcardOrder+'</td>'+
			'</tr></table>';

		}


	<?php echo $cookies; ?>
 
	$(document).ready(function() {
		var table = $('#cookies').DataTable( {
		"data": cookies,
		"createdRow": function( row, data, dataIndex ) {
			if ("signatureOverrides" in data)
					$('td', row).eq(5).html(data.signatureOverrides.length);
				else
					$('td', row).eq(5).html("0");
			if ( data['attackSignaturesCheck'] == true )
			  $('td', row).eq(4).html("<i class='fa fa-check-circle fa-2x green'></i>");
			else 
				$('td', row).eq(4).html("<i class='fa fa-times fa-2x' ></i>");				  
		  },
		  "columns": [
		    { "className":'details-control',"orderable":false,"data":null,"defaultContent": ''},
			{ "className": 'bold',"data": "name" },
			{ "className": 'attacks',"data": "type", "defaultContent": "explicit"},
			{ "className": 'attacks',"data": "enforcementType", "defaultContent": "allow"},
			{ "className": 'attacks',"data": "attackSignaturesCheck", "defaultContent": true},
			{ "className": 'attacks',"data": "num_of_sign_overides", "defaultContent": 0},
			],
			"autoWidth": false,
			"processing": true,
			"language": {"processing": "Waiting.... " },
			"order": [[1, 'asc']]
		} );	

		$('#cookies tbody').on('click', 'td.details-control', function () {
			var tr = $(this).closest('tr');
			var row = table.row( tr );
	
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child( format_cookie(row.data()) ).show();
				tr.addClass('shown');
			}
		} );


	} );
</script>

<!-- HEADERS -->
<script>

	function format_header ( d ) {
		var wildcardOrder = "N/A";
		var urlNormalization ="<i class='fa fa-times fa-2x black' ></i>";
		var percentDecoding ="<i class='fa fa-times fa-2x black' ></i>";
		var normalizationViolations = "<i class='fa fa-times fa-2x black' ></i>";
		var htmlNormalization = "<i class='fa fa-times fa-2x black' ></i>";
		var decodeValueAsBase64 = "<i class='fa fa-times fa-2x black' ></i>";
		var mandatory = "<i class='fa fa-times fa-2x black' ></i>";
		var signatures = "N/A";
		

		if ("urlNormalization" in d)
			if (d.urlNormalization == true)
				urlNormalization = "<i class='fa fa-check-circle fa-2x green'></i>";

			
		if ("percentDecoding" in d)
			if (d.percentDecoding == true)
				percentDecoding = "<i class='fa fa-check-circle fa-2x green'></i>";

		if ("normalizationViolations" in d)
			if (d.normalizationViolations == true)
				normalizationViolations = "<i class='fa fa-check-circle fa-2x green'></i>";
					
		if ("htmlNormalization" in d)
			if (d.htmlNormalization == true)
				htmlNormalization = "<i class='fa fa-check-circle fa-2x green'></i>";

		if ("mandatory" in d)
			if (d.htmlNormalization == true)
				htmlNormalization = "<i class='fa fa-check-circle fa-2x green'></i>";				
						
		if ("wildcardOrder" in d)
			wildcardOrder = d.wildcardOrder



		if ("signatureOverrides" in d)
		{
			var signatures = "";
			for(var j in d.signatureOverrides){
				var sub_key = j;
				var sub_val = d.signatureOverrides[j];
				if (sub_key == "tag")
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"Tag" : <b> "' + sub_val.tag + '"</b><br>';
				else
					signatures = signatures + '"name" : <b> "' + sub_val.name + '" </b> - ' + '"SignatureID" : <b> "' + sub_val.signatureId + '"</b><br>';
			}				
		}

		return '<table cellpadding="5" cellspacing="0" border="0" class="table table-bordered">'+
		'<tr>'+
				'<td style="width:250px"><b>Signature Overrides:</b></td>'+
				'<td >'+signatures+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>mandatory:</b></td>'+
				'<td >'+mandatory+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>htmlNormalization:</b></td>'+
				'<td >'+htmlNormalization+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>normalizationViolations:</b></td>'+
				'<td >'+normalizationViolations+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>percentDecoding:</b></td>'+
				'<td >'+percentDecoding+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:250px"><b>urlNormalization:</b></td>'+
				'<td >'+urlNormalization+'</td>'+
			'</tr>'+			
			'<tr>'+
				'<td style="width:250px"><b>wildcardOrder:</b></td>'+
				'<td >'+wildcardOrder+'</td>'+
			'</tr></table>';

		}	
 

	<?php echo $headers; ?>
 
	$(document).ready(function() {
		var table = $('#headers').DataTable( {
		"data": headers,
		"createdRow": function( row, data, dataIndex ) {
			if ( data['checkSignatures'] == true )
			  $('td', row).eq(3).html("<i class='fa fa-check-circle fa-2x green'></i>");
			else 
			  $('td', row).eq(3).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
	  
		  },
		  "columns": [
		    { "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''},
			{ "className": 'bold',"data": "name", "defaultContent": '' },
			{ "className": 'attacks',"data": "type", "defaultContent": ''},
			{ "className": 'attacks',"data": "checkSignatures", "defaultContent": ''},
			{ "className": 'attacks',"data": "num_of_sign_overides", "defaultContent": 0}
			],
			"autoWidth": false,
			"processing": true,
			"language": {"processing": "Waiting.... " },
			"order": [[1, 'asc']]
		} );	

		$('#headers tbody').on('click', 'td.details-control', function () {
			var tr = $(this).closest('tr');
			var row = table.row( tr );
	
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child( format_header(row.data()) ).show();
				tr.addClass('shown');
			}
		} );


	} );
</script>

<!-- METHODS-->
<script>
	<?php echo $methods; ?>
	$(document).ready(function() {
		var table = $('#methods').DataTable( {
			"data": methods,
			"searching": false,
			"info": false,
			"columns": [
				{ "className": 'bold',"data": "name" },
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	
	} );
</script>

<!-- Disallowed file types -->
<script>

	<?php echo $file_types_disallowed; ?>

	$(document).ready(function() {
		var table = $('#file_types_disallowed').DataTable( {
			"data": file_types_disallowed,
			"createdRow": function( row, data, dataIndex ) {
				$('td', row).eq(1).html("<i class='fa fa-minus-circle fa-2x red' ></i>");

			  },			
			"columns": [
				{ "className": 'bold',"data": "name" },
				{  "className": 'attacks',"data": "allowed"}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	

	} );
</script>



<!-- JSON Profiles -->
<script>

	function format_json_profiles ( d ) {
		var handleJsonValuesAsParameters ="<i class='fa fa-times fa-2x black' ></i>";
		var hasValidationFiles = "<i class='fa fa-times fa-2x black' ></i>";
		var description = "-";
		var validationFiles = "N/A";

		if ("handleJsonValuesAsParameters" in d)
			if (d.handleJsonValuesAsParameters == true)
				handleJsonValuesAsParameters = "<i class='fa fa-check-circle fa-2x green'></i>";

		if (d.description != "")
			description = d.description;

		if ("hasValidationFiles" in d)
			if (d.hasValidationFiles == true)
				hasValidationFiles = "<i class='fa fa-check-circle fa-2x green'></i>";			

		if (d.validationFiles.length >11110)
		{

		}	

	
		return '<table cellpadding="5" cellspacing="0" border="0" class="table table-bordered">'+
			'<tr>'+
				'<td style="width:250px"><b>Handle JsonValues As Parameters:</b></td>'+
				'<td >'+handleJsonValuesAsParameters+'</td>'+
			'</tr>'+ 
			'<tr>'+
				'<td style="width:250px"><b>Has Validation Files:</b></td>'+
				'<td >'+hasValidationFiles+'</td>'+
			'</tr>'+
			'<tr>'+
				'<td style="width:150px"><b>Description:</b></td>'+
				'<td >'+description+'</td>'+
			'</tr>'+
			'</table>';
	}


	<?php echo $json_profiles; ?>

	$(document).ready(function() {
		var table = $('#json_profiles').DataTable( {
			"data": json_profiles,
			"createdRow": function( row, data, dataIndex ) {
				if ( data['defenseAttributes']['tolerateJSONParsingWarnings'] == true )
					$('td', row).eq(6).html("<i class='fa fa-check-square-o fa-2x green'></i>");
				else 
					$('td', row).eq(6).html("<i class='fa fa-minus-square-o fa-2x red' ></i>");

				if ( data['attackSignaturesCheck'] == true )
					$('td', row).eq(7).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
				 	$('td', row).eq(7).html("<i class='fa fa-minus-circle fa-2x red' ></i>");

				if ( data['metacharElementCheck'] == true )
					$('td', row).eq(8).html("<i class='fa fa-check-circle fa-2x green'></i>");
				else 
					$('td', row).eq(8).html("<i class='fa fa-minus-circle fa-2x red' ></i>");
				if ("signatureOverrides" in data)
					$('td', row).eq(9).html(data.signatureOverrides.length);
				else
					$('td', row).eq(9).html("0");
				if ("metacharOverrides" in data)
					$('td', row).eq(10).html(data.metacharOverrides.length);
				else
					$('td', row).eq(10).html("0");

			  },
			  "columns": [
				{"className":'details-control',"orderable":false,"data":null,"defaultContent": ''},		
				{"className":'bold',"data": "name"},
				{"className":'attacks',"data": "defenseAttributes.maximumArrayLength", "defaultContent": 1000 },
				{"className":'attacks',"data": "defenseAttributes.maximumStructureDepth", "defaultContent": 1000 },
				{"className":'attacks',"data": "defenseAttributes.maximumTotalLengthOfJSONData", "defaultContent": 1000 },
				{"className":'attacks',"data": "defenseAttributes.maximumValueLength", "defaultContent": 1000 },
				{"className":'attacks',"data": "defenseAttributes.tolerateJSONParsingWarnings", "defaultContent": false },
				{"className":'attacks',"data": "attackSignaturesCheck", "defaultContent": true },
				{"className":'attacks',"data": "metacharElementCheck", "defaultContent": true },
				{"className":'attacks',"data": "num_of_sig_overrides", "defaultContent": 0 },
				{"className":'attacks',"data": "num_of_meta_overrides", "defaultContent": 0 },
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );
		$('#json_profiles tbody').on('click', 'td.details-control', function () {
			var tr = $(this).closest('tr');
			var row = table.row( tr );
	
			if ( row.child.isShown() ) {
				// This row is already open - close it
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				// Open this row
				row.child( format_json_profiles(row.data()) ).show();
				tr.addClass('shown');
			}
		} );

	} );
</script>



<!--JSON validation files -->
<script>

	<?php echo $json_validation_files; ?>

	$(document).ready(function() {
		var table = $('#json_validation_files').DataTable( {
			"data": json_validation_files,	
			"columns": [
				{ "className": 'bold',"data": "fileName" },
				{  "className": 'attacks',"data": "allowed", "defaultContent": "<a href='#'><i class='fa fa-search'></i></a>"}
				],
				"autoWidth": false,
				"processing": true,
				"language": {"processing": "Waiting.... " }
		} );	

	} );
</script>

