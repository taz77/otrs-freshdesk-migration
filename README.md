# OTRS to Freshdesk Converstion Tool #

## Purpose: ##
Convert OTRS tickets to Freshdesk tickets

## Requirements: ##
-OTRS Database (copy)
-Freshdesk Account

### WARNING: ###
This script modifies the OTRS database! Do not run this against your 
production/inuse OTRS database! Make a copy of the database to run this against. 

Freshdesk limits the number of API calls to 1000 per hour. So in order to
complete a migration this scrip may have to run many, many, many times.  In order
to do this this script will add fields into the database for tracking of the migration
process.

##Usage:##

1. Edit configuration ./includes/config.php