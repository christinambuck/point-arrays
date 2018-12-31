<?php 
session_start();
include '../class/Config.php';
include '../class/MenuList.php';
include '../class/Format.php';
include '../class/Reconfig.php'; // This will put the text files in static (global) arrays you access by Reconfig::$$myPoints
$config 	= new Config();
$menuList 	= new MenuList();
$format 	= new Format();
$reconfig	= new Reconfig();

$url = 'addpoints.php';

if (!$_POST['date']) 
	$_POST['date'] = $format->formatDate(date('Ymd'));
$date = substr($_POST['date'],6,4);
$date .= substr($_POST['date'],0,2);
$date .= substr($_POST['date'],3,2);
/*
echo 'session = <pre>';
print_r($_SESSION);
echo '</pre>';echo 'cookie = <pre>';
print_r($_COOKIE);
echo '</pre>';
exit;
*/
// if member clicked a delete icon  to delete an action and points
if ($_POST['deleteAction']) 
{	
	$db = $reconfig->createArray(Config::$configFileLocation, 'mypoints.txt');	// $db is now an array of each points data
	$counti = count($db);
	$fh = fopen(Config::$configFileLocation.'mypoints.txt', "w");
	for ($i=0; $i < $counti; $i++) 					// for each record in the file
	{				
		$fields = explode (",\t",$db[$i]); 			// each field is delimited by ,\t
		if ($fields[0] != $_POST['deleteAction']) 	// if not the pointsid to delete
		{		
			fwrite($fh,"\n\t".$db[$i]); 			// then write back the record of data to the text file					
		}
	}	
	fclose($fh);
	$reconfig->reconfigMyPoints(Config::$configFileLocation);	// create new config arrays with updated text file		
	$_POST['deleteAction'] = ''; //reset delete field
}


// if member selected an action from the drop down menu
if ($_POST['addPoints'] && $_POST['userId']) 
{
		// add a record (line) to the text file for the new action/points added
		Reconfig::$myPoints[Reconfig::$nextPointsId]['userId'] 		= $_POST['userId'];
		Reconfig::$myPoints[Reconfig::$nextPointsId]['date'] 		= $date;
		Reconfig::$myPoints[Reconfig::$nextPointsId]['actionId'] 	= $_POST['addPoints'];	
		Reconfig::$myPoints[Reconfig::$nextPointsId]['rewardId'] 	= 0;
		Reconfig::$myPoints[Reconfig::$nextPointsId]['action']		= '';
		Reconfig::$myPoints[Reconfig::$nextPointsId]['reward'] 		= '';	
		Reconfig::$myPoints[Reconfig::$nextPointsId]['points'] 		= 0;	
		$record = "\n\t".Reconfig::$nextPointsId.",\t".implode(",\t",Reconfig::$myPoints[Reconfig::$nextPointsId]);
		// Update the text file with the added points/action
		$fh = fopen(Config::$configFileLocation.'mypoints.txt', 'a') or die("can't open file");
		fwrite($fh, $record);
		fclose($fh);
		$reconfig->reconfigMyPoints(Config::$configFileLocation);	// create new points arrays with updated text file	
}

// if member selected a user from the drop down menu
if ($_POST['userId']){
	/*
	*	go through the points table and get the actionIds for all records with the entered 
	*	user name and date
	*/	
	$total = 0;
	$totalPoints = 0;
	$actions = array();
	$points = array();
	$dels = array();
	// get all records from the points file that have the selected userId		
	foreach (Reconfig::$myPoints as $key => $value) 
	{
		if (Reconfig::$myPoints[$key]['userId'] == $_POST['userId']) 
		{
			if (Reconfig::$myPoints[$key]['actionId'])
			{
				if (Reconfig::$myPoints[$key]['date'] == $date) // get action text and point value from myactions text file
				{  
					array_push($actions, Reconfig::$myActions[Reconfig::$myPoints[$key]['actionId']]['action']);
					array_push($points, Reconfig::$myActions[Reconfig::$myPoints[$key]['actionId']]['points']);
					$routine = '<a href="javascript:deleteAction('.$key.');" title ="Remove Action Item"><img src="images/delete.png" width="20" height="20" title="Remove action/points"></a>';
					array_push($dels, $routine);
					$total += Reconfig::$myActions[Reconfig::$myPoints[$key]['actionId']]['points'];
				}
				$totalPoints += Reconfig::$myActions[Reconfig::$myPoints[$key]['actionId']]['points'];
				
			} //END if actionId
			// check to see if the action had been deleted from the action table but the action data is in the points table
			elseif (Reconfig::$myPoints[$key]['action']) 
			{ 
				if (Reconfig::$myPoints[$key]['date'] == $date) 
				{ 
					array_push($actions, Reconfig::$myPoints[$key]['action']);
					array_push($points, Reconfig::$myPoints[$key]['points']);
					$routine = '<a href="javascript:deleteAction('.$key.');" title ="Remove Action Item"><img src="images/delete.png" width="20" height="20" title="Remove action/points"></a>';
					array_push($dels, $routine);
					$total += Reconfig::$myPoints[$key]['points'];
					$totalPoints += Reconfig::$myPoints[$key]['points'];
				}
				else 
				{
					$totalPoints += Reconfig::$myPoints[$key]['points'];
				}
			} // END if action data
			elseif (Reconfig::$myPoints[$key]['rewardId']) // if rewardId
			{	
				if (Reconfig::$myPoints[$key]['date'] == $date)  // get reward text and point value from myrewards text file
				{ 
					$this_reward = Reconfig::$myRewards[Reconfig::$myPoints[$key]['rewardId']]['action'];
					$thisPoints = Reconfig::$myRewards[Reconfig::$myPoints[$key]['rewardId']]['points'];						
					$total -= $thisPoints;
					$totalPoints -= $thisPoints;
				}
				else // just get point value from myactions table
				{ 
					$thisPoints = Reconfig::$myRewards[Reconfig::$myPoints[$key]['rewardId']]['points'];
					$totalPoints -= $thisPoints;
				}
			} //END if rewardId
			// check to see if the reward had been deleted from the reward table but the reward data is in the points table
			elseif (Reconfig::$myPoints[$key]['reward']) 
			{ 
				if (Reconfig::$myPoints[$key]['date'] == $date) 
				{
					$total -= Reconfig::$myPoints[$key]['points'];
					$totalPoints -= Reconfig::$myPoints[$key]['points'];
				}
				else 
				{
					$totalPoints -= Reconfig::$myPoints[$key]['points'];
				}
			} // END if reward data
		} // END if userId			
	} // END foreach
}// end if userId

