Aria-Spoof
==========

An imaginatively titled privacy tool for spoofing guest weight data sent to
Fitbit by the Aria wifi scales.

Introduction
------------

OK, so my use case is pretty specific. Another member of my household owns a
set of Fitbit Aria scales. This is a set of bathroom scales that detects the
user of a Fitbit device, and uploads their weight to their account on Fitbit's
servers, although it will happily weight anyone, regardless of whether or not
they are a Fitbit user.

Like all things that use my network for internet access, I've been
analysing its traffic, and it was very obvious from the
outset (even without doing any traffic analysis) that the device uploads
'guest' weight readings as well as readings from people with Fitbit accounts.

I keep track of my health. In fact, I did my PhD in lifelogging back in 2008
and have an awful lot of data on my activity and wherabouts for the past ten
years. I like to do this in private, without uploading
all of my personal data to some cloud-based service. This is why I do not own
a Fitbit device, it insists all your health data processing happens on their
server where you have no control over it. I prefer to use another brand of
fitness band to track my exercise. I keep a mental note of my weight,
although some form of automatic logging system would be nice from a
lifelogger/data-nerd point of view. However, now
the bathroom scales have been replaced with the Aria device, I, as a
non-Fitbit-user, am getting the worst of both worlds; my weight is being
uploaded to Fitbit's server every time I use them, and I'm not getting
an automatically generated log of my weight, like the Fitbit owner of the
household is.

So ideally, I'd like a way to use the scales - without a Fitbit account -
and have them upload data to a local device on my network, while not
uploading the data to Fitbit's servers.

Acknowledgements
----------------

Many thanks are due to architekt and krisha, authors of
[this document](https://www.hackerspace-bamberg.de/Fitbit_Aria_Wi-Fi_Smart_Scale)
for an earlier version of the firmware than the one I'm using.

Thanks are also due to micolous, author of
[helvetic](https://github.com/micolous/helvetic/), whose documentation
tipped me off that I should be using CRC16/XModem to calculate the
checksum.


