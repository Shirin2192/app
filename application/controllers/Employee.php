<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends CI_Controller {
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
		$this->load->view('employee/dashboard');
	}
	public function get_employees_profile_detail_on_id()
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

	// public function apply_salary_advance()
	// {
	//     $this->form_validation->set_rules('advance_amount', 'Advance Amount', 'required|numeric');    

	//     if ($this->form_validation->run() == FALSE) {
	//         echo json_encode([
	//             'status' => 'error',
	//             'errors' => $this->form_validation->error_array()
	//         ]);
	//         return;
	//     }

	//     $amount   = $this->input->post('advance_amount');	    
	//     $data = [
	//         'fk_employee_id'     => $this->session->userdata('employee_session')['employee_id'],
	//         'advance_amount'     => $amount,
	//         'reason'             => $this->input->post('reason'),
	//         'request_date'       => date('Y-m-d'),
	//         'required_by'        => $this->input->post('required_by'),	        
	//         'status'             => 'Pending'
	//     ];

	//     $this->model->insertData('tbl_salary_advances', $data);
	//     echo json_encode([
	//         'status' => 'success',
	//         'message' => 'Salary advance request submitted'
	//     ]);
	// }

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
    public function apply_salary_advance()
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
}