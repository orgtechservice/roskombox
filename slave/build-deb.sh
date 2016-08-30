#!/bin/bash

mkdir -p output/opt/roskomcheck
mkdir -p output/usr/bin
cp -r extra/DEBIAN output
cp config.py.example output/opt/roskomcheck/config.py
cp roskomcheck.py output/opt/roskomcheck/roskomcheck.py
cp extra/roskomcheck output/usr/bin/roskomcheck
fakeroot dpkg-deb --build output
rm -rf output
mv output.deb roskomcheck.deb
echo "All done!"
