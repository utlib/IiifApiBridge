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
 * Job for running through tasks. Reactivated manually or whenever an update hook fires.
 *
 * @author University of Toronto Libraries
 */
class IiifApiBridge_Job_UpdateDaemon extends Omeka_Job_AbstractJob {
    
    /**
     * The initial delay between queue polls.
     */
    const INITIAL_DELAY = 1;
    
    /**
     * The increment for the delay between queue polls when a queue is undone.
     */
    const DELAY_INCREMENT = 1;
    
    /**
     * The maximum delay between queue polls.
     */
    const MAX_DELAY = 10;
    
    /**
     * The maximum timeout for a queued API task.
     */
    const TIMEOUT = 180;
    
    /**
     * Main runnable method.
     */
    public function perform() {
        try {
            // Verify the authentication, get out if it's bad
            debug('Daemon waking.');
            debug('Authenticating...');
            $authRequest = new IiifApiBridge_Request_Authentication();
            if (!$authRequest->verify(get_option('iiifapibridge_api_key'))) {
                return;
            }
            debug('Login OK.');
            // Loop through all undone queued tasks
            $taskTable = get_db()->getTable('IiifApiBridge_Task');
            while ($task = $taskTable->getNextAvailableTask()) {
                debug('Performing task #' . $task->id . ':');
                if (!$this->tryTask($task) && !empty($task->backup_url)) {
                    $this->retryTask($task);
                }
                debug('Task #' . $task->id . ' completed.');
            }
            debug('Daemon going back to sleep.');
            set_option('iiifapibridge_daemon_id', '');
        } catch (Exception $ex) {
            debug("IIIF API Daemon exception: {$ex->getMessage()}");
        }
    }
    
    /**
     * Perform a single queued task (first try).
     * @param IiifApiBridge_Task $task
     * @return boolean Whether it is successful
     */
    private function tryTask($task) {
        return $this->performTask($task);
    }
    
    /**
     * Perform a single queued task (second try).
     * @param IiifApiBridge_Task $task
     * @return boolean Whether it is successful
     */
    private function retryTask($task) {
        $result = $this->performTask($task, true);
        if (!$result) {
            $task->fail();
        }
        return $result;
    }
    
    /**
     * Perform a single queued task.
     * @param IiifApiBridge_Task $task
     * @param boolean $retry Whether this is the second try
     * @return boolean Whether it is successful
     */
    private function performTask($task, $retry=false) {
        try {
            // Start the task
            $task->start();
            // Make the request
            debug('Making initial request...');
            $request = new IiifApiBridge_Request_Daemon();
            if ($retry) {
                $targetUrl = $task->backup_url;
                $targetVerb = $task->backup_verb;
            } else {
                $targetUrl = $task->url;
                $targetVerb = $task->verb;
            }
            $requestResponse = $request->push($targetUrl, $targetVerb, $task->getJsonData());
            debug('Request response: ' . json_encode($requestResponse));
            if (isset($requestResponse['status']) && strpos($requestResponse['status'], '/queue/')) {
                $queueUrl = $this->transformQueueUrl($requestResponse['status']);
                debug('Request accepted. Queue: ' . $queueUrl);
                // Start checking the queue
                $timeElapsed = 0;
                $resultReceived = false;
                $delay = self::INITIAL_DELAY;
                $queueRequester = new IiifApiBridge_Request_Queue();
                $queueResponseCode = 500;
                // Loop until result is received or on timeout
                do {
                    sleep($delay);
                    $queueResult = $queueRequester->status($queueUrl);
                    if (isset($queueResult['responseCode'])) {
                        $resultReceived = true;
                        $queueResponseCode = (int) $queueResult['responseCode'];
                    } else {
                        // If over timeout, fail
                        if ($timeElapsed >= self::TIMEOUT) {
                            debug('Queue timed out.');
                            return false;
                        }
                        // Otherwise, delay and increase the delay
                        else {
                            debug('Re-check in ' . $delay . 's...');
                            $timeElapsed += $delay;
                            if ($delay < self::MAX_DELAY) {
                                $delay += self::DELAY_INCREMENT;
                            }
                        }
                    }
                } while (!$resultReceived);
                debug('Queue passed after ' . $timeElapsed . 's.');
                // Look for failures
                if (floor($queueResponseCode / 100) != 2) {
                    throw new IiifApiBridge_Exception_FailedJsonRequestException($queueResponseCode, $queueResult);
                }
            }
        }
        // Catch request failures
        catch (IiifApiBridge_Exception_FailedJsonRequestException $ex) {
            debug("IIIF API Daemon - Task #{$task->id} failed with HTTP {$ex->getResponseCode()}: " . json_encode($ex->getResponseBody(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return false;
        }
        // Catch other failures
        catch (Exception $ex) {
            debug("IIIF API Daemon - Task #{$task->id} failed with exception: {$ex->getMessage()}");
            return false;
        }
        // Finish the task
        $task->finish();
        return true;
    }
    
    /**
     * Transform the queue URL back to a relative from the API's root.
     * @param string $queueUrl
     * @return string
     */
    private function transformQueueUrl($queueUrl) {
        $comps = explode('/', $queueUrl);
        return '/' . join('/', array_slice($comps, -2, 2));
    }
    
}
