#!/bin/bash
iconv -f CP1252 -t UTF-8 $1 > $1a
mv $1a $1
sed -i -e "s/\xc3\x82\xe2\x80\x98/'/g" $1
sed -i -e "s/\xc3\x82\xe2\x80\x99/'/g" $1
sed -i -e "s/\xc3\x82\xe2\x80\x9c/'/g" $1
sed -i -e "s/\xc3\x82\xe2\x80\x9d/'/g" $1
sed -i -e "s/\xc3\x82\xe2\x80\xa6/\.\.\./g" $1
