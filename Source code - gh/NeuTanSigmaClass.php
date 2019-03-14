<?php

class NeuTanSigma {
  
  var $parameters;  // array of arrays of parameters
  // This is the structure:
  // $parameters[<variable>][<index>] -- this carries the data for one variable.
  // e.g. $parameters["w1"][1] -- will carry the data for w1, within the range referred to by index 1
  // e.g. $parameters["w2"][1] -- will carry the data for w2, within the range referred to by index 1
  
  // This is an array that stores the indices that denote the various ranges
  var $indices;

  // These are the individual parameters. They will be pointers to structures within the $parameters array.
  // e.g. if input temperature is between $parameters["xtmin"][1] and $parameters["xtmax"][1], then $w1 points to $parameters["w1"][1] 
  var $w1, $w2, $w3;
  var $b1, $b2, $b3;
  var $xpmin, $xpmax;
  var $xtmin, $xtmax;

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
    $this->readInParameters("param_em.txt");

    // Now we need to check the input values, and set the pointers to the appropriate data
    foreach ($this->indices as $index)  { // loops over all indices in the parameters file
    	// check the values of the input data (stored in the $xp array) against
	// the ranges of the corresponding $xpmin and $xpmax for this index range
	// Note: the number of dimensions for $xpmin (and $xpmax) may vary!

	$dimensions = count($this->parameters["xpmin"][$index]);
	$withinRange = true;  // this will be set to FALSE if one of the ranges don't match
	for ($i = 0; $i < $dimensions; $i++) {
	    if ($xp[$i] < $this->parameters["xpmin"][$index][$i] or
	    	$xp[$i] > $this->parameters["xpmax"][$index][$i])
		$withinRange = false;
        }

        if ($withinRange == true)
        {
    //       print("<br>Using parameters from Range $index</br>");
		   $this->w1 = $this->parameters["w1"][$index];
		   $this->w2 = $this->parameters["w2"][$index]; 
      	   $this->w3 = $this->parameters["w3"][$index]; 
      	   $this->b1 = $this->parameters["b1"][$index];
      	   $this->b2 = $this->parameters["b2"][$index];
      	   $this->b3 = $this->parameters["b3"][$index];
      	   $this->xtmin = $this->parameters["xtmin"][$index];
      	   $this->xtmax = $this->parameters["xtmax"][$index];
      	   $this->xpmin = $this->parameters["xpmin"][$index];
      	   $this->xpmax = $this->parameters["xpmax"][$index];
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

  function calculateXT(&$xp) {

    $debug = false;
 //  $debug = true;

    // set values of parameters. Eventually this will take an argument
    if (!$this->setParameters($xp)) {
      return array("NaN", "NaN", "NaN");
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
      print("xpmin=");
      print_r($xpmin);
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
      print("xt=".$xt);

    $gamma = $this->findGamma($xp[0],$xp[1]);
//    print("Gamma is". $gamma ."<br>");
    $xt_all = $xt+$gamma;
    
    return(array($xt, $gamma, $xt_all));
  }

  }

?>
