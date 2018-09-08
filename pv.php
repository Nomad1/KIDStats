<!--
<?php
/*
 * Very simple PHP module for Midnite Kit
 *
 * Nomad1: This page uses PC mode response from KID 
 * Also I took the template code from
 * PhpEpsolarTracer project by Luca Soltoggio
 * https://github.com/toggio/PhpEpsolarTracer/blob/master/example_web.php
 * 
 * Status codes are taken from http://midniteftp.com/forum/index.php?topic=2034.0
 *
 */

// Constant values. Change for your needs!
// Automatically calculated for your battery voltage, no need to set 22.5 for 24V, etc.

$disconnectVoltage = 9;
$lowVoltage = 11.25;
$highVoltage = 15.25;
$tempFile = '/tmp/midnite';

// Read temporary PC Mode file and split it to array

$lines = explode(',', file_get_contents($tempFile));

// Get Info and check if is connected
if (count($lines) >= 10)
{
	$connection="Connected";
	$connection_bgcolor = "lime";

	$tracerstatus_bgcolor = "lime";

	$error = false;
	switch ($lines[10])
	{
		case 1: $eStatus = "Wake state"; break;
		case 2: $eStatus = "Insane Ibatt on WakeUp state (offset changed from off state)"; break;
		case 3: $eStatus = "Negative current on WakeUp state"; break;
		case 4: $eStatus = "dispavgVpv < (dispavgVbatt - 10) Now -25 (RestartTimerms = 1500)"; break;
		case 5: $eStatus = "Too low power and Vbatt below set point for 90 seconds"; break;
		case 6: $eStatus = "FETtemperature >= 100C Hot"; $error=true; break;
		case 7: $eStatus = "Ground Fault"; $error=true; break;
		case 8: $eStatus = "Arc Fault"; $error=true; break;
		case 9: $eStatus = "(IbattDisplaySi < -15) (negative current) (MB 4200)"; break;
		case 10: $eStatus = "(dispavgVbatt < LBDlowV) Battery less than 8 Volts"; break;
		case 11: $eStatus = "Vpv >= 0.9 of Voc but slow. Low Light #1"; break;
		case 12: $eStatus = "Vpv < 0.9 of Voc Low Light #2"; break;
		case 13: $eStatus = "Vpv > (Voc + 10V) in PV_Uset || Solar1_OandP"; break;
		case 14: $eStatus = "Vpv >= 0.9 of Voc but slow. Low Light #3"; break;
		case 15: $eStatus = "Vpv < 0.9 of Voc and taking too long. Low Light #4"; break;
		case 16: $eStatus = "Normally because user turned MODE OFF... Disabled"; break;
		case 17: $eStatus = "Vpv > 150V (classic 150)"; break;
		case 18: $eStatus = "Vpv > 200V (classic 200)"; break;
		case 19: $eStatus = "Vpv > 250V (classic 250)"; break;
		case 22: $eStatus = "Average Battery Voltage is too high above set point (RestartTimerms = 2 sec)"; break;
		case 25: $eStatus = "Battery breaker tripped (Vbatt shot up high)"; $error=true; break;
		case 26: $eStatus = "Mode changed while running"; break;
		case 27: $eStatus = "bridge center == 1023 (R132 might have been stuffed old units)"; break;
		case 28: $eStatus = "NOT Resting but RELAY is not engaged for some reason"; break;
		case 29: $eStatus = "ON/OFF stays off because WIND GRAPH is insane"; break;
		case 30: $eStatus = "PkAmpsOverLimit (will change somewhat 1-23-2013)"; break;
		case 31: $eStatus = "AD1CH.IbattMinus > 900 (peak negative battery current)"; break;
		case 32: $eStatus = "Aux 2 Logic input is high. Aux2Function 15 (external disable/enable)"; break;
		case 33: $eStatus = "OCP in a mode other than Solar or PV-Uset (1-10-2013)"; break;
		case 34: $eStatus = "AD1CH.IbattMinus > 900 Classic 150"; break;
		case 35: $eStatus = "Vbatt < 8.6 V (LOW LOW battery)"; break;
		case 36: $eStatus = "Battery temperature is Greater than reg address 4161 specified"; break;
		case 38: $eStatus = "is the new sleep because other charging sources appear to be active"; break;
		case 136: $eStatus = "Battery temperature fell below MB reg. 4161 - 10 C (Classic turned back on)"; break;
		default: $eStatus = "Unknown (" . $lines[10] . ")"; break;
	};
	if ($error)
	{
		$eStatus = "<font color=\"red\">FAULT</font>";
		$tracerstatus_bgcolor = "red";		
	}

	$battSoc = $lines[8]/1.0;
	$battVoltage = (($lines[1])/10.0);

	$divider = round($battVoltage / 12.5); // will give 1x for 6.25-18.25V, 2x for 18.25-31.25V, etc.
	$battNomVoltage = $divider * 12.0;
	$battLevel = $battVoltage / $divider; 

	if ($divider == 0)
	{
		$bStatus = "<font color=\"red\">FAULT</font>"; 
		$tracerstatus_bgcolor = "red";
	}
	else if ($battLevel < $disconnectVoltage)
		$bStatus = "<font color=\"red\">Low volt disconnect</font>";
	else if ($battLevel < $lowVoltage)
		$bStatus = "<font color=\"yellow\">Undervolt</font>";
	else if ($battLevel > $highVoltage)
		$bStatus = "<font color=\"red\">Overvolt</font>";
	else	
		$bStatus = "Normal";

	$battCurrent = (($lines[7])/10.0);
	$battPower = (($lines[9])/1.0); 
	$pvVoltage = (($lines[2])/10.0);
	$pvPower = (($lines[3])/1.0);
	$pvCurrent = round($pvPower*10.0/$pvVoltage)/10.0;
	$battTemperature = ($lines[6]/10.0);
	$totalKWH = ($lines[4])/10.0;
	$totalAH = ($lines[5])/10.0;
	$lastUpdate = date("D M j Y G:i:s", $lines[0]);
}
else
{
	$connection="Disconnected";
	$connection_bgcolor = "red";
	$tracerstatus_bgcolor = "#dedede";
}

