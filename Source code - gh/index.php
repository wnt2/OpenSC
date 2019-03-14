<html>
<head>
<title>Calculation of Emissivity/Absorptivity of a CO2/H2O/N2/soot mixture
       Using Neural Networks</title>

    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js"></script>
	<script type="text/javascript">

	// Allow/disallow input into the range/single value input boxes only if the appropriate buttons are pressed
	$(function(){
	    $("#sootmin-input").attr("disabled", true);
	    $("#sootmax-input").attr("disabled", true);
	    $("#sootstep-input").attr("disabled", true);
        $('#sootrange').click(function(){
        	$('#soot-input').attr('disabled',this.checked);
            $('#sootmin-input').attr('disabled',!this.checked);
            $('#sootmax-input').attr('disabled',!this.checked);
            $('#sootstep-input').attr('disabled',!this.checked);
        });
        $('#sootsingle').click(function(){
            $('#soot-input').attr('disabled', !this.checked);
            $('#sootmin-input').attr('disabled', this.checked);
            $('#sootmax-input').attr('disabled', this.checked);
            $('#sootstep-input').attr('disabled', this.checked);
        });
        $("#tempmin-input").attr("disabled", true);
	    $("#tempmax-input").attr("disabled", true);
	    $("#tempstep-input").attr("disabled", true);
        $('#temprange').click(function(){
        	$('#temp-input').attr('disabled',this.checked);
            $('#tempmin-input').attr('disabled',!this.checked);
            $('#tempmax-input').attr('disabled',!this.checked);
            $('#tempstep-input').attr('disabled',!this.checked);
        });
        $('#tempsingle').click(function(){
            $('#temp-input').attr('disabled', !this.checked);
            $('#tempmin-input').attr('disabled', this.checked);
            $('#tempmax-input').attr('disabled', this.checked);
            $('#tempstep-input').attr('disabled', this.checked);
        });
        $("#fco2min-input").attr("disabled", true);
	    $("#fco2max-input").attr("disabled", true);
	    $("#fco2step-input").attr("disabled", true);
        $('#fco2range').click(function(){
        	$('#fco2-input').attr('disabled',this.checked);
            $('#fco2min-input').attr('disabled',!this.checked);
            $('#fco2max-input').attr('disabled',!this.checked);
            $('#fco2step-input').attr('disabled',!this.checked);
        });
        $('#fco2single').click(function(){
            $('#fco2-input').attr('disabled', !this.checked);
            $('#fco2min-input').attr('disabled', this.checked);
            $('#fco2max-input').attr('disabled', this.checked);
            $('#fco2step-input').attr('disabled', this.checked);
        });
        $("#thicknessmin-input").attr("disabled", true);
	    $("#thicknessmax-input").attr("disabled", true);
	    $("#thicknessstep-input").attr("disabled", true);
        $('#thicknessrange').click(function(){
        	$('#thickness-input').attr('disabled',this.checked);
            $('#thicknessmin-input').attr('disabled',!this.checked);
            $('#thicknessmax-input').attr('disabled',!this.checked);
            $('#thicknessstep-input').attr('disabled',!this.checked);
        });
        $('#thicknesssingle').click(function(){
            $('#thickness-input').attr('disabled', !this.checked);
            $('#thicknessmin-input').attr('disabled', this.checked);
            $('#thicknessmax-input').attr('disabled', this.checked);
            $('#thicknessstep-input').attr('disabled', this.checked);
        });
        $("#pathmin-input").attr("disabled", true);
	    $("#pathmax-input").attr("disabled", true);
	    $("#pathstep-input").attr("disabled", true);
        $('#pathrange').click(function(){
        	$('#path-input').attr('disabled',this.checked);
            $('#pathmin-input').attr('disabled',!this.checked);
            $('#pathmax-input').attr('disabled',!this.checked);
            $('#pathstep-input').attr('disabled',!this.checked);
        });
        $('#pathsingle').click(function(){
            $('#path-input').attr('disabled', !this.checked);
            $('#pathmin-input').attr('disabled', this.checked);
            $('#pathmax-input').attr('disabled', this.checked);
            $('#pathstep-input').attr('disabled', this.checked);
        });
        $("#twallmin-input").attr("disabled", true);
	    $("#twallmax-input").attr("disabled", true);
	    $("#twallstep-input").attr("disabled", true);
        $('#twallrange').click(function(){
        	$('#twall-input').attr('disabled',this.checked);
            $('#twallmin-input').attr('disabled',!this.checked);
            $('#twallmax-input').attr('disabled',!this.checked);
            $('#twallstep-input').attr('disabled',!this.checked);
        });
        $('#twallsingle').click(function(){
            $('#twall-input').attr('disabled', !this.checked);
            $('#twallmin-input').attr('disabled', this.checked);
            $('#twallmax-input').attr('disabled', this.checked);
            $('#twallstep-input').attr('disabled', this.checked);
        });
    });

	$(document).ready(function(){
		$("#compute").validate({
			debug: false,
			rules: {
				soot: { required: "#sootsingle:checked" },
				sootmin: { required: "#sootrange:checked" },
				sootmax: { required: "#sootrange:checked" },
				sootstep: { required: "#sootrange:checked" },
				temp: { required: "#tempsingle:checked" },
				tempmin: { required: "#temprange:checked" },
				tempmax: { required: "#temprange:checked" },
				tempstep: { required: "#temprange:checked" },
				thickness: { required: "#thicknesssingle:checked" },
				thicknessmin: { required: "#thicknessrange:checked" },
				thicknessmax: { required: "#thicknessrange:checked" },
				thicknessstep: { required: "#thicknessrange:checked" },
				fco2: { required: "#fco2single:checked" },
				fco2min: { required: "#fco2range:checked" },
				fco2max: { required: "#fco2range:checked" },
				fco2step: { required: "#fco2range:checked" },
				path: { required: "#pathsingle:checked" },
				pathmin: { required: "#pathrange:checked" },
				pathmax: { required: "#pathrange:checked" },
				pathstep: { required: "#pathrange:checked" },
				twall: { required: "#twallsingle:checked" },
				twallmin: { required: "#twallrange:checked" },
				twallmax: { required: "#twallrange:checked" },
				twallstep: { required: "#twallrange:checked" },
			},

			submitHandler: function(form) {
				// do other stuff for a valid form
				$.post('compute.php', $("#compute").serialize(), function(data) {
					$('#results').html(data);
				});
			}
		});

		// adapted from http://stackoverflow.com/questions/16078544/export-to-csv-using-jquery-and-html
		function exportTableToCSV($table, filename) {
        	var $rows = $table.find('tr:has(td),tr:has(th)'),
            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character
            // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',
            // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                var $row = $(row),
                    $cols = $row.find('td,th');
                return $cols.map(function (j, col) {
                    var $col = $(col),
                        text = $col.text();
                    return text.replace(/"/g, '""'); // escape double quotes
                }).get().join(tmpColDelim);
            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '"',
            // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
        	$(this)
            	.attr({
            		'download': filename,
            	    'href': csvData,
            	    'target': '_blank'
        	});
  	  	}

    	// This must be a hyperlink
    	$("[id=export]").on("click", function(event){
        	exportTableToCSV.apply(this, [$('#results>table'), 'results.csv']);
    	});
	});

	jQuery.extend(jQuery.validator.messages, { required: "**" });
	</script>

    <style>
    label.error { width:250px; display: inline; color: red;}
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    th, td {
        padding: 3px;
    }
    </style>

    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-65620265-1', 'auto');
      ga('send', 'pageview');
    </script>
        
    <style>
    hr { 
        display: block;
        margin-top: 2.0em;
        margin-bottom: 2.0em;
        margin-left: auto;
        margin-right: auto;
        border-style: inset;
        border-width: 3px;
    } 
    </style>
</head>

<body>
<h1 align="center">
    A Neural Network based Calculation for Emissivity/Absorptivity of a 
    CO<sub>2</sub>/H<sub>2</sub>O/N<sub>2</sub>/Soot Mixture
</h1align="center">


<h2>Introduction</h2>

<p div="author">
    RAD-NNET computes the total emissivity and absorptivity of a combustion mixture
    (CO<sub>2</sub>/H<sub>2</sub>O/N<sub>2</sub>/soot) based on narrow band spectral data provided by RADCAL. 
    Currently, it is the only model available which can predict the 
    emissivity/absorptivity of a luminous combustion mixture with soot.  For a non-luminous
    combustion mixture without soot, it has been demonstrated to be superior to the
    approach using the Hottel's chart (as outlined in standard heat transfer text
    books).</a>
