# What is gCenter
Simple interface to monitor and manage ESXi hosts. <br/>
It will be a long road, getting from there to here...

# Requirements
- apache
- php
- mariadb
- sshpass  (actually the only method to connect to host is with sshpass, future improvement soon)

# How to install
a. download project from repo <br/>
b. import ```db_schema.dump``` in your mariadb database <br/>
c. create virtualhost that point to the ```./website``` subfolder <br/>
d. configure ```conf/db.php``` to point to your mariadb instance with the gcenter scheme imported on point "b" <br/>
e. insert into the db your hosts : 
   ```
   insert into hosts (hostname,username,password) values ('myhostname','myusername','mypassword')   
   ```

# Status
Created minimal gatherer scripts to collect data into the database for:
- esxi hosts informations
- vm
- datastores

Created single-page interface to display structure and host informations.

