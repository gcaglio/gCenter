 OVERVIEW
==========
This executable is a WMI-to-HTTP Windows service adapter.
This allow remote applications to get informations available only through WMI protocol via HTTP.


  USE CASE
==========
This is useful in case we need to implement remote automation or gatherer services that need to
perform WMI queries (or read/list WMI-queryable objects) without Kerberos or WinRM authentication.
This could be useful if you've some Hyper-V core servers (standalone installation) and you need to collect
information on all of them from simple scripts (e.g. monitoring or custom remote management).


 INSTALLATION
==============
This instruction will install the service in c:\HyperV-wmi-http-adapter-service\
Please adapt the path according to your needs.

a. open an elevated command prompt

b. create destination folder
   mkdir c:\HyperV-wmi-http-adapter-service

c. copy "HyperV-wmi-http-adapter-service.exe" and "HyperV-wmi-http-adapter-service.ini"
   to that folder using your preferred method

d. register the windows service using "SC" utility.
   NOTE : please retain the spaces after the "=" sign, they're required by SC utility
  
   cd c:\HyperV-wmi-http-adapter-service

   sc create HyperV-wmi-http-adapter-service binpath= "c:\HyperV-wmi-http-adapter-service\HyperV-wmi-http-adapter-service.exe" start= auto DisplayName= "Hyper-V WMI-to-HTTP adapter service"
   [SC] CreateService OPERAZIONI RIUSCITE



e. check the .ini file and customize based on your needs.
   In case you've multiple network interface on your Hyper-V, it's better to customize

   binding_ip  = the IPv4 address of your network interface you want the service will listen to
   biding_port = the TCP port on which you want to the service will listen


f. start the service, at next boot it will be started automatically since we used "start= auto" in the previous command line
   
   sc start HyperV-wmi-http-adapter-service

   NOME_SERVIZIO: HyperV-wmi-http-adapter-service
       TIPO                   : 10  WIN32_OWN_PROCESS
       STATO                  : 2  START_PENDING
                                (NOT_STOPPABLE, NOT_PAUSABLE, IGNORES_SHUTDOWN)
       CODICE_USCITA_WIN32    : 0  (0x0)
       CODICE_USCITA_SERVIZIO : 0  (0x0)
       PUNTO_CONTROLLO          : 0x0
       INDICAZIONE_ATTESA     : 0x7d0
       PID                    : 22192
       FLAG                   :


 TROUBLESHOOTING
=================
i. Hyper-V core could not have .net Framework 4.8 installed.
   I suggest to download the Offline package from : https://support.microsoft.com/en-us/topic/microsoft-net-framework-4-8-offline-installer-for-windows-9d23f658-3b97-68ab-d013-aa3c3e7495e0

   Since Windows Core does not have full GUI, the executable file should be launched with /q (quiet) flag from elevated command line

   eg: ndp48-x86-x64-allos-enu.exe /q


ii. The service will log a startup event with some diagnostic informations like the INI file path, listening ip and port.

     HyperV-wmi-http-adapter-service is starting.
     Ini file:c:\HyperV-wmi-http-adapter-service\HyperV-wmi-http-adapter-service.ini
     Listening on IP 192.168.123.130 and PORT 5000.
     HttpListener prefix : http://192.168.123.130:5000/


