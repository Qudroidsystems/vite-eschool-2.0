<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Str;
use App\Models\Studentclass;
use App\Models\Studenthouse;
use App\Models\StudentStatus;
use App\Models\Studentpicture;
use App\Models\PromotionStatus;
use App\Models\StudentBatchModel;
use App\Models\ParentRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Studentpersonalityprofile;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;

class StudentsImport implements ToModel, WithProgressBar, WithStartRow, WithUpsertColumns, WithUpserts, WithValidation
{
    use Importable;

    public $id = 0;

    public $_sclassid = 0;

    public $_teremid = 0;

    public $_sessionid = 0;

    public $_batchid = 0;

    /**
     * Handle a single row of the Excel file and map it to models.
     */
    public function model(array $row)
    {
        // Helper function to return "N/A" for null, empty, or whitespace-only values
        $naIfEmpty = function ($value) {
            return (is_null($value) || trim($value) === '') ? 'N/A' : trim($value);
        };

        // Retrieve session data with "N/A" fallback
        $schoolclassid = $naIfEmpty(Session::get('sclassid'));
        $termid = $naIfEmpty(Session::get('tid'));
        $sessionid = $naIfEmpty(Session::get('sid'));
        $batchid = $naIfEmpty(Session::get('batchid'));

        // Map row data with "N/A" for missing/empty values
        $admissionno = $naIfEmpty($row[0] ?? null);
        $surname = $naIfEmpty($row[1] ?? null);
        $firstname = $naIfEmpty($row[2] ?? null);
        $othername = $naIfEmpty($row[3] ?? null);
        $gender = $naIfEmpty($row[4] ?? null);
        $homeaddress = $naIfEmpty($row[5] ?? null);
        $dob = $naIfEmpty($row[6] ?? null);
        $age = $naIfEmpty($row[7] ?? null);
        $placeofbirth = $naIfEmpty($row[8] ?? null);
        $nationality = $naIfEmpty($row[9] ?? null);
        $state = $naIfEmpty($row[10] ?? null);
        $local = $naIfEmpty($row[11] ?? null);
        $religion = $naIfEmpty($row[12] ?? null);
        $lastschool = $naIfEmpty($row[13] ?? null);
        $lastclass = $naIfEmpty($row[14] ?? null);

        $father_title = $naIfEmpty(Str::limit($row[18] ?? '', 3, ''));
        $father = $naIfEmpty(Str::substr($row[18] ?? '', 3));
        $father_phone = $naIfEmpty($row[19] ?? null);
        $office_address = $naIfEmpty($row[20] ?? null);
        $father_occupation = $naIfEmpty($row[21] ?? null);
        $mother_title = $naIfEmpty(Str::limit($row[22] ?? '', 3, ''));
        $mother = $naIfEmpty(Str::substr($row[22] ?? '', 3));
        $mother_phone = $naIfEmpty($row[23] ?? null);
        $mother_occupation = $naIfEmpty($row[24] ?? null);
        $mother_office_address = $naIfEmpty($row[25] ?? null);
        $parent_address = $naIfEmpty($row[26] ?? null);
        $parent_religion = $naIfEmpty($row[27] ?? null);

        // Validate required fields
        if (in_array($admissionno, ['N/A', ''], true) || in_array($surname, ['N/A', ''], true) || in_array($firstname, ['N/A', ''], true)) {
            throw new \Exception("Required fields (admissionno, surname, firstname) cannot be empty or 'N/A' in row " . ($this->startRow() + $this->id));
        }

        // Validate session-based fields
        if (in_array($schoolclassid, ['N/A', ''], true) || in_array($termid, ['N/A', ''], true) || in_array($sessionid, ['N/A', ''], true) || in_array($batchid, ['N/A', ''], true)) {
            throw new \Exception("Session data (schoolclassid, termid, sessionid, batchid) cannot be empty or 'N/A' in row " . ($this->startRow() + $this->id));
        }

        // Initialize models
        $studentbiodata = new Student();
        $studentclass = new Studentclass();
        $promotion = new PromotionStatus();
        $parent = new ParentRegistration();
        $studenthouse = new Studenthouse();
        $picture = new Studentpicture();
        $studentpersonalityprofile = new Studentpersonalityprofile();
        $studentStatus = StudentStatus::where('status', 'old')->first();

        // Use transaction to ensure data consistency
        return \DB::transaction(function () use (
            $studentbiodata, $studentclass, $promotion, $parent, $studenthouse, $picture, $studentpersonalityprofile, $studentStatus,
            $admissionno, $surname, $firstname, $othername, $gender, $homeaddress, $dob, $age, $placeofbirth, $nationality, $state, $local, $religion, $lastschool, $lastclass,
            $father_title, $father, $father_phone, $office_address, $father_occupation, $mother_title, $mother, $mother_phone, $mother_occupation, $mother_office_address, $parent_address, $parent_religion,
            $schoolclassid, $termid, $sessionid, $batchid
        ) {
            // Populate student biodata
            $studentbiodata->admissionNo = $admissionno;
            $studentbiodata->title = 'N/A'; // Hardcoded as per original
            $studentbiodata->firstname = $firstname;
            $studentbiodata->lastname = $surname;
            $studentbiodata->othername = $othername;
            $studentbiodata->gender = $gender;
            $studentbiodata->home_address = $homeaddress;
            $studentbiodata->home_address2 = 'N/A'; // Hardcoded as per original
            $studentbiodata->dateofbirth = $dob;
            $studentbiodata->age = $age;
            $studentbiodata->placeofbirth = $placeofbirth;
            $studentbiodata->religion = $religion;
            $studentbiodata->nationality = $nationality;
            $studentbiodata->state = $state;
            $studentbiodata->local = $local;
            $studentbiodata->last_school = $lastschool;
            $studentbiodata->last_class = $lastclass;
            $studentbiodata->registeredBy = Auth::user()->id ?? 'N/A';
            $studentbiodata->batchid = $batchid;
            $studentbiodata->statusId = $studentStatus ? $studentStatus->id : 'N/A';
            $studentbiodata->save();
            $studentId = $studentbiodata->id;

            // Populate parent data
            $parent->studentId = $studentId;
            $parent->father_title = $father_title;
            $parent->father = $father;
            $parent->father_phone = $father_phone;
            $parent->office_address = $office_address;
            $parent->father_occupation = $father_occupation;
            $parent->mother_title = $mother_title;
            $parent->mother = $mother;
            $parent->mother_phone = $mother_phone;
            $parent->mother_occupation = $mother_occupation;
            $parent->mother_office_address = $mother_office_address;
            $parent->parent_address = $parent_address;
            $parent->religion = $parent_religion;
            $parent->save();

            // Populate student picture
            $picture->studentid = $studentId;
            $picture->picture = 'N/A'; 
            $picture->save();

            // Populate student class
            $studentclass->studentId = $studentId;
            $studentclass->schoolclassid = $schoolclassid;
            $studentclass->termid = $termid;
            $studentclass->sessionid = $sessionid;
            $studentclass->save();

            // Populate promotion status
            $promotion->studentId = $studentId;
            $promotion->schoolclassid = $schoolclassid;
            $promotion->termid = $termid;
            $promotion->sessionid = $sessionid;
            $promotion->promotionStatus = 'PROMOTED';
            $promotion->classstatus = 'CURRENT';
            $promotion->save();

            // Populate student house
            $studenthouse->studentid = $studentId;
            $studenthouse->termid = $termid;
            $studenthouse->sessionid = $sessionid;
            $studenthouse->schoolhouse = 'N/A';  
            $studenthouse->save();

            // Populate student personality profile
            $studentpersonalityprofile->studentid = $studentId;
            $studentpersonalityprofile->schoolclassid = $schoolclassid;
            $studentpersonalityprofile->termid = $termid;
            $studentpersonalityprofile->sessionid = $sessionid;
            $studentpersonalityprofile->save();

            $this->id++; // Increment row counter

            return $studentbiodata;
        });
    }

