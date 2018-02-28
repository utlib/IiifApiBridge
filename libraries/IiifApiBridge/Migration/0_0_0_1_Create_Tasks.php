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
class IiifApiBridge_Migration_0_0_0_1_Create_Tasks extends IiifApiBridge_BaseMigration {

    /**
     *
     * @var type
     */
    public static $version = '0.0.0.1';

    /**
     * Migrate up.
     */
    public function up() {
        $this->_createTable('iiif_api_bridge_tasks',
<<<SQL
`id` INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
`record_id` INT(10) NOT NULL,
`record_type` VARCHAR(50) NOT NULL,
`status` VARCHAR(8) NOT NULL DEFAULT 'queued',
`url` VARCHAR(255) NOT NULL,
`verb` VARCHAR(8) NOT NULL,
`data` MEDIUMTEXT,
`added` TIMESTAMP DEFAULT '2018-02-01 00:00:00',
`modified` TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
SQL
        );
    }

}
