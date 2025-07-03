@extends('layouts.app')
@push('page-title')
    Distributor Appointment Form
@endpush
    
    @push('styles')

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8; /* Light gray background */
        }
        /* Custom styling for active step indicator */
        .step-indicator.active .circle {
            background-color: #3b82f6; /* Blue for active */
            color: white;
        }
        .step-indicator.active .line {
            background-color: #3b82f6;
        }
        .step-indicator.completed .circle {
            background-color: #10b981; /* Green for completed */
            color: white;
        }
        .step-indicator.completed .line {
            background-color: #10b981;
        }
        /* Hide all steps by default */
        .form-step {
            display: none;
        }
        /* Ensure the current step is displayed */
        .form-step.active {
            display: block;
        }
        /* Style for required field asterisks */
        .required::after {
            content: '*';
            color: #ef4444; /* Red color */
            margin-left: 4px;
        }
    </style>
    @endpush
@section('content')


    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 md:p-10 max-w-4xl w-full mx-auto my-8">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Distributor Appointment Form</h1>

        <!-- Step Indicator/Progress Bar -->
        <div class="flex justify-between items-center mb-8 relative">
            <!-- Line connecting steps -->
            <div class="absolute inset-x-0 top-1/2 h-1 bg-gray-200 transform -translate-y-1/2 mx-auto" style="width: calc(100% - 6rem);"></div>
            <div id="progress-line" class="absolute inset-x-0 top-1/2 h-1 bg-blue-500 transform -translate-y-1/2 mx-auto transition-all duration-300 ease-in-out" style="width: 0;"></div>

            <!-- Step 1 -->
            <div class="step-indicator flex flex-col items-center flex-1 z-10" data-step="1">
                <div class="circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-gray-200 transition-all duration-300 ease-in-out">1</div>
                <span class="text-sm mt-2 text-center text-gray-600 hidden sm:block">Entity Details</span>
            </div>
            <!-- Line between 1 and 2 -->
            <div class="step-indicator flex flex-col items-center flex-1 z-10" data-step="2">
                <div class="circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-gray-200 transition-all duration-300 ease-in-out">2</div>
                <span class="text-sm mt-2 text-center text-gray-600 hidden sm:block">Ownership & Bank</span>
            </div>
            <!-- Line between 2 and 3 -->
            <div class="step-indicator flex flex-col items-center flex-1 z-10" data-step="3">
                <div class="circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-gray-200 transition-all duration-300 ease-in-out">3</div>
                <span class="text-sm mt-2 text-center text-gray-600 hidden sm:block">Distribution & Plan</span>
            </div>
            <!-- Line between 3 and 4 -->
            <div class="step-indicator flex flex-col items-center flex-1 z-10" data-step="4">
                <div class="circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-gray-200 transition-all duration-300 ease-in-out">4</div>
                <span class="text-sm mt-2 text-center text-gray-600 hidden sm:block">Financial Info</span>
            </div>
            <!-- Line between 4 and 5 -->
            <div class="step-indicator flex flex-col items-center flex-1 z-10" data-step="5">
                <div class="circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-gray-200 transition-all duration-300 ease-in-out">5</div>
                <span class="text-sm mt-2 text-center text-gray-600 hidden sm:block">Questionnaire</span>
            </div>
            <!-- Line between 5 and 6 -->
            <div class="step-indicator flex flex-col items-center flex-1 z-10" data-step="6">
                <div class="circle w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-500 bg-gray-200 transition-all duration-300 ease-in-out">6</div>
                <span class="text-sm mt-2 text-center text-gray-600 hidden sm:block">Declaration</span>
            </div>
        </div>

        <!-- The Form Itself -->
        <form id="distributorForm" class="space-y-6">

            <!-- Step 1: General & Entity Information -->
            <div class="form-step active" data-step="1">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Step 1: General & Entity Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="territory" class="block text-sm font-medium text-gray-700 mb-1 required">Territory</label>
                        <input type="text" id="territory" name="territory" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="cropVertical" class="block text-sm font-medium text-gray-700 mb-1 required">Crop Vertical</label>
                        <input type="text" id="cropVertical" name="cropVertical" value="Field Crop" readonly class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm">
                    </div>
                    <div>
                        <label for="zone" class="block text-sm font-medium text-gray-700 mb-1 required">Zone</label>
                        <input type="text" id="zone" name="zone" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 mb-1 required">District</label>
                        <input type="text" id="district" name="district" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1 required">State</label>
                        <input type="text" id="state" name="state" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Entity Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="establishmentName" class="block text-sm font-medium text-gray-700 mb-1 required">Name of the Establishment “Distributor”</label>
                            <input type="text" id="establishmentName" name="establishmentName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="establishmentType" class="block text-sm font-medium text-gray-700 mb-1 required">Type/Nature of Establishment</label>
                            <select id="establishmentType" name="establishmentType" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                <option value="">Select Type</option>
                                <option value="Individual Person">Individual Person</option>
                                <option value="Sole Proprietorship">Sole Proprietorship</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Limited Liability Partnership">Limited Liability Partnership</option>
                                <option value="Company (Private/Public)">Company (Private/Public)</option>
                                <option value="Cooperative Society">Cooperative Society</option>
                                <option value="Trust">Trust</option>
                                <option value="HUF">HUF</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Business Place/Shop Address</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="shopHouseNo" class="block text-sm font-medium text-gray-700 mb-1 required">House No. / Building</label>
                                <input type="text" id="shopHouseNo" name="shopHouseNo" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="shopLandmark" class="block text-sm font-medium text-gray-700 mb-1">Landmark</label>
                                <input type="text" id="shopLandmark" name="shopLandmark" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="shopCity" class="block text-sm font-medium text-gray-700 mb-1 required">City</label>
                                <input type="text" id="shopCity" name="shopCity" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="shopDistrict" class="block text-sm font-medium text-gray-700 mb-1 required">District</label>
                                <input type="text" id="shopDistrict" name="shopDistrict" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="shopState" class="block text-sm font-medium text-gray-700 mb-1 required">State</label>
                                <input type="text" id="shopState" name="shopState" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="shopPincode" class="block text-sm font-medium text-gray-700 mb-1 required">Pincode</label>
                                <input type="text" id="shopPincode" name="shopPincode" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="\d{6}" title="Pincode must be 6 digits">
                            </div>
                            <div class="md:col-span-2">
                                <label for="shopCountry" class="block text-sm font-medium text-gray-700 mb-1 required">Country</label>
                                <input type="text" id="shopCountry" name="shopCountry" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mobileNumber" class="block text-sm font-medium text-gray-700 mb-1 required">Mobile Number</label>
                            <input type="tel" id="mobileNumber" name="mobileNumber" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[0-9]{10}" title="Mobile number must be 10 digits">
                        </div>
                        <div>
                            <label for="emailAddress" class="block text-sm font-medium text-gray-700 mb-1 required">E-mail address</label>
                            <input type="email" id="emailAddress" name="emailAddress" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="panNumber" class="block text-sm font-medium text-gray-700 mb-1 required">PAN Number</label>
                            <input type="text" id="panNumber" name="panNumber" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" title="PAN must be 10 alphanumeric characters (e.g., ABCDE1234F)">
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="block text-sm font-medium text-gray-700 required">GST Applicable?</label>
                            <div class="flex items-center">
                                <input type="radio" id="gstYes" name="gstApplicable" value="Yes" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" required>
                                <label for="gstYes" class="ml-2 block text-sm text-gray-900">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="gstNo" name="gstApplicable" value="No" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" required>
                                <label for="gstNo" class="ml-2 block text-sm text-gray-900">No</label>
                            </div>
                        </div>
                        <div id="gstDetails" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="gstNumber" class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                                <input type="text" id="gstNumber" name="gstNumber" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}" title="GST number must be 15 characters (e.g., 22ABCDE1234F1Z5)">
                            </div>
                            <div>
                                <label for="gstValidity" class="block text-sm font-medium text-gray-700 mb-1">GST Validity</label>
                                <input type="date" id="gstValidity" name="gstValidity" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="seedLicenseNumber" class="block text-sm font-medium text-gray-700 mb-1 required">Seed License Number</label>
                            <input type="text" id="seedLicenseNumber" name="seedLicenseNumber" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="seedLicenseValidity" class="block text-sm font-medium text-gray-700 mb-1 required">Seed License Validity</label>
                            <input type="date" id="seedLicenseValidity" name="seedLicenseValidity" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Ownership & Bank Details -->
            <div class="form-step" data-step="2">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Step 2: Ownership & Bank Details</h2>

                <!-- Sole Proprietorship Section (Conditional) -->
                <div id="soleProprietorshipSection" class="hidden bg-blue-50 p-4 rounded-md mb-6 shadow-sm">
                    <h3 class="text-lg font-medium text-blue-800 mb-4">In Case of Sole Proprietorship</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="proprietorName" class="block text-sm font-medium text-gray-700 mb-1 required">Name of Proprietor</label>
                            <input type="text" id="proprietorName" name="proprietorName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="proprietorDOB" class="block text-sm font-medium text-gray-700 mb-1 required">Date of Birth</label>
                            <input type="date" id="proprietorDOB" name="proprietorDOB" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label for="proprietorFatherHusbandName" class="block text-sm font-medium text-gray-700 mb-1 required">Father’s/ Husband’s Name</label>
                            <input type="text" id="proprietorFatherHusbandName" name="proprietorFatherHusbandName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Permanent Address of Proprietor</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="proprietorHouseNo" class="block text-sm font-medium text-gray-700 mb-1 required">House No. / Building</label>
                                <input type="text" id="proprietorHouseNo" name="proprietorHouseNo" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="proprietorLandmark" class="block text-sm font-medium text-gray-700 mb-1">Landmark</label>
                                <input type="text" id="proprietorLandmark" name="proprietorLandmark" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="proprietorCity" class="block text-sm font-medium text-gray-700 mb-1 required">City</label>
                                <input type="text" id="proprietorCity" name="proprietorCity" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="proprietorDistrict" class="block text-sm font-medium text-gray-700 mb-1 required">District</label>
                                <input type="text" id="proprietorDistrict" name="proprietorDistrict" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="proprietorState" class="block text-sm font-medium text-gray-700 mb-1 required">State</label>
                                <input type="text" id="proprietorState" name="proprietorState" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="proprietorPincode" class="block text-sm font-medium text-gray-700 mb-1 required">Pincode</label>
                                <input type="text" id="proprietorPincode" name="proprietorPincode" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" pattern="\d{6}" title="Pincode must be 6 digits">
                            </div>
                            <div class="md:col-span-2">
                                <label for="proprietorCountry" class="block text-sm font-medium text-gray-700 mb-1 required">Country</label>
                                <input type="text" id="proprietorCountry" name="proprietorCountry" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="proprietorMobile" class="block text-sm font-medium text-gray-700 mb-1 required">Mobile Number</label>
                                <input type="tel" id="proprietorMobile" name="proprietorMobile" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" pattern="[0-9]{10}" title="Mobile number must be 10 digits">
                            </div>
                            <div>
                                <label for="proprietorEmail" class="block text-sm font-medium text-gray-700 mb-1 required">E-mail address</label>
                                <input type="email" id="proprietorEmail" name="proprietorEmail" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Partnership Section (Conditional and Dynamic) -->
                <div id="partnershipSection" class="hidden bg-green-50 p-4 rounded-md mb-6 shadow-sm">
                    <h3 class="text-lg font-medium text-green-800 mb-4">In Case of Partnership (Can be Multiple)</h3>
                    <div id="partnersContainer" class="space-y-4">
                        <!-- Partner rows will be added here by JS -->
                    </div>
                    <button type="button" id="addPartnerBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Add New Partner
                    </button>
                </div>

                <!-- Authorized Person Section (Conditional and Dynamic) -->
                <div id="authorizedPersonSection" class="hidden bg-yellow-50 p-4 rounded-md mb-6 shadow-sm">
                    <h3 class="text-lg font-medium text-yellow-800 mb-4">Directors/Trustees/Authorized Persons (Can be Multiple)</h3>
                    <div id="authorizedPersonsContainer" class="space-y-4">
                        <!-- Authorized Person rows will be added here by JS -->
                    </div>
                    <button type="button" id="addAuthorizedPersonBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        Add New Director/Authorized Person
                    </button>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Bank Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bankName" class="block text-sm font-medium text-gray-700 mb-1 required">Name of the Bank</label>
                            <input type="text" id="bankName" name="bankName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="bankAccountHolder" class="block text-sm font-medium text-gray-700 mb-1 required">Name of Bank Account holder</label>
                            <input type="text" id="bankAccountHolder" name="bankAccountHolder" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="accountNumber" class="block text-sm font-medium text-gray-700 mb-1 required">Account Number</label>
                            <input type="text" id="accountNumber" name="accountNumber" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[0-9]+" title="Account number must contain only digits">
                        </div>
                        <div>
                            <label for="ifscCode" class="block text-sm font-medium text-gray-700 mb-1 required">IFSC code of Bank</label>
                            <input type="text" id="ifscCode" name="ifscCode" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[A-Z]{4}0[A-Z0-9]{6}" title="IFSC code must be 11 alphanumeric characters (e.g., ABCD0123456)">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Distribution & Business Plan -->
            <div class="form-step" data-step="3">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Step 3: Distribution & Business Plan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="areaToBeCovered" class="block text-sm font-medium text-gray-700 mb-1 required">Area to be covered</label>
                        <input type="text" id="areaToBeCovered" name="areaToBeCovered" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="appointmentType" class="block text-sm font-medium text-gray-700 mb-1 required">Appointment Type</label>
                        <select id="appointmentType" name="appointmentType" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            <option value="">Select Type</option>
                            <option value="New Area">New Area</option>
                            <option value="Replacement of an existing Distributor">Replacement of an existing Distributor</option>
                            <option value="Addition in current distributor area">Addition in current distributor area</option>
                        </select>
                    </div>
                </div>
                <div id="replacementDetails" class="hidden mt-6 bg-red-50 p-4 rounded-md shadow-sm">
                    <h3 class="text-lg font-medium text-red-800 mb-4">Replacement Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="replacementReason" class="block text-sm font-medium text-gray-700 mb-1 required">Reason for Replacement</label>
                            <textarea id="replacementReason" name="replacementReason" rows="3" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                        <div>
                            <label for="outstandingRecoveryCommitment" class="block text-sm font-medium text-gray-700 mb-1 required">Commitment to Recover Outstanding (Previous Firm Name & Code)</label>
                            <textarea id="outstandingRecoveryCommitment" name="outstandingRecoveryCommitment" rows="3" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div id="newAreaDetails" class="hidden mt-6 bg-blue-50 p-4 rounded-md shadow-sm">
                    <h3 class="text-lg font-medium text-blue-800 mb-4">New Area Details</h3>
                    <div>
                        <label for="earlierDistributorNewArea" class="block text-sm font-medium text-gray-700 mb-1 required">Who was the earlier distributor covering that area?</label>
                        <input type="text" id="earlierDistributorNewArea" name="earlierDistributorNewArea" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-700 mb-4 mt-6">Business Plan (Next Two Years)</h3>
                <div id="businessPlanContainer" class="space-y-4">
                    <!-- Business plan rows will be added here by JS -->
                    <div class="business-plan-row grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-md shadow-sm">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Crop</label>
                            <input type="text" name="businessPlanCrop" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">FY 2025-26 (in MT)</label>
                            <input type="number" name="businessPlanFY25" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">FY 2026-27 (in MT)</label>
                            <input type="number" name="businessPlanFY26" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required min="0">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-row-btn w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" id="addBusinessPlanRowBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Add Crop Plan
                </button>
            </div>

            <!-- Step 4: Financial & Operational Information -->
            <div class="form-step" data-step="4">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Step 4: Financial & Operational Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="netWorth" class="block text-sm font-medium text-gray-700 mb-1 required">Net Worth (Previous FY)</label>
                        <input type="text" id="netWorth" name="netWorth" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., ₹3 Crores" required>
                    </div>
                    <div>
                        <label for="shopOwnership" class="block text-sm font-medium text-gray-700 mb-1 required">Shop Ownership</label>
                        <select id="shopOwnership" name="shopOwnership" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            <option value="">Select Ownership</option>
                            <option value="Owned">Owned</option>
                            <option value="Rented">Rented</option>
                            <option value="Leased">Leased</option>
                        </select>
                    </div>
                    <div>
                        <label for="godownArea" class="block text-sm font-medium text-gray-700 mb-1 required">Godown Area (Sq. Ft.)</label>
                        <input type="number" id="godownArea" name="godownArea" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" min="0" required>
                    </div>
                    <div>
                        <label for="godownOwnership" class="block text-sm font-medium text-gray-700 mb-1 required">Godown Ownership</label>
                        <select id="godownOwnership" name="godownOwnership" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            <option value="">Select Ownership</option>
                            <option value="Owned">Owned</option>
                            <option value="Rented">Rented</option>
                            <option value="Leased">Leased</option>
                        </select>
                    </div>
                    <div>
                        <label for="yearsInBusiness" class="block text-sm font-medium text-gray-700 mb-1 required">Years in Business</label>
                        <input type="number" id="yearsInBusiness" name="yearsInBusiness" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" min="0" required>
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-700 mb-4 mt-6">Annual Turnover</h3>
                <div id="annualTurnoverContainer" class="space-y-4">
                    <!-- Annual turnover rows will be added here by JS -->
                    <div class="annual-turnover-row grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-md shadow-sm">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Financial Year</label>
                            <input type="text" name="annualTurnoverFY" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., FY 2024–25" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Net Turnover</label>
                            <input type="text" name="annualTurnoverAmount" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., ₹3 Crores" required>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-row-btn w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" id="addAnnualTurnoverRowBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Add Annual Turnover
                </button>

                <h3 class="text-lg font-medium text-gray-700 mb-4 mt-6">Existing Distributorships (Agro Inputs)</h3>
                <div id="existingDistributorshipsContainer" class="space-y-4">
                    <!-- Existing distributorship rows will be added here by JS -->
                    <div class="existing-distributorship-row flex flex-col md:flex-row items-end gap-4 bg-gray-50 p-4 rounded-md shadow-sm">
                        <div class="flex-grow w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Company Name</label>
                            <input type="text" name="existingCompanyName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <button type="button" class="remove-row-btn w-full md:w-auto px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" id="addExistingDistributorshipBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Add Existing Distributorship
                </button>

                <h3 class="text-lg font-medium text-gray-700 mb-4 mt-6">Financial Status & Other Banking Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="financialStatus" class="block text-sm font-medium text-gray-700 mb-1 required">Financial Status</label>
                        <select id="financialStatus" name="financialStatus" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            <option value="">Select Status</option>
                            <option value="Good">Good</option>
                            <option value="Very good">Very good</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Average">Average</option>
                        </select>
                    </div>
                    <div>
                        <label for="retailersDealtWith" class="block text-sm font-medium text-gray-700 mb-1 required">No. of Retailers Dealt With</label>
                        <input type="number" id="retailersDealtWith" name="retailersDealtWith" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" min="0" required>
                    </div>
                    <div>
                        <label for="bankNameFinancial" class="block text-sm font-medium text-gray-700 mb-1 required">Bank Name</label>
                        <input type="text" id="bankNameFinancial" name="bankNameFinancial" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="relationshipDuration" class="block text-sm font-medium text-gray-700 mb-1 required">Relationship Duration (Years)</label>
                        <input type="number" id="relationshipDuration" name="relationshipDuration" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" min="0" required>
                    </div>
                    <div>
                        <label for="accountTypeFinancial" class="block text-sm font-medium text-gray-700 mb-1 required">Account Type</label>
                        <select id="accountTypeFinancial" name="accountTypeFinancial" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                            <option value="">Select Type</option>
                            <option value="Savings">Savings</option>
                            <option value="Current">Current</option>
                            <option value="OD">OD</option>
                        </select>
                    </div>
                    <div>
                        <label for="accountNumberFinancial" class="block text-sm font-medium text-gray-700 mb-1 required">Account Number</label>
                        <input type="text" id="accountNumberFinancial" name="accountNumberFinancial" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[0-9]+" title="Account number must contain only digits">
                    </div>
                    <div>
                        <label for="odLimit" class="block text-sm font-medium text-gray-700 mb-1 required">OD Limit</label>
                        <input type="text" id="odLimit" name="odLimit" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., ₹20 Lakhs" required>
                    </div>
                    <div>
                        <label for="odSecurity" class="block text-sm font-medium text-gray-700 mb-1 required">OD Security</label>
                        <input type="text" id="odSecurity" name="odSecurity" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., Godown & Land" required>
                    </div>
                </div>

                <h3 class="text-lg font-medium text-gray-700 mb-4 mt-6">Security Deposit Details</h3>
                <div id="securityDepositContainer" class="space-y-4">
                    <!-- Security Deposit rows will be added here by JS -->
                    <div class="security-deposit-row grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-md shadow-sm">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Date</label>
                            <input type="date" name="depositDate" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Amount</label>
                            <input type="text" name="depositAmount" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., ₹50,000" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Mode of Payment</label>
                            <input type="text" name="depositMode" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., NEFT/Cheque" required>
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-row-btn w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Remove
                            </button>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">If NEFT/Online, Reference No.</label>
                            <input type="text" name="depositReferenceNo" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cheque Number 1</label>
                            <input type="text" name="chequeNumber1" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cheque Number 2</label>
                            <input type="text" name="chequeNumber2" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <p class="md:col-span-4 text-xs text-gray-500 mt-1">(Cheques should be from operative bank account)</p>
                    </div>
                </div>
                <button type="button" id="addSecurityDepositBtn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Add Security Deposit Entry
                </button>
            </div>

            <!-- Step 5: Questions (Yes/No with Specification) -->
            <div class="form-step" data-step="5">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Step 5: Answer the following questions carefully</h2>

                <!-- Question Template (will be dynamically created or repeated) -->
                <div id="questionsContainer" class="space-y-6">
                    <!-- Questions will be populated here by JS -->
                </div>
            </div>

            <!-- Step 6: Declaration -->
            <div class="form-step" data-step="6">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Step 6: Declaration & Finalization</h2>
                <div class="mb-6 bg-blue-50 p-4 rounded-md shadow-sm">
                    <p class="text-gray-800 text-sm md:text-base">
                        I/We hereby solemnly affirm the truthfulness and completeness of the foregoing information and agree to be bound by all terms and conditions of the appointment/agreement with the Company.
                        I/We undertake to inform the company of any changes to the information provided herein within a period of 7 days, accompanied by relevant documentation.
                    </p>
                </div>
                <div class="mb-6 flex items-center">
                    <input type="checkbox" id="declarationAgree" name="declarationAgree" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                    <label for="declarationAgree" class="ml-2 block text-base text-gray-900 required">I/We agree to the above declaration.</label>
                </div>
                <div>
                    <label for="formFilledBy" class="block text-sm font-medium text-gray-700 mb-1 required">Form Filled by (Your Name)</label>
                    <input type="text" id="formFilledBy" name="formFilledBy" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between pt-8 border-t border-gray-200 mt-8">
                <button type="button" id="prevBtn" class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out hidden">
                    Previous
                </button>
                <button type="button" id="nextBtn" class="ml-auto px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                    Next
                </button>
                <button type="submit" id="submitBtn" class="ml-auto px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out hidden">
                    Submit
                </button>
            </div>
        </form>

        <!-- Success/Error Modal -->
        <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full mx-4">
                <h3 id="modalTitle" class="text-xl font-semibold mb-4"></h3>
                <p id="modalMessage" class="text-gray-700 mb-6"></p>
                <button id="modalCloseBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Close</button>
            </div>
        </div>

    </div>

    
