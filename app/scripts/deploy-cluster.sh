#!/bin/bash

## 1. Create a mount
php artisan dfe:mount create $mount_id -t local -p $root_path --config '{"disk":"$mount_id"}'

## 2. Create servers
php artisan dfe:server create $web_server_id -t web -a $web_server_host_name -m $mount_id
php artisan dfe:server create $app_server_id -t app -a $app_server_host_name -m $mount_id
php artisan dfe:server create $db_server_id -t db -a $db_server_host_name -m $mount_id -c '{"port":"3306","username":"dfe_user","password":"dfe_user","driver":"mysql","default-database-name":"","multi-assign":"on"}'

## 3. Create cluster
php artisan dfe:cluster create $cluster_id --subdomain $subdomain

## 4. Add servers to cluster
php artisan dfe:cluster add $cluster_id --server-id $web_server_id
php artisan dfe:cluster add $cluster_id --server-id $app_server_id
php artisan dfe:cluster add $cluster_id --server-id $db_server_id
