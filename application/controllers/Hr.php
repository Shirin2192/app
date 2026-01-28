<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hr extends CI_Controller {
	public function __construct()
	{
		parent::__construct();

		 // Prevent browser caching for all admin pages
		 $this->output
		 ->set_header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0")
		 ->set_header("Cache-Control: post-check=0, pre-check=0", false)
		 ->set_header("Pragma: no-cache");
	}
	public function dashboard()
	{
		$this->load->view('hr/dashboard');
	}
	// ************* ADD EMPLOYEE DETAILS********************
	public function add_employee()
	{
	    // ===============================
	    // Validation Rules
	    // ===============================
	    $this->form_validation->set_rules('employee_code', 'Employee Code', 'required|trim');
	    $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
	    $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
	    $this->form_validation->set_rules(
	        'email',
	        'Email',
	        'required|trim|valid_email|is_unique[tbl_users.email]'
	    );
	    $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim|min_length[10]');
	    $this->form_validation->set_rules('department', 'Department', 'required|trim');
	    $this->form_validation->set_rules('designation', 'Designation', 'required|trim');
	    $this->form_validation->set_rules('date_of_joining', 'Date of Joining', 'required');
	    $this->form_validation->set_rules('employment_type', 'Employment Type', 'required');
	    $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
	    $this->form_validation->set_rules('role', 'Role', 'required');

	    if ($this->form_validation->run() == FALSE) {
	        echo json_encode([
	            'status' => 'error',
	            'errors' => $this->form_validation->error_array()
	        ]);
	        return;
	    }

	    // ===============================
	    // Insert into tbl_employees
	    // ===============================
	    $employeeData = [
	        'employee_code'   => $this->input->post('employee_code'),
	        'first_name'      => $this->input->post('first_name'),
	        'last_name'       => $this->input->post('last_name'),
	        'email'           => $this->input->post('email'),
	        'mobile'          => $this->input->post('mobile'),
	        'department'      => $this->input->post('department'),
	        'designation'     => $this->input->post('designation'),
	        'date_of_joining' => $this->input->post('date_of_joining'),
	        'employment_type' => $this->input->post('employment_type'),
	        'basic_salary'    => $this->input->post('basic_salary'),
	        'monthly_salary'  => $this->input->post('monthly_salary'),
	        'bank_name'       => $this->input->post('bank_name'),
	        'bank_account_no' => $this->input->post('bank_account_no'),
	        'ifsc_code'       => $this->input->post('ifsc_code'),
	        'status'          => 'Active'
	    ];

	    $employee_id = $this->model->insertData('tbl_employees', $employeeData);

	    // Employee insert failed
	    if (!$employee_id) {
	        echo json_encode([
	            'status' => 'error',
	            'message' => 'Employee creation failed'
	        ]);
	        return;
	    }

	    // ===============================
	    // Insert into tbl_users
	    // ===============================
	    $userData = [
	        'employee_id' => $employee_id,
	        'role'        => $this->input->post('role'),
	        'email'       => $this->input->post('email'),
	        'password'    => decy_ency('encrypt', $this->input->post('password')),
	        'status'      => 'Active'
	    ];

	    $user_id = $this->model->insertData('tbl_users', $userData);

	    if (!$user_id) {
	        echo json_encode([
	            'status' => 'error',
	            'message' => 'User creation failed'
	        ]);
	        return;
	    }

	    echo json_encode([
	        'status' => 'success',
	        'message' => 'Employee registered successfully!'
	    ]);
	}

	public function fetch_employees_list()
	{
	    $employees = $this->model->selectWhereData('tbl_employees',['is_delete'=>'1'],'*',false,['id', 'DESC']
	    );
	    echo json_encode([
	        'status' => 'success',
	        'data' => $employees
	    ]);
	}

	public function get_employees_detail_on_id()
	{
		$id = $this->input->post('id');
	    $employees = $this->model->selectWhereData('tbl_employees',['id'=>$id],'*',false,['id', 'DESC']
	    );
	    echo json_encode([
	        'status' => 'success',
	        'data' => $employees
	    ]);
	}
	public function update_employee()
	{
	    $employee_id = $this->input->post('employee_id');

	    // ===============================
	    // Employee Fields
	    // ===============================
	    $employeeData = [
	        'employee_code'   => $this->input->post('employee_code'),
	        'first_name'      => $this->input->post('first_name'),
	        'last_name'       => $this->input->post('last_name'),
	        'mobile'          => $this->input->post('mobile'),
	        'department'      => $this->input->post('department'),
	        'designation'     => $this->input->post('designation'),
	        'date_of_joining' => $this->input->post('date_of_joining'),
	        'employment_type' => $this->input->post('employment_type'),
	        'basic_salary'    => $this->input->post('basic_salary'),
	        'monthly_salary'  => $this->input->post('monthly_salary'),
	        'bank_name'       => $this->input->post('bank_name'),
	        'bank_account_no' => $this->input->post('bank_account_no'),
	        'ifsc_code'       => $this->input->post('ifsc_code')
	    ];
	    // ===============================
	    // Validation
	    // ===============================
	    $this->form_validation->set_rules('employee_id', 'Employee ID', 'required');
	    $this->form_validation->set_rules('employee_code', 'Employee Code', 'required');
	    $this->form_validation->set_rules('first_name', 'First Name', 'required');
	    $this->form_validation->set_rules('last_name', 'Last Name', 'required');
	    $this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[10]');
	    $this->form_validation->set_rules('role', 'Role', 'required');

	    if ($this->form_validation->run() == FALSE) {
	        echo json_encode([
	            'status' => 'error',
	            'errors' => $this->form_validation->error_array()
	        ]);
	        return;
	    }

	    // ===============================
	    // Update via Model
	    // ===============================
	    $employeeUpdated = $this->model->updateData(
	        'tbl_employees',
	        $employeeData,
	        ['id' => $employee_id]
	    );

	    if ($employeeUpdated) {
	        echo json_encode([
	            'status' => 'success',
	            'message' => 'Employee details updated successfully!'
	        ]);
	    } else {
	        echo json_encode([
	            'status' => 'error',
	            'message' => 'Update failed. Please try again.'
	        ]);
	    }
	}

	public function delete_employee()
	{
	    $employee_id = $this->input->post('employee_id');

	    if (empty($employee_id)) {
	        echo json_encode([
	            'status'  => 'error',
	            'message' => 'Employee ID is required'
	        ]);
	        return;
	    }

	    // ===============================
	    // Soft delete employee
	    // ===============================
	    $updateData = [
	        'is_delete'     => '0',
	    ];

	    $whereData = [
	        'id' => $employee_id
	    ];

	    $deleteEmployee = $this->model->deleteData(
	        'tbl_employees',
	        $updateData,
	        $whereData
	    );

	    if (!$deleteEmployee) {
	        echo json_encode([
	            'status'  => 'error',
	            'message' => 'Employee delete failed'
	        ]);
	        return;
	    }

	    // ===============================
	    // Soft delete user also
	    // ===============================
	    $this->model->deleteData(
	        'tbl_users',
	        ['status' => 'Inactive'],
	        ['employee_id' => $employee_id]
	    );

	    echo json_encode([
	        'status'  => 'success',
	        'message' => 'Employee deleted successfully'
	    ]);
	}
	// ********************* Employee Advance Salary List ***********************

	public function fetch_all_advance_salary_employee_list(){
		$this->load->model('Salary_advance_model');
		$rows = $this->Salary_advance_model->fetch_all_advance_salary_employee_list();

        $data = [];
        $count = 1;
        foreach ($rows as $row) {
            $data[] = [
                'sr_no'            => $count++,
                'employee_code'    => $row['employee_code'],
                'employee_name'    => $row['employee_name'],
                'advance_amount'   => number_format($row['advance_amount'], 2),
                'request_date'     => $row['request_date'],
                'required_by'      => $row['required_by'],
                'repayment_months'=>  $row['repayment_months'],
                'monthly_deduction'=> number_format($row['monthly_deduction'], 2),
                'status'           => $row['status']
            ];
        }
        echo json_encode(['data' => $data]);
	}
	public function get_advance_salary_details_on_id(){
		$id = $this->input->post('id');
		$data = $this->Salary_advance_model->get_advance_salary_details_on_id($id);
	}

	/**
     * Check advance eligibility & calculation
     * AJAX/API
     */
    public function check_eligibility()
    {
        $employee_id = $this->input->post('employee_id');
        $application_date = date('Y-m-d');

        if (empty($employee_id)) {
            return $this->_json(false, 'Employee ID is required');
        }

        $result = $this->Salary_advance_model
                       ->calculate_advance_salary($employee_id, $application_date);

        if (!$result['eligible']) {
            return $this->_json(false, $result['reason']);
        }

        return $this->_json(true, 'Eligible for advance', $result);
    }

    /**
     * Apply for advance salary
     */
    public function apply()
    {
        $data = [
            'fk_employee_id'   => $this->input->post('employee_id'),
            'advance_amount'   => $this->input->post('advance_amount'),
            'reason'           => $this->input->post('reason'),
            'request_date'     => date('Y-m-d'),
            'required_by'      => $this->input->post('required_by'),
            'repayment_months' => $this->input->post('repayment_months'),
            'monthly_deduction'=> $this->input->post('monthly_deduction')
        ];

        if (empty($data['fk_employee_id']) || empty($data['advance_amount'])) {
            return $this->_json(false, 'Invalid request data');
        }

        $advance_id = $this->Salary_advance_model->apply_advance($data);

        if ($advance_id) {
            return $this->_json(true, 'Advance request submitted', [
                'advance_id' => $advance_id
            ]);
        }

        return $this->_json(false, 'Failed to submit advance request');
    }

    /**
     * HR / Management Approve
     */
    public function approve()
    {
        $advance_id = $this->input->post('advance_id');
        $approved_by = $this->input->post('approved_by'); // user_id
        $role = $this->input->post('role'); // HR / Management
        $remarks = $this->input->post('remarks');

        if (!$advance_id || !$approved_by || !$role) {
            return $this->_json(false, 'Missing approval parameters');
        }

        $result = $this->Salary_advance_model->approve_or_reject(
            $advance_id,
            $approved_by,
            $role,
            'Approved',
            $remarks
        );

        if ($result) {
            return $this->_json(true, 'Advance approved successfully');
        }

        return $this->_json(false, 'Approval failed');
    }

    /**
     *  HR / Management Reject
     */
    public function reject()
    {
        $advance_id = $this->input->post('advance_id');
        $approved_by = $this->input->post('approved_by');
        $role = $this->input->post('role'); // HR / Management
        $remarks = $this->input->post('remarks');

        if (!$advance_id || !$approved_by || !$role) {
            return $this->_json(false, 'Missing rejection parameters');
        }

        if (empty($remarks)) {
            return $this->_json(false, 'Remarks are mandatory for rejection');
        }

        $result = $this->Salary_advance_model->approve_or_reject(
            $advance_id,
            $approved_by,
            $role,
            'Rejected',
            $remarks
        );

        if ($result) {
            return $this->_json(true, 'Advance rejected successfully');
        }

        return $this->_json(false, 'Rejection failed');
    }

    /**
     *  Utility: JSON response
     */
    private function _json($status, $message, $data = [])
    {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }

	// public function hr_approve_advance()
	// {
	//     $id = $this->input->post('id');
	//     $status = $this->input->post('status');

	//     $this->model->updateData(
	//         'tbl_salary_advances',
	//         [
	//             'status' => $status,
	//             'hr_remarks' => $this->input->post('remarks')
	//         ],
	//         ['id' => $id]
	//     );
	//     echo json_encode([
	//         'status' => 'success',
	//         'message' => 'Advance approved by HR'
	//     ]);
	// }
}