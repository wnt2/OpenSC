<?php

   require_once "NeuTanSigmaClass.php";
   require_once "AbsorbClass.php";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		// Processing from the form starts here.
   		// print_r($_POST);

   		// The input variables
		list($sootmin, $sootmax, $sootstep, $tempmin, $tempmax, $tempstep,
   			$fco2min, $fco2max, $fco2step, $thicknessmin, $thicknessmax, $thicknessstep,
   			$pathmin, $pathmax, $pathstep, $twallmin, $twallmax, $twallstep) = handleInputData($_POST);

   		//print("Incoming data: ");
   		//print_r($_POST);

   		// Make sure that the number of steps is an integer for all parameters
   		$sootstep = intval($sootstep);
   		if ($sootstep < 1 or (strcmp($sootparam, "single") == 0)) $sootstep = 1;
   		$tempstep = intval($tempstep);
   		if ($tempstep < 1 or (strcmp($tempparam, "single") == 0)) $tempstep = 1;
   		$fco2step = intval($fco2step);
   		if ($fco2step < 1 or (strcmp($fco2param, "single") == 0)) $fco2step = 1;
   		$thicknessstep = intval($thicknessstep);
   		if ($thicknessstep < 1 or (strcmp($thicknessparam, "single") == 0)) $thicknessstep = 1;
   		$pathstep = intval($pathstep);
   		if ($pathstep < 1 or (strcmp($pathparam, "single") == 0)) $pathstep = 1;
   		$twallstep = intval($twallstep);
   		if ($twallstep < 1 or (strcmp($twallparam, "single") == 0)) $twallstep = 1;

   		// print("sootstep=$sootstep tempstep=$tempstep fco2step=$fco2step");

   		$NTS_table_rows = array();
   		$absorb_table_rows = array();

   		// Calculate
  	 	for ($s = 0; $s < $sootstep; $s++) {
  	 		$soot = $sootstep == 1 ? $sootmin : ($sootmax-$sootmin)/($sootstep-1)*$s+$sootmin;
  	 		for ($t = 0; $t < $tempstep; $t++) {
  	 			$temp = $tempstep == 1 ? $tempmin : ($tempmax-$tempmin)/($tempstep-1)*$t+$tempmin;
  	 			for ($f = 0; $f < $fco2step; $f++){
  	 				$fco2 = $fco2step == 1 ? $fco2min :($fco2max-$fco2min)/($fco2step-1)*$f+$fco2min;
  	 				for ($th = 0; $th < $thicknessstep; $th++) {
  	 					$thickness = $thicknessstep == 1 ? $thicknessmin :($thicknessmax-$thicknessmin)/($thicknessstep-1)*$th+$thicknessmin;
  	 					$minthickness = 0.01;
  	 					$thickflag = 0;
  	 					if ($thickness < $minthickness){
  	 								$thickflag = 1;
 // 	 						 		print(" thickness too low = ". $thickness . "<br>");
  	 						 		$thicknessold = $thickness;
  	 						 		$thickness = $minthickness;
 // 	 						 		print("thicknessold = ". $thicknessold . "<br>");
//  	 						 		print("thickness = ". $thickness . "<br>");
//  	 						 		print("thickflag = ". $thickflag . "<br>");
  	 						 		}
  	   	 					for ($p = 0; $p < $pathstep; $p++) {
  	 						$path = $pathstep == 1 ? $pathmin :($pathmax-$pathmin)/($pathstep-1)*$p+$pathmin;

  	 							$maxpress = 101;
  	 						 	$gaspressure = $thickness/$path;
  	 						 	if ($gaspressure > $maxpress){
  	 						 		print(" totoal pressure too high = ". $gaspressure . "<br>");
  	 						 		exit();
  	 						 		}

  	 						list($xt, $gamma, $xt_all) = runOneNTSCalculation($soot, $temp, $fco2, $thickness, $path);

  	 						$thicknesspr = $thickness;
  	 						if ($thickflag == 1){
  	 							$xtold = $xt;
  	 							$xt = $xt*$thicknessold/$minthickness;
  	 							$xt_all = $xt +$gamma;
  	 							$thicknesspr = $thicknessold;
  	 							}

   							$nts_result_data = array($soot, $temp, $fco2, $thicknesspr, $path, $xt_all, $gamma, $xt);
   							$nts_result_row = generateTableRow($nts_result_data, false);
   							array_push($NTS_table_rows, $nts_result_row);

  	 						for ($tw = 0; $tw < $twallstep; $tw++) {
  	 							$twall = $twallstep == 1 ? $twallmin :($twallmax-$twallmin)/($twallstep-1)*$tw+$twallmin;

  	 							if ($twall == $temp) {
  	 								list($axt, $agamma, $axt_all) = array($xt, $gamma, $xt_all);
  	 							} else {
  	 								list($axt, $agamma, $axt_all) = runOneAbsorbCalculation($soot, $temp, $fco2, $thickness, $path, $twall);
  	 								$thicknesspr = $thickness;
  	 								if ($thickflag == 1){
  	 								$axt = $axt*$thicknessold/$minthickness;
  	 								$axt_all = $axt +$agamma;
  	 								$thicknesspr = $thicknessold;
  	 								}
  	 							}
  	 							$absorb_result_data = array($soot, $temp, $fco2, $thicknesspr, $path, $twall, $axt_all, $agamma, $axt);
   								$absorb_result_row = generateTableRow($absorb_result_data, false);
   								array_push($absorb_table_rows, $absorb_result_row);
   							}
   						}
   					}
   				}
   			}
   		}

   		print("<h1>Results</h1>");
   		print("<h2>Emissivity</h2>");
   		// generate table header
    	$NTS_header_data = array("Soot Volume Fraction Pathlength (m)", "Mixture Temperature (K)",
    							"CO<sub>2</sub>/(CO<sub>2</sub>+H<sub>2</sub>O) Fraction", "Absorption Pressure Pathlength (kPa-m)", "Path Length (m)",
    							"Total Emissivity", "Soot Emissivity",
    							"Mixture Emissivity");
    	$NTSheader = generateTableRow($NTS_header_data, true);
    	print("<table borders=1 style='max-width:1000px'>\n");
   		print("<tr><th colspan=5>Input Parameters</th><th colspan=3>Emissivity</th></tr>\n");
   		print("$NTSheader\n");
   		for ($i = 0; $i < count($NTS_table_rows); $i++) {
   			print($NTS_table_rows[$i] . "\n");
   		}
   		print("</table></br>\n");

   		print("<h2>Absorptivity</h2>");
    	$absorb_header_data = array("Soot Volume Fraction Pathlength (m)", "Mixture Temperature (K)",
    								"CO<sub>2</sub>/(CO<sub>2</sub>+H<sub>2</sub>O) Fraction", "Absorption Pressure Pathlength (kPa-m)", "Path Length (m)",
    								"Source Temperature (K)", "Total Absorptivity", "Soot Absorptivity",
    								"Mixture Absorptivity");
   		$absorbheader = generateTableRow($absorb_header_data, true);
   		print("<table borders=1 style='max-width:1000px'>\n");
   		print("<tr><th colspan=6>Input Parameters</th><th colspan=3>Absorptivity</th></tr>\n");
   		print("$absorbheader\n");
  	 	for ($i = 0; $i < count($absorb_table_rows); $i++) {
   			print($absorb_table_rows[$i] . "\n");
   		}
  	 	print ("</table></br>\n");


  	 	if ($downloadfile) {
  	 		print('<a href="results.csv">Click here to download results</a>');
  	 	}

   		exit();

}


