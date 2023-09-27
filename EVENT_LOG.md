# Where are events logged ?
Events are logged into 'events' table.<br/>
Events have these informations:<br/>
- timestamp
- username of the user performing the action
- ip address of the client the user is connecting from (be aware that particular proxying techniques could alter this IP with the PROXY ip address)
- event type (INFO|ERROR|.... see next sections)
- resource id in the form <b>/&lt;hostname&gt;/&lt;subcategory&gt;/&lt;id_of_the_resource_on_the_host&gt;</b> (see next sections)
- additional notes about the action

# Event types / severities
INFO              informational
ERROR		  critical error
LOGIN_OK          successfull login
LOGIN_ERR         error during login
LOGOUT            user logout


# Resource ID subcategory
/vm/          identify a virtual machines<br/>
/datastore/   identify datastores<br/>
/vswitch/     identify vswitches

Examples:
  /HYPERSRV02/vm/6134D048-ED5B-4A34-B378-0841E5803A05  
  identify the virtual machine "6134D048-ED5B-4A34-B378-0841E5803A05" on host "HYPERSRV02"

  /192.168.123.1/vswitch/vSwitch0
  identify virtual switch "vSwitch0" on host "192.168.123.1"


# Retention
Event log retention table could be managed editing the variable

  ```# events table retention in days```
  ```$event_log_retention_days=30;```

in the configuration file ```/conf/eventlog.php```
