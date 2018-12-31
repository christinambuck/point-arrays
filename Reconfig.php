<?php

class Reconfig {

	// Points
	static $myPoints = array();
	static $nextPointsId = '';
	// Users
	static $myUsers = array();
	static $nextUsersId = '';
	static $usersLabels = array (' ');
	static $usersValues = array (' ');
	// Actions
	static $myActions = array();
	static $nextActionsId = '';
	static $actionsLabels = array (' ');
	static $actionsValues = array (' ');
	// Rewards
	static $myRewards = array();
	static $nextRewardsId = '';
	static $rewardsLabels = array (' ');
	static $rewardsValues = array (' ');

	/**
	 * Methods to create arrays from text file data to be used instead of using the mysql database
	 *
	 */	

	public function __construct() {
		$this->reconfigMyPoints(Config::$configFileLocation);	// put contents of mypoint text file into an array
		$this->reconfigMyUsers(Config::$configFileLocation);		// put contents of myusers text file into an array
		$this->reconfigMyActions(Config::$configFileLocation);	// put contents of myactions text file into an array
		$this->reconfigMyRewards(Config::$configFileLocation);	// put contents of myrewards text file into an array
	} 

	/*
	*	Split a string from a text file into an array of records
	*/
	public function createArray($location, $filename)
	{
		$file = file_get_contents($location.$filename);
		if ($file) 
		{
			if (substr($file,0,3) == "\r\n\t")
			{
				$file = substr($file,3);
				file_put_contents($location.$filename,$file);
			}
			elseif (substr($file,0,2) == "\n\t")
			{
				$file = substr($file,2);
				file_put_contents($location.$filename,$file);
			}		
			//$db = explode("\r\n\t",$file); 			// $db is now an array of each points data
			//if (count($db) == 1)
			$db = explode("\n\t",$file); 			// $db is now an array of each points data	
			return $db;
		}
	}
	
	public function getNextId($newArray){
		krsort($newArray);
		$nextId = key($newArray)+1;
		return $nextId;
	}
	
	/*
	*	 Add a record (line) to the myactions text file for the new action/points that have been added by the member
	*
	*/
	public function addAction($label,$points) {
		if (empty($points)) $points = 0;		
		Reconfig::$myActions[Reconfig::$nextActionsId]['action'] = $label;
		Reconfig::$myActions[Reconfig::$nextActionsId]['points'] = $points;	
		$record = "\n\t".Reconfig::$nextActionsId.",\t".implode(",\t",Reconfig::$myActions[Reconfig::$nextActionsId]);
		$fh = fopen(Config::$configFileLocation.'myactions.txt', 'a') or die("can't open file");
		fwrite($fh, $record);									// Update the text file with the added action/points
		fclose($fh);	
		$this->reconfigMyActions(Config::$configFileLocation);	// put contents of myactions text file into an array
	} 	

	/*
	*	 Add a record (line) to the myrewards text file for the new reward/points that have been added by the member
	*
	*/
	public function addReward($label,$points) {
		if (empty($points)) $points = 0;		
		Reconfig::$myRewards[Reconfig::$nextRewardsId]['reward'] = $label;
		Reconfig::$myRewards[Reconfig::$nextRewardsId]['points'] = $points;	
		$record = "\n\t".Reconfig::$nextRewardsId.",\t".implode(",\t",Reconfig::$myRewards[Reconfig::$nextRewardsId]);
		$fh = fopen(Config::$configFileLocation.'myrewards.txt', 'a') or die("can't open file");
		fwrite($fh, $record);									// Update the text file with the added action/points
		fclose($fh);	
		$this->reconfigMyRewards(Config::$configFileLocation);	// put contents of myactions text file into an array
	} 

	// Check if username already exists in myusers.txt
	public function checkForUserName($userName) {
		foreach (Reconfig::$myUsers as $key => $value) 
		{
			if( strtolower( Reconfig::$myUsers[$key]['userName'] ) == strtolower( $userName ) )
				return (0); 	// if username found; return false
		} // END foreach
		return (1); 			// return true if username not found
	} 
	
