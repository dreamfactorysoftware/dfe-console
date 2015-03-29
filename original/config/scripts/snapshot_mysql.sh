#!/bin/bash
#
# @(#)$Id: snapshot_mysql.sh,v 1.0.0 2013-03-04 jablan $
#
# Creates a dump of a DSP's MySQL
#

if [ `whoami` != "dfadmin" ] && [ `whoami` != "jablan" ]; then
    echo "This script must by run as \"dfadmin\""
    exit 1
fi

SCRIPT_NAME=`basename "${0}"`
SCRIPT_VERSION="v1.1.0"
SHORT_OPTIONS="hvqi:o:n:u:p:P:"
LONG_OPTIONS="help,version,quiet,instance:,output:,db-host:,db-user:,db-pass:,db-port:"
VERBOSE=1
DB_HOST="cumulus.fabric.dreamfactory.com"
DB_USER="cerberus"
DB_PASS="KlL8ZF-E-rBFw_h9ygQZh3ZF"
DB_PORT=3306
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
${SCRIPT_NAME} ${SCRIPT_VERSION} -- Creates a dump of a DSP MySQL database and output to STDOUT

usage:  ${SCRIPT_NAME} [options] instanceName

Options:
EO

	cat <<EO | column -s\& -t
  -i, --instance & the instance name
  -o, --output & the output destination
  -n, --db-host & the database host if not legacy
  -u, --db-user & the database user name
  -p, --db-pass & the database password
  -P, --port & the database port if not 3306
  -h, --help & show this output
  -q, --quiet & keeps output to a minimum
  -v, --version & show version information

EO

	echo ""

}

# Note that we use `"$@"' to let each command-line parameter expand to a
# separate word. The quotes around `$@' are essential!
# We need TEMP as the `eval set --' would nuke the return value of getopt.
TEMP=`getopt -o ${SHORT_OPTIONS} --long ${LONG_OPTIONS} -n "${SCRIPT_NAME}" -- "$@"`

# Note the quotes around `$TEMP': they are essential!
eval set -- "${TEMP}"

while true ; do
    case "$1" in
    	-n|--db-host)
			DB_HOST=$2; shift 2 ;;

    	-P|--db-port)
    		DB_PORT=$2; shift 2 ;;

    	-u|--db-user)
    		DB_USER=$2; shift 2 ;;

    	-p|--db-pass)
    		DB_PASS=$2; shift 2 ;;

    	-i|--instance)
    		INSTANCE_NAME=$2; shift 2 ;;

    	-o|--output)
    		OUTPUT=$2; shift 2 ;;

        -h|--help)
            usage; exit 0 ;;

        -v|--version)
            echo "${SCRIPT_VERSION}"; exit 0 ;;

        -q|--quiet)
            VERBOSE=0; shift ;;
        --)
            shift; break ;;
        *)
        	usage 'Yikes! Me no likey!'; exit 1 ;;
    esac
    shift
done

if [ "${INSTANCE_NAME}" = "" ] ; then
	usage "Error: No instance name specified."
	exit 1
fi

if [ "${OUTPUT}" = "" ] ; then
	OUTPUT="${INSTANCE_NAME}.`date +%Y%m%d%H%M%S`.mysql.sql"
fi

/usr/bin/mysqldump --compress --delayed-insert -u ${DB_USER} -p${DB_PASS} -h ${DB_HOST} --databases ${INSTANCE_NAME} > "${OUTPUT}"

if [ 0 != $? ] ; then
	echo "error while dumping database. stopping."
	exit 2
fi

[ -f "${OUTPUT}" ] && gzip -fq "${OUTPUT}"

exit 0
