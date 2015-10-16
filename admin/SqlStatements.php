<?php

/**
 * Class SqlStatements
 */
class SqlStatements
{
    /**
     * PDO Cursor Helper: Forwards Only
     * Enable after PHP 5.6
     */
    //const FORWARD_CURSOR = [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY];
    /**
     * PDO Cursor Helper: Scroll
     * Enable after PHP 5.6
     */
    //const SCROLL_CURSOR = [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL];
    /**
     * Query to get an employee and their credentials from the database
     * @param string :username
     */
    const GET_USER_CREDENTIALS = 'SELECT employee_list.user_id,
                                         user_hashes.uhsh_hash AS user_hash,
                                         user_hashes.uhsh_created AS user_created,
                                         user_salts.uslt_salt AS user_salt,
                                         user_emails.ueml_email AS user_email
                                  FROM employee_list
                                  LEFT JOIN user_hashes ON employee_list.user_id = user_hashes.uhsh_user
                                  LEFT JOIN user_salts ON employee_list.user_id = user_salts.uslt_user
                                  LEFT JOIN user_emails ON employee_list.user_email_primary = user_emails.ueml_id
                                  WHERE employee_list.user_name = :username';
    /**
     * Query to get set questions from the database for a specific employee
     * @param int :userid
     */
    const GET_SECURITY_QUESTIONS = 'SELECT eque_number
                                    FROM employee_questions
                                    WHERE eque_user = :userid';
    /**
     * Query to insert a stamp into the timestamp_list table
     */
    const SET_INSERT_STAMP = 'INSERT INTO timestamp_list (user_id_stamp, tsl_stamp) VALUES (:userid, :now)';
    /**
     * Query to check if user has administrative permissions
     */
    const GET_CHECK_PERMISSIONS = 'SELECT company_code, department_id
                                   FROM employee_supervisors
                                   WHERE user_id = :userid';
    /**
     * Query to get all stamps from employee from range
     */
    const GET_STAMPS_EMPLOYEE_RANGE = 'SELECT stamp_id,tsl_stamp,stamp_special,stamp_department,stamp_partner
                                       FROM timestamp_list
                                       WHERE user_id_stamp = :userid AND tsl_stamp BETWEEN :date0 AND :date1
                                       ORDER BY tsl_stamp';
    /**
     * Query to get a user's name and start date
     */
    const GET_USER_NAME_DATE = 'SELECT user_first, user_last, user_start
                                FROM employee_list
                                WHERE user_id = :userid';
    /**
     * Query to return if a timecard is locked
     * @param int :userid
     * @param int :pyear
     * @param int :pweek
     */
    const GET_IS_LOCKED = 'SELECT EXISTS (SELECT 1 FROM approved_timecards
                                          WHERE apt_user = :userid AND apt_year = :pyear AND apt_week = :pweek)';
    /**
     * Query to insert a new timestamp
     * @param int :userid
     * @param string :date
     */
    const INSERT_NEW_STAMP = 'INSERT INTO timestamp_list (user_id_stamp,tsl_stamp) VALUES (:userid, :date)';
    /**
     * Query to modify a stamp
     * @param string :stamp
     * @param int :stampid
     */
    const MODIFY_STAMP = 'UPDATE timestamp_list SET tsl_stamp = :stamp WHERE stamp_id = :stampid';
    /**
     * Query to delete a stamp
     * @param int :stampid
     */
    const DELETE_STAMP_BY_ID = 'DELETE FROM timestamp_list WHERE stamp_id = :stampid';
    /**
     * Logs a transaction that happened to the database
     * @param int :adminid
     * @param string :transaction (should be a serialized array)
     */
    const LOG_TRANSACTION = 'INSERT INTO change_list (change_userid,change_from_to) VALUES (:adminid, :trasaction)';
}
