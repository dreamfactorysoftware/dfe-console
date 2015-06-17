#!/bin/bash
#
# @(#)$Id: snapshot_mysql.sh,v 1.0.1 2015-02-13 jablan $
#
# Creates a dump of a DSP's MySQL
#

if [ `whoami` != "dfadmin" ] && [ `whoami` != 'jablan' ] ; then
    echo "This script must by run as \"dfadmin\""
    exit 1
fi

SCRIPT_NAME=`basename "${0}"`
SCRIPT_VERSION="v1.0.1"
SHORTOPTS="hvqD:o:n:u:p:P:"
LONGOPTS="help,version,quiet,db-name:,output:,db-host:,db-user:,db-pass:,db-port:"
VERBOSE=1
DB_HOST="db-east-1.enterprise.dreamfactory.com"
DB_NAME="$1"
DB_USER="cerberus"
DB_PASS="KlL8ZF-E-rBFw_h9ygQZh3ZF"
DESTINATION=
NO_ZIP=0

usage()
{
	if [ "${1}x" != "x" ] ; then
		echo $1
		echo
	fi

	cat << EO
${SCRIPT_NAME} ${SCRIPT_VERSION} -- Creates a dump of a DSP MySQL database and output to STDOUT

usage:  ${SCRIPT_NAME} [options] instanceName outputFileName

Options:
EO

	cat <<EO | column -s\& -t
  -o, --output & the output destination
  -n, --db-host & the database host if not legacy
  -u, --db-user & the database user name
  -p, --db-pass & the database password
  -P, --db-port & the database port if not 3306
  -D, --db-name & the database name
  -x, --no-zip & If set, do not zip the result file
  -h, --help & show this output
  -q, --quiet & keeps output to a minimum
  -v, --version & show version information

EO

	echo ""

}

while :; do
     case $1 in
        -q|--quiet)
            VERBOSE=0
            ;;
    	-x|--no-zip)
    		NO_ZIP=1
    		;;
    	-n|--db-host=?*)
    		DB_HOST=${1#*=}
    		shift 2
    		continue;
    		;;
    	-D|--db-name=?*)
    		DB_NAME=${1#*=}
    		shift 2
    		continue;
    		;;
    	-P|--db-port=?*)
    		DB_PORT=${1#*=}
    		shift 2
    		continue
    		;;
    	-u|--db-user=?*)
    		DB_USER=${1#*=}
    		shift 2
    		continue
    		;;
    	-p|--db-pass=?*)
    		DB_PASS=${1#*=}
    		shift 2
    		continue;
    		;;
    	-o|--output=?*)
    		DESTINATION=${1#*=}
    		shift 2
    		continue
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        -v|--version)
            echo "${SCRIPT_VERSION}"
            exit 0
            ;;
        -?*)
     	 	DB_NAME=$1
       	 	DESTINATION=$2
       	 	shift 2
       	 	continue
            ;;
        --)
            shift
            break
            ;;

        *)
        	break
        	;;
    esac

    command shift
done

echo "$1"
echo "$2"
echo "$3"
echo "---"
DB_NAME="$1"
DESTINATION="$2"

if [ "${1}" = "" ] ; then
	usage "error: No instance name specified."
	exit 1
fi

OUTPUT=
if [ "${DESTINATION}" != "" ] ; then
	_base="`dirname ${DESTINATION}`"

	if [ ! -d ${_base} ] ; then
		mkdir -p ${_base}
		[ 0 != $? ] && echo "error: cannot create destination directory." && exit 1
	fi

	OUTPUT="--result-file=${DESTINATION}"
fi

/usr/bin/mysqldump --compress --delayed-insert -u ${DB_USER} -p${DB_PASS} -h ${DB_HOST} "${OUTPUT}" ${DB_NAME}

if [ 0 != $? ] ; then
	echo "error while dumping database. stopping."
	exit 2
fi

[ ${NO_ZIP} -eq 0 ] && gzip -fq "${DESTINATION}"
