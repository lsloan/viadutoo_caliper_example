#!/bin/sh --

phpAppFilename=$(echo $0 | sed 's/\.sh$/.php/')
port=$1
[ -z "$port" ] && port='8989'

php -S 127.0.0.1:${port} ${phpAppFilename}
