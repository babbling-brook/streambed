<?php
/**
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

// Used by an admin user to access signupcode options.
$this->menu=array(
    array('label' => 'Create Signup Codes', 'url' => '/site/admin/signupcodes/create'),
);

?>

<h2>Signup Codes</h2>

<div class="content-indent">
    <p>
        If signup codes are turned on in the config then users can only sign up with the site if they have entered a
        signup code.
    </p>
</div>