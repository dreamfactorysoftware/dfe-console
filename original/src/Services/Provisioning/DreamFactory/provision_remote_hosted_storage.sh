#!/bin/bash
#
# provision_remote_hosted_storage.sh
#

if [ "x" = "x$1" ] ; then
        echo "Usage: $0 <instanceName>"
        exit 1
fi

ssh cumulus.fabric.dreamfactory.com -e "/data/scripts/provision.sh \"$1\""

BASE_STORAGE="/data/storage/${1}"
BASE_SNAPSHOT="/data/snapshots/${1}"
BLOB_PATH="/blob"
PRIVATE_PATH="/.private"

[ ! -d "$BASE_STORAGE" ] && mkdir $BASE_STORAGE
[ ! -d "$BASE_SNAPSHOT" ] && mkdir $BASE_SNAPSHOT
[ ! -d "${BASE_STORAGE}${BLOB_PATH}" ] && mkdir "${BASE_STORAGE}${BLOB_PATH}"
[ ! -d "${BASE_STORAGE}${PRIVATE_PATH}" ] && mkdir "${BASE_STORAGE}${PRIVATE_PATH}"

exit 0

