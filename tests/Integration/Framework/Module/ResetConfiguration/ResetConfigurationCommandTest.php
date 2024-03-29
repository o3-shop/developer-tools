<?php

/**
 * This file is part of O3-Shop.
 *
 * O3-Shop is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU General Public License as published by  
 * the Free Software Foundation, version 3.
 *
 * O3-Shop is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with O3-Shop.  If not, see <http://www.gnu.org/licenses/>
 *
 * @copyright  Copyright (c) 2022 OXID eSales AG (https://www.oxid-esales.com)
 * @copyright  Copyright (c) 2022 O3-Shop (https://www.o3-shop.com)
 * @license    https://www.gnu.org/licenses/gpl-3.0  GNU General Public License 3 (GPLv3)
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Tests\Integration\Framework\Module\ResetConfiguration;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopSettingType;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleInstallerInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\Framework\Console\ConsoleTrait;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

final class ResetConfigurationCommandTest extends TestCase
{
    use ContainerTrait;
    use ConsoleTrait;

    /** @var DatabaseRestorer */
    private $databaseRestorer;
    /** @var string  */
    private $moduleId = 'some-module';
    /** @var int  */
    private $shopId = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseRestorer = new DatabaseRestorer();
        $this->databaseRestorer->dumpDB(__CLASS__);
    }

    protected function tearDown(): void
    {
        $this->databaseRestorer->restoreDB(__CLASS__);
        $this->cleanupTestData();
        parent::tearDown();
    }

    public function testResetWithConfigModificationWillReturnInitialValue(): void
    {
        $settingName = 'some-setting';
        $defaultValueFromMetadata = 'some-default-value';

        $this->installTestModule();
        $configurationDao = $this->get(ModuleConfigurationDaoInterface::class);
        $configuration = $configurationDao->get($this->moduleId, $this->shopId);
        $setting = $configuration->getModuleSetting($settingName);
        $setting->setValue('new-value');
        $configurationDao->save($configuration, $this->shopId);

        $this->execute(
            $this->getApplication(),
            $this->get('oxid_esales.console.commands_provider.services_commands_provider'),
            new ArrayInput(['command' => 'oe:module:reset-configurations'])
        );
        $configuration = $configurationDao->get($this->moduleId, $this->shopId);
        $setting = $configuration->getModuleSetting($settingName);
        $value = $setting->getValue();

        $this->assertSame($defaultValueFromMetadata, $value);
    }

    private function installTestModule(): void
    {
        $this->get(ModuleInstallerInterface::class)
            ->install(
                new OxidEshopPackage($this->moduleId, Path::join(__DIR__ . '/Fixtures', 'TestModule'))
            );
    }

    private function cleanupTestData(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove(Path::join(Registry::getConfig()->getModulesDir(), $this->moduleId));
        $activeModules = new ShopConfigurationSetting();
        $activeModules
            ->setName(ShopConfigurationSetting::ACTIVE_MODULES)
            ->setValue([])
            ->setShopId(1)
            ->setType(ShopSettingType::ASSOCIATIVE_ARRAY);
        $this->get(ShopConfigurationSettingDaoInterface::class)->save($activeModules);
    }

    protected function getApplication(): Application
    {
        $application = $this->get('oxid_esales.console.symfony.component.console.application');
        $application->setAutoExit(false);
        return $application;
    }
}
