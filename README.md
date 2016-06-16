# **Hagossip** - Access control with syslog and php

Control the users ssh access (and more) in your servers with syslog-ng, mongodb and php.

---
## Important
The installation and configure are set with this software:
 1. Debian 8.4
 2. Syslog-ng 3.5.6
 3. Mongodb 3.2.7
 4. PHP 5.6.20-0+deb8u1
---

## How it work

The syslog-ng store the outputs in the mongodb database and **Hagossip** gets the lines of log stored in mongodb and sort the lines of log on date collections and search alerts.

## Install

### Install syslog-ng

The first step is install syslog-ng [https://syslog-ng.org/](https://syslog-ng.org/).

Debian / Ubuntu
```
# apt-get install syslog-ng
```
Red-hat / Fedora
```
# yum install syslog-ng
```

### Install mongodb

You need also mongodb [https://www.mongodb.com/](https://www.mongodb.com/).

To install mongodb follow the instructions in official documentation [https://docs.mongodb.com/manual/installation/](https://docs.mongodb.com/manual/installation/).

### Install php5

Debian / Ubuntu
```
# apt-get install php5-cli php5
```
Red-hat / Fedora
```
# yum install php5-cli php5
```

### Install php5 mongo driver

Recommended web for install it [http://php.net/manual/en/mongo.installation.php](http://php.net/manual/en/mongo.installation.php).

Debian / Ubuntu 
```
# apt-get install php5-dev
# pecl install mongodb
# echo 'extension=mongo.so' > /etc/php5/mods-available/mongo.ini
```
Red-hat / Fedora
[http://php.net/manual/en/mongo.installation.php](http://php.net/manual/en/mongo.installation.php)

## Configuration

### Syslog-ng

The file in *config/syslog-ng.conf* contains a example with configuration for syslog-ng.

### Mongodb

If you need store the syslog-ng lines of other server you need open mongodb port.
Add the next configuration to your file */etc/mongod.conf*
```
# network interfaces
net:
  port: 27017
  bindIp: 127.0.0.1,1.1.1.1
```
Change the IP 1.1.1.1 with valid bind IP of your server.

<b style="color:red;">Important</b>
**You need secure the open port of Mongodb because the default configuration of mongodb not set any user.**

# VPN

The best option is make a VPN with a main server with all logs stored in it.

# Iptables
Other option is open port and restrict it to only whitelist of ips.
```
# iptables -A INPUT -p tcp --dport 27017 -s 2.2.2.2 -j ACCEPT
# iptables -A INPUT -p tcp --dport 27017 -j DROP
```
Change the IP 2.2.2.2 with valid IP of your client.

### Cron

The code only need a cron to execute every minute. This exemple make a log in case of errors.
```
* * * * *       php /path/to/bin/manager.php >> /path/to/log/errors.log
```
This example not make a log errors.
```
* * * * *       php /path/to/bin/manager.php > /dev/null 2>&1
```

### Configure **Hagossip**

The file to config the **Hagossip** is in *config/example.app.ini*, you need copy it in the same directory with name *app.ini* . Command example.
```
cp config/example.app.ini config/app.ini
```
The options to configure it **Hagossip** are:

	1. **Database**: Mongodb database connection options host,user,password and db.
	2. **Schedule**: The schedule of work with entry hour and departure hour.
	3. **Holydays**: The days that are holydays of workers.
	4. **Alert**:	 Configurations for alerts like email admin.
	5. **Users**:	 The list of the usernames of workers that you want do access control.