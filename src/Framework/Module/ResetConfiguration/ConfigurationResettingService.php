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

namespace OxidEsales\DeveloperTools\Framework\Module\ResetConfiguration;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleConfigurationInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ProjectConfigurationGeneratorInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Path\ModulePathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Webmozart\PathUtil\Path;

class ConfigurationResettingService implements ConfigurationResettingServiceInterface
{
    /** @var ModuleConfigurationInstallerInterface */
    private $moduleConfigurationInstaller;
    /** @var ModulePathResolverInterface */
    private $modulePathResolver;
    /** @var ShopConfigurationDaoInterface */
    private $shopConfigurationDao;
    /** @var ProjectConfigurationGeneratorInterface */
    private $projectConfigurationGenerator;
    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        ModuleConfigurationInstallerInterface $moduleConfigurationInstaller,
        ModulePathResolverInterface $modulePathResolver,
        ShopConfigurationDaoInterface $shopConfigurationDao,
        ProjectConfigurationGeneratorInterface $projectConfigurationGenerator,
        ContextInterface $context
    ) {
        $this->moduleConfigurationInstaller = $moduleConfigurationInstaller;
        $this->modulePathResolver = $modulePathResolver;
        $this->shopConfigurationDao = $shopConfigurationDao;
        $this->projectConfigurationGenerator = $projectConfigurationGenerator;
        $this->context = $context;
    }

    public function reset(): void
    {
        $shopId = $this->getAnyShopIdFromConfiguration();
        $moduleConfigurations = $this->getModuleConfigurationsPrototype($shopId);
        $fullPaths =$this->getAllModulesFullPathFromConfiguration($moduleConfigurations, $shopId);

        $this->resetConfigurationStorage();
        foreach ($moduleConfigurations as $moduleConfiguration) {
            $this->moduleConfigurationInstaller->install(
                $fullPaths[$moduleConfiguration->getId()],
                Path::join($this->context->getModulesPath(), $moduleConfiguration->getPath())
            );
        }
    }

    private function getAllModulesFullPathFromConfiguration(array $moduleConfigurations, int $shopId): array
    {
        $fullPaths = [];
        foreach ($moduleConfigurations as $moduleConfiguration) {
            $fullPaths[$moduleConfiguration->getId()] =
                $this->modulePathResolver->getFullModulePathFromConfiguration
                (
                    $moduleConfiguration->getId(),
                    $shopId
                );
        }

        return $fullPaths;
    }

    private function getAnyShopIdFromConfiguration(): int
    {
        $shopIds = array_keys($this->shopConfigurationDao->getAll());

        return $this->getFirstShopId($shopIds);
    }

    private function getFirstShopId(array $ids): int
    {
        return reset($ids);
    }

    private function getModuleConfigurationsPrototype(int $shopId): array
    {
        return $this->shopConfigurationDao->get($shopId)
            ->getModuleConfigurations();
    }

    private function resetConfigurationStorage(): void
    {
        $this->shopConfigurationDao->deleteAll();
        $this->projectConfigurationGenerator->generate();
    }
}