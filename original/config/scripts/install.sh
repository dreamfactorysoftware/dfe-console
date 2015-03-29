#!/bin/bash
# Cerberus install/update utility
# Copyright (C) 2012-2013 DreamFactory Software, Inc. All Rights Reserved
#
# CHANGELOG:
#
# v1.0.1
#   chmod +x on scripts after install
#
# v1.0.0
#   Initial release

##
##	Initial settings
##
VERSION=1.0.1
SYSTEM_TYPE=`uname -s`
INSTALL_DIR="${HOME}/bin"
BASE_INSTALL_PATH=$(dirname "${0}")
COMPOSER=composer.phar
COMPOSER_INSTALLED=0
PHP=/usr/bin/php
WEB_USER=www-data
BASE=`pwd`
B1=`tput bold`
B2=`tput sgr0`
FABRIC=0
FABRIC_MARKER=/var/www/.fabric_hosted
CURRENT_PATH=${BASE_INSTALL_PATH}/cerberus.current
RELEASES_PATH=${BASE_INSTALL_PATH}/cerberus.releases
RELEASE_TAG_FORMAT="%Y%m%d%k%M%S"
TAG="Mode: ${B1}Fabric${B2}"
VERBOSE=
QUIET="--quiet"
GIT_REMOTE="origin"
GIT_BRANCH="master"

# Hosted or standalone?
echo "${B1}DreamFactory Fabric(tm) Cerberus${B2} ${SYSTEM_TYPE} Installer [${TAG} v${VERSION}]"

#	Execute getopt on the arguments passed to this program, identified by the special character $@
PARSED_OPTIONS=$(getopt -n "$0"  -o hvcp --long "help,verbose,clean,purge"  -- "$@")

#	Bad arguments, something has gone wrong with the getopt command.
if [ $? -ne 0 ] ; then
	exit 1
fi

# Composer already installed?
if [ -f "${INSTALL_DIR}/${COMPOSER}" ] ; then
	COMPOSER_INSTALLED=1
fi

#	A little magic, necessary when using getopt.
eval set -- "${PARSED_OPTIONS}"

while true ;  do
	case "$1" in
		-h|--help)
			echo "usage: $0 [-v|--verbose] [-c|--clean]"
			shift
	    	;;

		-v|--verbose)
			VERBOSE="--verbose"
			QUIET=
			echo "  * Verbose mode enabled"
			shift
			;;

		-p|--purge)
			if [ ${COMPOSER_INSTALLED} -eq 0 ] ; then
				if [ -f "/usr/local/bin/composer.phar" ] ; then
					rm /usr/local/bin/composer.phar
					if [ $? -ne 0 ] ; then
						echo "  ! Cannot remove \"${B1}/usr/local/bin/composer.phar${B2}\". Please remove manually and re-run script."
					fi
				fi
			else
				echo "  * ${B1}Did not remove composer.phar as we did not install it."
			fi

			rm -rf ./vendor/ ./composer.lock >/dev/null 2>&1
			echo "  * Clean/purge install. Local dependencies removed."
			exit 0
			shift
			;;

		-c|--clean)
			if [ ${COMPOSER_INSTALLED} -eq 0 ] ; then
				if [ -f "/usr/local/bin/composer.phar" ] ; then
					rm /usr/local/bin/composer.phar
					if [ $? -ne 0 ] ; then
						echo "  ! Cannot remove \"${B1}/usr/local/bin/composer.phar${B2}\". Please remove manually and re-run script."
					fi
				fi
			else
				echo "  * ${B1}Did not remove composer.phar as we did not install it."
			fi

			rm -rf ./vendor/ ./composer.lock >/dev/null 2>&1
			echo "  * Clean install. Local dependencies removed."
			shift
			;;

		--)
			shift
			break;;
	esac
done

echo "  * Install user is ${B1}\"${USER}\"${B2}"

if [ "Darwin" = "${SYSTEM_TYPE}" ] ; then
	WEB_USER=_www
	echo "  * OS X installation: Apache user set to \"${B1}_www${B2}\""
elif [ "Linux" != "${SYSTEM_TYPE}" ] ; then
	echo "  * Windows/other installation. ${B1}Not fully tested so your mileage may vary${B2}."
fi

##
## Shutdown non-essential services
##
service apache2 stop >/dev/null 2>&1
service mysql stop >/dev/null 2>&1

