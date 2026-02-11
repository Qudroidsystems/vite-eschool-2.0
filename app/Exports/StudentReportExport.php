<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnGrouping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class StudentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithColumnGrouping
{
    protected $data;
    protected $groupedColumns = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->groupedColumns = $this->determineColumnGroups();
    }

    public function collection()
    {
        return $this->data['students'];
    }

    public function headings(): array
    {
        $headings = [];
        $columns = $this->data['columns'];

        $map = [
            'photo'          => 'Photo',
            'admissionNo'    => 'Admission Number',
            'lastname'       => 'Last Name',
            'firstname'      => 'First Name',
            'othername'      => 'Other Name',
            'gender'         => 'Gender',
            'dateofbirth'    => 'Date of Birth',
            'age'            => 'Age',
            'class'          => 'Class / Arm',
            'status'         => 'Student Status',
            'admission_date' => 'Admission Date',
            'phone_number'   => 'Phone Number',
            'state'          => 'State of Origin',
            'local'          => 'Local Government Area (LGA)',
            'religion'       => 'Religion',
            'blood_group'    => 'Blood Group',
            'father_name'    => "Father's Name",
            'mother_name'    => "Mother's Name",
            'guardian_phone' => 'Guardian Contact Phone',
            'term'           => 'Term',
            'session'        => 'Session',
        ];

        foreach ($columns as $col) {
            $headings[] = $map[$col] ?? ucwords(str_replace('_', ' ', $col));
        }

        return $headings;
    }

    public function map($student): array
    {
        $row = [];
        $columns = $this->data['columns'];

        foreach ($columns as $col) {
            switch ($col) {
                case 'photo':
                    // Show student initials or photo status
                    if ($student->has_photo) {
                        $row[] = $student->photo_initials ?: 'Photo Available';
                    } else {
                        $row[] = 'No Photo';
                    }
                    break;

                case 'admissionNo':
                    $admissionNo = $student->admissionNo ?? $student->admission_no ?? 'N/A';
                    $row[] = $admissionNo;
                    break;

                case 'class':
                    $class = $student->schoolclass ?? 'N/A';
                    $arm = $student->arm_name ?? '';
                    $row[] = $arm ? "$class - $arm" : $class;
                    break;

                case 'guardian_phone':
                    $phone = $student->father_phone ?? $student->mother_phone ?? '';
                    $row[] = $phone ?: 'N/A';
                    break;

                case 'admission_date':
                    if ($student->admission_date) {
                        try {
                            $row[] = Carbon::parse($student->admission_date)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $row[] = $student->admission_date;
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                case 'dateofbirth':
                    if ($student->dateofbirth) {
                        try {
                            $row[] = Carbon::parse($student->dateofbirth)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $row[] = $student->dateofbirth;
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                case 'status':
                    $status = '';
                    if (isset($student->statusId)) {
                        if ($student->statusId == 1) $status = 'Old Student';
                        if ($student->statusId == 2) $status = 'New Student';
                    }
                    if ($student->student_status) {
                        $status .= ($status ? ' (' . $student->student_status . ')' : $student->student_status);
                    }
                    $row[] = $status ?: 'N/A';
                    break;

                case 'gender':
                    $gender = $student->gender ?? 'N/A';
                    $row[] = $gender;
                    break;

                case 'term':
                    if ($student->current_term_name) {
                        $row[] = $student->current_term_name;
                    } elseif ($student->termid) {
                        try {
                            $term = \App\Models\Schoolterm::find($student->termid);
                            $row[] = $term ? $term->term : 'N/A';
                        } catch (\Exception $e) {
                            $row[] = 'N/A';
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                case 'session':
                    if ($student->current_session_name) {
                        $row[] = $student->current_session_name;
                    } elseif ($student->sessionid) {
                        try {
                            $session = \App\Models\Schoolsession::find($student->sessionid);
                            $row[] = $session ? $session->session : 'N/A';
                        } catch (\Exception $e) {
                            $row[] = 'N/A';
                        }
                    } else {
                        $row[] = 'N/A';
                    }
                    break;

                // Handle separate name fields
                case 'lastname':
                    $row[] = $student->lastname ?? 'N/A';
                    break;

                case 'firstname':
                    $row[] = $student->firstname ?? 'N/A';
                    break;

                case 'othername':
                    $row[] = $student->othername ?? 'N/A';
                    break;

                default:
                    // Try different property names
                    $value = $this->getStudentProperty($student, $col);
                    $row[] = $value !== null && $value !== '' ? $value : 'N/A';
                    break;
            }
        }

        return $row;
    }

    /**
     * Get student property with multiple fallbacks
     */
    private function getStudentProperty($student, $property)
    {
        // Try direct property access
        if (property_exists($student, $property)) {
            return $student->$property;
        }

        // Try snake_case version
        $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $property));
        if (property_exists($student, $snakeCase)) {
            return $student->$snakeCase;
        }

        // Try camelCase version
        $camelCase = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $property))));
        if (property_exists($student, $camelCase)) {
            return $student->$camelCase;
        }

        // Common property mappings
        $mappings = [
            'phone_number' => ['phone_number', 'phone'],
            'blood_group' => ['blood_group', 'bloodgroup'],
            'mother_tongue' => ['mother_tongue', 'mothertongue'],
            'father_name' => ['father_name', 'father'],
            'mother_name' => ['mother_name', 'mother'],
            'father_phone' => ['father_phone', 'fatherphone'],
            'mother_phone' => ['mother_phone', 'motherphone'],
            'parent_email' => ['parent_email', 'parentemail'],
            'parent_address' => ['parent_address', 'parentaddress'],
            'father_occupation' => ['father_occupation', 'fatheroccupation'],
            'father_city' => ['father_city', 'fathercity'],
            'last_school' => ['last_school', 'lastschool'],
            'last_class' => ['last_class', 'lastclass'],
            'reason_for_leaving' => ['reason_for_leaving', 'reasonforleaving'],
        ];

        if (isset($mappings[$property])) {
            foreach ($mappings[$property] as $mappedProperty) {
                if (property_exists($student, $mappedProperty)) {
                    return $student->$mappedProperty;
                }
            }
        }

        return null;
    }

    /**
     * Determine column groups for better organization
     */
    private function determineColumnGroups()
    {
        $columns = $this->data['columns'];
        $groups = [];

        $groupDefinitions = [
            'Student Information' => ['photo', 'admissionNo', 'firstname', 'lastname', 'othername', 'gender', 'dateofbirth', 'age'],
            'Academic Information' => ['class', 'status', 'term', 'session', 'admission_date'],
            'Personal Information' => ['phone_number', 'email', 'blood_group', 'religion', 'mother_tongue'],
            'Geographical Information' => ['state', 'local', 'city', 'nationality', 'placeofbirth'],
            'Parent Information' => ['father_name', 'mother_name', 'guardian_phone', 'parent_email', 'parent_address'],
            'Additional Information' => ['student_category', 'future_ambition', 'last_school', 'last_class'],
        ];

        foreach ($groupDefinitions as $groupName => $groupColumns) {
            $foundColumns = array_intersect($columns, $groupColumns);
            if (!empty($foundColumns)) {
                $groups[$groupName] = $foundColumns;
            }
        }

        return $groups;
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->data['students']->count() + 6; // Added extra rows for metadata
        $startRow = 7; // Header row starts here

        $styles = [
            $startRow => [ // Header row
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => $this->data['confidential'] ? 'DC3545' : '1E40AF'
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],

            'A' . ($startRow + 1) . ':A' . $totalRows => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC']
                ]
            ],

            'A' . $startRow . ':' . $sheet->getHighestColumn() . $totalRows => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ]
            ]
        ];

        // Alternate row coloring
        for ($i = $startRow + 1; $i <= $totalRows; $i++) {
            if ($i % 2 == 0) {
                $styles['A' . $i . ':' . $sheet->getHighestColumn() . $i] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC']
                    ]
                ];
            }
        }

        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert title rows at the top
                $sheet->insertNewRowBefore(1, 6);

                // Get highest column letter
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                // School header if included
                if ($this->data['include_header'] ?? true) {
                    $schoolInfo = $this->data['school_info'] ?? null;

                    if ($schoolInfo) {
                        $sheet->setCellValue('A1', $schoolInfo->school_name ?? 'STUDENT MASTER LIST REPORT');
                        $sheet->mergeCells('A1:' . $highestColumn . '1');
                        $sheet->getStyle('A1')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 16,
                                'color' => [
                                    'rgb' => $this->data['confidential'] ? 'DC3545' : '1E40AF'
                                ]
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                        if ($schoolInfo->school_motto) {
                            $sheet->setCellValue('A2', $schoolInfo->school_motto);
                            $sheet->mergeCells('A2:' . $highestColumn . '2');
                            $sheet->getStyle('A2')->applyFromArray([
                                'font' => [
                                    'italic' => true,
                                    'size' => 12
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER
                                ]
                            ]);
                        }

                        $details = 'Class: ' . $this->data['className'] .
                                  ' | Term: ' . $this->data['termName'] .
                                  ' | Session: ' . $this->data['sessionName'] .
                                  ' | Generated: ' . $this->data['generated'] .
                                  ' | Total Students: ' . $this->data['total'];

                        if ($this->data['confidential']) {
                            $details .= ' | CONFIDENTIAL';
                        }

                        $sheet->setCellValue('A3', $details);
                        $sheet->mergeCells('A3:' . $highestColumn . '3');
                        $sheet->getStyle('A3')->applyFromArray([
                            'font' => ['size' => 10],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    } else {
                        $title = 'STUDENT MASTER LIST REPORT';
                        if ($this->data['confidential']) {
                            $title = 'CONFIDENTIAL - ' . $title;
                        }

                        $sheet->setCellValue('A1', $title);
                        $sheet->mergeCells('A1:' . $highestColumn . '1');
                        $sheet->getStyle('A1')->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 16,
                                'color' => [
                                    'rgb' => $this->data['confidential'] ? 'DC3545' : '1E40AF'
                                ]
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);

                        $details = 'Class: ' . $this->data['className'] .
                                  ' | Term: ' . $this->data['termName'] .
                                  ' | Session: ' . $this->data['sessionName'] .
                                  ' | Generated: ' . $this->data['generated'] .
                                  ' | Total Students: ' . $this->data['total'];
                        $sheet->setCellValue('A2', $details);
                        $sheet->mergeCells('A2:' . $highestColumn . '2');
                        $sheet->getStyle('A2')->applyFromArray([
                            'font' => ['size' => 11],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    }
                } else {
                    $title = 'STUDENT MASTER LIST REPORT - ' . $this->data['className'];
                    if ($this->data['confidential']) {
                        $title = 'CONFIDENTIAL - ' . $title;
                    }

                    $sheet->setCellValue('A1', $title);
                    $sheet->mergeCells('A1:' . $highestColumn . '1');
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => [
                                'rgb' => $this->data['confidential'] ? 'DC3545' : '1E40AF'
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    $details = 'Generated: ' . $this->data['generated'] .
                              ' | Total Students: ' . $this->data['total'] .
                              ' | Males: ' . $this->data['males'] .
                              ' | Females: ' . $this->data['females'] .
                              ' | Term: ' . $this->data['termName'] .
                              ' | Session: ' . $this->data['sessionName'];
                    $sheet->setCellValue('A2', $details);
                    $sheet->mergeCells('A2:' . $highestColumn . '2');
                    $sheet->getStyle('A2')->applyFromArray([
                        'font' => ['size' => 11],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                // Add warning message if large report
                if ($this->data['is_large_report'] ?? false) {
                    $sheet->setCellValue('A3', '⚠️ Large report detected. Photos excluded for performance.');
                    $sheet->mergeCells('A3:' . $highestColumn . '3');
                    $sheet->getStyle('A3')->applyFromArray([
                        'font' => [
                            'italic' => true,
                            'color' => ['rgb' => '721C24']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8D7DA']
                        ],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                // Add generated by information
                $generatedBy = 'Generated by: ' . ($this->data['generated_by'] ?? 'System') .
                              ' | Template: ' . ucfirst($this->data['template'] ?? 'default');
                $sheet->setCellValue('A4', $generatedBy);
                $sheet->mergeCells('A4:' . $highestColumn . '4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Add timestamp
                $timestamp = 'Generated on: ' . date('d/m/Y H:i:s');
                $sheet->setCellValue('A5', $timestamp);
                $sheet->mergeCells('A5:' . $highestColumn . '5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Empty row before header
                $sheet->setCellValue('A6', '');

                // Auto-size columns
                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Set row height for header
                $sheet->getRowDimension(7)->setRowHeight(30);

                // Freeze header row (row 7)
                $sheet->freezePane('A8');

                // Add filter to header row
                $sheet->setAutoFilter('A7:' . $highestColumn . '7');

                // Add column groups headers
                $this->addColumnGroups($sheet, 6, $highestColumnIndex);

                // Add page setup for printing
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Add header and footer
                $headerText = '&C&"Arial,Bold"STUDENT MASTER LIST REPORT';
                if ($this->data['confidential']) {
                    $headerText .= ' - CONFIDENTIAL';
                }
                $headerText .= '&RPage &P of &N';

                $sheet->getHeaderFooter()
                    ->setOddHeader($headerText);

                $footerText = '&LGenerated by: ' . ($this->data['generated_by'] ?? 'System') .
                             '&CGenerated on: ' . date('d/m/Y H:i:s') .
                             '&RPage &P of &N';

                if ($this->data['confidential']) {
                    $footerText .= '&X&"Arial,Bold"CONFIDENTIAL';
                }

                $sheet->getHeaderFooter()
                    ->setOddFooter($footerText);

                // Add confidential watermark
                if ($this->data['confidential']) {
                    $this->addConfidentialWatermark($sheet);
                }
            }
        ];
    }

    /**
     * Add column group headers for better organization
     */
    private function addColumnGroups($sheet, $rowIndex, $highestColumnIndex)
    {
        $currentColumn = 1;
        $groupRow = $rowIndex; // Row 6 for group headers

        foreach ($this->groupedColumns as $groupName => $columns) {
            $groupColumns = $this->getColumnIndicesForGroup($columns);
            if (empty($groupColumns)) continue;

            $startColumn = $groupColumns[0];
            $endColumn = end($groupColumns);

            // Merge cells for the group header
            if ($startColumn <= $endColumn) {
                $startCell = Coordinate::stringFromColumnIndex($startColumn);
                $endCell = Coordinate::stringFromColumnIndex($endColumn);

                $sheet->mergeCells($startCell . $groupRow . ':' . $endCell . $groupRow);
                $sheet->setCellValue($startCell . $groupRow, $groupName);
                $sheet->getStyle($startCell . $groupRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '333333']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E5E7EB']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '9CA3AF']
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB']
                        ]
                    ]
                ]);
            }
        }

        // Set group header row height
        $sheet->getRowDimension($groupRow)->setRowHeight(20);
    }

    /**
     * Get column indices for a group of columns
     */
    private function getColumnIndicesForGroup($columns)
    {
        $indices = [];
        $headerColumns = $this->data['columns'];

        foreach ($columns as $column) {
            $index = array_search($column, $headerColumns);
            if ($index !== false) {
                // Add 1 because Excel columns are 1-indexed
                $indices[] = $index + 1;
            }
        }

        sort($indices);
        return $indices;
    }

    /**
     * Add confidential watermark to the sheet
     */
    private function addConfidentialWatermark($sheet)
    {
        // Add a background text effect
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Add a shape/text box with watermark
        $sheet->getPageSetup()->setRowsToRepeatAtTopToStart(1);

        // Alternative: Add a header/footer watermark
        $sheet->getHeaderFooter()
            ->setOddHeader('&C&"Arial,Bold"&44&KFF0000CONFIDENTIAL');
    }

    public function columnGroupings(): array
    {
        $groupings = [];

        foreach ($this->groupedColumns as $groupName => $columns) {
            if (!empty($columns)) {
                $groupings[] = new \Maatwebsite\Excel\Sheet\Group($columns, $groupName);
            }
        }

        return $groupings;
    }
}
