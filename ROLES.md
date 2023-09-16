# Available roles
ADMIN           can do everything
VIEWER          can only view data (by default any configured user is VIEWER)
POWER_MGMT      like VIEWER but can also poweron and poweroff

# How to assign role to a user
In this version you have to assign roles directly on the database. <br/>
Roles could be assigned on specific vm, hosts or with wildcards.<br/>

# Viewer role
By default, creating a user will assign "VIEWER" role by default.<br/>
It cannot operate or change the state of a VM, for example, but can view all the hosts and the vms.

# Examples 
To assign role "ADMIN" on all the resources for user <b>mrossi</b> you should execute:
   ```
   insert into roles values ('mrossi', 'ADMIN', '*' );
   ```

To assign role "ADMIN" on host <b>srvhyp01.mydomain.local</b> and all its vms to user <b>mrossi</b> you should execute:
   ```
   insert into roles values ('mrossi', 'ADMIN', '/srvhyp01.mydomain.local/*' );
   ```

To assign only role "POWER_MGMT" on vm <b>srvapp02</b> on host <b>srvhyp01.mydomain.local</b> to user <b>mrossi</b> you should execute:
   ```
   insert into roles values ('mrossi', 'POWER_MGMT', '/srvhyp01.mydomain.local/srvapp02' );
   ```




