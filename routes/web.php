<?php

use \App\Http\Controllers\SchoolInformationController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BiodataController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\ClassBroadsheetController;
use App\Http\Controllers\ClasscategoryController;
use App\Http\Controllers\ClassOperationController;
use App\Http\Controllers\ClassTeacherController;
use App\Http\Controllers\CompulsorySubjectClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobStatusController;
use App\Http\Controllers\MockSubjectVettingController;
use App\Http\Controllers\MyClassController;
use App\Http\Controllers\MyMockSubjectVettingsController;
use App\Http\Controllers\MyresultroomController;
use App\Http\Controllers\MyScoreSheetController;
use App\Http\Controllers\MySubjectController;
use App\Http\Controllers\MySubjectVettingsController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrincipalsCommentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SchoolArmController;
use App\Http\Controllers\SchoolBillController;
use App\Http\Controllers\SchoolBillTermSessionController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolHouseController;
use App\Http\Controllers\SchoolPaymentController;
use App\Http\Controllers\SchoolsessionController;
use App\Http\Controllers\SchooltermController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffImageUploadController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentHouseController;
use App\Http\Controllers\StudentImageUploadController;
use App\Http\Controllers\StudentpersonalityprofileController;
use App\Http\Controllers\StudentResultsController;
use App\Http\Controllers\SubjectClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectOperationController;
use App\Http\Controllers\SubjectTeacherController;
use App\Http\Controllers\SubjectVettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewStudentController;
use App\Http\Controllers\ViewStudentMockReportController;
use App\Http\Controllers\ViewStudentReportController;
use Illuminate\Support\Facades\Route;












Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/all', [UserController::class, 'allUsers'])->name('users.all');
    Route::get('/users/paginate', [UserController::class, 'paginate'])->name('users.paginate');
    Route::get('/users/roles', [UserController::class, 'roles']);
    Route::resource('permissions', PermissionController::class);

    

    Route::get('users/add-student', [UserController::class, 'createFromStudentForm'])->name('users.add-student-form');
    Route::post('users/create-from-student', [UserController::class, 'createFromStudent'])->name('users.createFromStudent');
    Route::get('/get-students', [UserController::class, 'getStudents'])->name('get.students');

    Route::resource('biodata', BiodataController::class);
    Route::get('/overview/{id}', [OverviewController::class, 'show'])->name('user.overview');
    Route::get('/settings/{id}', [BiodataController::class, 'show'])->name('user.settings');
    Route::post('ajaxemailupdate', [BiodataController::class, 'ajaxemailupdate']);
    Route::post('ajaxpasswordupdate', [BiodataController::class, 'ajaxpasswordupdate']);

    Route::get('/adduser/{id}', [RoleController::class, 'adduser'])->name('roles.adduser');
    Route::post('/updateuserrole', [RoleController::class, 'updateuserrole'])->name('roles.updateuserrole');
    Route::delete('roles/removeuserrole/{userid}/{roleid}', [RoleController::class, 'removeuserrole'])->name('roles.removeuserrole');

    Route::resource('subject', SubjectController::class);
    Route::get('/subjectid/{subjectid}', [SubjectController::class, 'deletesubject'])->name('subject.deletesubject');
    Route::post('subjectid', [SubjectController::class, 'updatesubject'])->name('subject.updatesubject');

    Route::resource('subjectclass', SubjectClassController::class);
    Route::delete('subjectclass/deletesubjectclass/{subjectclassid}', [SubjectClassController::class, 'deletesubjectclass'])->name('subjectclass.deletesubjectclass');
    Route::get('/subjectclass/assignments/{subjectteacherid}', [SubjectClassController::class, 'assignments'])->name('subjectclass.assignments');
    Route::get('/subjectclass/assignments-by-teacher/{subjectTeacherId}', [SubjectClassController::class, 'assignmentsBySubjectTeacher'])->name('subjectclass.assignmentsByTeacher');
  

    Route::resource('staff', StaffController::class);


    Route::resource('subjectteacher', SubjectTeacherController::class)->except(['update']);
    Route::match(['put', 'post'], 'subjectteacher/{id}', [SubjectTeacherController::class, 'update'])->name('subjectteacher.update');
    Route::get('subjectteacher/{id}/subjects', [SubjectTeacherController::class, 'getSubjects'])->name('subjectteacher.subjects');
    Route::post('subjectteacher/delete', [SubjectTeacherController::class, 'deletesubjectteacher'])->name('subjectteacher.delete');

    Route::resource('classteacher', ClassTeacherController::class);
    Route::get('/classteacher/assignments/{staffId}/{termId}/{sessionId}', [ClassTeacherController::class, 'assignments'])->name('classteacher.assignments');
    Route::post('/classteacher/delete', [ClassTeacherController::class, 'deleteMultiple'])->name('classteacher.deleteMultiple');


    Route::resource('session', SchoolsessionController::class);
    Route::get('/sessionid/{sessionid}', [SchoolsessionController::class, 'deletesession'])->name('session.deletesession');
    Route::post('updatesessionid', [SchoolsessionController::class, 'updatesession'])->name('session.updatesession');

    Route::resource('schoolhouse', SchoolHouseController::class);
    Route::post('schoolhouse/deletehouse', [SchoolHouseController::class, 'deletehouse'])->name('schoolhouse.deletehouse');
    Route::post('schoolhouse/updatehouse', [SchoolHouseController::class, 'updatehouse'])->name('schoolhouse.updatehouse');

    Route::resource('term', SchooltermController::class);
    Route::post('term/deleteterm', [SchooltermController::class, 'deleteterm'])->name('term.deleteterm');
    Route::post('term/updateterm', [SchooltermController::class, 'updateterm'])->name('term.updateterm');

    Route::resource('schoolarm', SchoolArmController::class);
    Route::post('schoolarm/deletearm', [SchoolArmController::class, 'deletearm'])->name('schoolarm.deletearm');
    Route::post('schoolarm/updatearm', [SchoolArmController::class, 'updatearm'])->name('schoolarm.updatearm');
    Route::post('/schoolclass/deletes-schoolclass', [SchoolClassController::class, 'deleteschoolclass'])->name('schoolclass.deleteschoolclass');
    Route::get('/schoolclasses/{getArms}/arms', [SchoolClassController::class, 'getArms'])->name('schoolclass.getArms');

    Route::get('schoolclass', [SchoolClassController::class, 'index'])->name('schoolclass.index');
    Route::post('schoolclass', [SchoolClassController::class, 'store'])->name('schoolclass.store');
    Route::put('schoolclass/{schoolclass}', [SchoolClassController::class, 'update'])->name('schoolclass.update');
    Route::delete('schoolclass/{schoolclass}', [SchoolClassController::class, 'destroy'])->name('schoolclass.destroy');
    Route::post('schoolclass/deleteschoolclass', [SchoolClassController::class, 'deleteschoolclass'])->name('schoolclass.deleteschoolclass');
    Route::get('schoolclass/{schoolclass}/arms', [SchoolClassController::class, 'getArms'])->name('schoolclass.getarms');
    Route::put('/schoolclass/{id}', [SchoolClassController::class, 'update'])->name('schoolclass.update');

    Route::resource('student', StudentController::class)->except(['destroy']); // Exclude destroy to avoid conflict
    Route::get('/students/data', [App\Http\Controllers\StudentController::class, 'data'])->name('student.data');
    Route::delete('/student/{id}/destroy', [StudentController::class, 'destroy'])->name('student.destroy');
    Route::get('/studentid/{studentid}', [StudentController::class, 'deletestudent'])->name('student.deletestudent');
    Route::get('/studentoverview/{id}', [StudentController::class, 'overview'])->name('student.overview');
    Route::get('/studentsettings/{id}', [StudentController::class, 'setting'])->name('student.settings');
    Route::get('/studentbulkupload', [StudentController::class, 'bulkupload'])->name('student.bulkupload');
    Route::post('/studentbulkuploadsave', [StudentController::class, 'bulkuploadsave'])->name('student.bulkuploadsave');
    Route::get('/batchindex', [StudentController::class, 'batchindex'])->name('studentbatchindex');
    Route::delete('/student/deletestudentbatch', [StudentController::class, 'deletestudentbatch'])->name('student.deletestudentbatch');
    Route::post('/students/destroy-multiple', [StudentController::class, 'destroyMultiple'])->name('students.destroy-multiple');
    Route::put('/student/updateclass', [StudentController::class, 'updateClass'])->name('student.updateclass');
    
    Route::resource('classoperation', ClassOperationController::class);

    Route::resource('classcategories', ClasscategoryController::class);
    Route::get('/classcategoryid/{classcategoryid}', [ClasscategoryController::class, 'deleteclasscategory'])->name('classcategories.deleteclasscategory');
    Route::post('updateclasscategoryid', [ClasscategoryController::class, 'updateclasscategory'])->name('classcategories.updateclasscategory');


    Route::resource('parent', ParentController::class);
    Route::resource('studentImageUpload', StudentImageUploadController::class);
    Route::resource('myclass', MyClassController::class);
    Route::resource('mysubject', MySubjectController::class);

    Route::get('/myresultroom', [MyresultroomController::class, 'index'])->name('myresultroom.index');
    Route::post('/myresultroom', [MyresultroomController::class, 'index']);
    Route::post('/myresultroom/store', [MyresultroomController::class, 'store']);
    Route::delete('/subjects/registered-classes', [MyresultroomController::class, 'delete']); // Adjust as needed
    // Route::get('/subjectscoresheet/{schoolclassid}/{subjectclassid}/{userid}/{termid}/{session_id}', [MyScoreSheetController::class, 'index'])->name('subjectscoresheet.index');
    // Route::get('/subjectscoresheet-mock/{schoolclassid}/{subjectclassid}/{userid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'index'])->name('subjectscoresheet-mock.index');
    Route::resource('studentresults', StudentResultsController::class);
    

    // Terminal Scoresheet Routes
    // Route::resource('subjectscoresheet', MyScoreSheetController::class);
    Route::get('subjectscoresheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'subjectscoresheet'])->name('subjectscoresheet');
    Route::get('subjectscoresheet/edit/{id}', [MyScoreSheetController::class, 'edit'])->name('subjectscoresheet.edit');
    Route::put('subjectscoresheet/update/{id}', [MyScoreSheetController::class, 'update'])->name('subjectscoresheet.update');
    Route::delete('subjectscoresheet/delete/{id}', [MyScoreSheetController::class, 'destroy'])->name('subjectscoresheet.destroy');
    Route::get('subjectscoresheet/export', [MyScoreSheetController::class, 'export'])->name('subjectscoresheet.export');
    Route::post('subjectscoresheet/import', [MyScoreSheetController::class, 'import'])->name('subjectscoresheet.import');
    Route::get('/subjectscoresheet/results', [MyScoreSheetController::class, 'results'])->name('subjectscoresheet.results');
    Route::post('/subjectscoresheet/grade-preview', [MyScoreSheetController::class, 'calculateGradePreview'])->name('subjectscoresheet.grade-preview');
    Route::post('subjectscoresheet/bulk-update', [MyScoreSheetController::class, 'bulkUpdateScores'])->name('subjectscoresheet.bulk-update');
    Route::get('/subjectscoresheet/import-progress', [MyScoreSheetController::class, 'importProgress'])->name('subjectscoresheet.import_progress');
    // Mock Scoresheet Routes
    Route::get('subjectscoresheet-mock', [MyScoreSheetController::class, 'mockIndex'])->name('subjectscoresheet-mock.index');
    Route::get('subjectscoresheet-mock/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyScoreSheetController::class, 'mockSubjectscoresheet'])->name('subjectscoresheet-mock.show');
    Route::get('subjectscoresheet-mock/export', [MyScoreSheetController::class, 'mockExport'])->name('subjectscoresheet-mock.export');
    Route::post('subjectscoresheet-mock/import', [MyScoreSheetController::class, 'mockImport'])->name('subjectscoresheet-mock.import');
    Route::get('subjectscoresheet-mock/{id}/edit', [MyScoreSheetController::class, 'mockEdit'])->name('subjectscoresheet-mock.edit');
    Route::put('subjectscoresheet-mock/{id}', [MyScoreSheetController::class, 'mockUpdate'])->name('subjectscoresheet-mock.update');
    Route::post('scoresheet-mock/destroy', [MyScoreSheetController::class, 'mockDestroy'])->name('scoresheet-mock.destroy');
    Route::post('scoresheet-mock/bulk-update', [MyScoreSheetController::class, 'mockBulkUpdateScores'])->name('scoresheet-mock.bulk-update');
    Route::get('subjectscoresheet-mock/results', [MyScoreSheetController::class, 'mockResults'])->name('subjectscoresheet-mock.results');
    Route::get('subjectscoresheet-mock/download-marksheet', [MyScoreSheetController::class, 'mockDownloadMarkSheet'])->name('subjectscoresheet-mock.download-marksheet');
    // Route::get('/job/status/{job_id}', [JobStatusController::class, 'show'])->name('job.status');
    Route::post('subjectscoresheet-mock/calculate-grade', [MyScoreSheetController::class, 'calculateGradeForScore'])->name('subjectscoresheet-mock.calculate-grade');
   
        // Marks Sheet Download Routes
    Route::get('/scoresheet/download-marks-sheet', [MyScoreSheetController::class, 'downloadMarkSheet'])
    ->name('scoresheet.download-marks-sheet');
    
    Route::post('/subjectscoresheet/bulk-update', [MyScoreSheetController::class, 'bulkUpdateScores'])
    ->name('subjectscoresheet.bulk-update');

    // School Information Management Routes (Optional - for admin panel)

    Route::get('/school-info', [SchoolInformationController::class, 'index'])->name('admin.school-info.index');
    Route::post('/school-info', [SchoolInformationController::class, 'store'])->name('admin.school-info.store');
    Route::put('/school-info/{id}', [SchoolInformationController::class, 'update'])->name('admin.school-info.update');


    Route::resource('schoolbill', SchoolBillController::class);
    Route::get('/billid/{billid}', [SchoolBillController::class, 'deletebill'])->name('schoolbill.deletebill');
    Route::post('billid', [SchoolBillController::class, 'updatebill'])->name('schoolbill.updateschoolbill');

    Route::resource('schoolbilltermsession', SchoolBillTermSessionController::class);
    Route::get('/schoolbilltermsessionid/{schoolbilltermsessionid}', [SchoolBillTermSessionController::class, 'deleteschoolbilltermsession'])->name('schoolbilltermsession.deleteschoolbilltermsession');
    Route::post('schoolbilltermsessionbid', [SchoolBillTermSessionController::class, 'updateschoolbilltermsession'])->name('schoolbilltermsession.updateschoolbilltermsession');
    Route::get('/schoolbilltermsession/{id}/related', 'App\Http\Controllers\SchoolBillTermSessionController@getRelated')->name('schoolbilltermsession.related');


    Route::get('/schoolpayment', [SchoolPaymentController::class, 'index'])->name('schoolpayment.index');
    Route::get('/schoolpayment/term-session/{id}', [SchoolPaymentController::class, 'termSession'])->name('schoolpayment.termsession');
    Route::get('termsessionpayments', [SchoolPaymentController::class, 'termsessionpayments'])->name('schoolpayment.termsessionpayments');
    Route::get('/schoolpayment/term-session-payments', [SchoolPaymentController::class, 'termSessionPayments'])->name('schoolpayment.termsessionpayments');
    Route::post('/schoolpayment/store', [SchoolPaymentController::class, 'store'])->name('schoolpayment.store');
    Route::post('/schoolpayment/delete/{recordId}', [SchoolPaymentController::class, 'deletestudentpayment'])->name('schoolpayment.deletestudentpayment');
    Route::get('/schoolpayment/invoice/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'invoice'])->name('schoolpayment.invoice');
    Route::get('/schoolpayment/statement/{studentId}/{schoolclassid}/{termid}/{sessionid}', [SchoolPaymentController::class, 'statement'])->name('schoolpayment.statement');

      //analysis...
    Route::resource('analysis', AnalysisController::class);
    Route::post('analysisClassTermSession', [AnalysisController::class, 'analysisClassTermSession'])->name('analysis.analysisClassTermSession');
    Route::get('analysis/export-pdf/{class_id}/{termid_id}/{session_id}', 'App\Http\Controllers\AnalysisController@exportPDF')->name('analysis.exportPDF');
    Route::get('/analysis/pdf/{class_id}/{termid_id}/{session_id}/{action?}', [AnalysisController::class, 'exportPDF'])->name('analysis.viewPDF')->where('action', 'view|download');
    

    // School-wide payment analysis routes
    Route::get('/school-wide-payment-analysis/{termid_id}/{session_id}/{action?}/{format?}','App\Http\Controllers\AnalysisController@schoolWidePaymentAnalysis')->name('school.wide.payment.analysis')->where(['action' => 'view|download','format' => 'pdf|word' ]);
    


    Route::get('/viewstudent/{schoolclassid}/{termid}/{sessionid}', [ViewStudentController::class, 'show'])->name('viewstudent');
 
    Route::get('/studentreports', [ViewStudentReportController::class, 'index'])->name('studentreports.index');
    Route::get('/studentresult/{id}/{schoolclassid}/{sessionid}/{termid}', [ViewStudentReportController::class, 'studentresult'])->name('studentresult');
    Route::get('/student-reports/registered-classes', [ViewStudentReportController::class, 'registeredClasses'])->name('studentreports.registeredClasses');
    Route::get('/class-broadsheet/{schoolclassid}/{sessionid}/{termid}', [ViewStudentReportController::class, 'classBroadsheet'])->name('classbroadsheet');
    // Route::get('/studentreports/export/{id}/{schoolclassid}/{sessionid}/{termid}', [ViewStudentReportController::class, 'exportStudentResultPdf'])->name('studentreports.exportStudentResultPdf');
    Route::match(['get', 'post'], '/studentreports/export-class-results-pdf', [ViewStudentReportController::class, 'exportClassResultsPdf'])->name('studentreports.exportClassResultsPdf');
    


    Route::get('/studentmockreports', [ViewStudentMockReportController::class, 'index'])->name('studentmockreports.index');
  
    // Display individual student mock result
    Route::get('/studentmockresult/{id}/{schoolclassid}/{sessionid}/{termid}', [ViewStudentMockReportController::class, 'studentmockresult'])->name('studentmockreports.studentmockresult');

    // Fetch registered classes for a session
    Route::get('/registered-classes', [ViewStudentMockReportController::class, 'registeredClasses'])->name('studentmockreports.registeredClasses');

    // Display class broadsheet
    Route::get('/class-broadsheet/{schoolclassid}/{sessionid}/{termid}', [ViewStudentMockReportController::class, 'classBroadsheet'])->name('studentmockreports.classBroadsheet');


    // Export class mock results as PDF
    Route::post('/export-class-results-pdf', [ViewStudentMockReportController::class, 'exportClassMockResultsPdf'])->name('studentmockreports.exportClassMockResultsPdf');




    
    Route::resource('subjectoperation', SubjectOperationController::class);
    Route::get('/subjects', [SubjectOperationController::class, 'index'])->name('subjects.index');

    Route::post('/subjectregistration', [SubjectOperationController::class, 'store'])->name('subjects.store');
    Route::get('/subjectoperation/subjectinfo/{id}/{schoolclassid}/{termid}/{sessionid}', [SubjectOperationController::class, 'subjectinfo'])->name('subjects.subjectinfo');
    
    Route::delete('/subjects/registered-classes', [SubjectOperationController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/subjects/registered-classes', [SubjectOperationController::class, 'getRegisteredClasses'])->name('subjects.registered-classes');
    // Route for batch unregistration
    Route::post('/subjectregistration/destroy', [SubjectOperationController::class, 'destroy'])->name('subjectregistration.destroy');

    // Add (or update) your route for the batch endpoint:
    Route::post('/subjectregistration/batch', [SubjectOperationController::class, 'batchRegister'])->name('subjectregistration.batch');



    Route::get('/viewresults/{id}/{schoolclassid}/{sessid}/{termid}', [StudentResultsController::class, 'viewresults']);


    Route::get('/studentpersonalityprofile/{id}/{schoolclassid}/{sessid}/{termid}', [StudentpersonalityprofileController::class, 'studentpersonalityprofile'])->name('myclass.studentpersonalityprofile');
    Route::post('save', [StudentpersonalityprofileController::class, 'save'])->name('studentpersonalityprofile.save');

    Route::get('/classbroadsheet/{schoolclassid}/{sessionid}/{termid}', [ClassBroadsheetController::class, 'classBroadsheet'])->name('classbroadsheet.viewcomments');
    Route::patch('/classbroadsheet/{schoolclassid}/{sessionid}/{termid}/comments', [ClassBroadsheetController::class, 'updateComments'])->name('classbroadsheet.updateComments');
    

    // compulsory subject class
    Route::resource('compulsorysubjectclass', CompulsorySubjectClassController::class);

    //principal's comment
    Route::resource('principalscomment', PrincipalsCommentController::class);

    //subject vettings
    Route::resource('subjectvetting', SubjectVettingController::class);
    Route::resource('mocksubjectvetting', MockSubjectVettingController::class);

    // my subject vettings
    Route::get('/mysubjectvettings', [MySubjectVettingsController::class, 'index'])->name('mysubjectvettings.index');
    Route::get('/mysubjectvettings/classbroadsheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MySubjectVettingsController::class, 'classBroadsheet'])->name('mysubjectvettings.classbroadsheet');
    Route::get('/mysubjectvettings/classbroadsheetmock/{schoolclassid}/{sessionid}/{termid}', [MySubjectVettingsController::class, 'classBroadsheetMock'])->name('mysubjectvettings.classbroadsheetmock');
    Route::put('/mysubjectvettings/{id}', [MySubjectVettingsController::class, 'update'])->name('mysubjectvettings.update');
    Route::put('/mysubjectvettings/{id}', [MySubjectVettingsController::class, 'updateMock'])->name('mysubjectvettings.updatemock');


    Route::get('/mymocksubjectvettings', [MyMockSubjectVettingsController::class, 'index'])->name('mymocksubjectvettings.index');
    Route::get('/mymocksubjectvettings/classbroadsheet/{schoolclassid}/{subjectclassid}/{staffid}/{termid}/{sessionid}', [MyMockSubjectVettingsController::class, 'classBroadsheet'])->name('mymocksubjectvettings.classbroadsheet');
    Route::post('/mymocksubjectvettings/update-vetted-status', [MyMockSubjectVettingsController::class, 'updateVettedStatus'])->name('mymocksubjectvettings.update-vetted-status');
    Route::get('/mymocksubjectvettings/results', [MyMockSubjectVettingsController::class, 'results'])->name('mymocksubjectvettings.results');
    Route::put('/mymocksubjectvettings/{id}', [MyMockSubjectVettingsController::class, 'update'])->name('mymocksubjectvettings.update');

    

    Route::post('/broadsheets/update-vetted-status', [MySubjectVettingsController::class, 'updateVettedStatus'])->name('broadsheets.update-vetted-status');

    //school information
    Route::resource('school-information', SchoolInformationController::class);

    





    Route::get('image-upload', [StaffImageUploadController::class, 'imageUpload'])->name('image.upload');
    Route::post('image-upload', [StaffImageUploadController::class, 'imageUploadPost'])->name('image.upload.post');

    Route::resource('exams', ExamController::class);

    Route::resource('questions', QuestionController::class);
    Route::get('/questions/{question}/details', [QuestionController::class, 'showDetails']);
    Route::get('/{question}/details', [QuestionController::class, 'details'])->name('questions.details');
    Route::get('/questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');

    Route::resource('cbt', CBTController::class);
    Route::get('/cbt/{examid}/takecbt', [CBTController::class, 'takeCBT'])->name('cbt.take');
    Route::post('/cbt/submit', [CBTController::class, 'submit'])->name('cbt.submit');
});