    /**
     * Validation rules for the Excel import.
     */
    public function rules(): array
    {
        $this->_sclassid = Session::get('sclassid') ?? 'N/A';
        $this->_termid = Session::get('tid') ?? 'N/A';
        $this->_sessionid = Session::get('sid') ?? 'N/A';
        $this->_batchid = Session::get('batchid') ?? 'N/A';

        return [
            '0' => 'required', // admissionno
            '1' => 'required', // surname
            '2' => 'required', // firstname
           // '4' => 'in:Male,Female|nullable', // gender
            '7' => 'numeric|nullable', // age
            '15' => function ($attribute, $value, $onFailure) {
                if ($value != $this->_sclassid) {
                    $onFailure('This data does not match the selected School Class');
                }
            },
            '16' => function ($attribute, $value, $onFailure) {
                if ($value != $this->_termid) {
                    $onFailure('This data does not match the selected School Term');
                }
            },
            '17' => function ($attribute, $value, $onFailure) {
                if ($value != $this->_sessionid) {
                    $onFailure('This data does not match the selected School Session');
                }
            },
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages()
    {
        return [
            '0.required' => 'Admission number is required.',
            '1.required' => 'Surname is required.',
            '2.required' => 'First name is required.',
            '4.in' => 'Gender must be Male or Female.',
            '7.numeric' => 'Age must be a number.',
            '15' => 'School class ID does not match the selected class.',
            '16' => 'Term ID does not match the selected term.',
            '17' => 'Session ID does not match the selected session.',
        ];
    }

    /**
     * Custom validation attribute names.
     */
    public function customValidationAttributes()
    {
        return [
            '0' => 'admissionno',
            '1' => 'surname',
            '2' => 'firstname',
           // '4' => 'gender',
            '7' => 'age',
            '15' => 'schoolclassid',
            '16' => 'termid',
            '17' => 'sessionid',
        ];
    }

    /**
     * Start reading from row 2 (skip header).
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Unique identifier for upserts.
     */
    public function uniqueBy()
    {
        return 'admissionNo';
    }

    /**
     * Columns to update during upserts.
     */
    public function upsertColumns()
    {
        return [
            'title',
            'firstname',
            'lastname',
            'othername',
            'gender',
            'home_address',
            'home_address2',
            'dateofbirth',
            'age',
            'placeofbirth',
            'religion',
            'nationality',
            'state',
            'local',
            'last_school',
            'last_class',
            'registeredBy',
            'batchid',
            'statusId',
        ];
    }

    /**
     * Handle validation failures.
     */
    public function onFailure(Failure ...$failures)
    {
        StudentBatchModel::where('id', $this->_batchid)->update(['Status' => 'Failed']);
        foreach ($failures as $failure) {
            \Log::error('Excel Import Failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }
        throw new \Exception('Validation failed for row ' . $failure->row() . ': ' . implode(', ', $failure->errors()));
    }

    /**
     * Handle exceptions during import.
     */
    public function onError(\Throwable $e)
    {
        StudentBatchModel::where('id', $this->_batchid)->update(['Status' => 'Failed']);
        \Log::error('Excel Import Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
    }
}