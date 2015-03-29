#!/bin/bash

if [ "${1}x" = "x" ] || [ "${2}x" = "x" ] ; then
	echo 'usage: $0 <username> <password>'
	exit 1
fi

COUCH=http://dfadmin:Dream123@localhost:5984
USER_URI_BASE="$COUCH/_users"
USER_BASE="org.couchdb.user:${1}"
USER_URI="${USER_URI_BASE}/${USER_BASE}"
DB_NAME="storage_${1}"
echo ${USER_URI}

curl -HContent-Type:application/json \
  -vXPUT ${USER_URI} \
  --data-binary '{"_id": "${USER_BASE}","name": "${1}","roles": [],"type": "user","password": "${2}"}'

curl -vX PUT "$COUCH/${DB_NAME}"

curl -vX PUT "$COUCH/${DB_NAME}/_security"  \
   -Hcontent-type:application/json \
    --data-binary '{"admins":{"names":["dfadmin"],"roles":[]},"members":{"names":["${1}"],"roles":[]}}'