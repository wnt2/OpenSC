<?php

class Absorb {
  
  var $parameters;  // array of arrays of parameters
  // This is the structure:
  // $parameters[<variable>][<index>] -- this carries the data for one variable.
  // e.g. $parameters["w1"][1] -- will carry the data for w1, within the range referred to by index 1
  // e.g. $parameters["w2"][1] -- will carry the data for w2, within the range referred to by index 1
  
  // This is an array that stores the indices that denote the various ranges
  var $indices;

  // These are the individual parameters. They will be pointers to structures within the $parameters array.
  // e.g. if input temperature is between $parameters["xtmin"][1] and $parameters["xtmax"][1], then $w1 points to $parameters["w1"][1] 
  var $aw1, $aw2, $aw3;
  var $ab1, $ab2, $ab3;
  var $axpmin, $axpmax;
  var $axtmin, $axtmax;

  function readInParameters($filename) {
    $parameters = array();
    $indices = array();
    $current_variable = "";
    $current_index = "";
    $current_type = "";
    
    $infile = fopen($filename, "r");
    if ($infile) {
      while(($line = fgets($infile, 4096)) !== false) {
	$line = trim($line);
	if ($line == "" || $line[0] == "#") 
	  continue;
	else if ($line[0] == ":")  {  // this is the name of the variable
	  // parse the header line for the variable
	  // format of the line is ": var_index type", e.g. ": w1_1 matrix"
	  $tokens = explode(" ", $line);  
	  $current_type = $tokens[2];
	  $tokens = explode("_", $tokens[1]);
	  if (count($tokens) == 2) 
	     list($current_variable, $current_index) = $tokens;
	  else { // some values, such as gamma, do not have an index range.
	     $current_variable = $tokens[0];
	     $current_index = -1;
	  }
	  
	  // If this is the first time we're seeing this index, add to $indices array.
	  if (in_array($current_index, $indices, TRUE) == FALSE and $current_index != -1) {
	     array_push($indices, $current_index);
	  }

	  // If the data is a vector or a matrix, then we need to set the data structure
	  if ($current_type == "vector" or $current_type == "matrix")
	    $parameters[$current_variable][$current_index] = array();
	} else {
	  // there are three possible types of parameters: matrices, vectors and floating point values
	  if ($current_type == "float")   {
	    $parameters[$current_variable][$current_index] = floatval($line);
	  } else {
	    // This will handle vectors and matrices.
	    $parts = explode(",", $line);  // separate the CSV values
	    if ($current_type == "matrix") {
	      array_push($parameters[$current_variable][$current_index], array()); // add new row
	      $row_num = count($parameters[$current_variable][$current_index]);
	    }
	    for ($i = 0; $i < count($parts); $i++) {
	      if ($current_type == "matrix")
		$parameters[$current_variable][$current_index][$row_num-1][$i] = floatval($parts[$i]);
	      else
		$parameters[$current_variable][$current_index][$i] = floatval($parts[$i]);
	    }
	  }
	}
      }
    }

    // Set the global (instance) variables
    $this->parameters = $parameters;
    $this->indices = $indices;
  }

  function setParameters(&$xp) {
    // read in all the parameters from the file

//	print(" in setParameters");
//	print_r($xp);
	$chktwall = 1;
//	print("<li>Twall = " . $xp[5]."</li>");
//	print("<li>chktwall = " . $chktwall."</li>");
	if ($xp[5] == 300) {  
  	$chktwall = 2; 
  	$this->readInParameters("param_ab_300.txt");
	} else 
	if ($xp[5] == 400) {  
  	$chktwall = 2; 
  	$this->readInParameters("param_ab_400.txt");
	} else {
	if ($xp[5] == 500) {  
  	$chktwall = 2; 
  	$this->readInParameters("param_ab_500.txt");
	} else {
	if ($xp[5] == 700) {  
  	$chktwall = 2; 
  	$this->readInParameters("param_ab_700.txt");
	} else {
	if ($xp[5] == 1000) {  
  	$chktwall = 3; 
  	$this->readInParameters("param_ab_1k.txt");
	} else {
	if ($xp[5] == 1500) {  
  	$chktwall = 3; 
  	$this->readInParameters("param_ab_1p5k.txt");
	} else {
  	return false;
	}}}}}

    // Now we need to check the input values, and set the pointers to the appropriate data
    foreach ($this->indices as $index)  { // loops over all indices in the parameters file
    	// check the values of the input data (stored in the $xp array) against
	// the ranges of the corresponding $xpmin and $xpmax for this index range
	// Note: the number of dimensions for $xpmin (and $xpmax) may vary!

	$dimensions = count($this->parameters["axpmin"][$index]);
	$withinRange = true;  // this will be set to FALSE if one of the ranges don't match
	for ($i = 0; $i < $dimensions; $i++) {
	    if ($xp[$i] < $this->parameters["axpmin"][$index][$i] or
	    	$xp[$i] > $this->parameters["axpmax"][$index][$i])
		$withinRange = false;
        }

        if ($withinRange == true)
        {
        	$this->w1 = $this->parameters["aw1"][$index];
        	$this->w2 = $this->parameters["aw2"][$index]; 
      	   $this->w3 = $this->parameters["aw3"][$index]; 
      	   $this->b1 = $this->parameters["ab1"][$index];
      	   $this->b2 = $this->parameters["ab2"][$index];
      	   $this->b3 = $this->parameters["ab3"][$index];
      	   $this->xtmin = $this->parameters["axtmin"][$index];
      	   $this->xtmax = $this->parameters["axtmax"][$index];
      	   $this->xpmin = $this->parameters["axpmin"][$index];
      	   $this->xpmax = $this->parameters["axpmax"][$index];
	   return true;
        }
    }
    print("<br>No valid ranges found!</br>");
    return false;
  }

