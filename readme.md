Aria-Spoof
==========

An imaginatively titled privacy tool for spoofing guest weight data sent to
Fitbit by the Aria wifi scales.

Introduction
------------

My use case is pretty specific. Another member of my household owns a
set of Fitbit Aria scales. This is a set of bathroom scales that detects the
user of a Fitbit device, and uploads their weight to their account on Fitbit's
servers, although it will happily weigh anyone, regardless of whether or not
they are a Fitbit user.

Like all things that use my network for internet access, I've been
analysing its traffic, and it was very obvious from the
outset (even without doing any traffic analysis) that the device uploads
'guest' weight readings as well as readings from people with Fitbit accounts.

I keep track of my health. In fact, I did my PhD in lifelogging back in 2008
and have an awful lot of data on my activity and wherabouts for the past ten
years. Unlike many, I like to do this in private, without uploading
all of my personal data to some cloud-based service. This is why I do not own
a Fitbit device, it insists all your health data processing happens on their
server where you have no control over it. However, now
the bathroom scales have been replaced with the Aria device, I, as a
non-Fitbit-user, am getting the worst of both worlds; my weight is being
uploaded to Fitbit's server every time I use them, and I'm not getting
an automatically generated log of my weight, like the Fitbit owner of the
household is.

If I were the only person in the house, I could use
[Helvetic](https://github.com/micolous/helvetic/), which completely replaces
the Fitbit servers with a local network alternative. However, the owner of
the scales would still like their data uploaded to Fitbit's cloud, so
I needed something that ensures the Aria works as designed when
a Fitbit user steps on them, but intercepts all other weight events,
logging them locally and not uploading them to Fitbit.

This script is tested and works with the Fitbit Aria running firmware
version 39 (protocol v3). It is *not* tested with an Aria 2.

Acknowledgements
----------------

Many thanks are due to architekt and krisha, authors of
[this document](https://www.hackerspace-bamberg.de/Fitbit_Aria_Wi-Fi_Smart_Scale)
for an earlier version of the firmware than the one I'm using.

Thanks are also due to micolous, author of
[Helvetic](https://github.com/micolous/helvetic/), whose documentation
tipped me off that I should be using CRC16/XModem to calculate the
checksum.

Methods
-------

The scales are pretty good with network connection, or lack of it. Our
bathroom is quite a way from our wifi access point, so maybe 10% of the time
the scales will weigh you and fail to upload the data because they can't
connect to the wifi. This is fine, because they will store the failed attempt,
and the next time someone weighs themself, the Aria will upload all previously
stored data.

So it's not just a case of we can replay a 'success' response if a guest
user weighs themself, because the data may include the previous user's
weight, which they *do* want uploaded to Fitbit. Additionally, the time
stamp is returned as part of the response, and the scales set themselves
to this and use the timestamp in the next request. So we have to actually
interpret and modify the data being uploaded.

We *could* cleanly remove all guest data from uploads, and craft a fake
'OK' message from the server to send to the scales if only guest data is
included in a particular upload. But this is complicated, it's far easier
to just replace all guest weights with a random number, similar to how
PDroid for Android works. So that's what this script does.

Privacy
-------

The script only uploads the weight of Fitbit users to Fitbit's server
unmodified, but keeps *all* users' data. Thinking about it,
this is kind of a dick move. The script should only really log the
guest user's data locally; just because the Fitbit user of the house
has given consent for Fitbit to process her personal data, I should
not assume this consent applies to me too. For this reason, the
'interpret_data' function in htdocs/functions.php has an
$ignore_registered_users argument, which is set to true by
default. This ensures no data from registered Fitbit users ends up
in the JSON (although it's still in the raw data). If you want to
store *everyone's* data in the JSON, and have obtained their
consent, change the $ignore_registered_users value to false.

Installation and Configuration
------------------------------

The first bit is the hardest, and that's to get some kind of local DNS
spoofing set up. Basically, the server on which this script will be running
needs to respond to HTTP requests destined for www.fitbit.com. The way I've
done this is to set up a Raspberry Pi on my local network with an IP
address beginning with 192.168, and then configure my router to use that
IP address for DNS queries. The Raspberry Pi needs to be set up running
dnsmasq as shown in the following link

http://www.heystephenwood.com/2013/06/use-your-raspberry-pi-as-dns-cache-to.html

But you'll need to add an entry in the hosts file on the Pi so that
www.fitbit.com resolves to the host on which Aria-Spoof will be running.

You can test this is working by pinging www.fitbit.com from any place on
your network, and it should resolve to a local (eg 192.168.x.x) IP
address. If you want, you can set up the DNS resolver so that it only
responds with the local address to the Aria, but this is a bit more
complicated, and you only need to do it if you really want to view
Fitbit's website. If you're as worried about the security of IoT devices
as I am, you probably already have a separate wifi network for the
Aria anyway!

Once this is all done, you need to go to the device whose IP address is
resolved as www.fitbit.com by your DNS server, and ensure it's running
Apache2 and PHP. Configuration of these is out of scope of these instructions
but information is commonplace on Google.

Clone this repo into a directory accessible by the www-data user,
and, within that directory, also create a directory called data to which
the www-data user has write access (ideally, www-data should be the
owner.) Now configure a virtual host in the Apache config file that
sends HTTP requests on port 80 with the host www.fitbit.com to the
htdocs directory within the repo. An example apache sites config file,
taken from my Raspberry Pi DNS server, 'hook', which also handles the
Aria spoofing, is below

    # Ensure that Apache listens on port 80
    Listen 80
    
    <VirtualHost *:80>
      DocumentRoot /home/pi/websites/hook/htdocs
      ServerName hook
      ServerAlias 192.168.0.103
    </VirtualHost>
    
    <VirtualHost *:80>
      DocumentRoot /home/pi/websites/www.fitbit.com/htdocs
      ServerName www.fitbit.com
      <Directory />
        Options FollowSymLinks
        AllowOverride All
      </Directory>
      <Directory /home/pi/websites/www.fitbit.com/htdocs>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>
    </VirtualHost>

Once you've got it installed, you can test it by going to www.fitbit.com
in the browser of a device on the same network as the Aria. It should
try to redirect to the https version and then fail because we aren't doing
any SSL stripping and we don't have a valid certificate.


Using the Script
----------------

Once all is in place and working, step on the scales (as a guest). You
won't get any real confirmation that the weight is being spoofed, but
if you get a tick on the screen of the scales and a timestamped directory
gets created in the data directory you created on installation, then
all went well.

In the timestamped directory, you'll find basically a complete copy of
the exchange that went between the scales and the Fitbit server,
unmodified. In addition, you'll get a JSON file, request_data.json,
which contains the same data as the binary file, but nicely formatted
into a JSON object which can be read into pretty much anything. See the
'Privacy' section above if you expected to see more data in this file.


Configuring MQTT Support
------------------------

MQTT support can be enabled by adding the mosquitto extension to PHP.
To install the extension, see https://github.com/mgdm/Mosquitto-PHP 

Once the extension is available, adjust the constants MQTT_BROKER 
(hostname or IP address of the MQTT broker, MQTT_PORT (port of the
MQTT broker), MQTT_STATE_TOPIC (MQTT topic where state information 
like firmware version and battery level will be posted) and 
MQTT_SAMPLES_TOPIC (MQTT topic where weight samples will be posted)
to the values for your broker if needed.

Currently username/password authentication is not implemented.
