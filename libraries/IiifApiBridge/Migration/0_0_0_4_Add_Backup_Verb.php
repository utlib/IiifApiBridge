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
 * Migration for adding tasks.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Migration
 */
class IiifApiBridge_Migration_0_0_0_4_Add_Backup_Verb extends IiifApiBridge_BaseMigration {
    
    /**
     *
     * @var type 
     */
    public static $version = '0.0.0.4';
    
    /**
     * Migrate up.
     */
    public function up() {
        $this->_db->query("ALTER TABLE {$this->_db->prefix}iiif_api_bridge_tasks " .
<<<SQL
ADD `backup_url` VARCHAR(255) NULL AFTER `verb`,
ADD `backup_verb` VARCHAR(8) NULL AFTER `backup_url`;
SQL
        );
    }
    
}