	/*
	*	 Add a record (line) to the myrewards text file for the new reward/points that have been added by the member
	*
	*/
	public function addUser($label,$password) {
		if (empty($points)) $points = 0;		
		Reconfig::$myUsers[Reconfig::$nextUsersId]['user'] = $label;
		Reconfig::$myUsers[Reconfig::$nextUsersId]['userpw'] = $password;	
		$record = "\n\t".Reconfig::$nextUsersId.",\t".implode(",\t",Reconfig::$myUsers[Reconfig::$nextUsersId]);
		$fh = fopen(Config::$configFileLocation.'myusers.txt', 'a') or die("can't open file");
		fwrite($fh, $record);									// Update the text file with the added action/points
		fclose($fh);	
		$this->reconfigMyUsers(Config::$configFileLocation);	// put contents of myactions text file into an array
	} 

	/*
	*	Get the mypoints.txt file and put its content into Reconfig::$myPoints
	*	EXAMPLE: where 100 is the pointsId in the current record,
	*			 Reconfig::$myPoints[100]['userId'] = 2
	* 			 Reconfig::$myPoints[100]['date'] = 20101019
	*/
	public function reconfigMyPoints($location)
	{	
		$db = $this->createArray($location, 'mypoints.txt');
		if ( $db  )
		{
			Reconfig::$myPoints = array();	
			$counti = count($db);
			for ($i=0; $i < $counti; $i++)				// for each record in the file
			{				
				$fields = explode (",\t",$db[$i]); 		// each field is delimited by ,\t	
				Reconfig::$myPoints[$fields[0]] = array('userId' 	=> $fields[1], 		// $fields[0] is the points Id number
														'date' 		=> $fields[2], 
														'actionId' 	=> $fields[3],
														'rewardId' 	=> $fields[4],
														'action' 	=> htmlentities($fields[5]),
														'reward' 	=> htmlentities($fields[6]),
														'points' 	=> $fields[7]
														);	
			
			} // END for each record in the file
			Reconfig::$nextPointsId = $this->getNextId(Reconfig::$myPoints);
		} // END if $file
		else Reconfig::$nextPointsId = 1;
	} // END function reconfig_mypoints
	
	/*
	*	Get the myusers.txt file and put its content into Reconfig::$myUsers
	*/
	public function reconfigMyUsers($location)
	{
		$db = $this->createArray($location, 'myusers.txt');
		if ( $db  )
		{	
			
			Reconfig::$usersLabels = array (' ');
			Reconfig::$usersValues = array (' ');
			Reconfig::$myUsers = array();			
			$counti = count($db);
			for ($i=0; $i < $counti; $i++)			// for each record in the file
			{			
				$fields = explode (",\t",$db[$i]); 	// each field is delimited by ,\t
				Reconfig::$myUsers[$fields[0]] = array('userName' => $fields[1], 'userpw' => $fields[2]);	
			} // END for each record in the file
			Reconfig::$myUsers = $this->sortUserNameI(Reconfig::$myUsers);
			foreach (Reconfig::$myUsers as $key => $value)
			{	
				array_push(Reconfig::$usersLabels, Reconfig::$myUsers[$key]['userName']); 	// store each userName in the array
				array_push(Reconfig::$usersValues,$key);									// store each userId in the array
			}		
			Reconfig::$nextUsersId = $this->getNextId(Reconfig::$myUsers);
		}  // END if database array was created	
		else Reconfig::$nextUsersId = 1;			
			
	} // END function reconfig_myusers
	
	/*
	*	Get the myactions.txt file and put its content into Reconfig::$myActions
	*/
	public function reconfigMyActions($location)
	{
		$db = $this->createArray($location, 'myactions.txt'); // put contents of text file into an array
		if ( $db  )
		{
			Reconfig::$actionsLabels = array (' ');
			Reconfig::$actionsValues = array (' ');
			Reconfig::$myActions = array();	
			$counti = count($db);
			for ($i=0; $i < $counti; $i++)				// for each record in the file
			{				
				$fields = explode (",\t",$db[$i]); 		// each field is delimited by ,\t
				if (strlen($fields[1]) > 25)
                	$fields[1] = substr($fields[1],0,25);
				Reconfig::$myActions[$fields[0]] = array('action' => htmlentities($fields[1]), 'points' => $fields[2]);	
			} // END for each record in the file
			Reconfig::$myActions = $this->sortActionI(Reconfig::$myActions);

			foreach (Reconfig::$myActions as $key => $value)
			{			
				array_push(Reconfig::$actionsLabels,Reconfig::$myActions[$key]['action']); 	// store each action in the array
				array_push(Reconfig::$actionsValues,$key);									// store each actionId in the array
			}
			Reconfig::$nextActionsId = $this->getNextId(Reconfig::$myActions);
		} // END if database array was created			
		else Reconfig::$nextActionsId = 1; 
	} // END function reconfig_myactions
		
