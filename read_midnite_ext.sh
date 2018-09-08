#!/bin/bash

# Constants
device='/dev/ttyACM0'
#device='/dev/ttyXRUSB0'
speed=57600

disconnectVoltage="9.0"
lowVoltage="11.25"
highVoltage="15.25"
tempFile='/tmp/midnite'
templateFile='/var/www/pv.php'
outputFile='/var/www/pv.html'


tput reset > $device      #it won't hurt but will reset faulty devices
stty -F $device raw ispeed $speed ospeed $speed cs8 -parenb -cstopb
data=`timeout 60 head -n 1 < $device`
ts=`date '+%s'`
echo $data
echo "$ts,$data" > $tempFile

#### Idea of extended script is to read pv.php file as a template and produce comple html page without using PHP at all

# Data processing. Exactly the same script as in pv.php, but ported to bash

IFS=$','
lines=(`cat $tempFile`)

# Get Info and check if is connected
if [ "${#lines[@]}" -ge "10" ]
then
	connection="Connected"
	connection_bgcolor="lime"

	tracerstatus_bgcolor="lime"

	error=0
	reason=${lines[10]}
	case $reason in
		1) eStatus="Wake state";;
		2) eStatus="Insane Ibatt on WakeUp state (offset changed from off state)";;
		3) eStatus="Negative current on WakeUp state"; ;;
		4) eStatus="dispavgVpv < (dispavgVbatt - 10) Now -25 (RestartTimerms=1500)"; ;;
		5) eStatus="Too low power and Vbatt below set point for 90 seconds"; ;;
		6) eStatus="FETtemperature >= 100C Hot"; error=1 ;;
		7) eStatus="Ground Fault"; error=1 ;;
		8) eStatus="Arc Fault"; error=1 ;;
		9) eStatus="(IbattDisplaySi < -15) (negative current) (MB 4200)"; ;;
		10) eStatus="(dispavgVbatt < LBDlowV) Battery less than 8 Volts"; ;;
		11) eStatus="Vpv >= 0.9 of Voc but slow. Low Light #1"; ;;
		12) eStatus="Vpv < 0.9 of Voc Low Light #2"; ;;
		13) eStatus="Vpv > (Voc + 10V) in PV_Uset || Solar1_OandP"; ;;
		14) eStatus="Vpv >= 0.9 of Voc but slow. Low Light #3"; ;;
		15) eStatus="Vpv < 0.9 of Voc and taking too long. Low Light #4"; ;;
		16) eStatus="Normally because user turned MODE OFF... Disabled"; ;;
		17) eStatus="Vpv > 150V (classic 150)"; ;;
		18) eStatus="Vpv > 200V (classic 200)"; ;;
		19) eStatus="Vpv > 250V (classic 250)"; ;;
		22) eStatus="Average Battery Voltage is too high above set point (RestartTimerms=2 sec)";;		
		25) eStatus="Battery breaker tripped (Vbatt shot up high)";error=1 ;;
		26) eStatus="Mode changed while running"; ;;
		27) eStatus="bridge center == 1023 (R132 might have been stuffed old units)"; ;;
		28) eStatus="NOT Resting but RELAY is not engaged for some reason"; ;;
		29) eStatus="ON/OFF stays off because WIND GRAPH is insane"; ;;
		30) eStatus="PkAmpsOverLimit (will change somewhat 1-23-2013)"; ;;
		31) eStatus="AD1CH.IbattMinus > 900 (peak negative battery current)"; ;;
		32) eStatus="Aux 2 Logic input is high. Aux2Function 15 (external disable/enable)";;
		33) eStatus="OCP in a mode other than Solar or PV-Uset (1-10-2013)"; ;;
		34) eStatus="AD1CH.IbattMinus > 900 Classic 150"; ;;
		35) eStatus="Vbatt < 8.6 V (LOW LOW battery)"; ;;
		36) eStatus="Battery temperature is Greater than reg address 4161 specified"; ;;
		38) eStatus="is the new sleep because other charging sources appear to be active"; ;;
		136) eStatus="Battery temperature fell below MB reg. 4161 - 10 C (Classic turned back on)"; ;;
		*) eStatus="Unknown ($reason)"; ;;
	esac
	if [ $error == "1" ]
	then
		eStatus="<font color=\"red\">FAULT</font>"
		tracerstatus_bgcolor="red"
	fi

	battSoc=`echo "scale=0; ${lines[8]}/1"|bc -l`
	battVoltage=`echo "scale=1; ${lines[1]}/10.0"|bc -l`

	divider=`echo "scale=0; ($battVoltage / 12.5 + 0.5)/1"|bc -l`  # will give 1x for 6.25-18.25V, 2x for 18.25-31.25V, etc.
	battNomVoltage=`echo "scale=0; $divider * 12"|bc -l`
	battLevel=`echo "scale=0; ($battVoltage*10/$divider)/1"|bc -l`

	#In order for comparison to wrk we need to scale up everything 10x

	disconnectVoltage=`echo "scale=0; ($disconnectVoltage*10)/1"|bc -l`
	lowVoltage=`echo "scale=0; ($lowVoltage*10)/1"|bc -l`
	highVoltage=`echo "scale=0; ($highVoltage*10)/1"|bc -l`

	if [ "$divider" == "0" ]
	then
		bStatus="<font color=\"red\">FAULT</font>"; 
		tracerstatus_bgcolor="red"
	elif [ "$battLevel" -lt "$disconnectVoltage" ]
		then
			bStatus="<font color=\"red\">Low volt disconnect</font>"
		elif [ "$battLevel" -lt "$lowVoltage" ]
		then
			bStatus="<font color=\"yellow\">Undervolt</font>";
		elif [ "$battLevel" -gt "$highVoltage" ]
		then                 
			bStatus="<font color=\"red\">Overvolt</font>";
		else	
			bStatus="Normal"
	fi

	if [ ${lines[7]} ]
	then
		battCurrent=`echo "scale=1; ${lines[7]}/10.0"|bc -l`
	else
		battCurrent=0
	fi
	battPower=`echo "scale=0; ${lines[9]}/1.0"|bc -l`
	pvVoltage=`echo "scale=1; ${lines[2]}/10.0"|bc -l`
	if [ ${lines[3]} ]
	then
		pvPower=`echo "scale=0; ${lines[3]}/1.0"|bc -l`
	else
		pvPower=0
	fi
	pvCurrent=`echo "scale=1; ($pvPower*10/$pvVoltage)/10.0"|bc -l`
	battTemperature=`echo "scale=1; ${lines[6]}/10.0"|bc -l`
	totalKWH=`echo "scale=1; ${lines[4]}/10.0"|bc -l`;
	totalAH=`echo "scale=1; ${lines[5]}/10.0"|bc -l`;
	lastUpdate=$(printf "%(%a %b %e %Y %T)T" ${lines[0]})
