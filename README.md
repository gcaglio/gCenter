# What is gCenter
Free, open source, light interface to manage Vmware ESXi hosts and virtual machines.<br/>
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
f. insert into the db your user, for example, to create the first admin user :
   username : admin
   password : password
   ```
   insert into users values ('admin', md5('password'), 'ADMIN');
   ```
g. schedule the gatherer to get information from your ESXi hosts. <br/>
   Here the example of a crontab entry to query your ESXi hosts every 5 minutes, assuming that the gCenter was installed in folder ```/var/www/gCenter/``` <br>
   ```
   # m h  dom mon dow   command
   */5 * * * * cd /var/www/gCenter/gatherer/; php /var/www/gCenter/gatherer/gather.php > /tmp/gatherer.log 2>&1
   ```
   

# Status
Created minimal gatherer scripts to collect data into the database for:
- esxi hosts informations
- vm
- vm config
- vm runtime informations
- vm resource usage statistics
- vm snapshots
- datastores
- datastore content

# Features implemented (what you can do)
- login with username and password (note: actually ROLE is not yet implemented - everyone could operate on all resources)
- poweron/poweroffi/reboot vms
- take vm snapshot
- list esxi information (software, hardware, etc)
- list vm for each esxi host
- check vm cpu and memory statistics/graphs
- list datastore for each esxi host
- list vm snapshots
- list datastore content (filesystem tree)

# Main steps
2023-03-27 Feature : added summary view to see all hosts and all vms<br>
2023-03-27 Modified styles and added version in login page<br>
2023-03-22 Feature : added vm hard reboot<br>
2023-03-22 Feature : added login with username and password <br>
2023-03-10 Feature : added get datastore content (file and directories) in the gatherer and implemented datastore info table in UI <br>
2023-03-09 Feature : added get vm snapshot in the gatherer and implemented snapshot table in UI. <br>
2023-01-01 Created gatherers and single-page interface to display structure and host informations.<br>
2023-01-04 Feature : released VM poweroff/poweron <br>
2023-01-22 Feature : released "Take snapshot" feature<br>
2023-01-25 Feature : added vm runtime and configuration informations.<br>
2023-01-25 Added vm statistics runtime collection<br>




