<?php
/* uses updated syntax and stuff */
class Cluster extends Controller{
	function run($xml){
		global $FILE_ROOT, $STORAGE, $REQ_ID, $CMD_EXTRA, $LIB, $BIN;

		$numOfClusters = intval($xml->numClusters);
		$numOfDocuments = 0;

		$TREC = fopen($FILE_ROOT . fname($STORAGE . "trec.txt"), "w");//TODO make sure that you're not overwriting anything with a unique id or something

		$TREC_FILE_LIST = fopen($FILE_ROOT . fname($STORAGE . "trec_file.list"), "w");
		fwrite($TREC_FILE_LIST, $FILE_ROOT . fname($STORAGE . "trec.txt"));
		fclose($TREC_FILE_LIST);

		$trec_content = "";
		foreach($xml->resourceList->resource as $res){
			$trec_content .= "<DOC><DOCNO>". $res->id . "</DOCNO>";
			$trec_content .= "<TEXT>". $res->content . "</TEXT></DOC>";
			$numOfDocuments++;
		}

		fwrite($TREC, $trec_content);
		fclose($TREC);

		if($numOfClusters > $numOfDocuments){
			die(err("Number of clusters is more than the number of documents"));
		}

		//now build the index
		$IPARAM = fopen(fname($STORAGE . "build_index.param"), "w");
		$tmp = "<parameters><index>" . fname($STORAGE . "index") . "</index><indexType>indri</indexType><dataFiles>" . fname($STORAGE . "trec_file.list") . "</dataFiles><docFormat>trec</docFormat><stopwords>" . $LIB . "stopwords.param</stopwords></parameters>";
		fwrite($IPARAM, $tmp);
		fclose($IPARAM);

		system($BIN . "BuildIndex " . fname($STORAGE . "build_index.param") . $CMD_EXTRA);

		//create cluster parameters
		$CPARAM = fopen($FILE_ROOT . fname($STORAGE . "cluster.param"), "w");

		fwrite($CPARAM, "<parameters>\n<index>" . fname($STORAGE . "index") . "</index>\n<clusterType>centroid</clusterType>\n<numParts>" . $numOfClusters . "</numParts>\n</parameters>\n");
		fclose($CPARAM);

		//do clustering
		$out = Array();

		exec($BIN . "OfflineCluster " . fname($STORAGE . "cluster.param"), $out);

		$response = "<parameters>\n<requestID>" . $REQ_ID ."</requestID>\n<requestType>cluster</requestType>\n<clusterList>\n";

		//get the document id's of each cluster by parsing the output of the last function (pray to god it works)
		$i = 0;
		for($i = 0; $i < $numOfClusters; $i++){
			//line we need is line 2
			$arr =  explode(": ",$out[$i + 1]);
			$line = $arr[1]; //php 5.4 supports this
			$cids;
			if($line == ""){
				//add a blank
				$response .= "<cluster><clusterID>" . $i . "</clusterID><resource><id></id></resource></cluster>";
				continue;
			}
			$cids = explode(" ", $line);//cids is cluster ids

			//echo the result
			$response .= "<cluster><clusterID>" . $i ."</clusterID><resourceList>\n";
		        for($j = 0; $j < sizeof($cids); $j++){
		        	$response .= "<resource><id>" . $cids[$j] ."</id>";
		        	//$response .= "<url>TODO</url>";
		          	$response .= "</resource>";
		   	    }
		     $response .= "</resourceList></cluster>";
		}


		$response .= "</clusterList></parameters>";

		return $response;
	}
}