else
	connection="Disconnected"
	connection_bgcolor="red"
	tracerstatus_bgcolor="#dedede"
fi

awk '{
	gsub(/<\?=\$connection\?>/,"'$connection'");
	gsub(/<\?=\$connection_bgcolor\?>/,"'$connection_bgcolor'");
	gsub(/<\?=\$tracerstatus_bgcolor\?>/,"'$tracerstatus_bgcolor'");
	gsub(/<\?=\$eStatus\?>/,"'$eStatus'");
	gsub(/<\?=\$bStatus\?>/,"'$bStatus'");
	gsub(/<\?=\$battSoc\?>/,"'$battSoc'");
	gsub(/<\?=\$battVoltage\?>/,"'$battVoltage'");
	gsub(/<\?=\$battNomVoltage\?>/,"'$battNomVoltage'");
	gsub(/<\?=\$battCurrent\?>/,"'$battCurrent'");
	gsub(/<\?=\$battPower\?>/,"'$battPower'");
	gsub(/<\?=\$pvVoltage\?>/,"'$pvVoltage'");
	gsub(/<\?=\$pvCurrent\?>/,"'$pvCurrent'");
	gsub(/<\?=\$pvPower\?>/,"'$pvPower'");
	gsub(/<\?=\$battTemperature\?>/,"'$battTemperature'");
	gsub(/<\?=\$totalKWH\?>/,"'$totalKWH'");
	gsub(/<\?=\$totalAH\?>/,"'$totalAH'");
	gsub(/<\?=\$lastUpdate\?>/,"'$lastUpdate'");
	print $0;
}' $templateFile > $outputFile
