<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Distributorship Agreement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Calibri', 'Arial', sans-serif;
        }
        
        .agreement-container {
            max-width: 210mm;
            margin: 20px auto;
            background: white;
            padding: 25.4mm 31.75mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .agreement-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .agreement-header h2 {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
        
        .agreement-content {
            font-size: 11pt;
            line-height: 1.5;
            text-align: justify;
        }
        
        .agreement-content p {
            margin-bottom: 10px;
        }
        
        .agreement-content h5 {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        .agreement-content h6 {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 8px;
        }
        
        .agreement-content ul {
            margin: 10px 0;
            padding-left: 40px;
        }
        
        .agreement-content ul li {
            margin-bottom: 5px;
        }
        
        .agreement-content ol {
            margin: 10px 0;
            padding-left: 40px;
            list-style-type: lower-alpha;
        }
        
        .agreement-content ol li {
            margin-bottom: 8px;
        }
        
        .numbered-clause {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        table.blank-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }
        
        table.blank-table th,
        table.blank-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        table.blank-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .stamp-space {
            height: 400px;
            border: 2px dashed #999;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }
        
        .signature-section {
            margin-top: 40px;
        }
        
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        
        .section-break {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .agreement-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
                max-width: 100%;
            }
            
            .action-buttons,
            .no-print {
                display: none !important;
            }
            
            .section-break {
                border-top: none;
                page-break-before: always;
                margin-top: 0;
                padding-top: 0;
            }
            
            h5, h6 {
                page-break-after: avoid;
            }
            
            p {
                orphans: 3;
                widows: 3;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons no-print">
        <div class="btn-group-vertical" role="group">
            <button type="button" class="btn btn-primary btn-sm mb-2" onclick="window.print()">
                <i class="ri-printer-line"></i> Print
            </button>
            <a href="{{ route('approvals.draft-agreement.pdf', ['id' => $application->id, 'type' => 'stamp']) }}" 
               class="btn btn-success btn-sm mb-2">
                <i class="ri-download-line"></i> Download PDF (Stamp Paper)
            </a>
            <a href="{{ route('approvals.draft-agreement.pdf', ['id' => $application->id, 'type' => 'e-stamp']) }}" 
               class="btn btn-info btn-sm mb-2">
                <i class="ri-download-line"></i> Download PDF (E-Stamp)
            </a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()">
                <i class="ri-close-line"></i> Close
            </button>
        </div>
        
        <div class="mt-3">
            <p class="small text-muted mb-1"><strong>Application Details:</strong></p>
            <p class="small mb-1">Firm: {{ $application->entityDetails->firm_name ?? 'N/A' }}</p>
            <p class="small mb-1">Contact: {{ $application->entityDetails->contact_person_name ?? 'N/A' }}</p>
            <p class="small mb-0">Status: {{ ucfirst(str_replace('_', ' ', $application->status)) }}</p>
        </div>
    </div>

    <div class="agreement-container">
        <!-- Agreement Header -->
        <div class="agreement-header">
            <h2>DISTRIBUTORSHIP AGREEMENT</h2>
        </div>

        <!-- Agreement Content -->
        <div class="agreement-content">
            <p><strong>This Distributorship Agreement ("the Agreement")</strong> is made and entered into as of [_______________Effective Date], by and between:</p>
            
            <p><strong>VNR Seeds Private Limited</strong> a company incorporated under the provisions of the Companies Act, 1956, having its registered office at Corporate Centre, Canal Road Crossing, Ring Road No. 1, Raipur, Chhattisgarh – 492006 represented by Authorized Representative, (hereinafter referred to as <strong>the "First Party"</strong>, which expression shall, unless repugnant to the context or meaning thereof, mean and include its affiliates, successors and permitted assigns) of One Part.</p>
            
            <p><strong>AND</strong></p>
            
            <p><strong>{{ $application->entityDetails->firm_name ?? '__________________________________' }}</strong>, Distributor Name <strong>{{ $application->entityDetails->contact_person_name ?? '__________________________________' }}</strong>, having its Company place of business <strong>{{ $application->entityDetails->registered_address ?? '________________________________________________________________' }}</strong>, (hereinafter referred to as <strong>the "Second Party"</strong>, which expression shall, unless repugnant to the context or meaning thereof, mean and include its affiliates, successors and permitted assigns) of Other Part.</p>

            <p>For the purposes of this Agreement, the <strong>First Party shall be referred to as the "Company" and the Second Party shall be referred to as the "Authorized Distributor."</strong> The Company and the Authorized Distributor are hereinafter collectively referred to as the <strong>"Parties"</strong> and individually referred to as a <strong>"Party."</strong></p>

            <p><strong>WHEREAS,</strong> the Company is engaged in the manufacture, production, and supply of Seeds (the "Products") and desires to appoint the Authorized Distributor on the non exclusive basis to sell and distribute the Products;</p>

            <p><strong>AND WHEREAS,</strong> the Authorized Distributor possesses the ability to promote the sale of the Products and desires to develop demand for and sell the Products and has expressed interest in distributing and selling the Company's products within a specified territory; and</p>

            <p><strong>AND WHEREAS,</strong> the Company hereby appoints the Authorized Distributor, and the Authorized Distributor hereby accepts such appointment, to distribute and sell the Company's product under the terms and conditions set forth in this Agreement.</p>

            <p><strong>NOW, THEREFORE, IN LIGHT OF THE MUTUAL AGREEMENTS AND COMMITMENTS CONTAINED HEREIN, THE PARTIES HEREBY AGREE AS FOLLOWS:</strong></p>

            <!-- Continue with all your agreement clauses -->
            <!-- I'm showing the structure - include all your content here -->

            <div class="numbered-clause">1. Appointment of Authorized Distributor</div>
            
            <ol>
                <li>The Company hereby appoints the Authorized Distributor, upon completion and acceptance of the Distributor appointment form, on a non-exclusive basis for the sale of the Company's products. The Authorized Distributor accepts this appointment and agrees to diligently act as the Company's distributor for the crop vertical and within the specified territory as outlined in appointment form.</li>
                
                <li>The Authorized Distributor shall submit all requested documents to the Company. These may include, but are not limited to, photocopies of the Partnership Deed, Profit & Loss Account, Balance Sheets for prior years, educational certificates, GST Number, PAN, IEC, Seed License Number, Aadhaar Number, and any other documents specified by the Company. These documents must be provided along with the signed Agreement.</li>
            </ol>

            <!-- Include all other clauses from your original document -->
            <!-- ... -->

            <div class="signature-section">
                <p style="text-align: center; font-weight: bold; margin-top: 30px;">IN WITNESS WHEREOF THE PARTIES HERETO HAVE EXECUTED THIS AGREEMENT THE DAY AND YEAR FIRST ABOVE WRITTEN</p>

                <table style="width: 100%; margin-top: 40px; border: none;">
                    <tr>
                        <td style="width: 50%; border: none; vertical-align: top;">
                            <p>_________________________________</p>
                            <p><strong>First Party</strong></p>
                            <p><strong>VNR Seeds Private Limited</strong></p>
                            <p><strong>Name:</strong> ________________</p>
                            <p><strong>Designation:</strong> ________________</p>
                        </td>
                        <td style="width: 50%; border: none; vertical-align: top;">
                            <p>_________________________________</p>
                            <p><strong>Second Party</strong></p>
                            <p><strong>Name:</strong> {{ $application->entityDetails->contact_person_name ?? '________________' }}</p>
                            <p><strong>Designation:</strong> ________________</p>
                        </td>
                    </tr>
                </table>

                <table style="width: 100%; margin-top: 40px; border: none;">
                    <tr>
                        <td style="width: 50%; border: none; vertical-align: top;">
                            <p><strong>Witness:</strong></p>
                            <p>Signature_________________</p>
                            <p>Name- _________________</p>
                            <p>Address- ________________</p>
                        </td>
                        <td style="width: 50%; border: none; vertical-align: top;">
                            <p><strong>Witness:</strong></p>
                            <p>Signature_________________</p>
                            <p>Name- _________________</p>
                            <p>Address- ________________</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Include all annexures -->
            <!-- ... -->

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>