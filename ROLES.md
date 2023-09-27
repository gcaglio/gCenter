# Available roles
ADMIN           can do everything
VIEWER          can only view data (by default any configured user is VIEWER)
POWER_MGMT      like VIEWER but can also poweron and poweroff
SNAP_MGMT       like VIEWER but can also take snapshots

# How to assign role to a user
In this version you have to assign ADMIN role to the first admin user you create. <br/>
Roles can be deleted and added from the web UI by users with ADMIN role.<br />
Roles can be added on HOST or on single VM. The web UI will help identifying all the discovered/configured objects.<br/>

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