  function findGamma($sootvf,$tg) {
    $gammaArray = $this->parameters["gamma"][-1];
//    print_r($this->parameters["gamma"]);
    $low = count($gammaArray);
//      print("low = ". $low . "<br>");
    $kk = 7.0;
    $soot = 1.0 +$kk*$sootvf*$tg/0.014388;

    $xntot = 0.0;
    if ($soot > 2) $xntot = $soot -2;

    $epsilon=0.0000000;
//      print("sootvf = ". $sootvf . "  tg = ". $tg . "<br>");
//      print("soot = ". $soot . "xntot = ". $xntot ."<br>");
//    for ($i = 0; $i < count($gammaArray)+1; $i++) {
    for ($i = 0; $i < count($gammaArray); $i++) {
    	if ($soot >= $gammaArray[$i][0]-$epsilon && $soot <= $gammaArray[$i+1][0]+$epsilon)
	   $low = $i;
    }

//      print("low = ". $low . "<br>");

    if ($low == count($gammaArray))
       return $gammaArray[$low-1][1];   // return the maximum value

    $fraction = ($soot-$gammaArray[$low][0])/($gammaArray[$low+1][0]-$gammaArray[$low][0]);
    $gamma = $gammaArray[$low][1]+$fraction*($gammaArray[$low+1][1]-$gammaArray[$low][1]);
    $pentagamma = $gamma/$gammaArray[0][1];
//      print("fraction = ". $fraction . "  gammalow = ". $gammaArray[$low][1] . "  gammahi = ". $gammaArray[$low+1][1] ."<br>");
//      print("  gamma = ". $gamma ."  pentagamma = ". $pentagamma ."<br>");

	if ($soot > 2)
	{$soot = $soot -1;
	for ($i = 0; $i < $soot -2; $i++) {
	$soot = $soot +1;
	$gamma = $gamma -6.0/$soot^4;
	}
    $pentagamma = $gamma/$gammaArray[0][1];}


     $fvabs = 1.0 -$pentagamma;
    return $fvabs;
  }
  
  function calculateXTw(&$xp) {
  	list($soot, $temp, $fco2, $thickness, $path, $twall) = $xp;
  	
  	// find twall_low and twall_high for interpolation
  	$twdata = array(300, 400, 500, 700, 1000, 1500); // we have twall data for these values
  	$delta = 0.000001;
  	$twall_low = -1;
  	$twall_high = -1;
  	
  	if ($twall < $twdata[0] or $twall > $twdata[count($twdata)-1]) {
  	  	  // out of bounds, return error message
  	  	  return array(-1, -1, -1);
  	}
	
  	 // linear search for the time being
  	 for ($i = 0; $i < count($twdata)-1; $i++) {
  	  	 if ($twall >= $twdata[$i]-$delta && $twall <= $twdata[$i+1] +$delta) {
  	  	  	  $twall_low = $twdata[$i];
  	  	  	  $twall_high = $twdata[$i+1];
  	  	 }
  	 } 
  	if ($twall_low == -1) return array(-1, -1, -1);  // error check, should not happen 
  	
  	$xp_low = array($soot, $temp, $fco2, $thickness, $path, $twall_low);
  	$xp_high = array($soot, $temp, $fco2, $thickness, $path, $twall_high);

  	list($axt_low, $agamma_low, $axt_all_low) = $this->calculateXTw1($xp_low);
  	list($axt_high, $agamma_high, $axt_all_high) = $this->calculateXTw1($xp_high); 
  	$gamma = $this->findGamma($soot, $twall);
  	
  	$fract = ($twall -$twall_low)/($twall_high -$twall_low);
 	$axt = $axt_low +$fract*($axt_high -$axt_low);
 	$axt_all = $axt+$gamma;
 	
 	return array($axt, $gamma, $axt_all);
  	
  }

