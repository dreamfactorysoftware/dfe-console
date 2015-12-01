/********************************************************************************
* DreamFactory Enterprise(tm) Console/Dashboard Install/Setup Data
* Copyright (c) 2012-infinity DreamFactory Software, Inc. All Rights Reserved
********************************************************************************/

/********************************************************************************
* The supported environments
********************************************************************************/

INSERT INTO `environment_t` (`user_id`, `environment_id_text`, `create_date`, `lmod_date`)
VALUES (NULL, 'Development', NOW(), NOW()), (NULL, 'Production', NOW(), NOW());

/********************************************************************************
* The default local mount
********************************************************************************/

INSERT INTO `mount_t` (`mount_type_nbr`, `mount_id_text`, `root_path_text`, `owner_id`, `owner_type_nbr`, `config_text`, `create_date`)
VALUES (0, 'mount-local-1', '/data/storage/', NULL, NULL, '{"disk":"local"}', NOW());

/********************************************************************************
* The types of servers allowed in clusters
********************************************************************************/

INSERT INTO `server_type_t` (`id`, `type_name_text`, `schema_text`, `create_date`, `lmod_date`)
VALUES (1, 'db', '', NOW(), NOW()), (2, 'web', '', NOW(), NOW()), (3, 'app', '', NOW(), NOW());

/********************************************************************************
* Vendors supported with this version
********************************************************************************/

INSERT INTO `vendor_t` (`id`, `vendor_name_text`, `create_date`, `lmod_date`)
VALUES
  (1, 'Amazon EC2', NOW(), NOW()),
  (2, 'DreamFactory', NOW(), NOW()),
  (3, 'Windows Azure', NOW(), NOW()),
  (4, 'Rackspace', NOW(), NOW()),
  (5, 'OpenStack', NOW(), NOW());

/********************************************************************************
* Preloaded vendor images for instances (this is way old)
********************************************************************************/

INSERT INTO `vendor_image_t` (`id`, `vendor_id`, `os_text`, `license_text`, `image_id_text`, `image_name_text`, `image_description_text`, `architecture_nbr`, `region_text`, `availability_zone_text`, `root_storage_text`, `create_date`, `lmod_date`)
VALUES
  (34,
    1,
    'Linux',
    'Public',
    'ami-013f9768',
    'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120728',
    NULL,
    1,
    NULL,
    NULL,
    'ebs', NOW(), NOW()),
  (169, 1, 'Linux', 'Public', 'ami-057bcf6c', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120822', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW()),
  (430, 1, 'Linux', 'Public', 'ami-0d3f9764', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120728', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW()),
  (624, 1, 'Linux', 'Public', 'ami-137bcf7a', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120822', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW()),
  (1865, 1, 'Linux', 'Public', 'ami-3b4ff252', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121001', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW()),
  (1920, 1, 'Linux', 'Public', 'ami-3d4ff254', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121001', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW()),
  (3981, 1, 'Linux', 'Public', 'ami-82fa58eb', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120616', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW()),
  (4275, 1, 'Linux', 'Public', 'ami-8cfa58e5', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120616', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW()),
  (4647, 1, 'Linux', 'Public', 'ami-9878c0f1', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121026.1', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW()),
  (4764, 1, 'Linux', 'Public', 'ami-9c78c0f5', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121026.1', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW()),
  (4946, 1, 'Linux', 'Public', 'ami-a29943cb', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120424', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW()),
  (5246,
    1,
    'Linux',
    'Public',
    'ami-ac9943c5',
    'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120424',
    NULL,
    0,
    NULL,
    NULL,
    'ebs', NOW(), NOW()),
  (12601,
    1,
    'Linux',
    'Public',
    'ami-e720ad8e',
    'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121218',
    NULL,
    0,
    NULL,
    NULL,
    'ebs', NOW(), NOW()),
  (13287,
    1,
    'Linux',
    'Public',
    'ami-fd20ad94',
    'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121218',
    NULL,
    1,
    NULL,
    NULL,
    'ebs', NOW(), NOW());
