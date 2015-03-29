#!/bin/bash
#
# @(#)$Id: snapshot_storage.sh,v 1.0.0 2013-03-04 jablan $
#
# Creates a dump of a DSP's storage space
#

if [ `whoami` != "dfadmin" ]; then
    echo "This script must by run as \"dfadmin\""
    exit 1
fi

SCRIPT_NAME=`basename "${0}"`
SCRIPT_VERSION="v1.0.0"
SHORTOPTS="hvq"
LONGOPTS="help,version,quiet"
VERBOSE=1
DB_HOST="cumulus.fabric.dreamfactory.com"
DB_USER="cerberus"
DB_PASS="KlL8ZF-E-rBFw_h9ygQZh3ZF"
INSTANCE_NAME="$1"
DESTINATION="$2"
OUTPUT=""

usage()
{
	if [ "${1}x" != "x" ] ; then
		echo $1
		echo
	fi

	cat << EO
${SCRIPT_NAME} ${SCRIPT_VERSION} -- Creates a dump of a DSP's MySQL database and output to STDOUT

usage:  ${SCRIPT_NAME} [options] instanceName

Options:
EO

	cat <<EO | column -s\& -t
  -i, --instance & the instance name
  -o, --output & the output destination
  -h, --help & show this output
  -q, --quiet & keeps output to a minimum
  -v, --version & show version information

EO

	echo ""

}

ARGS=$(getopt -s bash --options ${SHORTOPTS} --longoptions ${LONGOPTS} --name ${SCRIPT_NAME} -- "$@")

eval set -- "${ARGS}"

while true; do
    case $1 in
    	-i|--instance)
    		INSTANCE_NAME=$2; shift; break;;
    	-o|--output)
    		DESTINATION=$2; shift; break;;
        -h|--help)
            usage; exit 0;;
        -v|--version)
            echo "${SCRIPT_VERSION}"; exit 0;;
        -q|--quiet)
            VERBOSE=0; shift;;
        --)
            shift; break;;
        *)
            usage; exit 1;;
    esac
    shift
done

if [ "${INSTANCE_NAME}x" = "x" ] ; then
	usage "Error: No instance name specified."
	exit 1
fi

if [ "${DESTINATION}x" != "x" ] ; then
	OUTPUT="--result-file=${DESTINATION}"
fi

/usr/bin/mysqldump --compress --delayed-insert -u ${DB_USER} -p${DB_PASS} -h ${DB_HOST} "${OUTPUT}" ${INSTANCE_NAME}

if [ 0 != $? ] ; then
	echo "error while dumping database. stopping."
	exit 2
fi

gzip -fq "${DESTINATION}"
