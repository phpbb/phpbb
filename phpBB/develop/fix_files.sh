#!/bin/bash
# 
# Remove all those annoying ^M characters that Winblows editor's like to add
# from all files in the current directory and all subdirectories.
#
# Written by: Jonathan Haase.

find . > FILELIST.$$
grep -sv FILELIST FILELIST.$$ > FILELIST2.$$
grep -sv $(basename $0) FILELIST2.$$ > FILELIST.$$
grep -sv "^\.$" FILELIST.$$ > FILELIST
rm FILELIST2.$$
rm FILELIST.$$

for i in $(cat FILELIST); do
	if [ -f $i ]; then  	 
  		sed -e s/
//g $i > $i.tmp
  		mv $i.tmp $i
	fi	
done
rm FILELIST
