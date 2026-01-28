<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function __construct()
	{
		parent::__construct();

		 // Prevent browser caching for all admin pages
		 $this->output
		 ->set_header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0")
		 ->set_header("Cache-Control: post-check=0, pre-check=0", false)
		 ->set_header("Pragma: no-cache");
	}
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function registration()
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

	    // ❌ Employee insert failed
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

	public function login_process()
	{
	    // ===============================
	    // Validation Rules
	    // ===============================
	    $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
	    $this->form_validation->set_rules('password', 'Password', 'required');

	    if ($this->form_validation->run() == FALSE) {

	        echo json_encode([
	            'status' => 'error',
	            'errors' => $this->form_validation->error_array()
	        ]);
	        return;
	    }

	    $email    = $this->input->post('email');
	    $password = $this->input->post('password');

	    // ===============================
	    // Fetch User + Employee Details
	    // ===============================
	    $this->db->select('
	        u.id as user_id,
	        u.employee_id,
	        u.role,
	        u.email,
	        u.password,
	        u.status,
	        e.first_name,
	        e.last_name
	    ');
	    $this->db->from('tbl_users u');
	    $this->db->join('tbl_employees e', 'e.id = u.employee_id', 'left');
	    $this->db->where('u.email', $email);
	    $this->db->where('u.status', 'Active');

	    $user = $this->db->get()->row_array();

	    if (!$user) {
	        echo json_encode([
	            'status' => 'error',
	            'email_error' => 'Invalid Email or Account Inactive'
	        ]);
	        return;
	    }

	    // ===============================
	    // Password Verification
	    // ===============================
	    if ($user['password'] != decy_ency('encrypt', $password)) {

	        echo json_encode([
	            'status' => 'error',
	            'password_error' => 'Invalid Password'
	        ]);
	        return;
	    }

	    // ===============================
	    // Role Based Session & Redirect
	    // ===============================
	    switch ($user['role']) {

	        case 'HR':
	            $session_name = 'hr_session';
	            $redirect_url = base_url('hr/dashboard');
	            break;

	        case 'Management':
	            $session_name = 'management_session';
	            $redirect_url = base_url('management/dashboard');
	            break;

	        default: // Employee
	            $session_name = 'employee_session';
	            $redirect_url = base_url('employee/dashboard');
	            break;
	    }

	    // ===============================
	    // Set Session
	    // ===============================
	    $session_data = [
	        'user_id'      => $user['user_id'],
	        'employee_id'  => $user['employee_id'],
	        'role'         => $user['role'],
	        'name'         => trim($user['first_name'].' '.$user['last_name']),
	        'email'        => $user['email'],
	        'logged_in'    => true
	    ];

	    $this->session->set_userdata($session_name, $session_data);

	    // ===============================
	    // Update Last Login
	    // ===============================
	    $this->db->where('id', $user['user_id']);
	    $this->db->update('tbl_users', ['last_login' => date('Y-m-d H:i:s')]);

	    echo json_encode([
	        'status' => 'success',
	        'redirect' => $redirect_url
	    ]);
	}

	public function logout() {
		// Destroy session data
		$this->session->sess_destroy(); // Completely destroy session
	
		// Redirect to login page
		redirect(base_url('common/index'));
	}
}
