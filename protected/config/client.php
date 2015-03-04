<?php
/**
 *
 * Copyright 2015 Sky Wickenden
 * 
 * This file is part of StreamBed.
 * An implementation of the Babbling Brook Protocol.
 * 
 * StreamBed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * at your option any later version.
 * 
 * StreamBed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with StreamBed.  If not, see <http://www.gnu.org/licenses/>
 */

/**
 * config/client.php
 *
 * This is the config file for the client domain. (often the root domain or www)
 * It includes configuration options that are specific to the client domain.
 */

return
    array(
        // application components
        "components" => array(
            // clientdomain/feature/domain/user/name/version_major/version_minor/version_patch/action/data
            "urlManager" => array(
                "urlFormat" => "path",
                "rules" => array(
                    "<user>/settings/" => "UserConfig/index",                           // All config requersts.
                    "<user>/settings/<action>" => "UserConfig/<action>",                // All config requersts.
                ),
                "showScriptName" => false,
            ),
        ),
        'params' => array(
            // How many seconds should elapse before cached public stream and post pages be regenerated.
            'public_post_cache_time' => 600,    // ten minutes.
        ),
    );