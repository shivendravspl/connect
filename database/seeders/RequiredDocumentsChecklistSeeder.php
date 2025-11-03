<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequiredDocument;

class RequiredDocumentsChecklistSeeder extends Seeder
{
    public function run()
    {
        $documents = [
            // Business Entity Proof Category
            [
                'category' => 'Business Entity Proof',
                'sub_category' => null,
                'document_name' => 'Valid Seed Licence',
                'checkpoints' => 'validity check, Proprietor, firm and crop vertical (VC & FC Dept) as applicable',
                'applicability_justification' => 'Mandatory for sale of seeds',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 1,
            ],
            [
                'category' => 'Business Entity Proof',
                'sub_category' => null,
                'document_name' => 'Registration certificate under the Shops and Establishments Act',
                'checkpoints' => 'Applicable for Complete address proof and entity and owner details, In case of not available in any other documents',
                'applicability_justification' => 'Entity & address proof',
                'applicability' => 'Optional',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 2,
            ],
            [
                'category' => 'Business Entity Proof',
                'sub_category' => null,
                'document_name' => 'Partnership Agreement',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'On Applicability',
                'entity_types' => ['partnership'],
                'sort_order' => 3,
            ],
            [
                'category' => 'Business Entity Proof',
                'sub_category' => null,
                'document_name' => 'Certificate of Incorporation',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'On Applicability',
                'entity_types' => ['llp', 'company'],
                'sort_order' => 4,
            ],
            [
                'category' => 'Business Entity Proof',
                'sub_category' => null,
                'document_name' => 'Certificate of Registration',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'On Applicability',
                'entity_types' => ['cooperative_society', 'trust'],
                'sort_order' => 5,
            ],
            [
                'category' => 'Business Entity Proof',
                'sub_category' => null,
                'document_name' => 'Copy of GST Certificate, if applicable',
                'checkpoints' => 'Entity, owner, GST NO and Complete address',
                'applicability_justification' => 'To provide data of GST registered & unregistered vendors, on filing if GST return',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 6,
            ],

            // Tax Compliance Category
            [
                'category' => 'Tax Compliance',
                'sub_category' => null,
                'document_name' => 'PAN of Establishment/Proprietor',
                'checkpoints' => 'Name, PAN NO and DOB',
                'applicability_justification' => 'TAX compliance, to deduct TDS u/s 194R (gifts)',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 10,
            ],
            [
                'category' => 'Tax Compliance',
                'sub_category' => null,
                'document_name' => 'TAN number (if any)',
                'checkpoints' => '',
                'applicability_justification' => 'TAX compliance (to take credit of TDS deducted)',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 11,
            ],

            // Address Proof - Owned
            [
                'category' => 'Address Proof',
                'sub_category' => 'Owned',
                'document_name' => 'Latest electricity bill or Property Tax Receipt',
                'checkpoints' => '',
                'applicability_justification' => 'To support ownership claims',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 20,
            ],

            // Address Proof - Rented
            [
                'category' => 'Address Proof',
                'sub_category' => 'Rented',
                'document_name' => 'Rent / Lease Agreement',
                'checkpoints' => '',
                'applicability_justification' => 'To validate rental ownership of premises',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 30,
            ],
            [
                'category' => 'Address Proof',
                'sub_category' => 'Rented',
                'document_name' => 'Latest utility bill or Property Tax Receipt',
                'checkpoints' => '',
                'applicability_justification' => 'Bill with owner detail listed',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 31,
            ],

            // Address Proof - Shared
            [
                'category' => 'Address Proof',
                'sub_category' => 'Shared',
                'document_name' => 'Consent Letter / No Objection Certificate',
                'checkpoints' => '',
                'applicability_justification' => 'To reduce fraud risk for applicants using relative\'s property without a lease/rent',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 40,
            ],
            [
                'category' => 'Address Proof',
                'sub_category' => 'Shared',
                'document_name' => 'Latest utility bill or Property Tax Receipt',
                'checkpoints' => '',
                'applicability_justification' => 'Bill with consent giver\'s details listed',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 41,
            ],

            // Ownership Information
            [
                'category' => 'Ownership Information',
                'sub_category' => null,
                'document_name' => 'Aadhar Card of Proprietor',
                'checkpoints' => 'Owner name, DOB, Aadhar Number and address check',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship'],
                'sort_order' => 50,
            ],
            [
                'category' => 'Ownership Information',
                'sub_category' => null,
                'document_name' => 'Aadhar Card (all the Partners in the firm)',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['partnership'],
                'sort_order' => 51,
            ],
            [
                'category' => 'Ownership Information',
                'sub_category' => null,
                'document_name' => 'Certified List of Partners',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['llp'],
                'sort_order' => 52,
            ],
            [
                'category' => 'Ownership Information',
                'sub_category' => null,
                'document_name' => 'Certified List of Directors',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['company', 'cooperative_society'],
                'sort_order' => 53,
            ],
            [
                'category' => 'Ownership Information',
                'sub_category' => null,
                'document_name' => 'Certified List of Trustees',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['trust'],
                'sort_order' => 54,
            ],

            // Authorised Person
            [
                'category' => 'Authorised Person',
                'sub_category' => null,
                'document_name' => 'Letter of Authorization from Proprietors/Partners/Board',
                'checkpoints' => '',
                'applicability_justification' => 'Authorisation of signatory of applicant',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 60,
            ],
            [
                'category' => 'Authorised Person',
                'sub_category' => null,
                'document_name' => 'Aadhar of the Authorised Person',
                'checkpoints' => '',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 61,
            ],

            // Bank Details & Security
            [
                'category' => 'Bank Details & Security',
                'sub_category' => null,
                'document_name' => 'Bank Statements',
                'checkpoints' => 'Online Bank Statement - 6 Months, Physical Bank Statement - 3 Months',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 70,
            ],
            [
                'category' => 'Bank Details & Security',
                'sub_category' => null,
                'document_name' => 'Security Cheques',
                'checkpoints' => '2 Security Cheques of operative account for which Bank Statement is submitted',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 71,
            ],
            [
                'category' => 'Bank Details & Security',
                'sub_category' => null,
                'document_name' => 'Security Deposit Cheque/DD',
                'checkpoints' => 'Cheque/DD of Deposit Amount',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 72,
            ],

            // Credit Worthiness
            [
                'category' => 'Credit Worthiness',
                'sub_category' => null,
                'document_name' => 'Income Tax Return Acknowledgement',
                'checkpoints' => '',
                'applicability_justification' => 'generally available with applicants',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 80,
            ],
            [
                'category' => 'Credit Worthiness',
                'sub_category' => null,
                'document_name' => 'Balance Sheet',
                'checkpoints' => 'Balance sheet of latest of FY',
                'applicability_justification' => '',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 81,
            ],

            // Declarations
            [
                'category' => 'Declarations',
                'sub_category' => null,
                'document_name' => 'Declaration of relations',
                'checkpoints' => '',
                'applicability_justification' => 'For confirmation if any relative is associated with VNR',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 90,
            ],
            [
                'category' => 'Declarations',
                'sub_category' => null,
                'document_name' => 'Declaration for non applicability of GST',
                'checkpoints' => '',
                'applicability_justification' => 'In case of non applicability of GST',
                'applicability' => 'On Applicability',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 91,
            ],
            [
                'category' => 'Declarations',
                'sub_category' => null,
                'document_name' => 'Declaration for Cheque usage',
                'checkpoints' => '',
                'applicability_justification' => 'Authorisation for presentment of security cheques for recovery of outstanding dues',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 92,
            ],
            [
                'category' => 'Declarations',
                'sub_category' => null,
                'document_name' => 'Entity Ownership/Management Declaration',
                'checkpoints' => '',
                'applicability_justification' => 'Self-declaration of ownership in case of proprietorship/partnership and confirmation of management in case of other entities',
                'applicability' => 'Mandatory',
                'entity_types' => ['sole_proprietorship', 'partnership', 'llp', 'company', 'cooperative_society', 'trust'],
                'sort_order' => 93,
            ],
        ];

        foreach ($documents as $document) {
            RequiredDocument::create($document);
        }
    }
}