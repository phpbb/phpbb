#!/bin/sh
cat sql_*.php | grep '/* SELECT' | sed 's,/\* ,,;s, \*/,,' | sort > _cache.txt