?>
-->
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title>PV Statistics</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<style>
	table.gridtable {
		font-family: verdana,arial,sans-serif;
		font-size:12px;
		color:#333333;
		border-width: 1px;
		border-color: #666666;
		border-collapse: collapse;
		width: 100%;
	}
	
	table.gridtable th {
		border-width: 1px;
		padding: 8px;
		border-style: solid;
		border-color: #666666;
		background-color: #dedede;
		text-align: center;
	}

	table.gridtable th.connection {
		background-color: <?=$connection_bgcolor?>;
		text-align:center;
	}
	
		table.gridtable th.tracerstatus {
		background-color: <?=$tracerstatus_bgcolor?>;
		text-align:center;
	}

	table.gridtable td {
		border-width: 1px;
		border-top: 0px;
		padding: 5px;
		border-style: solid;
		border-color: #666666;
		background-color: #ffffff;
		text-align:right;
		height:17px;
	}

	table.gridtable td.bold {
		font-weight: bold;
		width: 33.3%;
		text-align:left;
	}

	table.gridtable td.head {
		font-weight: bold;
		width: 33.3%;
		text-align:right;
	}

	table.gridtable td.button {
		width: 15%;
		text-align:center;
		background-color:#efefef;
		color:#cecece;
		cursor: default;
	}

	div.centered 
	{
	text-align: center;
	}

	div.inner
	{
	max-width: 650px;
	width: 95%;
	text-align: center;
	margin: 0 auto;
	}
	div.inner table
	{
	margin: 0 auto; 
	text-align: left;
	}

	#chargepercentp {
		width: 100%;
		height: 100%;
		position: absolute;
		vertical-align: middle;
		left:-5px;
		z-index: 10;
	}

	#chargepercentg {
		top: 0;
		width: <?=$battSoc?>%;
		height: 100%;
		position: absolute;
		background-color:#dedede;
		margin: 0 auto;
		padding: 0;
		z-index: 1;
	}

	#container {
		position: relative;
		top: 0;
		left: 0;
		width:100%;
		height:100%;
		margin: 0 auto;
		padding: 0;
		vertical-align: middle;
		line-height: 27px;
	}
	</style> 
    
  </head>
  <body>
  
<div class="centered">
<div class="inner">
<p style="font-family: verdana,arial,sans-serif; font-size:16px; font-weight:bold;">Midnite KID Charger statistics</p>


<table class="gridtable">
<tr>
	<th class="connection" id="connection"><?=$connection?></th>
</tr>

</table>

<br>

<table class="gridtable">
<tr>
	<th class="tracerstatus" id="tracerstatus" colspan=2>-= Charger Status =-</th>
</tr>
<tr>
	<td class="bold">Battery status</td><td class="status" id="batterystatus"><?=$bStatus?></td>
</tr>
<tr>
	<td class="bold">Reason For Resting</td><td class="status" id="equipmentstatus"><?=$eStatus?></td>
</tr>
<tr>
	<td class="bold">Battery SOC</td><td style="padding:0px; height:27px;"><div id="container"><div id="chargepercentg"></div><div id="chargepercentp"><?=$battSoc?>%</div></div></td>
</tr>
</table>

<br>

<table class="gridtable">
<tr>
	<th colspan=2>-= Midnite KID Data =-</th>
</tr>
<tr>
	<td class="bold">Nominal battery Voltage</td><td class="data" id="nbatteryvoltage"><?=$battNomVoltage?>V</td>
</tr>
<tr>
	<td class="bold">Battery Voltage</td><td class="data" id="batteryvoltage"><?=$battVoltage?>V</td>
</tr>
<tr>
	<td class="bold">Battery Current (WBJR)</td><td class="data" id="batterycurrent"><?=$battCurrent?>A</td>
</tr>
<tr>
	<td class="bold">Battery Charge (WBJR)</td><td class="data" id="batterypower"><?=$battPower?>Ah</td>
</tr>
<tr>
	<td class="bold">Panel Voltage</td><td class="data" id="panelvoltage"><?=$pvVoltage?>V</td>
</tr>
<tr>
	<td class="bold">Panel Current</td><td class="data" id="panelcurrent"><?=$pvCurrent?>A</td>
</tr>
<tr>
	<td class="bold">Panel Power</td><td class="data" id="panelpower"><?=$pvPower?>W</td>
</tr>
<tr>
	<td class="bold">Battery Temperature</td><td class="data" id="batterytemperature"><?=$battTemperature?><sup>o</sup>C</td>
</tr>
<tr>
	<td class="bold">Today Watt*hours produced</td><td class="data" id="totalkwh"><?=$totalKWH?>kWh</td>
</tr>                    
<tr>
	<td class="bold">Today Amper*hours produced</td><td class="data" id="totalah"><?=$totalAH?>Ah</td>
</tr>
<tr>
	<td class="bold">Last Update<td class="data" id="update"><?=$lastUpdate?></td>
</tr>
</table>

<br>

<br>

</div>
</div>

</body>
</html>