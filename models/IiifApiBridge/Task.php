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
 * A single update task request record for the daemon.
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/models
 */
class IiifApiBridge_Task extends Omeka_Record_AbstractRecord {
    
    /**
     * Status code for when the task is waiting to be run.
     */
    const STATUS_QUEUED = 'queued';
    
    /**
     * Status code for when the task is running.
     */
    const STATUS_RUNNING = 'running';
    
    /**
     * Status code for when the task is done.
     */
    const STATUS_DONE = 'done';
    
    /**
     * Status code for when the task is done but has failed.
     */
    const STATUS_FAILED = 'failed';
    
    /**
     * The database ID for this task.
     * @var int
     */
    public $id;
    
    /**
     * The database ID for the associated record.
     * @var int
     */
    public $record_id;
    
    /**
     * The class name for the associated record.
     * @var string
     */
    public $record_type;
    
    /**
     * Status of the task. Can be "queued", "running", "done" or "failed".
     * @var string
     */
    public $status;
    
    /**
     * The URL to send the request to.
     * @var string
     */
    public $url;
    
    /**
     * The verb to use for the request.
     * @var string
     */
    public $verb;
    
    /**
     * Body data for the task.
     * @var string
     */
    public $data;
    
    /**
     * Datetime on which the task was first created.
     * @var timestamp
     */
    public $added;
    
    /**
     * Datetime on which the task was last updated.
     * @var timestamp
     */
    public $modified;
    
    /**
     * Initialize mixins.
     * Add the timestamp mixin.
     */
    protected function _initialize_Mixins() {
        $this->_mixins[] = new Mixin_Timestamp($this);
    }
    
    /**
     * Return the associated record for this task.
     * @return Record
     */
    public function getRecord() {
        return get_record_by_id($this->record_type, $this->record_id);
    }
    
    /**
     * Set the associated record for this task.
     * @param Record $record
     */
    public function setRecord($record) {
        $this->record_type = get_class($record);
        $this->record_id = $record->id;
    }
    
    /**
     * Set the status of this task to running.
     * @param boolean $doSave Whether to perform a save after setting the status.
     */
    public function start($doSave=true) {
        $this->status = self::STATUS_RUNNING;
        if ($doSave) {
            $this->save();
        }
    }
    
    /**
     * Set the status of this task to done.
     * @param boolean $doSave Whether to perform a save after setting the status.
     */
    public function finish($doSave=true) {
        $this->status = self::STATUS_DONE;
        if ($doSave) {
            $this->save();
        }
    }
    
    /**
     * Set the status of this task to failed.
     * @param boolean $doSave Whether to perform a save after setting the status.
     */
    public function fail($doSave=true) {
        $this->status = self::STATUS_FAILED;
        if ($doSave) {
            $this->save();
        }
    }
    
    /**
     * Return the decoded JSON data embodied in this task.
     * @param boolean $asArray
     * @return array|object|null Decoded array by default, object if $asArray is false, null on error
     */
    public function getJsonData($asArray=true) {
        return json_decode($this->data, !$asArray);
    }
    
    /**
     * Set the encoded JSON data embodied in this task.
     * @param array|object $jsonData
     */
    public function setJsonData($jsonData) {
        $this->data = json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
