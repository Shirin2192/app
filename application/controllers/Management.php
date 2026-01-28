<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Management extends CI_Controller {
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
		$this->load->view('management/dashboard');
	}
	public function fetch_all_employees_list()
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
                'repayment_months'=> $row['repayment_months'],
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
	public function management_approve_advance()
	{
	    $advance_id = $this->input->post('id');

	    $advance = $this->model->selectWhereData(
	        'tbl_salary_advances',
	        ['id' => $advance_id]
	    );

	    // Update status
	    $this->model->updateData(
	        'tbl_salary_advances',
	        [
	            'status' => 'Management Approved',
	            'management_remarks' => $this->input->post('remarks')
	        ],
	        ['id' => $advance_id]
	    );

	    // Generate payroll deductions
	    for ($i = 1; $i <= $advance['repayment_months']; $i++) {

	        $month = date('Y-m-01', strtotime("+$i month"));

	        $this->model->insertData('tbl_payroll_deductions', [
	            'fk_employee_id'        => $advance['fk_employee_id'],
	            'fk_salary_advance_id'  => $advance_id,
	            'deduction_month'       => $month,
	            'deduction_amount'      => $advance['monthly_deduction'],
	            'status'                => 'Pending'
	        ]);
	    }

	    echo json_encode([
	        'status' => 'success',
	        'message' => 'Advance approved & deductions generated'
	    ]);
	}
}