  function calculateXTw1(&$xp) {

    $debug = false;
//    print("Gamma is". $gamma ."<br>");
//    $debug = true;

    // set values of parameters. Eventually this will take an argument
    if (!$this->setParameters($xp)) {
      print("Input Data out of Range!<br>");
      exit();
    }

    // set local variables to point to instance variables for convenenience's sake (so we don't need $this)
    $w1 = $this->w1; 
    $w2 = $this->w2; 
    $w3 = $this->w3; 
    $b1 = $this->b1;
    $b2 = $this->b2;
    $b3 = $this->b3;
    $xpmin = $this->xpmin;
    $xpmax = $this->xpmax;
    $xtmin = $this->xtmin;
    $xtmax = $this->xtmax;

    if ($debug) {
      print("xp=");
      print_r($xp);
    }

    $np = count($xpmin);  // the number of physical parameters
    
    $z = array();
    for ($ip = 0; $ip < $np; $ip++) {
      $val = -1.0 + ($xp[$ip] - $xpmin[$ip])/($xpmax[$ip] - $xpmin[$ip])*2.0;
      array_push($z, $val);
    }
    
    if ($debug) {
      print("z=");
      print_r($z);
    }
    
    $abdnnet = 0.0;
    $ns1 = count($w1); 
    if ($debug) {
      print("ns1 = ". $ns1 . "<br>");
      print("np = ". $np . "<br>");
    }
    for ($row = 0; $row < $ns1; $row++) {
      $xxnnet = 0.0;
      for ($col = 0; $col < $np; $col++) {
	$xxnnet += $w1[$row][$col] * $z[$col];
      if ($debug) {
	print($row . $col .": w1=".$w1[$row][$col]."<br>");
	print($row . ": xxnnet=".$xxnnet."<br>");
      }
      }
      $xxnnet += $b1[$row];
      
      $xnnet_1[$row] = tanh($xxnnet);
      if ($debug) {
	print($row . ": b1=".$b1[$row]."<br>");
	print($row . ": xxnnet=".$xxnnet."<br>");
	print($row . ": xnnet_1=".$xnnet_1[$row]."<br>");
      }
    }
    
    if ($debug) {
      print("xnnet_1=");
      print_r($xnnet_1);
    }
    
    $abdnnet = 0.0;
    $ns2 = count($w2); 
    if ($debug) {
      print("ns1 = ". $ns1 . "<br>");
      print("ns2 = " . $ns2 . "<br>");
    }
    for ($row = 0; $row < $ns2; $row++) {
      $xxnnet = 0.0;
      for ($col = 0; $col < $ns1; $col++) {
	$xxnnet += $w2[$row][$col] * $xnnet_1[$col];
	if ($debug) {
	  print("w2[$row][$col] = ". $w2[$row][$col] . " xnnet_1[$col] = " . $xnnet_1[$col] . " xxnnet = " . $xxnnet . "<br>"); 
	}
      }
      $xxnnet += $b2[$row];
      if ($debug)
	print($row . ": xxnnet = " . $xxnnet . "<br>");
      $xxnnet = tanh($xxnnet);
      if ($debug)
	print($row . ": xxnnet = " . $xxnnet . "<br>");
      $abdnnet += $xxnnet*$w3[$row];
      if ($debug)
	print("abdnnet = ". $abdnnet . "<br>");
    }
    
    $abdnnet += $b3;
    $xt = 0.5*($abdnnet+1.0)*($xtmax-$xtmin)+$xtmin;
    
    
    if ($debug) 
      print("xt=".$xt. "<br>");
	

    $gamma = $this->findGamma($xp[0],$xp[5]);
//    print("Gamma is". $gamma ."<br>");
    $xt_all = $xt+$gamma;
    return(array($xt, $gamma, $xt_all));

   }

  function calculateXTw0($soot, $twall) {

    $debug = false;
//    print("Gamma is". $gamma ."<br>");
//    $debug = true;
//print_r($xp);
	$xp1 = array($xp[0], $xp[1], $xp[2], 0.05, $xp[4], 300);
//	print_r($xp1);
    // set values of parameters. Eventually this will take an argument
    if (!$this->setParameters($xp1)) {
      print("Input Data out of Range!<br>");
      exit();
    }

	$xt = 0;
    $gamma = $this->findGamma($xp[0],$xp[5]);
    $xt_all = $xt+$gamma;
	print("thickness = ". $xp[3] ."<br>");
	print("xt = ". $xt ."<br>");
	print("gamma = ". $gamma ."<br>");
    return(array($xt, $gamma, $xt_all));
	}}

?>
