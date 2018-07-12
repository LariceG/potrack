<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['create-employee'] 			= "UserController/createNewEmployee";
$route['login'] 					= "UserController/login";
$route['userdata'] 					= "UserController/userdata";
$route['getcompanyclients']			= "UserController/getcompanyclients";
$route['getcompanyclientsCount']			= "UserController/getcompanyclientsCount";

$route['addclient']					= "ClientController/addclient";
$route['clientdata']				= "ClientController/clientdata";
$route['delete']				    = "ClientController/delete";
$route['delete-multiple']				    = "ClientController/deleteMultiple";
$route['update-profile-image']				    = "ClientController/uploadProfileImage";
$route['update']				    = "ClientController/update";
$route['insert']				    = "ClientController/insert";
$route['get']				    = "ClientController/get";
$route['get-table']				    = "ClientController/getTable";


$route['send-client-mail']				      = "ClientController/sendClientMail";
$route['send-bulk-mail']				        = "ClientController/sendBulkMail";
$route['submit-call-form']				      = "ClientController/submitCallForm";
$route['client-claims']				          = "ClientController/clientClaims";
$route['update-claim-form']				      = "ClientController/updateClaimForm";
$route['save-layout']				            = "ClientController/SaveLayout";
$route['get-layout']				            = "ClientController/getLayout";
$route['claim-list']				            = "ClientController/ClaimList";
$route['all-claim-list']				            = "ClientController/allCLaimList";


$route['client-label-pdf']				      = "ClientController/client_label_pdf";
$route['claim-label-pdf']				         = "ClientController/claim_label_pdf";


$route['add-employee']				         = "EmployeeController/addEmployee";
$route['update-employee']				         = "EmployeeController/updateEmployee";
$route['list-employees']				         = "EmployeeController/ListEmployee";
$route['all-employees']				         = "EmployeeController/allEmployee";


$route['submit-task']				       = "TaskController/submitTask";
$route['list-task']				         = "TaskController/ListTask";




$route['create-customer'] 			= "UserController/createCustomer";
$route["forgot-password"]			= "Welcome/forgotPassword";
$route["change-password"]			= "Welcome/changePassword";

$route['create-car'] 				= "UserController/createCar";
$route['search-customer'] 			= "UserController/searchCustomers";
$route['latest-customer'] 			= "UserController/latestCustomers";
$route['select-car'] 				= "UserController/selectCars";

$route['request-rejected'] 			= "UserController/partRequestRejected";

$route['make-request']				= "UserController/makeNewRequest";

$route['get-employeelist'] 			= "UserController/getAllEmployee";

$route['car-vin'] 					= "EdmundController/getCarDetailByVin";

$route['booking-request']   		= "UserController/bookingRequest";
$route['register-employee'] 		= "UserController/registerEmployee";
$route['all-departments'] 			= "Welcome/getDepartments";
$route['all-designations'] 			= "Welcome/getDesignations";
$route['bookings-by-department'] 	= "UserController/getBookingsByDepartment";
$route['calander-bookings'] 		= "UserController/calandarBookings";
$route['booking-worklog'] 			= "UserController/addBookingWorklog";
$route['booking-workloghistory'] 	= "UserController/bookingWorkLog";
$route['list-customers-employees'] 	= "UserController/bookingWorkLog";
$route['getallpendingbookings'] 	= "UserController/getAllPendingBookings";
$route['add-collision'] 			= "UserController/addCollision";
$route['add-collision-step2'] 		= "UserController/addCollisionStep2";

$route['listcarrentcompanies'] 		= "UserController/listCarRentCompanies";
$route['addrentedcar'] 				= "UserController/addrentedcar";
$route['deleteBooking'] 			= "UserController/deleteBooking";
$route['customerhistory'] 			= "UserController/customerHistory";
$route['update-booking-request']   	= "UserController/updatebookingRequest";
$route['completed-bookings']   		= "UserController/completedBookings";
$route['update-booking-status']   	= "UserController/updatebookingStatus";
$route['all-car-requests']   		= "UserController/allCarRequests";
$route['upload-booking-image']   	= "UserController/uploadBookingImage";
$route['booking-images']   			= "UserController/bookingImages";
$route['add-note']   				= "UserController/addNote";
$route['get-notes']   				= "UserController/getNotes";
$route['customer-filter']   		= "UserController/customerFilter";
$route['employee-car-requests']   	= "UserController/employeeCarRequests";
$route['car-audit']   				= "UserController/carAudit";
$route['part-history']   			= "UserController/partHistory";
$route['upload-invoice-pdf']   		= "UserController/uploadInvoicePDF";
$route['parts-by-booking']   		= "UserController/partsByBooking";
$route['generate-work-order']   	= "UserController/generateWorkOrder";
$route['insurance-mis-parts']   	= "UserController/skyline_insurancemisparts";
$route['collision-list']   			= "UserController/collisionList";
$route['collision-details']   		= "UserController/collisionDetails";
$route['zip-files']   				= "UserController/zipFiles";
$route['add-schedule']   			= "UserController/addSchedule";
$route['search-all']   				= "UserController/searchall";
$route['collision-final-visit']   	= "UserController/collisionFinalVisit";
$route['upload-collision-estimate'] = "UserController/uploadCollisionEstimate";
$route['collision-work-assign'] 	= "UserController/collisionWorkassign";
$route['generate-collision-workorder'] 	= "UserController/generateCollisionWorkOrder";
$route['get-collision-workorder'] 	= "UserController/getCollisionWorkOrder";
$route['change-collision-status'] 	= "UserController/changeCollisionStatus";

$route['add-collision-wi'] 	= "UserController/addCollisionWithoutInsurance";
