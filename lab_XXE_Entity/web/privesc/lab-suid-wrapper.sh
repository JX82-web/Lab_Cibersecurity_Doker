#!/bin/bash
# Helper used only to display the intended attack surface.
# Students should discover /usr/local/bin/lab-chroot themselves.
id
ls -l /usr/local/bin/lab-chroot
/usr/local/bin/lab-chroot --version || true
