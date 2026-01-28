<?php

class Salary_advance_model extends CI_Model
{
    public function fetch_all_advance_salary_employee_list()
    {
        $this->db->select("
            sa.id,
            e.employee_code,
            CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
            sa.advance_amount,
            sa.request_date,
            sa.required_by,
            sa.repayment_months,
            sa.monthly_deduction,
            sa.status
        ");
        $this->db->from('tbl_salary_advances sa');
        $this->db->join('tbl_employees e', 'e.id = sa.fk_employee_id');
        $this->db->where('e.is_delete', '1');
        $this->db->order_by('sa.id', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_advance_salary_details_on_id($id = "")
    {
        if (empty($id)) {
            return [];
        }

        $this->db->select("
            sa.id,
            sa.advance_amount,
            sa.request_date,
            sa.required_by,
            sa.repayment_months,
            sa.monthly_deduction,
            sa.status,

            e.employee_code,
            CONCAT(e.first_name, ' ', e.last_name) AS employee_name,

            hr.action AS hr_action,
            hr.remarks AS hr_remarks,
            hr.action_date AS hr_action_date,
            hr.approved_by AS hr_approved_by,

            mgmt.action AS management_action,
            mgmt.remarks AS management_remarks,
            mgmt.action_date AS management_action_date,
            mgmt.approved_by AS management_approved_by
        ");

        $this->db->from('tbl_salary_advances sa');
        $this->db->join('tbl_employees e', 'e.id = sa.fk_employee_id', 'left');

        // HR approval join
        $this->db->join(
            'tbl_advance_approvals hr',
            "hr.advance_id = sa.id AND hr.role = 'HR'",
            'left'
        );

        // Management approval join
        $this->db->join(
            'tbl_advance_approvals mgmt',
            "mgmt.advance_id = sa.id AND mgmt.role = 'Management'",
            'left'
        );

        $this->db->where('e.is_delete', '1');
        $this->db->where('sa.id', $id);

        return $this->db->get()->row_array();
    }

    /**
     * Full advance salary eligibility + calculation engine
     */
    public function calculate_advance_salary($employee_id, $application_date)
    {
        $this->load->helper('advance_salary');

        // Employee fetch
        $employee = $this->db
            ->where('id', $employee_id)
            ->where('is_delete', '1')
            ->get('tbl_employees')
            ->row_array();

        if (!$employee) {
            return ['eligible' => false, 'reason' => 'Employee not found'];
        }

        // Attendance (replace with HRMS integration)
        $attendance = $this->get_attendance_summary($employee_id);

        //  HARD STOP: Late marks rule
        if (!is_late_mark_eligible($attendance['late_marks'])) {
            return [
                'eligible' => false,
                'reason' => 'Advance not allowed due to more than 3 late marks'
            ];
        }

        //  Date window rule (till 22nd)
        if (date('j', strtotime($application_date)) > 22) {
            return ['eligible' => false, 'reason' => 'Advance allowed only till 22nd'];
        }

        //  Salary cap rule
        // if ($employee['monthly_salary'] > 40000) {
        //     return ['eligible' => false, 'reason' => 'Salary exceeds eligibility limit'];
        // }

        // Calculations
        $days_in_month = date('t', strtotime($application_date));
        $days_elapsed = get_days_elapsed($application_date);
        $per_day_salary = calculate_per_day_salary(
            $employee['monthly_salary'],
            $days_in_month
        );

        $earned_salary = calculate_earned_till_date($per_day_salary, $days_elapsed);

        $deductions = calculate_total_deductions(
            $per_day_salary,
            $attendance['unpaid_leave'],
            $attendance['late_marks']
        );

        $eligible_earned = calculate_eligible_earned($earned_salary, $deductions);
        $max_advance = calculate_max_advance($eligible_earned);

        if ($max_advance < 3000) {
            return ['eligible' => false, 'reason' => 'Advance amount below minimum limit'];
        }

        return [
            'eligible' => true,
            'employee_id' => $employee_id,
            'monthly_salary' => $employee['monthly_salary'],
            'days_elapsed' => $days_elapsed,
            'earned_salary' => $earned_salary,
            'deductions' => $deductions,
            'eligible_earned' => $eligible_earned,
            'max_advance' => $max_advance
        ];
    }

    /**
     * Attendance summary (mock)
     */
    private function get_attendance_summary($employee_id)
    {
        return [
            'unpaid_leave' => 1,
            'late_marks' => 2
        ];
    }

    /**
     * Apply advance salary
     */
    public function apply_advance($data)
    {
        $data['status'] = 'Pending';
        $this->db->insert('tbl_salary_advances', $data);
        return $this->db->insert_id();
    }

    /**
     * Approve / Reject advance salary
     */
    public function approve_or_reject($advance_id, $approved_by, $role, $action, $remarks
        ) 
    {
        $this->db->trans_start();

        // Insert approval log
        $this->db->insert('tbl_advance_approvals', [
            'advance_id'  => $advance_id,
            'approved_by' => $approved_by,
            'role'        => $role,          // HR or Management
            'action'      => $action,        // Approved or Rejected
            'remarks'     => $remarks
        ]);

        // ---- STATUS DECISION LOGIC ----
        if ($action === 'Rejected') {
            // HR or Management rejection = Final rejection
            $status = 'Rejected';
        } elseif ($action === 'Approved' && $role === 'HR') {
            $status = 'HR Approved';
        } elseif ($action === 'Approved' && $role === 'Management') {
            $status = 'Management Approved';
        } else {
            // Safety fallback
            $status = 'Pending';
        }

        // Update main table
        $this->db->where('id', $advance_id)
                 ->update('tbl_salary_advances', ['status' => $status]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }


}
