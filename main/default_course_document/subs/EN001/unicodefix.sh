#!/bin/bash
sed -i -e "s/\xe2\x80\x99/\'/g" *.srt
sed -i -e "s/\xe2\x80\xa6/\.\.\./g" *.srt
sed -i -e "s/\xc2\xbd/1\/2/g" *.srt
sed -i -e "s/\xc2\xa7/par\./g" *.srt
sed -i -e "s/\xce\xa5/Y/g" *.srt
sed -i -e "s/\xe2\x80\x98/\'/g" *.srt
sed -i -e "s/\xce\x91/A/g" *.srt
sed -i -e "s/\xe2\x80\x93/\-/g" *.srt
sed -i -e "s/\xe2\x80\x9c/\"/g" *.srt
sed -i -e "s/\xe2\x80\x9d/\"/g" *.srt
sed -i -e "s/\xc2\xbe/3\/4/g" *.srt
sed -i -e "s/\xce\x9f/O/g" *.srt