</p>

<p div="author">
    Details of the research are presented in 
    <a href="http://www.sciencedirect.com/science/article/pii/S0017931009001112" target="_blank">Walter W. Yuen, "RAD-NNET, a neural network based 
    correlation developed for a realistic simulation of the non-gray radiative heat transfer effect in 
    three-dimensional gas-particle mixtures", <i>International Journal of Heat and Mass Transfer</i>
    Volume 52, Issues 13-14, June 2009, Pages 3159-3168</a>
</p>
    

<hr>


<h2>Fill in Input Values: Total Pressure = 1 atm (101 kPa) </h2>
<form name="compute" id="compute" action="" method="post">

<table>
<tr><th rowspan="2">Mixture Properties</th><th colspan="2">Single Value Calculation</th><th colspan="4">Range Calculation</th></tr>
<tr><th cellpadding="0" cellmargin="0">Select</th><th>Value</th><th cellpadding="0" cellmargin="0">Select</th><th>Minimum Value</th><th>Maximum Value</th><th>Number of Steps (>=2)</th></tr>
<tr>
  <td>Soot Volume Fraction Pathlength (m): 0 to 0.000001</td>
  <td align="center"><input type="radio" id="sootsingle" name="sootparam" value="single" checked="checked"></td>
  <td><input type="number" name="soot" id="soot-input" step="0.01" value="<?php print $soot ; ?>"/></td>
  <td align="center"><input type="radio" id="sootrange" name="sootparam" value="range"></td>
  <td><input type="number" name="sootmin" id="sootmin-input" step="0.01" value="<?php print $sootmin ; ?>"/></td>
  <td><input type="number" name="sootmax" id="sootmax-input" step="0.01" value="<?php print $sootmax ; ?>"/></td>
  <td><input type="number" name="sootstep" id="sootstep-input" step="0.01" value="<?php print $sootstep ; ?>"/></td>