function generateTableRow($array, $isHeader)
{

	$row = "<tr>";
	foreach ($array as $data) {
		if ($isheader)
			$row .= "<th>".$data."</th>";
		else
			$row .= "<td>".$data."</td>";
   	}
   	$row .= "</tr>\n";
   	return $row;
}

function handleInputData($post)
{
   	if ($post["sootparam"] == "range") {
   		$sootmin = testInput($post["sootmin"]);
   		$sootmax = testInput($post["sootmax"]);
   		$sootstep = testInput($post["sootstep"]);
   	} else {
   		$sootmin = testInput($post["soot"]);
   		$sootmax = testInput($post["soot"]);
   		$sootstep = -1;
   		}

	if ($post["tempparam"] == "range") {
   		$tempmin = testInput($post["tempmin"]);
   		$tempmax = testInput($post["tempmax"]);
   		$tempstep = testInput($post["tempstep"]);
   	} else {
   		$tempmin = testInput($post["temp"]);
   		$tempmax = testInput($post["temp"]);
   		$tempstep = -1;
   	}

   	if ($post["fco2param"] == "range") {
   		$fco2min = testInput($post["fco2min"]);
   		$fco2max = testInput($post["fco2max"]);
   		$fco2step = testInput($post["fco2step"]);
   	} else {
   		$fco2min = testInput($post["fco2"]);
   		$fco2max = testInput($post["fco2"]);
   		$fco2step = -1;
   	}

   	if ($post["thicknessparam"] == "range") {
   		$thicknessmin = testInput($post["thicknessmin"]);
   		$thicknessmax = testInput($post["thicknessmax"]);
   		$thicknessstep = testInput($post["thicknessstep"]);
   	} else {
   		$thicknessmin = testInput($post["thickness"]);
   		$thicknessmax = testInput($post["thickness"]);
   		$thicknessstep = -1;
	}

   	if ($post["pathparam"] == "range") {
   		$pathmin = testInput($post["pathmin"]);
   		$pathmax = testInput($post["pathmax"]);
   		$pathstep = testInput($post["pathstep"]);
   	} else {
   		$pathmin = testInput($post["path"]);
   		$pathmax = testInput($post["path"]);
   		$pathstep = -1;
   	}

   	if ($post["twallparam"] == "range") {
   		$twallmin = testInput($post["twallmin"]);
   		$twallmax = testInput($post["twallmax"]);
   		$twallstep = testInput($post["twallstep"]);
	} else {
   		$twallmin = testInput($post["twall"]);
   		$twallmax = testInput($post["twall"]);
   		$twallstep = -1;
   	}

   	if (isset($post["download"]))
   		$downloadfile = true;

   	return array($sootmin, $sootmax, $sootstep, $tempmin, $tempmax, $tempstep,
   				$fco2min, $fco2max, $fco2step, $thicknessmin, $thicknessmax, $thicknessstep,
   				$pathmin, $pathmax, $pathstep, $twallmin, $twallmax, $twallstep, $downloadfile);

}

