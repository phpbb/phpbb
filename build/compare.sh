#!/usr/bin/env bash
orig_dir="../../phpBB"


rm -rf test_release_files
mkdir test_release_files
cd test_release_files

for ext in "tar.bz2" "zip"
do
	cp "../new_version/release_files/$1.$ext" ./

	if [ "$ext" = "tar.bz2" ]
	then
		command="tar -xjf"
	else
		command="unzip -q"
	fi

	$command "$1.$ext"

	for file in `find phpBB3 -name '.svn' -prune -o -type f -print`
	do
		orig_file="${file/#phpBB3/$orig_dir}"
		diff_result=`diff $orig_file $file`

		if [ -n "$diff_result" ]
		then
			echo "Difference in package $1.$ext"
			echo $diff_result
		fi
	done

	rm -rf phpBB3
done

cd ..
rm -rf test_release_files

