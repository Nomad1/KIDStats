# KIDStats
Very simple interface for Midnite Solar KID PC Mode data

Statistics can be read from KID device with Serial UART cable. You can use almost any kind of cheap PL2303 or FT232 cables to connect to the device and after that you'll have the strings like that every minute:
```254,783,34,2,90,261,,100,400,1,```

According to the forum (http://midniteftp.com/forum/index.php?topic=3341.0) numbers mean the following:
   * Displayed battery voltage
   * Displayed PV Voltage
   * Displayed output watts
   * KWH
   * Amp Hours
   * Battery Temperature
   * WBJR Current
   * Battery SOC
   * WBJR Amp Hr Remaining
   
   Also there is one more number for device status, similar to Midnite Classic codes found here: http://midniteftp.com/forum/index.php?topic=2034.0 . However, I already seen the code 70 on firmware 1864, found nowhere else.

For simple statics I wrote a bash script `read_midnite.sh` that is called from crontab every minute. Script reads the data from serial port and puts them to `/tmp/midnite` file along with the timestamp (sample output file can be found at `tmp/midnite`).
Then a web server hosts PHP page that reads the file and populates the data to HTML. It can be easilly ported to any other language according to your task. Credit for web-page design goes to Luca Soltoggio, @toggio. It was meant for his library and EPSolar tracer but now it's library independent and works for EPSolar competitors :)

Note that part of the data is useless without WBJR, meaning that real battery Current, Amp Hours and may be even SOC will remain at fixed values without it.

## Installation
_Note: it requires at least basic Linux knowledge, since the script is written in Bash and it could be tricky to port it to Windows._

Grab the code from git, put pv.php file to your favorite web server with PHP support (you might need to give it some permissions to read from /tmp/ folder), edit read_midnite.sh script to point to your serial device, and add it to your crontab.
I.e. I set it to be called every minute: `* * * * * /etc/read_midnite.sh`

After that you can access http://127.0.0.1/pv.php page and see something like that:

![Screenshot](https://github.com/Nomad1/KIDStats/blob/master/stats.png?raw=true)

That's all. Enjoy!

## Extended version
There is a new Bash script called `read_midnite_ext.sh`. It does exactly the same as `pv.php` but _without_ using PHP at all - it generates a web page with all data put in. However `pv.php` file is still here, it is used as a HTML template and also you can continue to use it with your favorite web server and PHP. 
Script uses `awk` and `bc`, so you might need to install them as well.

P.S. I've also seen people reading KID data with completely different protocol and accessing registers. That would be nice to implement cause there are lot's of gaps, i.e. we don't know real battery charging state and load power with *PC Mode* data.   

