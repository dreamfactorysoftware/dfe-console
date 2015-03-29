#!/bin/bash
#
# @(#)$Id: fix-perms.sh,v 1.0.0 2015-01-16 jablan $
#
# Fixes private database config permissions
#
[ "" = "${1}" ] && echo "No target given." && exit 1

PATH_IS_DIR=0
[ "-d" = "${2}" ] && PATH_IS_DIR=1

WORK_PATH=$1

## Make the path if it doesn't exist
[ ${PATH_IS_DIR} -eq 1 ] && WORK_PATH=$1 || WORK_PATH=`basename ${1}`
[ ! -d ${WORK_PATH} ] && mkdir -p ${WORK_PATH}

## Find all entries under work path and chown/chmod
/usr/bin/find ${WORK_PATH} -type d -exec chown dfadmin:www-data {} \; -exec chmod 2775 {} \; >/dev/null

[ ${PATH_IS_DIR} -eq 0 ] && chmod 0660 "${1}" >/dev/null
chown dfadmin:www-data "${1}" >/dev/null
