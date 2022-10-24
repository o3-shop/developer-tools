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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetConfigurationCommand extends Command
{
    private const EXECUTE_SUCCESS_MESSAGE = 'Project configuration was reset successfully';
    private const COMMAND_DESCRIPTION = 'Resets changes in project configuration.';
    private const COMMAND_NAME = 'oe:module:reset-configurations';

    /** @var ConfigurationResettingServiceInterface */
    private $configurationResetter;

    /** @param ConfigurationResettingServiceInterface $configurationRestorer */
    public function __construct(
        ConfigurationResettingServiceInterface $configurationRestorer
    ) {
        parent::__construct();
        $this->configurationResetter = $configurationRestorer;
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configurationResetter->reset();
        $output->writeln(sprintf('<info>%s.</info>', self::EXECUTE_SUCCESS_MESSAGE));
        return 0;
    }
}
