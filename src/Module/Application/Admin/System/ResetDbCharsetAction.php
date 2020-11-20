<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Application\Admin\System;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Database\DatabaseCharsetUpdaterInterface;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ResetDbCharsetAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'reset_db_charset';

    private ConfigContainerInterface $configContainer;

    private UiInterface $ui;

    private DatabaseCharsetUpdaterInterface $databaseCharsetUpdater;

    public function __construct(
        ConfigContainerInterface $configContainer,
        UiInterface $ui,
        DatabaseCharsetUpdaterInterface $databaseCharsetUpdater
    ) {
        $this->configContainer        = $configContainer;
        $this->ui                     = $ui;
        $this->databaseCharsetUpdater = $databaseCharsetUpdater;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if (
            !Access::check('interface', 100) ||
            $this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE) === true
        ) {
            Ui::access_denied();

            return null;
        }

        $this->databaseCharsetUpdater->update();

        $this->ui->showHeader();

        show_confirmation(
            T_('No Problem'),
            T_('Your database and associated tables have been updated to match your currently configured charset'),
            sprintf('%s/admin/system.php?action=show_debug', $this->configContainer->getWebPath())
        );

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}