##------------------------------------------------------------------------------
## Application Settings
##------------------------------------------------------------------------------

##dfe.chaoticwave.com settings

APP_ENV=local
APP_DEBUG=true
APP_KEY=Wol5POG85A7gVQqlXglEE7mAYWU1eqMD
APP_URL=http://console.dfe.chaoticwave.com

##------------------------------------------------------------------------------
## Database Settings
##------------------------------------------------------------------------------

DB_DRIVER=mysql
DB_HOST=localhost
DB_DATABASE=dfe_local
DB_USERNAME=dfe_user
DB_PASSWORD=dfe_user
DB_COLLATION=utf8_unicode_ci
DB_CHARSET=utf8
DB_PREFIX=

##------------------------------------------------------------------------------
## Cache/Session Settings
##------------------------------------------------------------------------------

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

##------------------------------------------------------------------------------
## DFE Defaults
##------------------------------------------------------------------------------

# The cluster in which this system is included
DFE_CLUSTER_ID=cluster-dfe
# User to run scripts as
DFE_SCRIPT_USER=dfadmin
## Default prefix for email sent from console
DFE_EMAIL_SUBJECT_PREFIX=[DFE]
# String to pre-pend to non-admin created instance names
DFE_DEFAULT_INSTANCE_PREFIX=
# The default cluster to use
DFE_DEFAULT_CLUSTER=cluster-dfe
# The default database to use
DFE_DEFAULT_DATABASE=db-dfe
# The default provisioner to use
DFE_DEFAULT_GUEST_LOCATION=2
# Used for non-DFE managed instances (i.e. EC2)
DFE_DEFAULT_RAM_SIZE=1
DFE_DEFAULT_DISK_SIZE=8
# Hash algorithm to use when signing requests
DFE_SIGNATURE_METHOD=sha256
## Allow self-registration. The default is "FALSE".
DFE_OPEN_REGISTRATION=false
## The redirect route if registration closed
DFE_CLOSED_ROUTE=auth/login
## The number of days to keep metrics
DFE_METRICS_KEEP_DAYS=365

##------------------------------------------------------------------------------
## DFE DNS settings
##------------------------------------------------------------------------------

# The subdomain for instances
DFE_DEFAULT_DNS_ZONE=dfe
# The TLD for instances
DFE_DEFAULT_DNS_DOMAIN=chaoticwave.com
# Full domain for instances
DFE_DEFAULT_DOMAIN=dfe.chaoticwave.com
# The http protocol to use by default. This can be "http" (the default) or "https"
DFE_DEFAULT_DOMAIN_PROTOCOL=http

##------------------------------------------------------------------------------
## DFE mail configuration
##------------------------------------------------------------------------------

SMTP_DRIVER=smtp
SMTP_HOST=localhost
SMTP_PORT=25
MAIL_FROM_ADDRESS=no.reply@chaoticwave.com
MAIL_FROM_NAME=chaoticwave.com
MAIL_USERNAME=
MAIL_PASSWORD=
# The dashboard URL
DFE_DASHBOARD_URL=http://dashboard.dfe.chaoticwave.com
# The support email address
DFE_SUPPORT_EMAIL_ADDRESS=support@dreamfactory.com

##------------------------------------------------------------------------------
## MailGun Settings
##------------------------------------------------------------------------------

MAILGUN_DOMAIN=mg.myplace.com
MAILGUN_SECRET_KEY=my-mailgun-secret-key

##------------------------------------------------------------------------------
## DFE Paths
##------------------------------------------------------------------------------

# The mount point of hosted-instance storage
DFE_HOSTED_BASE_PATH=/data/storage
# The type of storage zone. Can be "static" or "dynamic"
DFE_STORAGE_ZONE_TYPE=static
# The storage zone name to use if DFE_STORAGE_ZONE_TYPE=static
DFE_STATIC_ZONE_NAME=local
# The public base path for an instance
DFE_PUBLIC_PATH_BASE=/
# The private path directory name. Relative to owner's storage path and owner's private path
DFE_PRIVATE_PATH_NAME=.private
# The snapshot path directory name. This is relative to the owner's private path
DFE_SNAPSHOT_PATH_NAME=snapshots
## DFE public paths to create (pipe-delimited if multiple)
DFE_PUBLIC_PATHS=applications|.private
## DFE private paths to create (pipe-delimited if multiple)
DFE_PRIVATE_PATHS=.cache|config|scripts|scripts.user
## DFE owner-private paths to create (pipe-delimited if multiple)
DFE_OWNER_PRIVATE_PATHS=snapshots

##------------------------------------------------------------------------------
## Snapshots
##------------------------------------------------------------------------------

## The number of days to keep a snapshot on-hand
DFE_SNAPSHOT_DAYS_TO_KEEP=30
## If this is true, once expired, the snapshot is moved to "trash" path. Otherwise it is deleted.
DFE_SNAPSHOT_SOFT_DELETE=false
## If snapshots are to be soft-deleted, this is where they will be moved
DFE_SNAPSHOT_TRASH_PATH=/data/trash

##------------------------------------------------------------------------------
## Auditing/Data Collection
##------------------------------------------------------------------------------

## The host name of the data collection server
DFE_AUDIT_HOST=console.dfe.chaoticwave.com
## The port to which to send data
DFE_AUDIT_PORT=12202
## The message format of the audit data (0 = GELF, is only supported currently)
DFE_AUDIT_MESSAGE_FORMAT=0
## The host name for client requests (to the reporting server) if different from DFE_AUDIT_HOST
DFE_AUDIT_CLIENT_HOST=console.dfe.chaoticwave.com
## The port for client requests (to the reporting server), defaults to 5601
DFE_AUDIT_CLIENT_PORT=5601

##------------------------------------------------------------------------------
## Console Security
##------------------------------------------------------------------------------

## Set values from console.env file generated by "php artisan dfe:setup" command

# Console API endpoint
#DFE_CONSOLE_API_URL=http://console.dfe.chaoticwave.com/api/v1/ops
# Key generated by you to sign API requests
DFE_CONSOLE_API_KEY=OGNkNzYwY2ZhMmJmMjNlOTc4YWQ2ZTI0NjdlYWUwOTgyY2E2NWFjYzZjYWIyNTk0NGJjMzA4MmU2NTg3ODA4YQ
# The console's api key
DFE_CONSOLE_API_CLIENT_ID=69a6fe07b5a4f8c8a37176b1d1a71f2da68d5bc93a54045d477fa3079cc8aa6a
# The console's secret
DFE_CONSOLE_API_CLIENT_SECRET=05eb2ce022797341c5f5f12f951527916b4cdaeb07c95c4f5a861a6648ca1aa8


