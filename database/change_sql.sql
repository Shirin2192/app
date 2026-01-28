CREATE TABLE `tbl_attendance` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `employee_id` INT(11) NOT NULL,
  `attendance_date` DATE NOT NULL,

  `status` ENUM('Present','Absent','Half Day','Leave') NOT NULL,

  `is_paid_leave` TINYINT(1) DEFAULT 0 COMMENT '1 = Paid Leave, 0 = Unpaid Leave',
  `late_mark` TINYINT(1) DEFAULT 0 COMMENT '1 = Late, 0 = On Time',

  `check_in_time` TIME DEFAULT NULL,
  `check_out_time` TIME DEFAULT NULL,
  `late_minutes` INT(11) DEFAULT 0,

  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_emp_date` (`employee_id`, `attendance_date`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_attendance_date` (`attendance_date`),

  CONSTRAINT `fk_attendance_employee`
    FOREIGN KEY (`employee_id`)
    REFERENCES `tbl_employees` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
