<?php

/*
 * Copyright 2018 University of Toronto Libraries.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Migration for adding the "Top Level Collection" option.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Migration
 */
class IiifApiBridge_Migration_0_0_0_2_Add_Top_Level_Option extends IiifApiBridge_BaseMigration {
    
    /**
     * The applicable version for this migration.
     * @var string
     */
    public static $version = '0.0.0.2';
    
    /**
     * Migrate up.
     */
    public function up() {
        set_option('iiifapibridge_api_top_name', '');
        set_option('iiifapibridge_api_prefix_name', '');
    }
    
}
