<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequiredDocument;

class RequiredDocumentsSeeder extends Seeder
{
    public function run()
    {
        $documents = [
            // Business Entity Proof
            [
                'category' => 'Business Entity Proof',
                'document_name' => 'Valid Seed Licence',
                'description' => 'Valid Seed Licence for that particular place - validity check, Proprietor, firm and crop vertical if applicable',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 1,
            ],
            [
                'category' => 'Business Entity Proof',
                'document_name' => 'Registration Certificate',
                'description' => 'Registration certificate under the Shops and Establishments Act',
                'applicability' => 'Optional',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 2,
            ],
            [
                'category' => 'Business Entity Proof', 
                'document_name' => 'Partnership Agreement',
                'description' => 'Partnership Agreement',
                'applicability' => 'On Applicability',
                'entity_types' => ['partnership'],
                'sort_order' => 3,
            ],
            [
                'category' => 'Business Entity Proof',
                'document_name' => 'Certificate of Incorporation',
                'description' => 'Certificate of Incorporation',
                'applicability' => 'On Applicability', 
                'entity_types' => ['llp', 'company'],
                'sort_order' => 4,
            ],
            [
                'category' => 'Business Entity Proof',
                'document_name' => 'Certificate of Registration',
                'description' => 'Certificate of Registration',
                'applicability' => 'On Applicability',
                'entity_types' => ['cooperative_society', 'trust'],
                'sort_order' => 5,
            ],

            // GST & Tax Documents
            [
                'category' => 'Tax Compliance',
                'document_name' => 'GST Certificate',
                'description' => 'Copy of GST Certificate, if applicable - To provide data of GST registered & unregistered vendors, on filing if GST return',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 10,
            ],
            [
                'category' => 'Tax Compliance',
                'document_name' => 'PAN Card',
                'description' => 'PAN of Establishment/Proprietor - TAX compliance, to deduct TDS u/s 194R (gifts)',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 11,
            ],
            [
                'category' => 'Tax Compliance',
                'document_name' => 'TAN Number',
                'description' => 'TAN number (if any) - TAX compliance (to take credit of TDS deducted)',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 12,
            ],

            // Address Proof - Owned
            [
                'category' => 'Address Proof - Owned',
                'document_name' => 'Electricity Bill / Property Tax Receipt',
                'description' => 'Latest electricity bill or Property Tax Receipt - To support ownership claims',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 20,
            ],

            // Address Proof - Rented
            [
                'category' => 'Address Proof - Rented',
                'document_name' => 'Rent/Lease Agreement',
                'description' => 'Rent / Lease Agreement - To validate rental ownership of premises',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 30,
            ],
            [
                'category' => 'Address Proof - Rented',
                'document_name' => 'Utility Bill with Owner Details',
                'description' => 'Latest utility bill or Property Tax Receipt - Bill with owner detail listed',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 31,
            ],

            // Address Proof - Shared
            [
                'category' => 'Address Proof - Shared',
                'document_name' => 'Consent Letter / NOC',
                'description' => 'Consent Letter / No Objection Certificate - To reduce fraud risk for applicants using relative\'s property without a lease/rent',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 40,
            ],
            [
                'category' => 'Address Proof - Shared', 
                'document_name' => 'Utility Bill with Consent Giver Details',
                'description' => 'Latest utility bill or Property Tax Receipt - Bill with consent giver\'s details listed',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 41,
            ],

            // Ownership Information
            [
                'category' => 'Ownership Information',
                'document_name' => 'Aadhar Card of Proprietor',
                'description' => 'Aadhar Card of Proprietor',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship'],
                'sort_order' => 50,
            ],
            [
                'category' => 'Ownership Information',
                'document_name' => 'Aadhar Cards of Partners',
                'description' => 'Aadhar Card (all the Partners in the firm)',
                'applicability' => 'Mandatory',
                'entity_types' => ['partnership'],
                'sort_order' => 51,
            ],
            [
                'category' => 'Ownership Information',
                'document_name' => 'Certified List of Partners',
                'description' => 'Certified List of Partners',
                'applicability' => 'Mandatory',
                'entity_types' => ['llp'],
                'sort_order' => 52,
            ],
            [
                'category' => 'Ownership Information',
                'document_name' => 'Certified List of Directors',
                'description' => 'Certified List of Directors', 
                'applicability' => 'Mandatory',
                'entity_types' => ['company', 'cooperative_society'],
                'sort_order' => 53,
            ],
            [
                'category' => 'Ownership Information',
                'document_name' => 'Certified List of Trustees',
                'description' => 'Certified List of Trustees',
                'applicability' => 'Mandatory',
                'entity_types' => ['trust'],
                'sort_order' => 54,
            ],

            // Authorised Person
            [
                'category' => 'Authorised Person',
                'document_name' => 'Letter of Authorization',
                'description' => 'Letter of Authorization from Proprietors/Partners/Board Resolution for authorization',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 60,
            ],
            [
                'category' => 'Authorised Person',
                'document_name' => 'Aadhar of Authorised Person',
                'description' => 'Aadhar of the Authorised Person',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 61,
            ],

            // Bank Details & Security
            [
                'category' => 'Bank Details & Security',
                'document_name' => 'Bank Statements',
                'description' => 'Online Bank Statement - 6 Months, Physical Bank Statement - 3 Months',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 70,
            ],
            [
                'category' => 'Bank Details & Security',
                'document_name' => 'Security Cheques',
                'description' => '2 Security Cheques of operative account for which Bank Statement is submitted',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 71,
            ],
            [
                'category' => 'Bank Details & Security',
                'document_name' => 'Security Deposit Cheque/DD',
                'description' => 'Cheque/DD of Deposit Amount',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 72,
            ],

            // Credit Worthiness
            [
                'category' => 'Credit Worthiness',
                'document_name' => 'Income Tax Return Acknowledgement',
                'description' => 'Income Tax Return Acknowledgement - generally available with applicants',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 80,
            ],
            [
                'category' => 'Credit Worthiness',
                'document_name' => 'Balance Sheet',
                'description' => 'Balance sheet of latest of FY',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 81,
            ],

            // Declarations
            [
                'category' => 'Declarations',
                'document_name' => 'Declaration of Relations',
                'description' => 'Declarations of relations - For confirmation if any relative is associated with VNR',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 90,
            ],
            [
                'category' => 'Declarations',
                'document_name' => 'Authorization Letter for Signatory',
                'description' => 'Authorization letter - Authorisation of signatory of applicant',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 91,
            ],
            [
                'category' => 'Declarations',
                'document_name' => 'Non-GST Applicability Declaration',
                'description' => 'Declaration for non applicability of GST - In case of non applicability of GST',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 92,
            ],
            [
                'category' => 'Declarations',
                'document_name' => 'Cheque Usage Declaration',
                'description' => 'Declaration for Cheque usage - Authorisation for presentment of security cheques for recovery of outstanding dues',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 93,
            ],
            [
                'category' => 'Declarations',
                'document_name' => 'Entity Ownership/Management Declaration',
                'description' => 'Entity Ownership/Management Declaration - Self-declaration of ownership in case of proprietorship/partnership and confirmation of management in case of other entities',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 94,
            ],
        ];

        foreach ($documents as $document) {
            RequiredDocument::create($document);
        }
    }
}