#!/bin/sh
DIR=$(dirname "$0")/../cache;
cat "$DIR/sql_*.php" | grep '/* SELECT' | sed 's,/\* ,,;s, \*/,,' | sort
