#!/bin/bash
echo 1 > /tmp/compilation_Freebox_OS_in_progress
sudo apt-get install -qy android-tools-adb
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y --force-yes netcat
sudo chmod 755 /var/www/html/plugins/Freebox_OS/ressources/mini4k_cmd 
echo 100 > /tmp/compilation_Freebox_OS_in_progress
rm /tmp/compilation_Freebox_OS_in_progress
