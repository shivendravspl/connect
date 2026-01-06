<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Draft Distributorship Agreement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* =========================
            BASE STYLES
        ========================= */
        body {
            font-family: "Times New Roman", Times, serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* Document container */
        .agreement-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
            box-sizing: border-box;
        }

        /* =========================
            PAGE LAYOUT
        ========================= */
        .page {
            width: 210mm;
            min-height: 297mm;
            position: relative;
            page-break-after: always;
            box-sizing: border-box;
            background: #fff;
        }

        /* Page content area with proper margins */
        .page-content {
            padding: 20mm 25mm;
            height: 100%;
            box-sizing: border-box;
        }

        /* =========================
            TYPOGRAPHY
        ========================= */
        .agreement-content {
            font-size: 12pt;
            line-height: 1.6;
            text-align: justify;
            text-rendering: optimizeLegibility;
        }

        .agreement-content p {
            margin: 0 0 12pt 0;
            text-align: justify;
            line-height: 1.6;
        }

        h2 {
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin: 10mm 0 15mm 0;
            padding: 0;
        }

        h5, h6 {
            font-size: 13pt;
            font-weight: bold;
            margin: 15pt 0 8pt 0;
        }

        /* Section headings */
        .numbered-clause {
            font-weight: bold;
            font-size: 12pt;
            margin: 15pt 0 8pt 0;
            page-break-after: avoid;
        }

        /* =========================
            LISTS
        ========================= */
        ol, ul {
            margin: 8pt 0 12pt 0;
            padding-left: 1.5em;
        }

        li {
            margin-bottom: 8pt;
            page-break-inside: avoid;
        }

        /* =========================
            TABLES
        ========================= */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12pt 0;
            page-break-inside: avoid;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 11pt;
        }

        .blank-table td {
            height: 24px;
        }

        /* =========================
            STAMP SPACE HANDLING
        ========================= */
        /* Default: without stamp */
        .stamp-space {
            height: 0;
            overflow: hidden;
        }

        /* When stamp mode is active */
        .stamp-mode .page:first-child .stamp-space {
            display: block;
            height: 100mm;
            margin: 0;
            padding: 0;
        }

        .stamp-mode .page:first-child .page-content {
            padding-top: 0;
        }

        .stamp-text {
            height: 120mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-weight: bold;
            color: #999;
            border-bottom: 2px dashed #ccc;
            margin-bottom: 10mm;
        }

        /* =========================
            SIGNATURE SECTION
        ========================= */
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        /* =========================
            PRINT STYLES
        ========================= */
        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
                width: 210mm;
            }

            .agreement-container {
                width: 210mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .page {
                min-height: 297mm;
                height: 297mm;
                margin: 0;
                border: none;
                page-break-after: always;
            }

            .page-content {
                padding: 20mm 25mm;
            }

            /* Remove shadow and background for print */
            .page {
                box-shadow: none !important;
            }

            /* Handle orphans and widows */
            p, li, h2, h5, h6 {
                orphans: 3;
                widows: 3;
                page-break-inside: avoid;
            }

            /* Prevent breaking in clauses */
            .numbered-clause {
                page-break-after: avoid;
            }

            /* Keep tables together */
            table {
                page-break-inside: avoid;
            }

            /* Keep signature sections together */
            .signature-section {
                page-break-inside: avoid;
            }

            @page {
                size: A4;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            /* Stamp space printing */
            .stamp-space {
                display: none;
            }

            .stamp-mode .page:first-child .stamp-space {
                display: block;
                height: 100mm;
            }

            .stamp-text {
                color: #666;
                border-bottom: 2px dashed #999;
            }
        }

        /* =========================
            SCREEN PREVIEW STYLES
        ========================= */
        @media screen {
            .agreement-container {
                margin: 20px auto;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }

            .page {
                margin-bottom: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.15);
                background: #fff;
            }

            /* Show page breaks in preview */
            .page:not(:last-child) {
                border-bottom: 1px dashed #ccc;
            }
        }

        /* =========================
            UTILITY CLASSES
        ========================= */
        .text-center {
            text-align: center !important;
        }

        .fw-bold {
            font-weight: bold !important;
        }

        .mt-20 {
            margin-top: 20px !important;
        }

        .mb-20 {
            margin-bottom: 20px !important;
        }

        /* Control buttons */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .print-controls label {
            display: block;
            margin-bottom: 5px;
        }

        .print-controls input[type="radio"] {
            margin-right: 8px;
        }

        .print-btn {
            width: 100%;
            margin-top: 10px;
        }

        /* Keep content in pages properly spaced */
        .content-wrapper {
            min-height: calc(297mm - 40mm);
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body>
    <!-- Print Controls -->
    <div class="no-print print-controls">
        <div class="mb-2">
            <label>
                <input type="radio" name="printMode" value="e" checked> Without Stamp
            </label>
            <label>
                <input type="radio" name="printMode" value="s"> With Stamp
            </label>
        </div>
        <button onclick="window.print()" class="btn btn-sm btn-primary print-btn">Print</button>
    </div>

    <!-- Document Container -->
    <div class="agreement-container" id="doc">

        <!-- PAGE 1 -->
        <div class="page">
            <!-- Stamp space (only on first page when in stamp mode) -->
            <div class="stamp-space">
                <div class="stamp-text">
                    <div style="font-size: 14pt; margin-bottom: 10px;">Space for Physical Stamp Paper</div>
                    <div style="font-size: 10pt; color: #666;">
                        (Print on stamp paper; content starts below)
                    </div>
                </div>
            </div>

            <div class="page-content">
                <div class="content-wrapper">
                    <h2>DISTRIBUTORSHIP AGREEMENT</h2>

                    <div class="agreement-content">
                        <p><strong>This Distributorship Agreement ("the Agreement")</strong>
                        is made and entered into as of [_______________Effective Date], by and between:</p>

                        <p>
                            <strong>VNR Seeds Private Limited</strong>,
                            a company incorporated under the provisions of the Companies Act, 1956,
                            having its registered office at Corporate Centre, Canal Road Crossing,
                            Ring Road No. 1, Raipur, Chhattisgarh – 492006
                            (hereinafter referred to as <strong>the "First Party"</strong>).
                        </p>

                        <p class="text-center fw-bold">AND</p>

                        <p>
                            <strong>{{ $establishmentName ?? '____________________' }}</strong>,
                            Distributor Name:
                            <strong>{{ $contactPersonName ?? '____________________' }}</strong>,
                            having its place of business at
                            <strong>{{ $formattedAddress ?? '____________________' }}</strong>,
                            a <strong>{{ $entityType ?? '_____________' }}</strong>
                            (hereinafter referred to as <strong>the "Second Party"</strong>).
                        </p>

                        <p>For the purposes of this Agreement, the <strong>First Party shall be referred to as the "Company" and the Second Party shall be referred to as the "Authorized Distributor."</strong> The Company and the Authorized Distributor are hereinafter collectively referred to as the <strong>"Parties"</strong> and individually referred to as a <strong>"Party."</strong></p>

                        <p><strong>WHEREAS,</strong> the Company is engaged in the manufacture, production, and supply of Seeds (the "Products") and desires to appoint the Authorized Distributor on the non exclusive basis to sell and distribute the Products;</p>

                        <p><strong>AND WHEREAS,</strong> the Authorized Distributor possesses the ability to promote the sale of the Products and desires to develop demand for and sell the Products and has expressed interest in distributing and selling the Company's products within a specified territory; and</p>

                        <p><strong>AND WHEREAS,</strong> the Company hereby appoints the Authorized Distributor, and the Authorized Distributor hereby accepts such appointment, to distribute and sell the Company's product under the terms and conditions set forth in this Agreement.</p>

                        <p><strong>NOW, THEREFORE, IN LIGHT OF THE MUTUAL AGREEMENTS AND COMMITMENTS CONTAINED HEREIN, THE PARTIES HEREBY AGREE AS FOLLOWS:</strong></p>

                        <div class="numbered-clause">1. Appointment of Authorized Distributor</div>
                        <ol>
                            <li>The Company hereby appoints the Authorized Distributor, upon completion and acceptance of the Distributor appointment form, on a non-exclusive basis for the sale of the Company's products. The Authorized Distributor accepts this appointment and agrees to diligently act as the Company's distributor for the crop vertical and within the specified territory as outlined in appointment form.</li>
                            <li>The Authorized Distributor shall submit all requested documents to the Company. These may include, but are not limited to, photocopies of the Partnership Deed, Profit & Loss Account, Balance Sheets for prior years, educational certificates, GST Number, PAN, IEC, Seed License Number, Aadhaar Number, and any other documents specified by the Company. These documents must be provided along with the signed Agreement.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 2 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div class="numbered-clause">2. Term of Agreement</div>
                        <p>This agreement shall be effective from ___________ and shall remain in force for the period unless terminated by either Party in writing.</p>

                        <div class="numbered-clause">3. Security Deposit / Bank guarantee</div>
                        <ol>
                            <li>The Authorized Distributor shall maintain a security deposit with the Company of Rs.25,000/- (Rupees Twenty Five Thousand Only), or any other amount as may be specified by the Company based on the volume of business. This security deposit is to ensure the fulfillment of the obligations and terms and conditions outlined in this Agreement, including prompt payment for supplies made by the Company.</li>
                            <li>At the Company's discretion, and upon specific request by the Authorized Distributor, the Company may allow the Authorized Distributor to substitute the security deposit with a Bank Guarantee from any Scheduled Bank, in a form approved by the Company. The right to enforce the Bank Guarantee shall vest in the Company as stated in the terms of the Guarantee. The Authorized Distributor agrees not to dispute the enforcement of the Guarantee in accordance with its terms.</li>
                        </ol>

                        <div class="numbered-clause">4. Responsibility of Authorized Distributor</div>
                        <ol>
                            <li><strong>Placement of order</strong>
                                <p>The Authorized Distributor shall place the orders to the Company at its registered office, or to such other office/s as may subsequently be notified by the Company.</p>
                            </li>
                            <li><strong>Pricing and Payment</strong>
                                <p>The Authorized Distributor shall purchase products at the prices specified by the Company or as amended by the Company from time to time. The applicable prices will be those in effect at the time of delivery. The Authorized Distributor shall make payments for the products directly to the Company's bank account.</p>
                            </li>
                            <li><strong>Product packaging and labeling</strong>
                                <p>This Agreement pertains exclusively to the Company's Product portfolio marketed by the Company. The Authorized Distributor agrees to sell the Company's products exclusively in their original packaging with the original label affixed. The Authorized Distributor shall not tamper with, alter, or repackage any products supplied by the Company.</p>
                            </li>
                            <li><strong>Sale Price of Product</strong>
                                <p>The Authorized Distributor shall sell the Products at the prices fixed by the Company. The Authorized Distributor is not permitted to charge prices lower than the maximum prices fixed/intimated by the Company.</p>
                            </li>
                            <li><strong>Record Keeping and Change of Status</strong>
                                <ol style="list-style-type: lower-roman;">
                                    <li>The Authorized Distributor agrees to maintain accurate records of the stock of the Company's products. These records shall be open for inspection by the Company's representatives as and when required.</li>
                                    <li>The Authorized Distributor shall promptly notify the Company in writing of any changes to its legal status or organizational structure, including but not limited to changes in ownership, management, business constitution, or any form of legal reorganization, to facilitate the updating of official records. The continuation of this Agreement following such changes shall be subject solely to the discretion of the Company.</li>
                                </ol>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 3 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <ol start="6" style="margin-top: 0;">
                            <li><strong>Promotion and Cooperation</strong>
                                <p>In addition to maintaining adequate stock levels, it is understood that the Authorized Distributor shall actively engage in sales activities, including participation in local and/or regional fairs and exhibitions, to promote the Company's products to the best of their ability. The Authorized Distributor shall endeavor to foster close cooperation with the Company's representatives. This includes:</p>
                                <ol style="list-style-type: lower-roman;">
                                    <li>Actively promoting the Company's products through local and regional events.</li>
                                    <li>Contributing proactively to sales promotion efforts.</li>
                                    <li>Collaborating closely with Company's representatives to enhance market presence and sales effectiveness.</li>
                                </ol>
                            </li>
                            <li><strong>Compliance with Laws and Licenses</strong>
                                <p>The Authorized Distributor shall be responsible for obtaining and maintaining all necessary licenses and permissions required under Local, Municipal, State, and Central Government laws and regulations to possess, store, deal with, and dispose of the products. The Authorized Distributor further agrees to comply with all applicable laws and regulations in force from time to time.</p>
                            </li>
                        </ol>

                        <div class="numbered-clause">5. Delivery of Products</div>
                        <ol>
                            <li><strong>Delivery Address:</strong> The Company shall deliver the ordered products to the address specified by the Authorized Distributor.</li>
                            <li><strong>Liability and Transport Cost:</strong> The Company's liability for the products shall cease upon delivery to the carrier at the dispatching point. The transport cost shall be borne by the Company.</li>
                            <li><strong>Self-Pickup Approval:</strong> The Authorized Distributor may request to collect the products directly from the Company's location, subject to prior approval by the Company.</li>
                            <li><strong>Responsibility Upon Pickup:</strong> If approved, the entire responsibility for the products shall transfer to the Authorized Distributor upon collection, in such case no claims and liability other than the transport or delivery costs shall be entertained by the Company.</li>
                        </ol>

                        <div class="numbered-clause">6. Sales Return and Representations</div>
                        <ol>
                            <li>The Company does not accept returns of sold and supplied products under any circumstances. The Company is not obligated to accept back any damaged stocks without prior written approval, except as agreed upon in normal business practices between the Company and the Authorized Distributor.</li>
                            <li>During the term of this agreement, the Authorized Distributor shall sell the Company's products according to the specifications provided by the Company. The Authorized Distributor shall not make any representations or warranties regarding the products beyond those specified in the Company's prevailing conditions of sale at the time of offering of sale. The Authorized Distributor agrees to indemnify the Company against losses, damages, or claims arising from unauthorized representations. The Company is not liable for acts or defaults of the Authorized Distributor, their employees, or representatives.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 4 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div class="numbered-clause">7. Credit Limit Facility</div>
                        <ol>
                            <li>The Authorized Distributor shall be eligible to avail a credit limit facility as determined and communicated by an Authorized Representative of the Company. This credit limit will be based on the security deposit maintained with the Company, Company's policies (subject to changes), and the performance of the Authorized Distributor.</li>
                            <li>The Company may, at its sole discretion, increase the Authorized Distributor's credit limit beyond the initially approved amount, subject to any terms and conditions it may prescribe.</li>
                        </ol>

                        <div class="numbered-clause">8. Payment Terms and Methods</div>
                        <ol>
                            <li><strong>Payment term</strong>
                                <p>The Authorized Distributor shall make all payments to the Company by Demand Draft, RTGS, or NEFT, in advance.</p>
                            </li>
                            <li><strong>Payment Modes and Consequences of Dishonored Cheques</strong>
                                <p>The Authorized Distributor shall make all payments due to the Company for supplies provided under this Agreement by way of Demand Draft, RTGS, or NEFT, to the bank account designated by the Company. In exceptional circumstances where the Company agrees to accept a cheque, such cheques must be honored at all times. The dishonor of any cheque shall constitute a material breach of this Agreement. In such an event, the Company reserves the right to immediately terminate the appointment of the Authorized Distributor without prior notice. Furthermore, the Company may initiate appropriate legal proceedings and impose penalties or recovery charges at its discretion.</p>
                            </li>
                            <li><strong>Credit Limit Payment Requirements</strong>
                                <p>If the Authorized Distributor avails the credit limit facility, payments must be accompanied by specific references to the corresponding invoices paid. In the absence of invoice references, the Company will allocate the payment amount to the oldest outstanding invoices.</p>
                            </li>
                        </ol>

                        <div class="numbered-clause">9. Appointment of Sub Distributors and Indemnification</div>
                        <ol>
                            <li>The Authorized Distributor shall not appoint Sub Distributors without the prior written consent of the Company. The Authorized Distributor shall be solely responsible for ensuring that any Sub Distributors comply with all rights and liabilities conferred upon the Authorized Distributor by the Company under this Agreement.</li>
                            <li>The Authorized Distributor agrees and undertakes to indemnify the Company against any loss, damage, claim, or demand arising from:
                                <div style="margin-left: 20px;">- Any act, deed, misfeasance, or negligence of the Sub Distributor, its servants, agents, or contracted parties.</div>
                                <div style="margin-left: 20px;">- Breach of any terms or conditions of this Agreement, including failure to comply with the Distributor's directions or instructions related to the products sold by the Company.</div>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 5 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <ol start="3">
                            <li>The Authorized Distributor acknowledges that its appointment under this Agreement does not constitute or imply any agency, partnership, joint venture, or employment relationship between the Company and either the Authorized Distributor or any Sub-Distributor appointed by the Authorized Distributor. Any Sub-Distributor shall be engaged solely at the discretion and responsibility of the Authorized Distributor and shall have no authority to act on behalf of, bind, or make representations for the Company. The relationship between the Authorized Distributor and any Sub-Distributor shall be entirely independent, that of a supplier and purchaser, and the Company shall have no obligation or liability in connection therewith.</li>
                        </ol>

                        <div class="numbered-clause">10. Cheque Issuance and Authorization</div>
                        <ol>
                            <li>The Authorized Distributor acknowledges and agrees that, in compliance with its payment obligations under this Agreement, it has issued the following cheques in favor of the Company to settle outstanding dues arising from its transactions with the Company. The Authorized Distributor further agrees to promptly issue replacement cheques and furnish the updated banking information in the event of any changes of its bank account details, or upon request by the Company.</li>
                        </ol>

                        <table class="blank-table">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Cheque No.</th>
                                    <th>Bank Name</th>
                                    <th>Branch</th>
                                    <th>Account Holder Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                 <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                 <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <ol start="2">
                            <li><strong>Authorization to Present Cheques</strong>
                                <p>The Authorized Distributor hereby authorizes the Company to present the aforementioned cheques, or any cheques issued in the future, for payment of outstanding dues. The Authorized Distributor agrees not to contest any legal proceedings arising from the dishonor of the cheques, including but not limited to claims related to material alterations, insufficient funds, or other issues pertaining to the presentation of these cheques.</p>
                            </li>
                            <li><strong>Commitments of the Authorized Distributor</strong>
                                <p>The Authorized Distributor commits to the following regarding the issued cheques:</p>
                                <ol style="list-style-type: lower-roman;">
                                    <li>If there are any changes to the bank details (including mergers, demergers, or other alterations), the Authorized Distributor shall promptly issue new cheques and provide an updated declaration.</li>
                                    <li>The Authorized Distributor shall not issue stop-payment instructions on any of the cheques issued.</li>
                                    <li>The Authorized Distributor shall not close the account from which the cheques are drawn or request the return or deferment of the cheques for any reason.</li>
                                    <li>The Authorized Distributor shall ensure that sufficient funds are maintained in the relevant account(s) to cover the total amount of the cheques at all times.</li>
                                </ol>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 6 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div class="numbered-clause">11. Declarations and Documentation</div>
                        <p>The Authorized Distributor agrees to provide the following declarations as an integral part of this Agreement and acknowledges that all the information provided in these declarations is crucial to this Agreement. Any failure to submit accurate or complete information may lead to legal consequences or the termination of this Agreement:</p>
                        <ol>
                            <li><strong>Declaration of Relations</strong> – In the format prescribed by the Company.</li>
                            <li><strong>Authorization Letter</strong> – For the Authorized Distributor's appointed representatives.</li>
                            <li><strong>Declaration of Non-Applicability of GST</strong> – If applicable.</li>
                            <li><strong>Entity Ownership/ Management Declaration</strong> – Detailing the ownership and management structure of the Authorized Distributor's entity.</li>
                        </ol>

                        <div class="numbered-clause">12. Confidentiality</div>
                        <p>All information provided by the Company to the Authorized Distributor under or in connection with this agreement shall remain confidential and is the exclusive property of the Company. The Authorized Distributor shall take all necessary measures to prevent theft, damage, loss, or unauthorized access to this confidential information. The Authorized Distributor shall not copy or disclose any confidential information without the prior written consent of the Company. This obligation of confidentiality shall survive any variation, renewal, or termination of this agreement. However, it shall not apply to information that becomes part of the public domain through no fault of either party, their employees, agents, or representatives.</p>

                        <div class="numbered-clause">13. Prohibition Clauses</div>
                        <p>The Authorized Distributor hereby undertakes and agrees with the Company to observe and perform the terms and conditions outlined in this Agreement throughout its duration, including:</p>
                        <ol>
                            <li>shall comply with all policies of the Company as announced from time to time.</li>
                            <li>shall not sell goods to any person, body corporate, or entity that they know or have reason to believe intends to resell the products outside the Authorized Distributor's territory.</li>
                            <li>shall not initiate any contest or promotional/prize scheme concerning the Company's products without prior written approval from the Company.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 7 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <ol start="4">
                            <li>shall not use the name, trademark, or logo of the Company on letterheads or in any manner except as approved by the Company.</li>
                            <li>shall not assign or attempt to assign the benefits of this Agreement without the prior written consent of the Company.</li>
                        </ol>

                        <div class="numbered-clause">14. Termination of Agreement</div>
                        <ol>
                            <li><strong>Termination Notice:</strong>
                                <p>Either Party may terminate this Agreement at any time by providing one month's notice in writing. Notice shall be sent by registered post, speed post, fax, or courier to the registered office of the Company or the Authorized Distributor, as applicable.</p>
                            </li>
                            <li><strong>Company's Right to Terminate:</strong>
                                <p>Without prejudice to any other remedies available to the Company, the Company may terminate this Agreement immediately by giving written notice to the Authorized Distributor under any of the following circumstances:</p>
                                <ol style="list-style-type: lower-roman;">
                                    <li>The Authorized Distributor breaches any terms or conditions of this Agreement.</li>
                                    <li>The Authorized Distributor is unable to perform their duties for a continuous period of three months or for a total of three months within any twelve-month period.</li>
                                    <li>The Authorized Distributor engages in conduct deemed prejudicial to the Company's interests.</li>
                                    <li>The Authorized Distributor attempts to assign or transfer the rights or obligations of this Agreement without the written consent of the Company.</li>
                                </ol>
                            </li>
                            <li><strong>Consequences of Termination:</strong>
                                <p>Upon termination of this Agreement for any reason the Authorized Distributor shall promptly return or dispose of, as instructed by the Company, all samples, instruction books, technical pamphlets, catalogs, advertising material, POP material, signboards, and any other materials related to the Company's business. Any materials in possession of the Authorized Distributor shall remain the property of the Company, and the Authorized Distributor shall hold them as Bailee until their return or disposal. The Authorized Distributor is obligated to promptly settle all outstanding dues with the Company, as per the provided statement of account and notices. Non-compliance may lead to the Company utilizing the security deposit or encashing the Bank Guarantee, or pursuing other legal remedies as deemed necessary.</p>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 8 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <ol start="4">
                            <li><strong>Revocation of Authorizations</strong>
                                <ol style="list-style-type: lower-roman;">
                                    <li><strong>Automatic Revocation of Authorization:</strong>
                                        <p>Upon termination of this Agreement for any reason, all authorizations granted to the Authorized Distributor under this Agreement will be automatically revoked.</p>
                                    </li>
                                    <li><strong>Prohibition on Use of Principal Certificate:</strong>
                                        <p>After termination, the Authorized Distributor shall not use any certificates or documents issued by the Company to carry out business as a dealer, as per applicable seed laws and regulations.</p>
                                    </li>
                                    <li><strong>Legal Action:</strong>
                                        <p>The Authorized Distributor hereby acknowledges and accepts these terms, fully understanding that any violation of this clause may result in legal action by the Company. The Authorized Distributor accepts these terms with full knowledge of the potential legal consequences.</p>
                                    </li>
                                </ol>
                            </li>
                        </ol>

                        <div class="numbered-clause">15. Force Majeure</div>
                        <p>The Company shall not incur any legal liability for delays in performance of this agreement resulting directly or indirectly from circumstances beyond its control. These circumstances include, but are not limited to, fire, explosion, accidents, floods, labor disputes or shortages, war, hostilities, acts of government or authorized bodies, government orders or restrictions, inability to obtain suitable materials, transportation issues, or acts of God. The Company shall be the sole judge in determining the impact of such events, and its decision will be binding on the Authorized Distributor.</p>

                        <div class="numbered-clause">16. Governing Law and Dispute Resolution</div>
                        <ol>
                            <li>The laws of India shall govern this Agreement. Both parties irrevocably submit to the exclusive jurisdiction of the Courts in Raipur, for any action or proceeding regarding this Agreement.</li>
                            <li>Any dispute, claim, or controversy arising out of or relating to this Agreement shall first be attempted to be resolved amicably through negotiation or mediation. If unresolved within thirty (30) days of written notice, the dispute shall be referred to arbitration under the Arbitration and Conciliation Act, 1996 (as amended). A sole arbitrator, mutually appointed by the Parties, shall conduct the arbitration. The arbitration shall be conducted in Raipur, Chhattisgarh, and the proceedings shall be conducted in English or Hindi. The arbitral award shall be final and binding on both Parties.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 9 -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div class="numbered-clause">17. Entire Agreement</div>
                        <p>This Agreement, including all annexures, constitutes the entire agreement between the Parties with respect to the subject matter hereof and supersedes all prior and contemporaneous agreements, understandings, and representations.</p>

                        <div class="numbered-clause">18. Amendments</div>
                        <p>No amendment or modification of this Agreement shall be effective unless in writing and signed by both Parties.</p>

                        <div class="numbered-clause">19. Assignment</div>
                        <p>Neither Party may assign its rights or obligations under this Agreement without the prior written consent of the other Party.</p>

                        <div class="numbered-clause">20. Notices</div>
                        <p>All notices and confirmations required or permitted under this Agreement shall be in writing and delivered to the addresses specified in this Agreement (or to such other addresses as a party may designate by written notice). Notices shall be deemed given (a) when delivered personally, (b) three days after being sent by certified mail, return receipt requested, postage prepaid, or (c) upon confirmation of delivery when sent by email.</p>

                        <div class="numbered-clause">21. Severability</div>
                        <p>If any provision of this Agreement is held to be invalid or unenforceable, the remaining provisions shall continue in full force and effect.</p>

                        <div class="numbered-clause">22. Waiver</div>
                        <p>The failure of either Party to enforce any right or provision of this Agreement shall not constitute a waiver of such right or provision.</p>

                        <div class="signature-section">
                            <p style="text-align: center; font-weight: bold; margin: 30px 0;">IN WITNESS WHEREOF THE PARTIES HERETO HAVE EXECUTED THIS AGREEMENT THE DAY AND YEAR FIRST ABOVE WRITTEN</p>

                            <table style="width: 100%; margin-top: 40px; border: none;">
                                <tr>
                                    <td style="width: 50%; border: none; vertical-align: top; padding-right: 20px;">
                                        <p style="border-bottom: 1px solid #000; padding-bottom: 40px;">&nbsp;</p>
                                        <p><strong>First Party</strong></p>
                                        <p><strong>VNR Seeds Private Limited</strong></p>
                                        <p><strong>Name:</strong> ________________</p>
                                        <p><strong>Designation:</strong> ________________</p>
                                    </td>
                                    <td style="width: 50%; border: none; vertical-align: top; padding-left: 20px;">
                                        <p style="border-bottom: 1px solid #000; padding-bottom: 40px;">&nbsp;</p>
                                        <p><strong>Second Party</strong></p>
                                        <p><strong>Name:</strong> {{ $contactPersonName ?? '________________' }}</p>
                                        <p><strong>Designation:</strong> ________________</p>
                                    </td>
                                </tr>
                            </table>

                            <table style="width: 100%; margin-top: 60px; border: none;">
                                <tr>
                                    <td style="width: 50%; border: none; vertical-align: top; padding-right: 20px;">
                                        <p><strong>Witness:</strong></p>
                                        <p style="border-bottom: 1px solid #000; padding-bottom: 20px; margin-top: 20px;">&nbsp;</p>
                                        <p>Signature_________________</p>
                                        <p>Name- _________________</p>
                                        <p>Address- ________________</p>
                                    </td>
                                    <td style="width: 50%; border: none; vertical-align: top; padding-left: 20px;">
                                        <p><strong>Witness:</strong></p>
                                        <p style="border-bottom: 1px solid #000; padding-bottom: 20px; margin-top: 20px;">&nbsp;</p>
                                        <p>Signature_________________</p>
                                        <p>Name- _________________</p>
                                        <p>Address- ________________</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 10 - Appendix A -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div style="text-align: center; margin: 0 0 30px 0;">
                            <h5 style="text-decoration: underline;">A. Declaration of Relations</h5>
                        </div>

                        <p><strong>Please update applicable information</strong></p>

                        <h6>A. In case no relative is working with the Company</h6>

                        <p>To,</p>
                        <p>VNR Seeds Private Limited</p>
                        <p>Corporate Center, Canal Road Crossing,</p>
                        <p>Ring Road No.1, Raipur-492006, Chhattisgarh</p>

                        <p><strong>Subject:</strong> Declaration of Relations</p>

                        <p>Dear Sir/Madam,</p>

                        <p>I/ We hereby declare that none of our relatives are working with VNR Seeds Private Limited or any of its group companies directly or indirectly.</p>

                        <p style="margin-top: 40px;">______________________________</p>
                        <p>Seal & Signature of Authorized Distributor</p>
                        <p style="font-size: 10pt; font-style: italic;">I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents.</p>

                        <h6 style="margin-top: 30px;">B. In case any relative is working with the Company</h6>

                        <p>To,<br>
                        VNR Seeds Private Limited<br>
                        Corporate Center, Canal Road Crossing,<br>
                        Ring Road No.1, Raipur-__________, Chhattisgarh</p>

                        <p><strong>Subject:</strong> Declaration of Relations</p>

                        <p>Dear Sir/Madam,</p>

                        <p>I/ We hereby declare that following persons is my / our relative working with the Company:</p>

                        <table class="blank-table">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Name of Persons</th>
                                    <th>Location</th>
                                    <th>Relation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="margin-top: 40px;">______________________________</p>
                        <p>Seal & Signature of Authorized Distributor</p>
                        <p style="font-size: 10pt; font-style: italic;">I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 11 - Appendix B -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div style="text-align: center; margin: 0 0 30px 0;">
                            <h5 style="text-decoration: underline;">B. Authorisation Letter</h5>
                        </div>

                        <p>To,<br>
                        VNR Seeds Private Limited<br>
                        Corporate Center, Canal Road Crossing,<br>
                        Ring Road No.1, Raipur-__________, Chhattisgarh</p>

                        <p><strong>Subject:</strong> Authorisation letter</p>

                        <p>Dear Sir/Ma'am,</p>

                        <p>I/We hereby grant authority to the individual named below to execute documents on our behalf. We affirm our absolute liability for the contents of all documents signed by the authorized individual.</p>

                        <p>Authorisations include but not limited to:</p>
                        <ol type="a">
                            <li>Balance Confirmations</li>
                            <li>Stock & Invoices Confirmations</li>
                            <li>Any other official documents</li>
                        </ol>

                        <p><strong>Details of Authorized Person:</strong></p>

                        <table class="blank-table">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Name of Person</th>
                                    <th>Relationship</th>
                                    <th>Specimen Signature</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="margin-top: 40px;">______________________________</p>
                        <p>Seal & Signature of Proprietor/ Partners/Directors</p>
                        <p style="font-size: 10pt; font-style: italic;">I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 12 - Appendix C -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div style="text-align: center; margin: 0 0 30px 0;">
                            <h5 style="text-decoration: underline;">C. Declaration of Non-Applicability of GST</h5>
                        </div>

                        <p><strong>Date:</strong> ________________</p>

                        <p>To,<br>
                        VNR Seeds Private Limited<br>
                        Corporate Center, Canal Road Crossing,<br>
                        Ring Road No.1, Raipur-492006, Chhattisgarh</p>

                        <p><strong>Subject:</strong> Declaration of Non-Applicability of GST</p>

                        <p>Dear Sir/Madam,</p>

                        <p>I/We, the undersigned, hereby confirm that our business is not subject to the provisions of the Goods and Services Tax (GST) Act, 2017, and accordingly, we are not required to obtain GST registration or comply with its associated procedures and formalities. This declaration is made for the following reason(s) (please select as applicable):</p>

                        <p style="margin-left: 20px;">☐ Our business involves goods or services that are exempt under the Goods and Services Tax Act, 2017</p>
                        <p style="margin-left: 20px;">☐ Our turnover is below the threshold limit as specified under the Goods and Services Tax Act, 2017</p>

                        <p>I/We acknowledge that information furnished above is true to the best of our knowledge and request you to treat this communication as a declaration regarding non-requirement to be registered under the Goods and Service Tax Act, 2017.</p>

                        <p>Yours sincerely,</p>

                        <p style="margin-top: 40px;">_________________________</p>
                        <p>Seal & Signature of Authorized Distributor</p>
                        <p style="font-size: 10pt; font-style: italic;">I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 13 - Appendix D -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div style="text-align: center; margin: 0 0 30px 0;">
                            <h5 style="text-decoration: underline;">D. Entity Ownership/Management Declaration</h5>
                        </div>

                        <p>To,<br>
                        VNR Seeds Private Limited<br>
                        Corporate Center, Canal Road Crossing,<br>
                        Ring Road No.1, Raipur-__________, Chhattisgarh</p>

                        <p><strong>Subject:</strong> Entity Ownership/Management Declaration</p>

                        <p>Dear Sir/Ma'am,</p>

                        <p>This is to certify that ______________________ [Applicant Name], a _______________ [Type of Business Entity, e.g., Sole Proprietor, Partnership Firm, Private Limited Company, etc.], having its place of business at __________ ________________ __________________ ____________ _________________ [Address of Business Place]. The details of ownership/management of the entity are as follows:</p>

                        <p><strong>Name of Proprietor/Partners/Directors/Trustees:</strong></p>

                        <table class="blank-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Signature</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>

                        <p>[Company Name] is not a subsidiary or affiliate of any other company or entity.</p>
                        <p>[Additional details about ownership structure, if applicable]</p>

                        <p style="margin-top: 40px;">______________________________</p>
                        <p>Seal & Signature of Authorized Distributor</p>
                        <p style="font-size: 10pt; font-style: italic;">I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE 14 - Appendix E -->
        <div class="page">
            <div class="page-content">
                <div class="content-wrapper">
                    <div class="agreement-content">
                        <div style="text-align: center; margin: 0 0 30px 0;">
                            <h5 style="text-decoration: underline;">E. Consent Letter / No Objection Certificate</h5>
                        </div>

                        <p><strong>Applicable only if using a relative's premises</strong></p>

                        <p>
                            To,<br>
                            VNR Seeds Private Limited<br>
                            Corporate Center, Canal Road Crossing,<br>
                            Ring Road No.1, Raipur-_____, Chhattisgarh
                        </p>

                        <p><strong>To Whom It May Concern,</strong></p>

                        <p>
                            This is to certify that I, _____ [Owner Name], residing at _____ _____ [Owner's Address],
                            hereby consent to the use of my premises located at _____ for
                            [Business Purpose, e.g., office space, warehouse, retail store, etc.] by
                            [Name of Business Entity/ Applicant].
                        </p>

                        <p>
                            I hereby confirm that I have no objection to the use of the said premises
                            for the aforementioned purpose.
                        </p>

                        <p>[Add any specific conditions or limitations, if applicable]</p>

                        <p style="margin-top: 40px;">__________________________</p>
                        <p>Signature of Owner</p>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- End of agreement-container -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('doc');
            const radios = document.querySelectorAll('input[name="printMode"]');
            
            // Initialize with no stamp mode
            container.classList.remove('stamp-mode');
            
            radios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.value === 's') {
                        container.classList.add('stamp-mode');
                    } else {
                        container.classList.remove('stamp-mode');
                    }
                });
            });
            
            // Handle print events
            window.addEventListener('beforeprint', function() {
                // Ensure proper styling before print
                document.body.style.zoom = "100%";
            });
        });
    </script>
</body>
</html>