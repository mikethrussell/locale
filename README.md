locale
======

Execute functions based on 2 user's locations using Google Latitude API.


Requires PHP and SQLite to be installed

(script uses 'mike' and 'caroline' as 2 users names for clarity)

Script includes 3 house states; home, away, holiday and 2 additional scenarios; mike arriving and caroline arriving


Edit script with your Latitude user IDs, home postcode and commands to run during different house states


Make sure the php script can be executed:

chmod +x locale.php

Check that the script works by executing it manually:

cd /path/to/script/

./locale.php

You should receive no errors


Add a cron job to execute the shell script once a minute:

crontab -e

Add following line to the file:

*/1 * * * * /path/to/script/locale.php

