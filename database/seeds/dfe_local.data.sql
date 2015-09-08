/********************************************************************************
* DreamFactory Enterprise(tm) Console/Dashboard Install/Setup Data
* Copyright (c) 2012-infinity DreamFactory Software, Inc. All Rights Reserved
********************************************************************************/

/********************************************************************************
* The supported environments
********************************************************************************/

INSERT INTO `environment_t` (`id`, `user_id`, `environment_name_text`, `create_date`, `lmod_date`)
VALUES ( '1', NULL, 'Development', now(), now() ), ( '2', NULL, 'Production', now(), now() );

/********************************************************************************
* The types of servers allowed in clusters
********************************************************************************/

INSERT INTO `server_type_t` (`id`, `type_name_text`, `schema_text`, `create_date`, `lmod_date`)
VALUES ( 1, 'db', '', now(), now() ), ( 2, 'web', '', now(), now() ), ( 3, 'app', '', now(), now() );

/********************************************************************************
* Vendors supported with this version
********************************************************************************/

INSERT INTO `vendor_t` (`id`, `vendor_name_text`, `create_date`, `lmod_date`)
VALUES
    ( 1, 'Amazon EC2', now(), now() ),
    ( 2, 'DreamFactory', now(), now() ),
    ( 3, 'Windows Azure', now(), now() ),
    ( 4, 'Rackspace', now(), now() ),
    ( 5, 'OpenStack', now(), now() );

/********************************************************************************
* Preloaded vendor images for instances (this is way old)
********************************************************************************/