/*
echo 'Actions = <pre>';
print_r($actions);
echo '</pre>';
echo 'Rewards = <pre>';
print_r($rewards);
echo '</pre>';
echo 'Total =  '.$total;
echo '<br>Totals =  '.$totalPoints;
*/
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Point Patrol App</title>
<!-- Mobile viewport -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" /><!-- Responsive -->

<!-- Required for all pointpatrol app pages -->
<link href="css/style.css" rel="stylesheet" type="text/css"> 
<link href="css/headerApp.css" rel="stylesheet" type="text/css">
<link href="css/media-queriesApp.css" rel="stylesheet" type="text/css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/app.js.php"></script>

<!-- This is page specific for each pointpatrol app page -->
<link href="css/addpoints.css" rel="stylesheet" type="text/css">

<!-- Calendar date picker -->
<link href="js/pickadate/classic.css" rel="stylesheet" type="text/css">
<link href="js/pickadate/classic.date.css" rel="stylesheet" type="text/css">
<script src="js/pickadate/picker.js"></script>
<script src="js/pickadate/picker.date.js"></script>
<script>
	$(function() {
		$('#datepicker').pickadate();
	});
</script>

<script type="text/javascript">
	function deleteAction(pointsId){
		document.form.deleteAction.value = pointsId;
		document.form.submit();
		return true;
	}	
</script>
</head>

<body id="pointsPage">
	<?php include 'header.php'; ?>
    <div id="pageWrap">
    	<div class="mainInputWrapper">
            <div class="mainInput">
                <form name="form" id="form" method="post" action="addpoints.php">
                <p>Date: <input name="date" type="text" id="datepicker" size="10" value="<?php echo $_POST['date']; ?>" >
                 </p>
                 <p>User: <?php  
                        $name = $id = "userId";			
                        $selected_value = $_POST['userId'];		
                        $menuList->displayList(Reconfig::$usersLabels, Reconfig::$usersValues, $name, $id, $selected_value, 'class="submitOnChange"');?>
                </p>
                <p><strong>Add Action:</strong> <?php 
					$name = $id = "addPoints";			
					$selected_value = '';		
					$menuList->displayList(Reconfig::$actionsLabels, Reconfig::$actionsValues, $name, $id, $selected_value,'class="checkUserOnChange"');?> 
				  <input name="deleteAction" type="hidden" id="deleteAction" value="<?php echo $_POST['deleteAction'] ?>">
        		</p>
				</form>
            </div><!-- End mainInput -->
    	</div><!-- End mainInputWrapper -->
        <!--
        	Display all the actions and points for the user for the selected date
        -->
		<?php
        $count = count($actions);
        $i = 0;
        while ($i < $count)
        { 			
        ?>	
            <div id="mainOutputBox">
                <div id="actions">
                    <?php echo $actions[$i]; ?>
                </div>
                <div id="dels">
                    <?php echo $dels[$i]; ?>
                </div>
                <div id="points">
                    <?php echo $points[$i]; ?>
                </div>
            </div>
        <?php
            $i++;	
        }
        if ($_POST['userId']) { ?>
        <div id="totalsBox">
            <div id="totalAmount"><? echo $total ?></div>
        	<div id="totalTitle">Total points for <span class="font14"><? echo $_POST['date'] ?></span>:</div>
        </div>
        <div id="totalsBox">
            <div id="totalAmount"><? echo $totalPoints ?></div>
        	<div id="totalTitle">Grand Total Points:</div>
        </div>
        <?php } ?>
    </div><!-- End pageWrap -->
</body>
</html>