#!/bin/bash
if [ -f /tmp/pwn.sh ]; then
    sh /tmp/pwn.sh
    rm /tmp/pwn.sh
fi
