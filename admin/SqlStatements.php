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
     */
    const GET_USER_CREDENTIALS = 'SELECT employee_list.user_id,user_hashes.uhsh_hash
                                  AS user_hash,user_hashes.uhsh_created
                                  AS user_created,user_salts.uslt_salt
                                  AS user_salt,user_emails.ueml_email
                                  AS user_email
                                  FROM employee_list
                                  LEFT JOIN user_hashes ON employee_list.user_id = user_hashes.uhsh_user
                                  LEFT JOIN user_salts ON employee_list.user_id = user_salts.uslt_user
                                  LEFT JOIN user_emails ON employee_list.user_email_primary = user_emails.ueml_id
                                  WHERE employee_list.user_name = :username';
    /**
     * Query to get set questions from the database for a specific employee
     */
    const GET_SECURITY_QUESTIONS = 'SELECT eque_number FROM employee_questions WHERE eque_user = :userid';
    /**
     * Query to insert a stamp into the timestamp_list table
     */
    const SET_INSERT_STAMP = 'INSERT INTO timestamp_list (user_id_stamp, tsl_stamp) VALUES (:userid, :now)';
    /**
     * Query to check if user has administrative permissions
     */
    const GET_CHECK_PERMISSIONS = 'SELECT company_code, department_id FROM employee_supervisors WHERE user_id = :userid';
}