</tr>

<tr>
  <td>Temperature (K): 300 to 2000</td>
  <td align="center"><input type="radio" name="tempparam" id="tempsingle" value="single" checked="checked"></td>
  <td><input type="number" name="temp" id="temp-input" step="0.01" value="<?php print $temp ; ?>"/></td>
  <td align="center"><input type="radio" name="tempparam" id="temprange" value="range"></td>
  <td><input type="number" name="tempmin" id="tempmin-input" step="0.01" value="<?php print $temp ; ?>"/></td>
  <td><input type="number" name="tempmax" id="tempmax-input" step="0.01" value="<?php print $temp ; ?>"/></td>
  <td><input type="number" name="tempstep" id="tempstep-input" step="0.01" value="<?php print $temp ; ?>"/></td>
</tr>

<tr>
  <td>CO<sub>2</sub>/(CO<sub>2</sub>+H<sub>2</sub>O) Mole Fraction: 0.0 to 1.0</td>
  <td align="center"><input type="radio" name="fco2param" id="fco2single" value="single" checked="checked"></td>
  <td><input type="number" name="fco2" id="fco2-input" step="0.01" value="<?php print $fco2 ; ?>"/></td>
  <td align="center"><input type="radio" name="fco2param" id="fco2range" value="range"></td>
  <td><input type="number" name="fco2min" id="fco2min-input" step="0.01" ivalue="<?php print $fco2 ; ?>"/></td>
  <td><input type="number" name="fco2max" id="fco2max-input" step="0.01" value="<?php print $fco2 ; ?>"/></td>
  <td><input type="number" name="fco2step" id="fco2step-input" step="0.01" value="<?php print $fco2 ; ?>"/></td>
</tr>

<tr>
  <td>Pressure Pathlength (kPa-m): 0 to 2000 (< Path Length x 1 atm)</td>
  <td align="center"><input type="radio" name="thicknessparam" id="thicknesssingle" value="single" checked="checked"></td>
  <td><input type="number" name="thickness" id="thickness-input" step="0.01" value="<?php print $thickness ; ?>"/></td>
  <td align="center"><input type="radio" name="thicknessparam" id="thicknessrange" value="range"></td>
  <td><input type="number" name="thicknessmin" id="thicknessmin-input" step="0.01" value="<?php print $thickness ; ?>"/></td>
  <td><input type="number" name="thicknessmax" id="thicknessmax-input" step="0.01" value="<?php print $thickness ; ?>"/></td>
  <td><input type="number" name="thicknessstep" id="thicknessstep-input" step="0.01" value="<?php print $thickness ; ?>"/></td>
