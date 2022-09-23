<?php

namespace Alathea\Core\Install;

use XF\AddOn\AddOn;
use XF\App;
use XF\Db\Exception;

abstract class AbstractSetup extends \XF\AddOn\AbstractSetup
{
	protected Manager $manager;

    /**
     * @throws \Exception
     */
    public function __construct(AddOn $addOn, App $app)
	{
		parent::__construct($addOn, $app);

		$this->manager = new Manager($addOn, $app);
	}

	protected function _preInstall()
	{
		
	}
	
	protected function _postInstall()
	{
		
	}
	
	protected function _preUpgrade()
	{
		
	}
	
	protected function _postUpgrade()
	{
		
	}
	
	protected function _preUninstall()
	{
		
	}
	
	protected function _postUninstall()
	{
		
	}

    /**
     * @throws Exception
     */
    public function install(array $stepParams = [])
	{
		$this->_preInstall();

		$mySQLData = $this->manager->getMySQLData();

		foreach($mySQLData as $tableId => $data)
		{
			if (!isset($data['import']) || $data['import'])
			{
				if (isset($data['create']))
				{
					$this->manager->importTable($tableId, $tableId);
				}
				else
				{
					$this->manager->alterTable($tableId);
				}
			}
		}

		$this->_postInstall();
	}

	public function upgrade(array $stepParams = [])
	{
		$this->_preUpgrade();

		$currentVersion = $this->manager->getCurrentVersion();
		$nextVersionIds = $this->manager->getRemainingUpgradeVersionIds($currentVersion);

		foreach ($nextVersionIds AS $versionId => $file)
		{
			$upgrade = $this->manager->getUpgrade($versionId);
			
			for ($i = 1; true; $i++)
			{
				$step = 'step' . $i;
				
				if (method_exists($upgrade, $step))
			    {
					$upgrade->$step();
					
					continue;
				}
				
				break;
			}
		}

		$this->_postUpgrade();
	}

	public function uninstall(array $stepParams = [])
	{
		$this->_preUninstall();

		$mySQLData = $this->manager->getMySQLData();
		$sm = $this->db()->getSchemaManager();
		foreach($mySQLData as $tableId => $data)
		{
			if (!isset($data['drop']) || $data['drop'])
			{
				if (isset($data['create']))
				{
					$sm->dropTable($tableId);
				}
				elseif (isset($data['alter_drop']))
				{
					$sm->alterTable($tableId, $data['alter_drop']);
				}
			}
		}

		$this->_postUninstall();
	}
}