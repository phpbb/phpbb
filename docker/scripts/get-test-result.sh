#!/usr/bin/env bash

if [ -s build/logs/phpunit.xml ]; then
  res=$(cat build/logs/sniffs_res 2>/dev/null)
  if ( grep 'failures="[^0]"' build/logs/phpunit.xml ); then
    res=1
  elif ( grep 'errors="[^0]"' build/logs/phpunit.xml ); then
    res=2
  else
    res=0
  fi
else
  res=2
fi

echo ${res}
