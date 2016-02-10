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
    const GET_STAMPS_EMPLOYEE_RANGE = 'SELECT stamp_id,tsl_stamp,stamp_special,stamp_department,stamp_partner,
                                              timestamp_comments.tsc_text AS tsl_comment
                                       FROM timestamp_list
                                       LEFT JOIN timestamp_comments ON stamp_id = timestamp_comments.tsc_stamp_id
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
    /**
     * @param int :stampid
     * @param string :tscomment
     */
    const INSERT_STAMP_COMMENT = 'INSERT INTO timestamp_comments (tsc_stamp_id, tsc_text)
                                         VALUES (:stampid, :tscomment)';
    /**
     * @param int :stampid
     */
    const GET_STAMP_COMMENT = 'SELECT tsc_text FROM timestamp_comments WHERE tsc_stamp_id = :stampid';
    /**
     * @param int :stampid
     * @param string :tscomment
     */
    const MODIFY_COMMENT = 'UPDATE timestamp_comments SET tsc_text = :tscomment WHERE tsc_stamp_id = :stampid';
    /**
     * @param int :stampid
     */
    const DELETE_COMMENT = 'DELETE FROM timestamp_comments WHERE tsc_stamp_id = :stampid';
    /**
     * @param int :userid
     * @param int :syear
     * @param int :sweek
     */
    const GET_SCHEDULE = 'SELECT schedule_id, schedule_day, schedule_in, schedule_out, schedule_department
                          FROM employee_schedule
                          WHERE employee_id = :userid
                                AND ((schedule_week = :sweek AND schedule_day != 7)
                                OR (schedule_week = :sweek - 1 AND schedule_day = 7))
                                AND schedule_year = :syear
                          ORDER BY schedule_day';
    // todo: OR it's schedule_pair fulfills those requirements
    const GET_SCHEDULE_2 = 'SELECT schedule_id, schedule_unix, schedule_pair FROM employee_schedule
                            WHERE employee_id = :userid
                            AND (schedule_unix >= :ustart AND schedule_unix <= :uends)';
    /**
     * @param int :userid
     * @param int :syear
     * @param int :sweek
     * @param int :sday
     */
    const INSERT_SCHEDULE = 'INSERT INTO employee_schedule (employee_id, schedule_unix) VALUES (:userid, :sched);
                             INSERT INTO employee_schedule (employee_id, schedule_unix, schedule_pair)
                                         VALUES (:userid, :sched, LAST_INSERT_ID())';
    /**
     * @param int :userid
     */
    const GET_PERMISSIONS = 'SELECT company_code, department_id FROM employee_supervisors WHERE user_id = :userid';
    /**
     * @param int :userid
     */
    const GET_LAST_EXCEPTION_GENERATION_BY_USER = 'SELECT UNIX_TIMESTAMP(exh_time), exh_department, exh_property
                                                   FROM exception_history
                                                     INNER JOIN employee_supervisors
                                                     ON employee_supervisors.user_id = :userid
                                                     WHERE exh_property = employee_supervisors.company_code
                                                     AND exh_department = employee_supervisors.department_id';
    /**
     * @param int :questionNumber
     * @param int :userID
     */
    const GET_USER_SECURITY_ANSWER = 'SELECT eque_answer FROM employee_questions
                                         WHERE eque_number = :questionNumber AND eque_user = :userID';
    /**
     * @param int :userID
     * @param string :hashWord
     * @param string :salt
     * @param string :resetString
     */
    const INSERT_NEW_USER_PASSWORD = 'INSERT INTO user_hashes (uhsh_user, uhsh_hash, uhsh_created)
                                      VALUES (:userID, :hashWord, UNIX_TIMESTAMP());
                                      INSERT INTO user_salts (uslt_user, uslt_salt) VALUES (:userID, :salt);
                                      DELETE FROM reset_list WHERE reset_string = :resetString';
    /**
     * @param string :userName
     */
    const GET_USER_EMAIL = 'SELECT user_id, user_emails.ueml_email AS user_email FROM employee_list
                            LEFT JOIN user_emails ON user_email_primary = user_emails.ueml_id
                            WHERE user_name = :userName';
    /**
     * @param int :userID
     * @param string :resetString
     */
    const INSERT_NEW_RESET_STRING = 'DELETE FROM reset_list WHERE reset_uid = :userID;
                                     INSERT INTO reset_list (reset_uid, reset_string) VALUES (:userID, :resetString);';
    /**
     * @param string :resetString
     */
    const DELETE_RESET_BY_STRING = 'DELETE FROM reset_list WHERE reset_string = :resetString;';
    /**
     * @param int :userID
     */
    const SELECT_RANDOM_SECURITY_QUESTION = 'SELECT sque_id, sque_question FROM security_questions WHERE sque_id IN
                                             (SELECT eque_number FROM employee_questions WHERE eque_user = :userID)
                                             ORDER BY RAND() LIMIT 1';
    /**
     * @param string :email
     * @param string :username
     */
    const GET_USER_ID_FROM_USERNAME_EMAIL = 'SELECT user_id FROM employee_list
                                             LEFT JOIN user_emails ON user_email_primary = user_emails.ueml_id
                                             WHERE user_name = :username AND user_emails.ueml_email = :email';
    /**
     * @param string :resetString
     */
    const GET_RESET_INFORMATION = 'SELECT reset_uid,reset_string,reset_date FROM reset_list
                                   WHERE reset_string = :resetString';
    /**
     * Just get all the security questions.
     */
    const GET_ALL_QUESTIONS = 'SELECT sque_question FROM security_questions';
    /**
     * @param int :userID
     * @param int :questionOne
     * @param int :questionTwo
     * @param int :questionThree
     * @param string :answerOne
     * @param string :answerTwo
     * @param string : answerThree
     */
    const INSERT_SECURITY_ANSWERS = 'INSERT INTO employee_questions (eque_number, eque_answer, eque_user)
                                     VALUES (:questionOne,   :answerOne,   :userID),
                                            (:questionTwo,   :answerTwo,   :userID),
                                            (:questionThree, :answerThree, :userID);';
}
