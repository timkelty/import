<?php
namespace Craft;

class Import_HistoryService extends BaseApplicationComponent {
    
    public function show() {
    
        // Set criteria
        $criteria = new \CDbCriteria;
        $criteria->order = 'id desc';
    
        return Import_HistoryRecord::model()->findAll($criteria);
    
    }
    
    public function showLog($history) {
    
        // Set criteria
        $criteria = new \CDbCriteria;
        $criteria->condition = 'historyId = :history_id';
        $criteria->params = array(
            ':history_id' => $history,
        );
    
        // Get errors
        $errors = array();
        $logs = Import_LogRecord::model()->findAll($criteria);
        foreach($logs as $log) {
            $errors[$log['line']] = $log['errors'];
        }
        
        // Make "total" list
        $total = array();
        for($i = 1; $i <= 5; $i++) {
            $total[$i] = isset($errors[$i]) ? $errors[$i] : array(Craft::t('None'));
        }
        
        return $total;
    
    }
    
    public function start($settings) {
    
        $history = new Import_HistoryRecord();
        $history->userId = craft()->userSession->getUser()->id;
        $history->sectionId = $settings->section;
        $history->entrytypeId = $settings->entrytype;
        $history->file = basename($settings->file);
        $history->rows = $settings->rows;
        $history->behavior = $settings->behavior;
        $history->status = 'started';
        
        $history->save(false);
                
        return $history->id;
    
    }

    public function log($history, $line, $errors) {
    
        $log = new Import_LogRecord();
        $log->historyId = $history;
        $log->line = $line + 1;
        $log->errors = $errors;
        
        $log->save(false);
    
        return $errors;
    
    }
    
    public function end($history) {
    
        $history = Import_HistoryRecord::model()->findById($history);
        $history->status = 'finished';
        
        $history->save(false);
    
    }

}