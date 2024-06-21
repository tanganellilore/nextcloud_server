<?php

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OC\Files\Mount;

use OCP\Files\Config\IMountProvider;
use OCP\Files\Storage\IStorageFactory;
use OCP\IConfig;
use OCP\IUser;

/**
 * Mount provider for custom cache storages
 */
class CacheMountProvider implements IMountProvider {
	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * ObjectStoreHomeMountProvider constructor.
	 *
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * Get the cache mount for a user
	 *
	 * @param IUser $user
	 * @param IStorageFactory $loader
	 * @return \OCP\Files\Mount\IMountPoint[]
	 */
	public function getMountsForUser(IUser $user, IStorageFactory $loader) {
		$cacheBaseDir = $this->config->getSystemValueString('cache_path', '');
		$mounts = [];
		if ($cacheBaseDir !== '') {
			$cacheDir = rtrim($cacheBaseDir, '/') . '/' . $user->getUID();
			if (!file_exists($cacheDir)) {
				mkdir($cacheDir, 0770, true);
			}
			$mounts[] = new MountPoint('\OC\Files\Storage\Local', '/' . $user->getUID() . '/cache', ['datadir' => $cacheDir], $loader, null, null, self::class);
		}

		$uploadsPath = $this->config->getSystemValueString('uploads_path', $this->config->getSystemValueString('cache_path', ''));
		if ($uploadsPath !== '') {
			$uploadsDir = rtrim($uploadsPath, '/') . '/' . $user->getUID() . '/uploads';
			if (!file_exists($uploadsDir)) {
				mkdir($uploadsDir, 0770, true);
			}
			$mounts[] = new MountPoint('\OC\Files\Storage\Local', '/' . $user->getUID() . '/uploads', ['datadir' => $uploadsDir], $loader, null, null, self::class);
		}
		
		return $mounts;
	}
}
