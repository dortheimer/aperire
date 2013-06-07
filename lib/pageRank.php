<?php
function calculatePageRank($linkGraph, $dampingFactor = 0.15) {
	$pageRank = array();
	$tempRank = array();
	$nodeCount = count($linkGraph);

	// initialise the PR as 1/n
	foreach($linkGraph as $node => $outbound) {
		$pageRank[$node] = 1/$nodeCount;
		$tempRank[$node] = 0;
	}

	$change = 1;
	$i = 0;
	while($change > 0.00005 && $i < 100) {
		$change = 0;
		$i++;

		// distribute the PR of each page
		foreach($linkGraph as $node => $outbound) {
			$outboundCount = count($outbound);
			foreach($outbound as $link) {
				$tempRank[$link] += $pageRank[$node] / $outboundCount;
			}
		}

		$total = 0;
		// calculate the new PR using the damping factor
		foreach($linkGraph as $node => $outbound) {
			$tempRank[$node]  = ($dampingFactor / $nodeCount)
			+ (1-$dampingFactor) * $tempRank[$node];
			$change += abs($pageRank[$node] - $tempRank[$node]);
			$pageRank[$node] = $tempRank[$node];
			$tempRank[$node] = 0;
			$total += $pageRank[$node];
		}

		// Normalise the page ranks so it's all a proportion 0-1
		foreach($pageRank as $node => $score) {
			$pageRank[$node] /= $total;
		}
	}

	return $pageRank;
}
?>