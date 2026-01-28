<?php
defined('BASEPATH') OR exit('No direct script access allowed');


function is_within_advance_date_window($application_date, $from = 15, $to = 22, $override = false)
    {
        if ($override) return true;

        $day = (int) date('j', strtotime($application_date));
        return ($day >= $from && $day <= $to);
    }
/**
 * Days elapsed till application date
 */
function get_days_elapsed($application_date)
{
    return (int) date('j', strtotime($application_date));
}

/**
 * Per-day salary calculation
 */
function calculate_per_day_salary($monthly_salary, $days_in_month)
{
    return round($monthly_salary / $days_in_month, 2);
}

/**
 * HARD RULE: Late marks eligibility
 * More than 3 late marks → NOT eligible
 */
function is_late_mark_eligible($late_marks, $max_allowed = 3)
{
    return $late_marks <= $max_allowed;
}

/**
 * Convert late marks to penalty days (only if eligible)
 * 3 late marks = 0.5 day
 */
function late_marks_to_penalty_days($late_marks)
{
    return floor($late_marks / 3) * 0.5;
}

/**
 * Earned salary till date
 */
function calculate_earned_till_date($per_day_salary, $days_elapsed)
{
    return round($per_day_salary * $days_elapsed, 2);
}

/**
 * Total deductions (unpaid leave + late penalties)
 */
function calculate_total_deductions($per_day_salary, $unpaid_leave, $late_marks)
{
    $unpaid_deduction = $unpaid_leave * $per_day_salary;
    $late_penalty_days = late_marks_to_penalty_days($late_marks);
    $late_deduction = $late_penalty_days * $per_day_salary;

    return round($unpaid_deduction + $late_deduction, 2);
}

/**
 * Eligible earned amount
 */
function calculate_eligible_earned($earned_salary, $deductions)
{
    $eligible = $earned_salary - $deductions;
    return $eligible > 0 ? round($eligible, 2) : 0;
}

/**
 * Maximum advance allowed
 * 80% of eligible earned, rounded down to nearest 100
 */
function calculate_max_advance($eligible_earned, $percentage = 80)
{
    return floor((($eligible_earned * $percentage) / 100) / 100) * 100;
}