</tr>

<tr>
  <td>Path Length (m): > 0 </td>
  <td align="center"><input type="radio" name="pathparam" id="pathsingle" value="single" checked="checked"></td>
  <td><input type="number" name="path" id="path-input" step="0.01" value="<?php print $path ; ?>"/></td>
  <td align="center"><input type="radio" name="pathparam" id="pathrange" value="range"></td>
  <td><input type="number" name="pathmin" id="pathmin-input" step="0.01" value="<?php print $path ; ?>"/></td>
  <td><input type="number" name="pathmax" id="pathmax-input" step="0.01" value="<?php print $path ; ?>"/></td>
  <td><input type="number" name="pathstep" id="pathstep-input" step="0.01" value="<?php print $path ; ?>"/></td>
</tr>

<tr>
  <td>Source Temperature (K): 300 to 1500</td>
  <td align="center"><input type="radio" name="twallparam" id="twallsingle" value="single" checked="checked"></td>
  <td><input type="number" name="twall" id="twall-input" step="0.01" value="<?php print $twall ; ?>"/></td>
  <td align="center"><input type="radio" name="twallparam" id="twallrange" value="range"></td>
  <td><input type="number" name="twallmin" id="twallmin-input" step="0.01" value="<?php print $twall ; ?>"/></td>
  <td><input type="number" name="twallmax" id="twallmax-input" step="0.01" value="<?php print $twall ; ?>"/></td>
  <td><input type="number" name="twallstep" id="twallstep-input" step="0.01" value="<?php print $twall ; ?>"/></td>
</tr>

</table>
</p>
    
<input type=submit name="submit" value="Calculate"></p>

</form>
<!--<label><input type="checkbox" name="download">Download results to file</label>-->
<a href="#" id="export">Download Results to File</a>
<!-- We will output the results from compute.php here -->
<div id="results"></div>

    
<hr>


<h2>References</h2>
<p div="author">
    Other publications related to RAD-NNET:
</p>
<a href="http://www.sciencedirect.com/science/article/pii/S0017931009001112" target="_blank">Walter W. Yuen, "RAD-NNET, a neural network based 
correlation developed for a realistic simulation of the non-gray radiative heat transfer effect in 
three-dimensional gas-particle mixtures", <i>International Journal of Heat and Mass Transfer</i>
Volume 52, Issues 13-14, June 2009, Pages 3159-3168</a>
</p>
<a href="http://www.sciencedirect.com/science/article/pii/S0017931013006698" target="_blank">Walter W. Yuen, W. C. Tam and W. K. Chow, "Assessment of 
radiative heat transfer characteristics of a combustion mixture in a three-dimensional enclosure using RAD-NETT (with application 
to a fire resistance test furnace)", 
<i>International Journal of Heat and Mass Transfer</i>
Volume 68, January 2014, Pages 383-390</a>
</p>
<a href="https://doi.org/10.2514/6.2014-2389 target="_blank"">Walter W. Yuen, W. C. Tam and W. K. Chow, "A Realistic Radiation Heat Transfer
Model for Builidng Energy Simulation Program", accepted for publicatin in <i>Numerical Heat Transfer</i>, also
AIAA Paper 2014-2389, the 11th AIAA/ASME Joint Thermophysics and Heat Transfer Conference, June, 2014</a>
</p>
<a href="https://doi.org/10.2514/1.T4805 target="_blank"">Walter W. Yuen, L. C. Chow and W. C. Tam, "An Exact Analysis of Radiative Heat Transfer 
in Three-Dimensional Inhomogeneous Non-Isothermal Media Using Neural Networks", 
<i>AIAA Journal of Thermophysics and Heat Transfer</i>, Volume 30, Issue 4, May 2016, Pages 897-911</a>
</p>

        
</p>
        All rights reserved. For more details, please contact Walter Yuen at yuen _at_ engr.ucsb.edu or Wai Cheong Tam at waicheong.tam _at_ nist.gov.
</p>

</body>

</html>