@endsection

@push('scripts')
    <script>
        const form = document.getElementById('distributorForm');
        const steps = Array.from(document.querySelectorAll('.form-step'));
        const stepIndicators = Array.from(document.querySelectorAll('.step-indicator'));
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressLine = document.getElementById('progress-line');

        let currentStep = 0; // 0-indexed

        // Modal elements
        const statusModal = document.getElementById('statusModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalCloseBtn = document.getElementById('modalCloseBtn');

        // Questions data from the document
        const questionsData = [
            "Whether the Distributor is an Agent/Distributor of any other Company?",
            "Whether the Distributor has any sister concern or affiliated entity other than the one applying for this distributorship?",
            "Whether the Distributor is acting as an Agent/Distributor for any other entities in the distribution of similar crops?",
            "Whether the Distributor is a partner, relative, or otherwise associated with any entity engaged in the business of agro inputs?",
            "Whether the Distributor has previously acted as an Agent/Distributor of VNR Seeds and is again applying for a Distributorship?",
            "Whether any disputed dues are payable by the Distributor to the other Company/Bank/Financial Institution?",
            "Whether the Distributor has ceased to be Agent/Distributor of any other company in the last twelve months?",
            "Whether the Distributor’s relative is connected in any way with VNR Seeds and any other Seed Company?",
            "Whether the Distributor is involved in any other capacity with the Company apart from this application?",
            "Whether the Distributor has been referred by any Distributors or other parties associated with the Company?",
            "Whether the Distributor is currently marketing or selling products under its own brand name?",
            "Whether the Distributor has been employed in the agro-input industry at any point during the past 5 years?"
        ];

        /**
         * Shows the modal with a given title and message.
         * @param {string} title - The title of the modal.
         * @param {string} message - The message content of the modal.
         */
        function showModal(title, message) {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            statusModal.classList.remove('hidden');
        }

        /**
         * Hides the modal.
         */
        function hideModal() {
            statusModal.classList.add('hidden');
        }

        /**
         * Updates the visibility of form steps and navigation buttons.
         * Also updates the step indicators and progress line.
         */
        function updateFormVisibility() {
            // Hide all steps
            steps.forEach((step, index) => {
                step.classList.remove('active');
                if (index !== currentStep) {
                    step.querySelectorAll('input, select, textarea').forEach(input => {
                        input.setAttribute('tabindex', '-1'); // Make inactive inputs non-focusable
                    });
                }
            });

            // Show current step
            steps[currentStep].classList.add('active');
            steps[currentStep].querySelectorAll('input, select, textarea').forEach(input => {
                input.removeAttribute('tabindex'); // Make active inputs focusable
            });

            // Update navigation buttons
            prevBtn.classList.toggle('hidden', currentStep === 0);
            nextBtn.classList.toggle('hidden', currentStep === steps.length - 1);
            submitBtn.classList.toggle('hidden', currentStep !== steps.length - 1);

            // Update step indicators
            stepIndicators.forEach((indicator, index) => {
                indicator.classList.remove('active', 'completed');
                if (index === currentStep) {
                    indicator.classList.add('active');
                } else if (index < currentStep) {
                    indicator.classList.add('completed');
                }
            });

            // Update progress line width
            const progressPercentage = (currentStep / (steps.length - 1)) * 100;
            progressLine.style.width = `${progressPercentage}%`;
        }

        /**
         * Validates all required fields within the current active step.
         * @returns {boolean} True if all required fields are valid, false otherwise.
         */
        function validateCurrentStep() {
            const currentActiveStep = steps[currentStep];
            const requiredFields = currentActiveStep.querySelectorAll('[required]:not([disabled])');
            let isValid = true;
            let firstInvalidField = null;

            requiredFields.forEach(field => {
                let fieldValue = field.value.trim();
                let isFieldValid = true;

                // Handle specific field types/names for validation logic
                if (field.type === 'radio' && field.name === 'gstApplicable') {
                    // For radio buttons, check if any option in the group is checked
                    const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
                    isFieldValid = Array.from(radioGroup).some(radio => radio.checked);
                    if (!isFieldValid) {
                        // Highlight all radios in the group if none are checked
                        radioGroup.forEach(radio => radio.classList.add('border-red-500', 'ring-red-500'));
                    } else {
                        radioGroup.forEach(radio => radio.classList.remove('border-red-500', 'ring-red-500'));
                    }
                } else if (field.tagName === 'SELECT' && fieldValue === '') {
                    isFieldValid = false;
                } else if (field.type === 'checkbox') {
                    isFieldValid = field.checked;
                } else if (field.type === 'number' && field.min && parseFloat(fieldValue) < parseFloat(field.min)) {
                     isFieldValid = false; // Check min attribute for number inputs
                } else if (field.pattern && !new RegExp(field.pattern).test(fieldValue)) {
                    isFieldValid = false; // Validate against pattern
                } else if (fieldValue === '') {
                    isFieldValid = false;
                }


                if (!isFieldValid) {
                    isValid = false;
                    field.classList.add('border-red-500', 'ring-red-500'); // Add error styling
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.classList.remove('border-red-500', 'ring-red-500'); // Remove error styling
                }
            });

            // Specific validation for dynamic sections if they are visible
            if (currentStep === 1) { // Ownership details step (new Step 2)
                const establishmentType = document.getElementById('establishmentType').value;
                if (establishmentType === 'Sole Proprietorship' && document.getElementById('soleProprietorshipSection').classList.contains('hidden') === false) {
                    const proprietorRequiredFields = document.querySelectorAll('#soleProprietorshipSection [required]:not([disabled])');
                    proprietorRequiredFields.forEach(field => {
                        if (field.value.trim() === '' || (field.pattern && !new RegExp(field.pattern).test(field.value.trim()))) {
                            isValid = false;
                            field.classList.add('border-red-500', 'ring-red-500');
                            if (!firstInvalidField) firstInvalidField = field;
                        } else {
                            field.classList.remove('border-red-500', 'ring-red-500');
                        }
                    });
                } else if (establishmentType === 'Partnership' && document.getElementById('partnershipSection').classList.contains('hidden') === false) {
                    const partners = document.querySelectorAll('.partner-row');
                    if (partners.length === 0) {
                        isValid = false;
                        showModal('Validation Error', 'Please add at least one partner.');
                        return false;
                    }
                    partners.forEach(partnerRow => {
                        partnerRow.querySelectorAll('[required]:not([disabled])').forEach(field => {
                             if (field.value.trim() === '' || (field.pattern && !new RegExp(field.pattern).test(field.value.trim()))) {
                                isValid = false;
                                field.classList.add('border-red-500', 'ring-red-500');
                                if (!firstInvalidField) firstInvalidField = field;
                            } else {
                                field.classList.remove('border-red-500', 'ring-red-500');
                            }
                        });
                    });
                } else if (establishmentType === 'Company (Private/Public)' || establishmentType === 'Cooperative Society' || establishmentType === 'Trust' || establishmentType === 'HUF' || establishmentType === 'Limited Liability Partnership') {
                     const authorizedPersons = document.querySelectorAll('.authorized-person-row');
                    if (authorizedPersons.length === 0) {
                        isValid = false;
                        showModal('Validation Error', 'Please add at least one director/authorized person.');
                        return false;
                    }
                     authorizedPersons.forEach(authPersonRow => {
                        authPersonRow.querySelectorAll('[required]:not([disabled])').forEach(field => {
                            if (field.value.trim() === '' || (field.pattern && !new RegExp(field.pattern).test(field.value.trim()))) {
                                isValid = false;
                                field.classList.add('border-red-500', 'ring-red-500');
                                if (!firstInvalidField) firstInvalidField = field;
                            } else {
                                field.classList.remove('border-red-500', 'ring-red-500');
                            }
                        });
                    });
                }
            } else if (currentStep === 2) { // Distribution Details step (new Step 3)
                const appointmentType = document.getElementById('appointmentType').value;
                if (appointmentType === 'Replacement of an existing Distributor' && document.getElementById('replacementDetails').classList.contains('hidden') === false) {
                    const replacementFields = document.querySelectorAll('#replacementDetails [required]:not([disabled])');
                    replacementFields.forEach(field => {
                        if (field.value.trim() === '') {
                            isValid = false;
                            field.classList.add('border-red-500', 'ring-red-500');
                            if (!firstInvalidField) firstInvalidField = field;
                        } else {
                            field.classList.remove('border-red-500', 'ring-red-500');
                        }
                    });
                } else if (appointmentType === 'New Area' && document.getElementById('newAreaDetails').classList.contains('hidden') === false) {
                    const newAreaField = document.getElementById('earlierDistributorNewArea');
                    if (newAreaField && newAreaField.value.trim() === '') {
                        isValid = false;
                        newAreaField.classList.add('border-red-500', 'ring-red-500');
                        if (!firstInvalidField) firstInvalidField = newAreaField;
                    } else if (newAreaField) {
                        newAreaField.classList.remove('border-red-500', 'ring-red-500');
                    }
                }
                const businessPlanRows = document.querySelectorAll('.business-plan-row');
                 if (businessPlanRows.length === 0) {
                     isValid = false;
                     showModal('Validation Error', 'Please add at least one crop plan.');
                     return false;
                 }
                 businessPlanRows.forEach(row => {
                     row.querySelectorAll('[required]:not([disabled])').forEach(field => {
                         if (field.value.trim() === '' || (field.type === 'number' && parseFloat(field.value) < 0)) {
                             isValid = false;
                             field.classList.add('border-red-500', 'ring-red-500');
                             if (!firstInvalidField) firstInvalidField = field;
                         } else {
                             field.classList.remove('border-red-500', 'ring-red-500');
                         }
                     });
                 });
            } else if (currentStep === 3) { // Financial & Operational Information step (new Step 4)
                const annualTurnoverRows = document.querySelectorAll('.annual-turnover-row');
                if (annualTurnoverRows.length === 0) {
                    isValid = false;
                    showModal('Validation Error', 'Please add at least one annual turnover entry.');
                    return false;
                }
                annualTurnoverRows.forEach(row => {
                    row.querySelectorAll('[required]:not([disabled])').forEach(field => {
                        if (field.value.trim() === '') {
                            isValid = false;
                            field.classList.add('border-red-500', 'ring-red-500');
                            if (!firstInvalidField) firstInvalidField = field;
                        } else {
                            field.classList.remove('border-red-500', 'ring-red-500');
                        }
                    });
                });

                const existingDistRows = document.querySelectorAll('.existing-distributorship-row');
                if (existingDistRows.length === 0) {
                    isValid = false;
                    showModal('Validation Error', 'Please add at least one existing distributorship.');
                    return false;
                }
                existingDistRows.forEach(row => {
                    row.querySelectorAll('[required]:not([disabled])').forEach(field => {
                        if (field.value.trim() === '') {
                            isValid = false;
                            field.classList.add('border-red-500', 'ring-red-500');
                            if (!firstInvalidField) firstInvalidField = field;
                        } else {
                            field.classList.remove('border-red-500', 'ring-red-500');
                        }
                    });
                });

                const securityDepositRows = document.querySelectorAll('.security-deposit-row');
                if (securityDepositRows.length === 0) {
                    isValid = false;
                    showModal('Validation Error', 'Please add at least one security deposit entry.');
                    return false;
                }
                securityDepositRows.forEach(row => {
                    row.querySelectorAll('[required]:not([disabled])').forEach(field => {
                        if (field.value.trim() === '') {
                            isValid = false;
                            field.classList.add('border-red-500', 'ring-red-500');
                            if (!firstInvalidField) firstInvalidField = field;
                        } else {
                            field.classList.remove('border-red-500', 'ring-red-500');
                        }
                    });
                });
            } else if (currentStep === 4) { // Questions step (new Step 5)
                const questionGroups = document.querySelectorAll('.question-group');
                questionGroups.forEach(group => {
                    const radioYes = group.querySelector('input[value="Yes"]');
                    const radioNo = group.querySelector('input[value="No"]');
                    const specificationField = group.querySelector('textarea');

                    // Check if either Yes or No is selected
                    if (!radioYes.checked && !radioNo.checked) {
                        isValid = false;
                        radioYes.classList.add('border-red-500', 'ring-red-500');
                        radioNo.classList.add('border-red-500', 'ring-red-500');
                        if (!firstInvalidField) firstInvalidField = radioYes; // Mark the first radio as invalid
                    } else {
                        radioYes.classList.remove('border-red-500', 'ring-red-500');
                        radioNo.classList.remove('border-red-500', 'ring-red-500');
                    }

                    // If Yes is selected, ensure specification is filled
                    if (radioYes.checked && specificationField.hasAttribute('required') && specificationField.value.trim() === '') {
                        isValid = false;
                        specificationField.classList.add('border-red-500', 'ring-red-500');
                        if (!firstInvalidField) firstInvalidField = specificationField;
                    } else if (specificationField) {
                        specificationField.classList.remove('border-red-500', 'ring-red-500');
                    }
                });
            }


            if (!isValid) {
                showModal('Validation Error', 'Please fill out all required fields and correct any errors before proceeding.');
                if (firstInvalidField) {
                    firstInvalidField.focus(); // Focus on the first invalid field
                }
            }
            return isValid;
        }

        /**
         * Handles the click event for the 'Next' button.
         * Validates the current step and moves to the next if valid.
         */
        nextBtn.addEventListener('click', () => {
            if (validateCurrentStep()) {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    updateFormVisibility();
                }
            }
        });

        /**
         * Handles the click event for the 'Previous' button.
         * Moves to the previous step.
         */
        prevBtn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateFormVisibility();
            }
        });

        /**
         * Handles the form submission.
         * Collects all form data and logs it to the console.
         * In a real application, this would send data to a server.
         */
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (validateCurrentStep()) {
                const formData = {};
                // Collect simple input data
                new FormData(form).forEach((value, key) => {
                    // This handles only the last value for multiple inputs with the same name.
                    // For dynamic lists, custom logic is needed.
                    if (!formData[key]) {
                        formData[key] = value;
                    } else if (Array.isArray(formData[key])) {
                        formData[key].push(value);
                    } else {
                        formData[key] = [formData[key], value];
                    }
                });

                // Manually collect data for dynamic sections
                // Partners
                formData.partners = [];
                document.querySelectorAll('.partner-row').forEach(row => {
                    const partner = {
                        name: row.querySelector('[name="partnerName"]').value,
                        fatherHusbandName: row.querySelector('[name="partnerFatherHusbandName"]').value,
                        fullAddress: row.querySelector('[name="partnerFullAddress"]').value,
                        contact: row.querySelector('[name="partnerContact"]').value,
                        email: row.querySelector('[name="partnerEmail"]').value,
                    };
                    formData.partners.push(partner);
                });

                // Authorized Persons (Directors/Trustees etc.)
                formData.authorizedPersons = [];
                document.querySelectorAll('.authorized-person-row').forEach(row => {
                    const person = {
                        name: row.querySelector('[name="authPersonName"]').value,
                        fullAddress: row.querySelector('[name="authPersonFullAddress"]').value,
                        contact: row.querySelector('[name="authPersonContact"]').value,
                        email: row.querySelector('[name="authPersonEmail"]').value,
                        relation: row.querySelector('[name="authPersonRelation"]').value,
                    };
                    formData.authorizedPersons.push(person);
                });

                // Business Plan
                formData.businessPlan = [];
                document.querySelectorAll('.business-plan-row').forEach(row => {
                    const plan = {
                        crop: row.querySelector('[name="businessPlanCrop"]').value,
                        fy2025_26: row.querySelector('[name="businessPlanFY25"]').value,
                        fy2026_27: row.querySelector('[name="businessPlanFY26"]').value,
                    };
                    formData.businessPlan.push(plan);
                });

                // Annual Turnover
                formData.annualTurnover = [];
                document.querySelectorAll('.annual-turnover-row').forEach(row => {
                    const turnover = {
                        financialYear: row.querySelector('[name="annualTurnoverFY"]').value,
                        netTurnover: row.querySelector('[name="annualTurnoverAmount"]').value,
                    };
                    formData.annualTurnover.push(turnover);
                });

                // Existing Distributorships
                formData.existingDistributorships = [];
                document.querySelectorAll('.existing-distributorship-row').forEach(row => {
                    const dist = {
                        companyName: row.querySelector('[name="existingCompanyName"]').value,
                    };
                    formData.existingDistributorships.push(dist);
                });

                // Security Deposit Details
                formData.securityDeposits = [];
                document.querySelectorAll('.security-deposit-row').forEach(row => {
                    const deposit = {
                        date: row.querySelector('[name="depositDate"]').value,
                        amount: row.querySelector('[name="depositAmount"]').value,
                        modeOfPayment: row.querySelector('[name="depositMode"]').value,
                        referenceNo: row.querySelector('[name="depositReferenceNo"]').value,
                        chequeNumber1: row.querySelector('[name="chequeNumber1"]').value,
                        chequeNumber2: row.querySelector('[name="chequeNumber2"]').value,
                    };
                    formData.securityDeposits.push(deposit);
                });


                // Questions
                formData.questions = {};
                questionsData.forEach((questionText, index) => {
                    const questionGroup = document.getElementById(`question-${index}`);
                    const selectedOption = questionGroup.querySelector(`input[name="question${index}"]:checked`);
                    const specification = questionGroup.querySelector(`textarea[name="question${index}Specification"]`).value;
                    formData.questions[questionText] = {
                        answer: selectedOption ? selectedOption.value : null,
                        specification: specification || null
                    };
                });


                console.log('Form Data:', formData);

                // Simulate API call for submission (replace with actual fetch to your backend)
                /*
                // Example of sending data to a hypothetical backend using Gemini API (for text generation)
                // If you need to *generate* text using the form data, you would construct a prompt.
                // This is purely illustrative, as form submission usually goes to a dedicated backend.
                let chatHistory = [];
                chatHistory.push({ role: "user", parts: [{ text: JSON.stringify(formData) }] });
                const payload = { contents: chatHistory };
                const apiKey = ""; // Canvas will provide this at runtime if empty
                const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`;

                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    console.log('API Response:', result);
                    showModal('Success!', 'Form submitted successfully! Your data has been processed.');
                } catch (error) {
                    console.error('Submission failed:', error);
                    showModal('Submission Failed', 'There was an error submitting your form. Please try again.');
                }
                */

                showModal('Success!', 'Form data logged to console. In a real application, this would be sent to a server.');
                // Optionally reset form or navigate away
                // form.reset();
                // currentStep = 0;
                // updateFormVisibility();
            }
        });

        // Event listener for modal close button
        modalCloseBtn.addEventListener('click', hideModal);

        // Conditional display for GST details
        document.querySelectorAll('input[name="gstApplicable"]').forEach(radio => {
            radio.addEventListener('change', (event) => {
                const gstDetails = document.getElementById('gstDetails');
                const gstNumberInput = document.getElementById('gstNumber');
                const gstValidityInput = document.getElementById('gstValidity');
                if (event.target.value === 'Yes') {
                    gstDetails.classList.remove('hidden');
                    gstNumberInput.setAttribute('required', 'required');
                    gstValidityInput.setAttribute('required', 'required');
                } else {
                    gstDetails.classList.add('hidden');
                    gstNumberInput.removeAttribute('required');
                    gstValidityInput.removeAttribute('required');
                    gstNumberInput.value = ''; // Clear value
                    gstValidityInput.value = ''; // Clear value
                    gstNumberInput.classList.remove('border-red-500', 'ring-red-500'); // Clear validation
                    gstValidityInput.classList.remove('border-red-500', 'ring-red-500'); // Clear validation
                }
            });
        });

        // Conditional display for Ownership Details sections (Step 2)
        document.getElementById('establishmentType').addEventListener('change', (event) => {
            const type = event.target.value;
            const soleProprietorshipSection = document.getElementById('soleProprietorshipSection');
            const partnershipSection = document.getElementById('partnershipSection');
            const authorizedPersonSection = document.getElementById('authorizedPersonSection');

            // Reset all sections and their required attributes
            const sections = [
                { el: soleProprietorshipSection, inputs: soleProprietorshipSection.querySelectorAll('[required]') },
                { el: partnershipSection, inputs: partnershipSection.querySelectorAll('[required]') },
                { el: authorizedPersonSection, inputs: authorizedPersonSection.querySelectorAll('[required]') }
            ];

            sections.forEach(section => {
                section.el.classList.add('hidden');
                section.inputs.forEach(input => input.removeAttribute('required'));
            });

            // Enable/show relevant sections and set required attributes
            if (type === 'Sole Proprietorship') {
                soleProprietorshipSection.classList.remove('hidden');
                soleProprietorshipSection.querySelectorAll('input, select, textarea').forEach(input => {
                    input.setAttribute('required', 'required');
                });
                soleProprietorshipSection.querySelector('[name="proprietorMobile"]').setAttribute('pattern', '[0-9]{10}');
                soleProprietorshipSection.querySelector('[name="proprietorPincode"]').setAttribute('pattern', '\\d{6}');
            } else if (type === 'Partnership') {
                partnershipSection.classList.remove('hidden');
                 // Required partners are handled by dynamic validation, not input attributes
            } else if (type === 'Limited Liability Partnership' || type === 'Company (Private/Public)' || type === 'Cooperative Society' || type === 'Trust' || type === 'HUF') {
                authorizedPersonSection.classList.remove('hidden');
                 // Required authorized persons are handled by dynamic validation, not input attributes
            }
             // Re-evaluate current step validation on type change
            validateCurrentStep();
        });

        // Conditional display for Appointment Type details (Step 3)
        document.getElementById('appointmentType').addEventListener('change', (event) => {
            const type = event.target.value;
            const replacementDetails = document.getElementById('replacementDetails');
            const newAreaDetails = document.getElementById('newAreaDetails');

            // Reset all
            const allAppointmentConditionalInputs = document.querySelectorAll('#replacementDetails [required], #newAreaDetails [required]');
            allAppointmentConditionalInputs.forEach(input => {
                input.removeAttribute('required');
                input.value = ''; // Clear values
                input.classList.remove('border-red-500', 'ring-red-500'); // Clear validation
            });
            replacementDetails.classList.add('hidden');
            newAreaDetails.classList.add('hidden');

            if (type === 'Replacement of an existing Distributor') {
                replacementDetails.classList.remove('hidden');
                document.getElementById('replacementReason').setAttribute('required', 'required');
                document.getElementById('outstandingRecoveryCommitment').setAttribute('required', 'required');
            } else if (type === 'New Area') {
                newAreaDetails.classList.remove('hidden');
                document.getElementById('earlierDistributorNewArea').setAttribute('required', 'required');
            }
        });


        /**
         * Adds a new partner row to the Partnership section.
         */
        function addPartnerRow() {
            const container = document.getElementById('partnersContainer');
            const newPartnerDiv = document.createElement('div');
            newPartnerDiv.classList.add('partner-row', 'bg-gray-100', 'p-4', 'rounded-md', 'shadow-inner', 'grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'relative');
            newPartnerDiv.innerHTML = `
                <button type="button" class="remove-row-btn absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Name of Partner/Director/Trustee</label>
                    <input type="text" name="partnerName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Father’s/Husband’s Name</label>
                    <input type="text" name="partnerFatherHusbandName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Full address</label>
                    <input type="text" name="partnerFullAddress" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Contact</label>
                    <input type="tel" name="partnerContact" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[0-9]{10}" title="Contact must be 10 digits">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Email</label>
                    <input type="email" name="partnerEmail" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
            `;
            container.appendChild(newPartnerDiv);
            newPartnerDiv.querySelector('.remove-row-btn').addEventListener('click', () => newPartnerDiv.remove());
        }

        /**
         * Adds a new authorized person row to the Authorized Person section.
         */
        function addAuthorizedPersonRow() {
            const container = document.getElementById('authorizedPersonsContainer');
            const newPersonDiv = document.createElement('div');
            newPersonDiv.classList.add('authorized-person-row', 'bg-gray-100', 'p-4', 'rounded-md', 'shadow-inner', 'grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-4', 'relative');
            newPersonDiv.innerHTML = `
                <button type="button" class="remove-row-btn absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Name</label>
                    <input type="text" name="authPersonName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Full address</label>
                    <input type="text" name="authPersonFullAddress" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Contact</label>
                    <input type="tel" name="authPersonContact" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required pattern="[0-9]{10}" title="Contact must be 10 digits">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Email</label>
                    <input type="email" name="authPersonEmail" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Relation with owner/entity</label>
                    <input type="text" name="authPersonRelation" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
            `;
            container.appendChild(newPersonDiv);
            newPersonDiv.querySelector('.remove-row-btn').addEventListener('click', () => newPersonDiv.remove());
        }

        /**
         * Adds a new business plan row.
         */
        function addBusinessPlanRow() {
            const container = document.getElementById('businessPlanContainer');
            const newRow = document.createElement('div');
            newRow.classList.add('business-plan-row', 'grid', 'grid-cols-1', 'md:grid-cols-4', 'gap-4', 'bg-gray-50', 'p-4', 'rounded-md', 'shadow-sm', 'relative');
            newRow.innerHTML = `
                <button type="button" class="remove-row-btn absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Crop</label>
                    <input type="text" name="businessPlanCrop" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">FY 2025-26 (in MT)</label>
                    <input type="number" name="businessPlanFY25" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">FY 2026-27 (in MT)</label>
                    <input type="number" name="businessPlanFY26" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required min="0">
                </div>
                <div class="flex items-end md:items-center">
                    <button type="button" class="remove-row-btn w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            newRow.querySelector('.remove-row-btn').addEventListener('click', () => newRow.remove());
        }

        /**
         * Adds a new annual turnover row.
         */
        function addAnnualTurnoverRow() {
            const container = document.getElementById('annualTurnoverContainer');
            const newRow = document.createElement('div');
            newRow.classList.add('annual-turnover-row', 'grid', 'grid-cols-1', 'md:grid-cols-3', 'gap-4', 'bg-gray-50', 'p-4', 'rounded-md', 'shadow-sm', 'relative');
            newRow.innerHTML = `
                <button type="button" class="remove-row-btn absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Financial Year</label>
                    <input type="text" name="annualTurnoverFY" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., FY 2024–25" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Net Turnover</label>
                    <input type="text" name="annualTurnoverAmount" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., ₹3 Crores" required>
                </div>
                <div class="flex items-end md:items-center">
                    <button type="button" class="remove-row-btn w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            newRow.querySelector('.remove-row-btn').addEventListener('click', () => newRow.remove());
        }

        /**
         * Adds a new existing distributorship row.
         */
        function addExistingDistributorshipRow() {
            const container = document.getElementById('existingDistributorshipsContainer');
            const newRow = document.createElement('div');
            newRow.classList.add('existing-distributorship-row', 'flex', 'flex-col', 'md:flex-row', 'items-end', 'gap-4', 'bg-gray-50', 'p-4', 'rounded-md', 'shadow-sm', 'relative');
            newRow.innerHTML = `
                <button type="button" class="remove-row-btn absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                <div class="flex-grow w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Company Name</label>
                    <input type="text" name="existingCompanyName" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <button type="button" class="remove-row-btn w-full md:w-auto px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            newRow.querySelector('.remove-row-btn').addEventListener('click', () => newRow.remove());
        }

        /**
         * Adds a new security deposit row.
         */
        function addSecurityDepositRow() {
            const container = document.getElementById('securityDepositContainer');
            const newRow = document.createElement('div');
            newRow.classList.add('security-deposit-row', 'grid', 'grid-cols-1', 'md:grid-cols-4', 'gap-4', 'bg-gray-50', 'p-4', 'rounded-md', 'shadow-sm', 'relative');
            newRow.innerHTML = `
                <button type="button" class="remove-row-btn absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Date</label>
                    <input type="date" name="depositDate" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Amount</label>
                    <input type="text" name="depositAmount" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., ₹50,000" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 required">Mode of Payment</label>
                    <input type="text" name="depositMode" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g., NEFT/Cheque" required>
                </div>
                <div class="flex items-end md:items-center">
                    <button type="button" class="remove-row-btn w-full px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove
                    </button>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">If NEFT/Online, Reference No.</label>
                    <input type="text" name="depositReferenceNo" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cheque Number 1</label>
                    <input type="text" name="chequeNumber1" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cheque Number 2</label>
                    <input type="text" name="chequeNumber2" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <p class="md:col-span-4 text-xs text-gray-500 mt-1">(Cheques should be from operative bank account)</p>
            `;
            container.appendChild(newRow);
            newRow.querySelector('.remove-row-btn').addEventListener('click', () => newRow.remove());
        }


        /**
         * Populates the questions section dynamically.
         */
        function populateQuestions() {
            const questionsContainer = document.getElementById('questionsContainer');
            questionsContainer.innerHTML = ''; // Clear existing questions

            questionsData.forEach((questionText, index) => {
                const questionDiv = document.createElement('div');
                questionDiv.classList.add('question-group', 'bg-blue-50', 'p-4', 'rounded-md', 'shadow-sm');
                questionDiv.id = `question-${index}`;
                questionDiv.innerHTML = `
                    <p class="font-medium text-gray-800 mb-3 required">${index + 1}. ${questionText}</p>
                    <div class="flex items-center space-x-6 mb-4">
                        <div class="flex items-center">
                            <input type="radio" id="question${index}Yes" name="question${index}" value="Yes" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" required>
                            <label for="question${index}Yes" class="ml-2 block text-sm text-gray-900">Yes</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="question${index}No" name="question${index}" value="No" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" required>
                            <label for="question${index}No" class="ml-2 block text-sm text-gray-900">No</label>
                        </div>
                    </div>
                    <div id="specification${index}" class="hidden">
                        <label for="question${index}Specification" class="block text-sm font-medium text-gray-700 mb-1 required">Please specify:</label>
                        <textarea id="question${index}Specification" name="question${index}Specification" rows="2" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    </div>
                `;
                questionsContainer.appendChild(questionDiv);

                // Add event listeners for radio buttons to show/hide specification textarea
                const radioYes = questionDiv.querySelector(`#question${index}Yes`);
                const radioNo = questionDiv.querySelector(`#question${index}No`);
                const specificationDiv = questionDiv.querySelector(`#specification${index}`);
                const specificationTextarea = questionDiv.querySelector(`#question${index}Specification`);

                const toggleSpecification = () => {
                    if (radioYes.checked) {
                        specificationDiv.classList.remove('hidden');
                        specificationTextarea.setAttribute('required', 'required');
                    } else {
                        specificationDiv.classList.add('hidden');
                        specificationTextarea.removeAttribute('required');
                        specificationTextarea.value = ''; // Clear value if hidden
                        specificationTextarea.classList.remove('border-red-500', 'ring-red-500'); // Clear validation
                    }
                };

                radioYes.addEventListener('change', toggleSpecification);
                radioNo.addEventListener('change', toggleSpecification);
            });
        }


        // Initial setup
        document.addEventListener('DOMContentLoaded', () => {
            updateFormVisibility();
            populateQuestions();
            // Add initial rows for dynamic sections if desired, or let user add them
            // addPartnerRow(); // Example: add one partner row by default
            // addBusinessPlanRow(); // Example: add one business plan row by default
            // addAnnualTurnoverRow(); // Example: add one annual turnover row by default
            // addExistingDistributorshipRow(); // Example: add one existing distributorship row by default
            // addSecurityDepositRow(); // Example: add one security deposit row by default

            // Attach event listeners to Add buttons
            document.getElementById('addPartnerBtn').addEventListener('click', addPartnerRow);
            document.getElementById('addAuthorizedPersonBtn').addEventListener('click', addAuthorizedPersonRow);
            document.getElementById('addBusinessPlanRowBtn').addEventListener('click', addBusinessPlanRow);
            document.getElementById('addAnnualTurnoverRowBtn').addEventListener('click', addAnnualTurnoverRow);
            document.getElementById('addExistingDistributorshipBtn').addEventListener('click', addExistingDistributorshipRow);
            document.getElementById('addSecurityDepositBtn').addEventListener('click', addSecurityDepositRow);
        });

    </script>
@endpush
