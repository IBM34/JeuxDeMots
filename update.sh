#!/bin/bash
filename="update.txt"
while read -r line || [ -n "$line" ]
do
    terme="$line"
    php -f results.php $terme -1
done < "$filename"
truncate -s 0 $filename
