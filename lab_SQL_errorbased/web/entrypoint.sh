#!/bin/sh
/usr/sbin/sshd
exec apache2-foreground
