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

P.S. I've also seen people reading KID data with completely different protocol and accessing registers. That would be nice to implement cause there are lot's of gaps, i.e. we don't know real battery charging state and load power with *PC Mode* data.   
