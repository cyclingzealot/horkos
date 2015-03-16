#!/usr/bin/php



<?php

/*
 * Imported legacy code from cdn2014
 */

$testing=FALSE;

$limit=107;

if($testing)  $limit=3;

$outputFile='/tmp/results.csv';
$outputContent='';

$ridingsNames = array();
$resultsByRidingByParty = array();
$resultsByPartyByRiding = array();
$resultsByRidingSummary = array();
$wastedVotesByParty     = array();






foreach(array(1419, 1434, 1240, 1259) as $i) { 
#foreach(array(1419) as $i) { 
 
	$district=sprintf('%03d', $i);

	$url="http://enr.elections.ca/ElectoralDistricts.aspx?ed=$i&lang=e";

	echo "Getting results for riding $i $url...";
	$html = file_get_contents($url);

	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$xpath = new DOMXPath($doc);


    $xPathQuery = '//*[@id="grdResultsucElectoralDistrictResult'. $i . '"]/caption';

    echo "\n\nxPath: $xPathQuery\n\n";
	$ridingNode = $xpath->query($xPathQuery);
	$ridingNames[$i] = trim(substr($ridingNode->item(0)->textContent, 50));

    echo $ridingNames[$i];


	$tables = $doc->getElementsByTagName('table');

	#$nodes  = $xpath->query('/html/body/div[2]/div[2]/div[2]/div[3]/table/tbody');

	#echo "For " . $ridingNames[$i] . "\n";
	#var_export($tables->item(0)->textContent);
	#echo "\n\n";

	$rows = $tables->item(0)->getElementsByTagName('tr');


	$numRows = $rows->length;

    echo "\nThere are $numRows rows\n";

	$j=0;
    
	for($j=0; $j<$numRows-3; $j++) {



		if($j==0)  continue;
		#echo "\n\n";

		$row = $rows->item($j);

		#echo "Content of row is:";
		#echo $row->textContent;
		#echo "\n\n";

		echo "\n\n";
		$cells = $row->getElementsByTagName('td');

		#echo "Cells is a:";
		#var_export($cells);
		#echo "\n\n";


		#echo "\n\n";
		#echo "Cells length:";
		#var_export($cells->length);
		#echo "\n\n";


		$partyNode = $cells->item(0);

		if(!is_object($partyNode))  die("partyNode is not an object\n");

		$party = $cells->item(0)->textContent;

		$votes = preg_replace("/[^0-9]/", "", $cells->item(2)->textContent);

		echo "Scrapped: $party\t$votes\n";


		$resultsByRidingByParty[$i][$party] = $votes; 
		$resultsByPartyByRiding[$party][$i] = $votes;

		#var_export($resultsByRidingByParty);
		#echo "\n\n";
		#var_export($resultsByPartyByRiding);
		#echo "\n\n";
	}


    $xPathQuery = '//*[@id="divElectorNumberucElectoralDistrictResult' . $i . '"]/p';
	$numVoters = trim(substr($xpath->query($xPathQuery)->item(0)->textContent, 80));
    echo "Number of Voters: $numVoters\n";

    $row = $rows->item($numRows-1);
	$cells = $row->getElementsByTagName('td');
	$totalVotes = $cells->item(2)->textContent;
    echo "Number of total votes: $totalVotes\n";

    $participationRate = $totalVotes / $numVoters;
    echo "Participation rate: $participationRate\n";

    #$resultsByRidingSummary[$ridingID]['wastedVotes'] = preg_replace("/[^0-9]/", "", "$numVoters");
    $resultsByRidingSummary[$i]['numVoters']    = preg_replace("/[^0-9]/", "", "$numVoters");
    $resultsByRidingSummary[$i]['totalVotes']   = preg_replace("/[^0-9]/", "", "$totalVotes");
    $resultsByRidingSummary[$i]['pRate']        = $participationRate;
}



$listOfParties  =array_keys($resultsByPartyByRiding);
$listOfRidings 	=array_keys($resultsByRidingByParty);

### Make the header of the csv file
$outputContent .= " \t \t";
foreach($listOfParties as $partyName) {
	$outputContent .= $partyName . "\t"; 
}
$outputContent .= "Wasted Votes\tWasted Votes %\tParticipation Rate";
$outputContent .= "\n";


# For WV analysis (WVA)
$wastedVotesMax = 0;
$wastedVotesMaxRidingName = '';

### Make the rows of the csv file
foreach($ridingNames as $ridingID => $ridingName) {
	$outputContent .= "$ridingID\t$ridingName\t";


    # For WV analysis (WVA)
    $winnerVotes = 0;
    $winningParty = "";
    $totalValidVotes = 0;
    $wastedVotes = 0;

	foreach($listOfParties as $partyName) {

        # For CSV
		$votes = "";
		
		if(isset($resultsByRidingByParty[$ridingID][$partyName])) {
			$votes = $resultsByRidingByParty[$ridingID][$partyName];
            $totalValidVotes += $votes;

            # For WVA
            if($votes > $winnerVotes) {
                $winningParty = $partyName;
                $winnerVotes  = $votes;
            }

            $wastedVotes = $totalValidVotes - $winnerVotes;
            $resultsByRidingSummary[$ridingID]['wastedVotes']       = $wastedVotes;
            $resultsByRidingSummary[$ridingID]['wastedVotesPct']    = $wastedVotes/$totalValidVotes;


            #echo "For $ridingName, the winning party was $winningParty with $winnerVotes.  Among the $totalValidVotes votes, there were $wastedVotes wasted votes " . round($wastedVotes/$totalValidVotes*99) . "%\n";
		}
		$outputContent .= $votes . "\t";

	}


    #echo "For $ridingName, the winning party was $winningParty with $winnerVotes.  Among the $totalValidVotes votes, there were $wastedVotes wasted votes " . round($wastedVotes/$totalValidVotes*100) . "%\n";


    ### Write row by data
    $outputContent .= "$wastedVotes\t" . round($resultsByRidingSummary[$ridingID]['wastedVotesPct']*100,2)  .  "\t"  . round($resultsByRidingSummary[$i]['pRate']*100,2)   ;
	$outputContent .= "\n";

}





echo "\n\n\n\n";
echo $outputContent;

file_put_contents($outputFile, $outputContent);

echo "\n\n\n\n\n";

### More WVA
$resultsByPartySummary  = array();

uasort($resultsByRidingSummary, function ($a, $b) {
    #var_export($b); 
    return ($a['wastedVotesPct'] - $b['wastedVotesPct'])*100;
});   

foreach($resultsByRidingSummary as $ridingID => $ridingNumbers) {
    #The participation rate threshold for which we will calculate the wasted votes.
    # 0.12 is the third of the partipation rate for the riding the lowest turnout (Fort McMurray) of the election with the lowest turnout (2008)
    $pRateThreshold = .12;

    if($ridingNumbers['pRate'] > $pRateThreshold) {
        printf("%-50s%.2f %%\n", $ridingNames[$ridingID], $ridingNumbers['wastedVotesPct']*100);
    }
}


echo "\n\n\n\n\n";


echo "List of parties are:\n";

sort($listOfParties);
foreach($listOfParties as $partyName) {
	echo "$partyName\n";
}