function runOneNTSCalculation($soot, $temp, $fco2, $thickness, $path)
{
	// print("  path = ". $path ."<br>");
	$path1 = $path;
	if ($path1 < 1) $path = 1;
	if ($path1 > 8) $path = 8;
	$xp = array($soot, $temp, $fco2, $thickness, $path);


	//print("Input Parameters: ");
	//print_r($xp);

    $nts = new NeuTanSigma();
    list($xt, $gamma, $xt_all) = $nts->calculateXT($xp);

	return array($xt, $gamma, $xt_all);
}

function runOneAbsorbCalculation($soot, $temp, $fco2, $thickness, $path, $twall)
{
	//  print("  path = ". $path ."<br>");
	$path1 = $path;
	if ($path1 < 1) $path = 1;
	if ($path1 > 8) $path = 8;
	$xp = array($soot, $temp, $fco2, $thickness, $path, $twall);

	//print("Input Parameters: ");
	//print_r($xp);

    $absorb = new Absorb();
    list($xt, $gamma, $xt_all) = $absorb->calculateXTw($xp);

	return array($xt, $gamma, $xt_all);
}

function print1DArray(&$array) {
  print("(");
  for ($cell = 0; $cell < count($array); $cell++) {
    print($array[$cell]);
    if ($cell < count($array)-1)
      print(", ");
  }
}

function outputCSV($array) {
    $fp = fopen('php://output', 'w'); // this file actual writes to php output
    fputcsv($fp, $array);
    fclose($fp);
}

/**
 *  getCSV creates a line of CSV and returns it.
 */
function getCSV($array) {
    ob_start(); // buffer the output ...
    outputCSV($array);
    return ob_get_clean(); // ... then return it as a string!
}


// trims the input from the server -- deletes whitespace and stuff.
function testInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>
