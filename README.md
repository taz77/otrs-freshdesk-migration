# OTRS to Freshdesk Converstion Tool #

## Purpose: ##
Convert OTRS tickets to Freshdesk tickets

## Requirements: ##
* OTRS Database (copy)
* Freshdesk Account
* MySQL or Postgre (does not support MSSQL)
* PHP with PDO support

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

1. Edit configuration ./includes/config.php