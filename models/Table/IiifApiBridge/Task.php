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
 * Table class for a single update task
 *
 * @author University of Toronto Libraries
 * @package IiifApiBridge/Table
 */
class Table_IiifApiBridge_Task extends Omeka_Db_Table {
    
    /**
     * Return the latest task for the given record.
     * @param Record $record
     * @return IiifApiBridge_Task
     */
    public function getLatestTaskFor($record) {
        $select = $this->getSelect();
        $select->order('modified DESC');
        $select->where('record_id = ?', $record->id);
        $select->where('record_type = ?', get_class($record));
        return $this->fetchObject($select);
    }
    
    /**
     * Return the next queued task to execute.
     * @return IiifApiBridge_Task
     */
    public function getNextAvailableTask() {
        $select = $this->getSelect();
        $select->order('added ASC');
        $select->where('status = ?', array(IiifApiBridge_Task::STATUS_QUEUED));
        return $this->fetchOne($select);
    }
    
    /**
     * Insert a daemon update task.
     * @param Record $record A Collection or Item
     * @param string $url URL to request
     * @param string $verb The verb of the request
     * @param array $body The JSON body of the request (POST and PUT only)
     * @return IiifApiBridge_Task The inserted task.
     */
    public function insertTaskFor($record, $url, $verb, $body=array()) {
        // Insert the task
        $newTask = new IiifApiBridge_Task();
        $newTask->record_type = get_class($record);
        $newTask->record_id = $record->id;
        $newTask->status = IiifApiBridge_Task::STATUS_QUEUED;
        $newTask->url = $url;
        $newTask->verb = strtoupper($verb);
        $newTask->data = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $newTask->save();
        
        // Return the inserted task
        return $newTask;
    }
}