##
## Construct the various paths
##
RELEASE_TAG=$(date +${RELEASE_TAG_FORMAT})
BASE_PATH=${RELEASES_PATH}/${RELEASE_TAG}
LOG_DIR=${BASE_PATH}/log/
VENDOR_DIR=${BASE_PATH}/vendor
WEB_DIR=${BASE_PATH}/web
PUBLIC_DIR=${WEB_DIR}
ASSETS_DIR=${PUBLIC_DIR}/assets
APP_DIR=${BASE_PATH}/app

###
### Check directory permissions...
###
#echo "  * Checking file system"
#chown -R ${USER}:${WEB_USER} * .git*
#find ./ -type d -exec chmod 2775 {} \;
#find ./ -type f -exec chmod 0664 {} \;
#find ./ -name '*.sh' -exec chmod 0770 {} \;
##rm -rf ~${HOME}/.composer/
#chmod +x ${BASE_PATH}/scripts/*.sh ${BASE_PATH}/config/scripts/*.sh
#[ -f ${BASE_PATH}/git-ssh-wrapper ] && chmod +x ${BASE_PATH}/git-ssh-wrapper

##
## Check if composer is installed
## If not, install. If it is, make sure it's current
##

if [ ! -d "${INSTALL_DIR}" ] ; then
	mkdir -p "${INSTALL_DIR}" >/dev/null 2>&1
fi

if [ ! -f "${INSTALL_DIR}/${COMPOSER}" ] ; then
	echo "  * Installing package manager"
	curl -s https://getcomposer.org/installer | ${PHP} -- --install-dir=${INSTALL_DIR} ${QUIET} ${VERBOSE} --no-interaction
else
	[ "--verbose" = "${VERBOSE}" ] && echo "  * Composer pre-installed"
	echo "  * Checking for package manager updates"
	${PHP} ${INSTALL_DIR}/${COMPOSER} ${QUIET} ${VERBOSE} --no-interaction self-update
fi

##
##	Get latest version from git
##
echo "  * Installation Release Tag: ${RELEASE_TAG}"
pushd "${RELEASES_PATH}" >/dev/null 2>&1
mkdir -p "${RELEASE_TAG}"
[ "--verbose" = "${VERBOSE}" ] && echo "    - Release directory \"${RELEASES_PATH}/${RELEASE_TAG}\" created"
cd "${RELEASE_TAG}"
[ "--verbose" = "${VERBOSE}" ] && echo "    - Fetching ${GIT_REMOTE}/${GIT_BRANCH}"
git fetch ${QUIET} ${VERBOSE} ${GIT_REMOTE} ${GIT_BRANCH}
cd "${CURRENT_PATH}"
ln -s ${RELEASES_PATH} ${RELEASE_TAG}
[ "--verbose" = "${VERBOSE}" ] && echo "    - Symlink \"${CURRENT_PATH}/${RELEASE_TAG}\" created"
popd >/dev/null 2>&1

##
##	Install composer dependencies
##
pushd "${BASE_PATH}" >/dev/null

if [ ! -d "${VENDOR_DIR}" ] ; then
	echo "  * Installing dependencies"
	${PHP} "${INSTALL_DIR}/${COMPOSER}" ${QUIET} ${VERBOSE} --no-interaction install
else
	echo "  * Updating dependencies"
	${PHP} "${INSTALL_DIR}/${COMPOSER}" ${QUIET} ${VERBOSE} --no-interaction update
fi

##
##	Make sure our directories are in place...
##
if [ ! -d "${LOG_DIR}" ] ; then
	mkdir "${LOG_DIR}" >/dev/null 2>&1 && echo "  * Created ${LOG_DIR}"
fi

if [ ! -d "${ASSETS_DIR}" ] ; then
	mkdir "${ASSETS_DIR}" >/dev/null 2>&1 && echo "  * Created ${ASSETS_DIR}"
fi

# Back
cd - >/dev/null 2>&1

##
## make owned by user and scripts executable
##
chown -R ${USER}:${WEB_USER} * .git*
chmod +x "${BASE_PATH}/config/scripts/*.sh"

##
## Restart non-essential services
##
service mysql start >/dev/null 2>&1
service apache2 start >/dev/null 2>&1

echo
echo "Complete. Enjoy the rest of your day!"

exit 0
