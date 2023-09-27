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
INFO              informational<br/>
ERROR		  critical error<br/>
LOGIN_OK          successfull login<br/>
LOGIN_ERR         error during login<br/>
LOGOUT            user logout<br/>
ADD_HOST_OK	  host added successfully<br/>
DEL_HOST_OK	  host deleted successfully<br/>
ADD_HOST_ERR	  error adding host<br/>
DEL_HOST_ERR	  error deleting host<br/>


# Resource ID subcategory
/vm/          identify a virtual machines<br/>
/datastore/   identify datastores<br/>
/vswitch/     identify vswitches<br/>
<br/>
Examples:
  /HYPERSRV02/vm/6134D048-ED5B-4A34-B378-0841E5803A05 <br/> 
  identify the virtual machine "6134D048-ED5B-4A34-B378-0841E5803A05" on host "HYPERSRV02"<br/>
<br/>
  /192.168.123.1/vswitch/vSwitch0<br/>
  identify virtual switch "vSwitch0" on host "192.168.123.1"<br/>
<br/>

# Retention
Event log retention table could be managed editing the variable

  ```# events table retention in days
 $event_log_retention_days=30;```

in the configuration file ```/conf/eventlog.php```