	/*
	*	Get the myrewards.txt file and put its content into Reconfig::$myRewards
	*/
	public function reconfigMyRewards($location)
	{
		$db = $this->createArray($location, 'myrewards.txt');
		if ( $db  )
		{	
			
			Reconfig::$rewardsLabels = array (' ');
			Reconfig::$rewardsValues = array (' ');
			Reconfig::$myRewards = array();	
			$counti = count($db);
			for ($i=0; $i < $counti; $i++)				// for each record in the file
			{				
				$fields = explode (",\t",$db[$i]); 		// each field is delimited by ,\t
				if (strlen($fields[1]) > 25)
                	$fields[1] = substr($fields[1],0,25);
				Reconfig::$myRewards[$fields[0]] = array('reward' => htmlentities($fields[1]),'points' => $fields[2]);	
			} // END for each record in the file			
			Reconfig::$myRewards = $this->sortRewardI(Reconfig::$myRewards);
			foreach (Reconfig::$myRewards as $key => $value)
			{			
				array_push(Reconfig::$rewardsLabels,Reconfig::$myRewards[$key]['reward']); 	// store each reward in the array
				array_push(Reconfig::$rewardsValues,$key);									// store each rewardId in the array
			}		
			Reconfig::$nextRewardsId = $this->getNextId(Reconfig::$myRewards);
		} // END if $file		
		else Reconfig::$nextRewardsId = 1;
			/*echo 'myrewards<pre>';
			print_r($db);
			echo '</pre>'; 
			echo 'in reconfig_myrewards <pre>';
			print_r(Reconfig::$myRewards);
			echo '</pre>';*/
	} // END function reconfig_myrewards

		 
	public function sortUserNameI($arr) { 
	   $arr2 = $arr; 
	   foreach($arr2 as $key => $val) 
	   { 
		  $arr2[$key]['userName'] = strtolower($arr2[$key]['userName']); 
	   } 	
	   uasort($arr2, array($this,'sortUserName'));	
	   foreach($arr2 as $key => $val) 
	   { 
		  $arr2[$key] = $arr[$key]; 
	   } 	
	   return $arr2; 
	} 
	
	public function sortUserName($x, $y){ 
		if ( $x['userName'] == $y['userName'] )  
			return 0; 
		elseif ( $x['userName'] < $y['userName'] )  
			return -1;
		else  return 1;
	}
	
	public function sortActionI($arr) { 
	   $arr2 = $arr; 
	   // convert action value to lowercase
	   foreach($arr2 as $key => $val) 
	   { 
		  $arr2[$key]['action'] = strtolower($arr2[$key]['action']); 
	   } 	  	
	   uasort($arr2, array($this,'sortAction'));
	   foreach($arr2 as $key => $val)
	   { 
		  $arr2[$key] = $arr[$key]; 
	   } 
	   return $arr2; 
	}
	
	public function sortAction($x, $y){
		if ( $x['action'] == $y['action'] )  
			return 0; 
		elseif ( $x['action'] < $y['action'] )  
			return -1;
		else  return 1;
	}
	
	public function sortRewardI($arr) { 
	   $arr2 = $arr; 
	   foreach($arr2 as $key => $val) 
	   { 
		  $arr2[$key]['reward'] = strtolower($arr2[$key]['reward']); 
	   }    	
	   uasort($arr2, array($this,'sortReward'));	
	   foreach($arr2 as $key => $val) 
	   { 
		  $arr2[$key] = $arr[$key]; 
	   } 
	   return $arr2; 
	}
	 
	public function sortReward($x, $y){ 
		if ( $x['reward'] == $y['reward'] )  
			return 0; 
		elseif ( $x['reward'] < $y['reward'] )  
			return -1;
		else  return 1;
	}
}
?>
