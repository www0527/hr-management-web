<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payslip;
use App\Models\Positions;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User
        $admin = User::factory()->create([
            'name' => 'Alex Admin',
            'email' => 'admin@hr.test',
            'role' => 'admin',
        ]);

        $hr = User::factory()->create([
            'name' => 'Hana HR',
            'email' => 'hana@hr.test',
            'role' => 'hr',
        ]);

        $managerEmployee = User::factory()->create([
            'name' => 'Mia Manager',
            'email' => 'mia@hr.test',
            'role' => 'manager',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'Evan Employee',
            'email' => 'evan@hr.test',
            'role' => 'employee',
        ]);

        // LeaveType
        $annual = LeaveType::create([
            'name' => '年假',
            'default_days_per_year' => 20,
            'is_paid' => true,
        ]);

        $sick = LeaveType::create([
            'name' => '病假',
            'default_days_per_year' => 10,
            'is_paid' => true,
        ]);

        $unpaid = LeaveType::create([
            'name' => '事假',
            'default_days_per_year' => 0,
            'is_paid' => false,
        ]);

        $leaveTypes = [$annual, $sick, $unpaid];

        // Departments + Positions
        $blueprint = [
            'Engineering' => ['軟體工程師', '軟體開發經理', 'QA測試'],
            'Human Resource' => ['人資助理', '招募人員', '人資經理'],
            'Sales' => ['業務', '業務主任', '業務經理'],
            'Finance' => ['財務', '會計', '財務經理'],
            'Marketing' => ['行銷專員', '市場分析師'],
        ];

        $positions = collect();
        $departments = collect();

        foreach ($blueprint as $deptName => $titles) {
            $department = Department::create([
                'name' => $deptName,
                'code' => strtoupper(
                    substr(
                        str_replace(' ', '', $deptName),
                        0,
                        3
                    )),
                'description' => "The {$deptName} department",
            ]);

            $departments->push($department);

            foreach ($titles as $title) {
                $position = $department->positions()->create([
                    'title' => $title,
                    'description' => "{$deptName} Department {$title}",
                ]);

                $positions->push($position);
            }
        }
        // 主管
        $managers = collect();
        foreach ($departments as $deps => $department) {
            $mgrPosition = $department->positions()
                ->where('title', 'like', '%經理%')->first()
                ?? $department->positions()->first();

            $manager = Employee::factory()->create([
                'user_id' => $deps === 0 ? $managerEmployee->id : null,
                'department_id' => $department->id,
                'position_id' => $mgrPosition->id,
                'manager_id' => null,
            ]);

            $managers->push($manager);
        }

        Employee::factory()->create([
            'user_id' => $employeeUser->id,
            'first_name' => 'Evan',
            'last_name' => 'Employee',
            'email' => 'employee@hr.test',
            'department_id' => $departments->first()->id,
            'position_id' => $positions->first()->id,
            'manager_id' => $managers->first->id,
        ]);

        /**
         * 建立員工
         * Factory 的隨機預設會給予沒有存在的 foreign keys，進而新建一堆不可空的資料。
         * 因此要先在範圍內設定隨機值
         */
        for ($i = 0; $i < 30; $i++) {
            $department = $departments->random();
            $position = $positions->random();

            Employee::factory()->create([
                'department_id' => $department->id,
                'position_id' => $position->id,
                'manager_id' => $managers->random()->id,
            ]);
        }

        // ── Leave balances, requests, attendance and payslips per employee ──
        $year = (int) now()->year;

        Employee::all()->each(function (Employee $employee) use ($leaveTypes, $hr, $year) {
            foreach ($leaveTypes as $type) {
                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'year' => $year,
                    'entitled_days' => $type->default_days_per_year,
                    'used_days' => 0,
                ]);
            }

            // ~40% of staff have a leave request on file
            if (rand(1, 10) <= 4) {
                $type = $leaveTypes[array_rand($leaveTypes)];
                $start = Carbon::now()->addDays(rand(-20, 20));
                $end = (clone $start)->addDays(rand(0, 4));
                $status = ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])];

                LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'days' => $start->diffInDays($end) + 1,
                    'reason' => 'Personal time off.',
                    'status' => $status,
                    'reviewed_by' => $status === 'pending' ? null : $hr->id,
                    'reviewed_at' => $status === 'pending' ? null : now(),
                ]);
            }

            // Attendance for the last 5 working days
            for ($d = 1; $d <= 5; $d++) {
                $date = Carbon::now()->subDays($d);
                $in = (clone $date)->setTime(rand(8, 9), rand(0, 59));
                Attendance::create([
                    'employee_id' => $employee->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $in,
                    'clock_out' => (clone $in)->addHours(rand(7, 9)),
                    'status' => (int) $in->format('H') >= 9 ? 'late' : 'present',
                ]);
            }

            // This month's payslip
            $gross = (float) $employee->salary / 12;
            $deductions = round($gross * 0.2, 2);
            Payslip::create([
                'employee_id' => $employee->id,
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'gross_pay' => round($gross, 2),
                'deductions' => $deductions,
                'net_pay' => round($gross - $deductions, 2),
                'issued_at' => now(),
            ]);
        });
    }
}
