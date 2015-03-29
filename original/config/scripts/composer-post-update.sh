#!/bin/bash
#
# This script is run from the root's /vendor directory

ASSETS_PATH=../web/assets
LOG_PATH=/opt/dreamfactory/log/fabric/cerberus

echo "Cerberus post-update script (working from:${PWD})"
echo -n "Postulating"

#	Initialize submodules...
cd ../
echo -n ", submodules"
if [ -d ".git" ] ; then
	git submodule -q update --init >/dev/null
	if [ 0 != $? ] ; then
		cd vendor/
		exit $?
	fi
fi

cd vendor/

# Ensure path
echo -n ", paths"
mkdir -p "${ASSETS_PATH}" >/dev/null 2>&1
mkdir -p "${LOG_PATH}" >/dev/null 2>&1

# Make symlinks
echo -n ", links"
[ ! -L "/opt/dreamfactory/fabric/cerberus/log" ] && ln -s "${LOG_PATH}" /opt/dreamfactory/fabric/cerberus/log >/dev/null 2>&1

echo ", complete!"
echo

