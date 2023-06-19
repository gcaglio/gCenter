# What is gCenter
Free, open source, light interface to manage Vmware ESXi hosts and Hyper-V.<br/>
gCenter is a bundle of 
- SSH information gatherer for Esxi
- Windows Services (for Hyper-V, to allow WMI query execution)
- web interface to let you view, list and manage virtual machines and hosts
<br/>
This project aims to be a light version of vCenter and System Center, with a unified and simplified interface.<br />
<br />
It will be a long road, getting from there to here...



# Use Case
Small to Medium installation of Vmware ESXi hosts (for example an internal "Lab" that does not require DRS or similar advanced feature).<br/>
Mixed installation of ESXi and Hyper-V environment.<br />
Enabling basic support team (ServiceDesk or L1) to operate a virtual enviroment without SystemCenter or vCenter complex interface.<br/>
<br/>
Or for any installation that does not want to pay licenses for management software:
- ESXi is free
- Hyper-V core is free
and with gCenter you can list and manage vms on different host, completely free.



# Requirements
- apache
- php
- mariadb
- sshpass  (actually the only method to connect to ESXi host is with sshpass, future improvement soon)
- hyperv-wmi-http-adapter-service (windows service to enable gCenter to connect to Hyper-V hosts)


# How to install
a. download project from repo <br/>
b. import ```db_schema.dump``` in your mariadb database <br/>
c. create virtualhost that point to the ```./website``` subfolder <br/>
d. configure ```conf/db.php``` to point to your mariadb instance with the gcenter scheme imported on point "b" <br/>
e. insert into the db your user, for example, to create the first admin user :
   username : admin
   password : password
   ```
   insert into users values ('admin', md5('password'), 'ADMIN');
   ```


ESXi<br>
i. insert into the db your hosts : 
   ```
   insert into hosts (hostname,username,password) values ('myhostname','myusername','mypassword')   
   ```
ii. schedule the gatherer to get information from your ESXi hosts. <br/>
   Here the example of a crontab entry to query your ESXi hosts every 5 minutes, assuming that the gCenter was installed in folder ```/var/www/gCenter/``` <br>
   ```
   # m h  dom mon dow   command
   */5 * * * * cd /var/www/gCenter/gatherer/; php /var/www/gCenter/gatherer/gather.php > /tmp/gatherer.log 2>&1
   ```
   

Hyper-V<br>
i. insert into the db your hosts :
   ```
   insert into hyperv_hosts values ('myhostname','ip-wmi-http-adapter-service-binding','port-wmi-http-adapter-service-binding', '');
   ```
ii. schedule the gatherer to get information from your Hyper-V hosts. <br/>
   Here the example of a crontab entry to query your Hyper-V hosts every 5 minutes, assuming that the gCenter was installed in folder ```/var/www/gCenter/``` <br>
   ```
   # m h  dom mon dow   command
   */5 * * * * cd /var/www/gCenter/gatherer/; php /var/www/gCenter/gatherer/hyperv-gatherer.php > /tmp/hyperv-gatherer.log 2>&1
   ```
iii. install on the Hyper-V host(s) the hyperv-wmi-http-adapter-services. Please read installation instructions in the adapter folder.


# Status

Vmware ESXI
  Created gatherer scripts to collect data into the database for:
  - esxi hosts informations
  - vm
  - vm config
  - vm runtime informations
  - vm resource usage statistics
  - vm snapshots
  - datastores
  - datastore content

Microsoft Hyper-V
  Created gatherer scripts to collect data into the database for:
  - Hyper-V hosts informations
  - vm health status informations and power state
  - vm snapshots
  


# Features implemented (what you can do)
Vmware ESXI
  - login with username and password (note: actually ROLE is not yet implemented - everyone could operate on all resources)
  - poweron/poweroff/reboot vms
  - take vm snapshot
  - list esxi information (software, hardware, etc)
  - list vm for each esxi host
  - check vm cpu and memory statistics/graphs
  - list datastore for each esxi host
  - list and take vms snapshots
  - list datastore content (filesystem tree)
  - list vswitch/portgroup informations
  - list network interfaces and porgroup assignation

Micrososft Hyper-V 
  - hyperv-wmi-http-adapter-service windows service to interface WMI
  - collect hosts informations
  - collect vms informations
  - poweron/poweroff vms
  - taking vms snapshots
  - listing vms snapshots
  - check vm cpu and memory statistics/graphs

# Main steps
2023-06-20 Feature : added VM network interfaces and portgroup details<br>
2023-06-14 Feature : added vswitch and portgroup gatherer feature <br>
2023-05-21 Feature : added Hyper-V vm ram/memory usage feature/graph <br>
2023-05-20 Feature : added Hyper-V vm cpu load feature/graph <br>
2023-05-17 Feature : added Hyper-V 'list vm snapshots' feature<br>
2023-05-14 Feature : added Hyper-V 'take vm snapshot' feature<br>
2023-05-14 Feature : minor bugfixes<br>
2023-05-11 Feature : added Hyper-V virtual machine memory details<br>
2023-05-07 Feature : added Hyper-V virtual machine power management<br>
2023-05-05 Feature : added listing Hyper-V vms and hosts<br>
2023-05-04 Feature : added hyperv-wmi-http-adapter-service, that need to be installed on Hyper-V hosts<br>
2023-05-04 Feature : added hyper-v gatherer for virtual machines informations<br>
2023-05-04 Feature : added hyper-v gatherer for hosts informations<br>
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


# Screenshots
![Alt text](/docs/images/gCenter_login.png "Login")

![Alt text](/docs/images/gCenter_show_esxi_host.png "ESXi host details")

![Alt text](/docs/images/gCenter_show_esxi_vm.png "ESXi virtual machine details")

![Alt text](/docs/images/gCenter_show_hyperv_vm.png "Hyper-V host details")



