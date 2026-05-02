<?php
use App\Models\Salary;
use Illuminate\Support\Facades\DB;

$employees = Salary::select('employee_id')->distinct()->get();

foreach ($employees as $employee) {
    $salaries = Salary::where('employee_id', $employee->employee_id)
        ->orderBy('updated_at', 'desc')
        ->get();

    if ($salaries->count() > 1) {
        $keepId = $salaries->first()->id;
        Salary::where('employee_id', $employee->employee_id)
            ->where('id', '!=', $keepId)
            ->delete();
        echo "Cleaned up duplicates for employee ID: {$employee->employee_id}\n";
    }
}
unlink('cleanup_salaries.php');
