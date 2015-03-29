<?php
/**
 * sqlAzure.service.config.php
 * Configuration file for the azure blob service
 *
 * @copyright Copyright (c) 2012 DreamFactory Software, Inc.
 * @link      http://www.dreamfactory.com DreamFactory Software, Inc.
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 *
 * @filesource
 */
/** @noinspection SpellCheckingInspection */
return array(
	/**
	 * Blob service configuration
	 */
	'blob.development'  => false,
	//*************************************************************************
	//* Prod keys
	//*************************************************************************

	'blob.storage.name' => 'dreamfactorysoftware',
	'blob.storage.key'  => 'lpUCNR/7lmxBVsQuB3jD4yBQ4SWTvbmoJmJ4f+2q7vvm7/qQBHF0Lkfq4QQSk7KefNc5O3VJbQuW+wLLp79F3A==',

	//*************************************************************************
	//* Trial Keys
	//*************************************************************************

//	'blob.storage.name' => 'portalvhdsjtds9v20gtr5l',
//	'blob.storage.key'  => 'P/92i4oNutMOawl76/bQGz/bsMwk8K25uCNOmiyuICVhGgqfxY5UjHketyCVtZuDUMBSFtS5Kvcq3CmhCe8/zQ==',
);
