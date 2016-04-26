<?php

namespace xvmvc\model;

class ModelMenu extends Model
{
    public $entries = [];

    /**
     * ModelMenu constructor.
     */
    public function __construct()
    {
        // [<entry_text>,<entry_id>,<entry_href>]
        // menu options per user type (all have About Crona):
        // basic employee: view timecard, view schedule (this is on a special mode)
        // manager: manage timecards, manage schedules
        // administrator: manage timecards, manage schedules, export module (top, use MVC)
        // super admin: all + System Administration
        if ($_SESSION['userMode'] === 0) {
            array_push($this->entries, ['View Timecard', 'view_timecard', '#']);
        } elseif ($_SESSION['userMode'] === 1) {
            // todo: make a distinction between manager and administrator either here or in the login script
        } else {
            error_log('Invalid Session for user: ' . $_SESSION['userId'], 0);
        }
        array_push($this->entries, ['About Crona', 'about-log-button', '#about-log']);
    }
}
