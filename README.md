# OTRS to Freshdesk Converstion Tool #

## Purpose: ##
Convert OTRS tickets to Freshdesk tickets

## Requirements: ##
* OTRS Database (copy)
* Freshdesk Account
* MySQL or Postgre (does not support MSSQL)
* PHP with PDO support
* PHP Command Line Interface (CLI)

### WARNING: ###
This script modifies the OTRS database! Do not run this against your 
production/inuse OTRS database! Make a copy of the database to run this against. 

Freshdesk limits the number of API calls to 1000 per hour. To complicate this 
process, Freshdesk API doesn't accomodate multiple actions in a single call. 
Example: an OTRS ticket with 5 email responses will take 6 API calls to fully 
populate in Freshdesk: one call to create the initial ticket and 5 more calls 
for the responses. So in order to complete a migration this scrip may have to 
run many times.  In order to do this this script will add fields 
into the database for tracking of the migration process.

### How this works ###
The script will parse all the tickets within OTRS and add them to Freshdesk.
To do that the script will first process ticket table first to create the
"base" tickets this will create ticket IDs in Freshdesk that will be used to add
all replies and actions to a ticket.  Once all base tickets have been created
the script will then begin processing the replies and notes that were done to a
ticket in OTRS.  Depending on the size of your OTRS ticket database this process
could very well take days to complete due to the API restriction.

##Usage:##

1. Copy ./include/config.php.tpl to ./include/config.php
2. Edit values in newly created config.php (refer to in code documentation).
2. Run script: php migrateotrs.php

**NOTE**: You probably should turn off email notifications to your customers in Freshbooks to make sure no emails are sent.

##Repeating process##
More than likely you will have to run this process many times due to the API
limit. I cannot provide instruction on that because it depends on the operating
system you are using.

##How Long Will This Take##
Like said before, this could take a very long time to run.  Want to know how long?
Setup the config file then run this:

php howlongwillthistake.php

##Support and Disclaimer##
The process this script takes is not perfect.  There were assumptions made on a single use-case
that may not be true for your scenario. Use at your own risk!

Migrating data is always a moving target and you can never assume what someones
data may look like.  This scrip may or may not work for you, it is provided as-is with no
support.  Data migration can take a serious amount of time to troubleshoot because of
an infinite amount of variables.  If you want professional help, contact
info@fastglass.net

##Authors##
Written by Brady Owens, Chief Technical Officer, Fastglass LLC (http://www.fastglass.net)

##Credits##
Database abstraction classes come from Drupal (https://www.drupal.org)