#!/bin/sh

device='/dev/ttyACM0'
#device='/dev/ttyXRUSB0'
speed=57600

tput reset > $device #it won't hurt but will reset faulty devices
stty -F $device raw ispeed $speed ospeed $speed cs8 -parenb -cstopb
data=`timeout 60 head -n 1 < $device`
ts=`date '+%s'`
echo $data
echo "$ts,$data" > /tmp/midnite 
