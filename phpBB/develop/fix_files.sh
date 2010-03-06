#!/bin/bash
# 
# Remove all those annoying ^M characters that Winblows editor's like to add
# from all files in the current directory and all subdirectories.
#
# Written by: Jonathan Haase.
#
# UPDATE: 7/31/2001: fix so that it doesn't touch things in the images directory
#
# UPDATE: 12/15/2003: Fix so that it doesn't touch any "non-text" files
#

find . > FILELIST.$$
grep -sv FILELIST FILELIST.$$ > FILELIST2.$$
grep -sv $(basename $0) FILELIST2.$$ > FILELIST.$$
grep -sv "^\.$" FILELIST.$$ > FILELIST2.$$
file -f FILELIST2.$$  |grep text | grep -v icon_textbox_search.gif | sed -e 's/^\([^\:]*\)\:.*$/\1/' > FILELIST
file -f FILELIST2.$$  |grep -sv text | sed -e 's/^\([^\:]*\)\:.*$/Not Modifying file: \1/'
rm FILELIST2.$$
rm FILELIST.$$

for i in $(cat FILELIST); do
	if [ -f $i ]; then  	 
		cat $i | tr -d '\r' > $i.tmp
		mv $i.tmp $i
	fi	
done
rm FILELIST

