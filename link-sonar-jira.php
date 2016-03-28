<?php
/**
Enter Sonar Server URL with Port number.
- Example : $sonarServerURL = "172.27.56.81:9090/sonar";
**/
$sonarServerURL = "172.27.56.81:9090/sonar";


/**
Sonar Server Base URL generated from Sonar Server URL.
**/
$sonarBaseURL="http://".$sonarServerURL."/api/issues";

/**
PROJECT KEY
**/
$sonarComponentKey = "ALM-Demo-Project-For-With-Task-Manager";

/**
Severity
you can define comma seperated values like BLOCKER,CRITICAL,MAJOR,MINOR etc.
**/
$sonarServerity = "MINOR";

/**
REPORT FORMAT mainly supports xml and json. recommended is json for running this script
**/
$sonarReportFormat = "json";

/** 
ACTION to perform like .. Search or do_action
**/
$sonarAction = "search";

/**
Creating JIRA issue link array which will store all issue ID's
**/
$jiraIssueLinkURL= array();


function curlRequest($url){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 4);
	$output = curl_exec($ch);	
	if(!$output) {
		echo curl_error($ch);
		exit;
	}	
	curl_close($ch);
	return $output;
}

function curlRequestPost($url){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
            "");
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 4);
	$output = curl_exec($ch);	
	if(!$output) {
		echo curl_error($ch);
		exit;
	}	
	curl_close($ch);
	return $output;
}



/**
Generate URL
**/
$sonarCompleteURL =$sonarBaseURL."/".$sonarAction."?&componentKeys=".$sonarComponentKey."&severities=".$sonarServerity."&format=".$sonarReportFormat;

$curlOutput = curlRequest($sonarCompleteURL);
$allSonarIssues = json_decode($curlOutput,true);
$sonarAction = "do_action";
foreach ($allSonarIssues['issues'] as $key => $value){
	if(array_key_exists('attr',$value) && array_key_exists('jira-issue-key',$value['attr'])){		
	}
	else{		
		$jiraIssueLinkURL[]= $sonarBaseURL."/".$sonarAction."?&actionKey=link-to-jira&issue=".$value['key'];		
	}
}
if(count($jiraIssueLinkURL)>0){
echo "-------------------------------------------------------------------------------------------\n";
echo "\t\t\tLinking Sonar Issues to JIRA 								 \n";
echo "-------------------------------------------------------------------------------------------\n";

foreach ($jiraIssueLinkURL as $key => $value){	
			$jiraOutputAPI = curlRequestPost($value);
			$allJiraissues = json_decode($jiraOutputAPI,true);
			if(array_key_exists('issue',$allJiraissues)){				
				$assignee = $allJiraissues['issue']['assignee'];
				$createdAt = $allJiraissues['issue']['comments'][0]['createdAt'];
				$htmlText = $allJiraissues['issue']['comments'][0]['htmlText'];
				$jiraIssueID = $allJiraissues['issue']['attr']['jira-issue-key'];
				echo "JIRA Issue created with ID: ".$jiraIssueID.", assigned to ".$assignee." and created at ".$createdAt." link -->".$htmlText."\n";	
			}
}
echo "-------------------------------------------------------------------------------------------\n";
echo "\t\t\tLinking Sonar Issues to JIRA Finished						 \n";
echo "-------------------------------------------------------------------------------------------\n";
}
else{
echo "-------------------------------------------------------------------------------------------\n";
echo "\t\t\tNo new issues to link with JIRA						 \n";
echo "-------------------------------------------------------------------------------------------\n";

}
?>