INSERT INTO `vendor_image_t` (`id`, `vendor_id`, `os_text`, `license_text`, `image_id_text`, `image_name_text`, `image_description_text`, `architecture_nbr`, `region_text`, `availability_zone_text`, `root_storage_text`, `create_date`, `lmod_date`)
VALUES
    (
        34,
        1,
        'Linux',
        'Public',
        'ami-013f9768',
        'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120728',
        NULL,
        1,
        NULL,
        NULL,
        'ebs',
        NOW(),
        NOW() ),
    ( 169, 1, 'Linux', 'Public', 'ami-057bcf6c', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120822', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 430, 1, 'Linux', 'Public', 'ami-0d3f9764', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120728', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 624, 1, 'Linux', 'Public', 'ami-137bcf7a', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120822', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 1865, 1, 'Linux', 'Public', 'ami-3b4ff252', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121001', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 1920, 1, 'Linux', 'Public', 'ami-3d4ff254', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121001', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 3981, 1, 'Linux', 'Public', 'ami-82fa58eb', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120616', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 4275, 1, 'Linux', 'Public', 'ami-8cfa58e5', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120616', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 4647, 1, 'Linux', 'Public', 'ami-9878c0f1', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121026.1', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 4764, 1, 'Linux', 'Public', 'ami-9c78c0f5', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121026.1', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 4946, 1, 'Linux', 'Public', 'ami-a29943cb', 'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120424', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 5246, 1, 'Linux', 'Public', 'ami-ac9943c5', 'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120424', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 5635, 1, 'Linux', 'Public', 'ami-0baf7662', 'ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120403', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 5965, 1, 'Linux', 'Public', 'ami-1616ca7f', 'ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20120312', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 6944, 1, 'Linux', 'Public', 'ami-349b495d', 'ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120221', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 7050, 1, 'Linux', 'Public', 'ami-37af765e', 'ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120403', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 7274, 1, 'Linux', 'Public', 'ami-3e9b4957', 'ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120221', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 7665, 1, 'Linux', 'Public', 'ami-4bad7422', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120401', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 7728, 1, 'Linux', 'Public', 'ami-4dad7424', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120401', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 7984, 1, 'Linux', 'Public', 'ami-55dc0b3c', 'ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120110', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 8592, 1, 'Linux', 'Public', 'ami-699f3600', 'ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20120723', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 8657, 1, 'Linux', 'Public', 'ami-6ba27502', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120108', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 8808, 1, 'Linux', 'Public', 'ami-6fa27506', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120108', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 8879, 1, 'Linux', 'Public', 'ami-71dc0b18', 'ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120110', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 8923, 1, 'Linux', 'Public', 'ami-7339b41a', 'ubuntu/images/ebs/ubuntu-quantal-12.10-i386-server-20121218', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 8998, 1, 'Linux', 'Public', 'ami-7539b41c', 'ubuntu/images/ebs/ubuntu-quantal-12.10-amd64-server-20121218', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 9362, 1, 'Linux', 'Public', 'ami-81c31ae8', 'ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20120402', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 9364, 1, 'Linux', 'Public', 'ami-81cf46e8', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20130103', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 9441, 1, 'Linux', 'Public', 'ami-83cf46ea', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20130103', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 9559, 1, 'Linux', 'Public', 'ami-87c31aee', 'ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20120402', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 9909, 1, 'Linux', 'Public', 'ami-9265dbfb', 'ubuntu/images/ebs/ubuntu-quantal-12.10-i386-server-20121017', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 9966, 1, 'Linux', 'Public', 'ami-9465dbfd', 'ubuntu/images/ebs/ubuntu-quantal-12.10-amd64-server-20121017', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 10309, 1, 'Linux', 'Public', 'ami-9f9c35f6', 'ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20120723', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 10344, 1, 'Linux', 'Public', 'ami-a0ba68c9', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120222', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 10487, 1, 'Linux', 'Public', 'ami-a562a9cc', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20111205', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 10520, 1, 'Linux', 'Public', 'ami-a6b10acf', 'ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20121028', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11152, 1, 'Linux', 'Public', 'ami-bab10ad3', 'ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20121028', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11153, 1, 'Linux', 'Public', 'ami-baba68d3', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120222', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11297, 1, 'Linux', 'Public', 'ami-bf62a9d6', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20111205', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11313, 1, 'Linux', 'Public', 'ami-c012cea9', 'ubuntu/images/ebs/ubuntu-maverick-10.10-i386-server-20120310', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11368, 1, 'Linux', 'Public', 'ami-c19e37a8', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120722', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11445, 1, 'Linux', 'Public', 'ami-c412cead', 'ubuntu/images/ebs/ubuntu-maverick-10.10-amd64-server-20120310', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11502, 1, 'Linux', 'Public', 'ami-c5b202ac', 'ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120913', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11566, 1, 'Linux', 'Public', 'ami-c7b202ae', 'ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120913', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11702, 1, 'Linux', 'Public', 'ami-cbc072a2', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120918', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11771, 1, 'Linux', 'Public', 'ami-cdc072a4', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120918', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 11948, 1, 'Linux', 'Public', 'ami-d38f57ba', 'ubuntu/images/ebs/ubuntu-maverick-10.10-i386-server-20120410', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 12030, 1, 'Linux', 'Public', 'ami-d5e54dbc', 'ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120724', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 12079, 1, 'Linux', 'Public', 'ami-d78f57be', 'ubuntu/images/ebs/ubuntu-maverick-10.10-amd64-server-20120410', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 12148, 1, 'Linux', 'Public', 'ami-d99e37b0', 'ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120722', NULL, 1, NULL, NULL, 'ebs', NOW(), NOW() ),
    ( 12360, 1, 'Linux', 'Public', 'ami-dfe54db6', 'ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120724', NULL, 0, NULL, NULL, 'ebs', NOW(), NOW() ),
    (
        12367,
        1,
        'Linux',
        'Public',
        'ami-e016ca89',
        'ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20120312',
        NULL,
        1,
        NULL,
        NULL,
        'ebs',
        NOW(),
        NOW() ),
    (
        12601,
        1,
        'Linux',
        'Public',
        'ami-e720ad8e',
        'ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121218',
        NULL,
        0,
        NULL,
        NULL,
        'ebs',
        NOW(),
        NOW() ),
    (
        13287,
        1,
        'Linux',
        'Public',
        'ami-fd20ad94',
        'ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121218',
        NULL,
        1,
        NULL,
        NULL,
        'ebs',
        NOW(),
        NOW() );
