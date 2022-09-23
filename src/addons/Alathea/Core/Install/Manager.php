<?php

namespace Alathea\Core\Install;

use Alathea\Core\Install\Upgrade\AbstractUpgrade;
use Exception;
use InvalidArgumentException;
use XF;
use XF\AddOn\AddOn;
use XF\App;
use XF\Db\AbstractAdapter;

class Manager
{
	protected AddOn $addOn;
	protected App $app;
	protected AbstractAdapter $db;

	protected array $mySQLData = [];

    protected int $currentVersion = 0;

    /**
     * @throws Exception
     */
    public function __construct(AddOn $addOn, App $app)
	{
		$this->addOn = $addOn;
		$this->app = $app;
		$this->db = $app->db();
	}

    /**
     * @throws Exception
     */
    public function getMySQLData(): array
	{
		if (empty($this->mySQLData))
		{
			$class = '/' . $this->addOn->addon_id . '/Install/Data/MySQL';
			$class = str_replace('/', '\\', $class);
			$class = XF::extendClass($class);
			$class = new $class;
			$class->getData($this->mySQLData);
		}

		return $this->mySQLData;
	}

    /**
     * @throws XF\Db\Exception
     */
    public function importTable($tableId, $tableName = null, $query = true): void
    {
		if (!isset($this->mySQLData[$tableId]))
		{
			return;
		}
		
		$data = $this->mySQLData[$tableId];

		if ($tableName === null)
		{
			$tableName = $tableId;
		}

		if (!isset($data['create']))
		{
			return;
		}

		$sm = $this->db->getSchemaManager();
		$sm->createTable($tableName, $data['create']);

		if ($query && isset($data['query']))
		{
			$query = str_ireplace('[table]', $tableName, $data['query']);
			$this->db->query($query);
		}
	}

	public function alterTable($tableId): void
    {
		if (!isset($this->mySQLData[$tableId]))
		{
			return;
		}
		
		$data = $this->mySQLData[$tableId];

		if (isset($data['alter']))
		{
			$sm = $this->db->getSchemaManager();
			$sm->alterTable($tableId, $data['alter']);
		}
	}

	public function getPossibleUpgradeFileNames(): array
    {
		$searchDir = XF::getAddOnDirectory() . '/' . $this->addOn->addon_id . '/Install/Upgrade';

		$upgrades = [];
		foreach (glob($searchDir . '/*.php') AS $file)
		{
			$file = basename($file);

			$versionId = intval($file);
			if (!$versionId)
			{
				continue;
			}

			$upgrades[$versionId] = $searchDir . '/' . $file;
		}

		ksort($upgrades, SORT_NUMERIC);

		return $upgrades;
	}

	public function getRemainingUpgradeVersionIds($lastCompletedVersion): array
    {
		$upgrades = $this->getPossibleUpgradeFileNames();
		$offset = 0;

		foreach ($upgrades AS $upgrade => $file)
		{
			if ($upgrade > $lastCompletedVersion)
			{
				return array_slice($upgrades, $offset, null, true);
			}

			$offset++;
		}

		return [];
	}

	public function getNextUpgradeVersionId($lastCompletedVersion): int|string|null
    {
		$upgrades = $this->getRemainingUpgradeVersionIds($lastCompletedVersion);
		reset($upgrades);
		return key($upgrades);
	}

	public function getNewestUpgradeVersionId(): int|string|null
    {
		$upgrades = $this->getRemainingUpgradeVersionIds(0);
		end($upgrades);
		return key($upgrades);
	}

    /**
     * @param integer $versionId
     * @return AbstractUpgrade
     *
     */
	public function getUpgrade(int $versionId): AbstractUpgrade
    {
		if (!$versionId)
		{
			throw new InvalidArgumentException('No upgrade version ID specified.');
		}

		$upgrades = $this->getPossibleUpgradeFileNames();
		if (isset($upgrades[$versionId]))
		{
			require_once($upgrades[$versionId]);
			$class = '\\' . $this->addOn->addon_id . '\\Install\\Upgrade\\Upgrade' . $versionId;
            $class = str_ireplace('/', '\\', $class);
			return new $class($this, $this->app);
		}

		throw new InvalidArgumentException('Could not find the specified upgrade.');
	}

	public function getCurrentVersion()
	{
		if ($this->currentVersion === 0)
		{
			$existingVersion = $this->db->fetchOne("
				SELECT version_id
				FROM xf_addon
				WHERE addon_id = '{$this->addOn->addon_id}'
			");

			$this->currentVersion = $existingVersion ?: 0;
		}

		return $this->currentVersion;
	}
}