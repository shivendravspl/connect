<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Distributorship Agreement - {{ $application->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
    .agreement-container {
        max-width: 210mm;
        margin: 0 auto;
        background: white;
        padding: 20px;
    }
    .agreement-header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #333;
        padding-bottom: 20px;
    }
    .agreement-content {
        line-height: 1.6;
        font-size: 14px;
        margin-top: 4px;
    }
    .signature-section {
        margin-top: 50px;
    }
    .stamp-space {
        height: 400px;
        border: 1px dashed #ccc;
        margin-top: 20px;
    }
    .action-buttons {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }
    .blank-table td {
        height: 40px;
    }
    
    /* Improved Page Break Styles */
    .new-page {
        page-break-before: always;
        margin-top: 80px !important; /* Increased top margin for better spacing */
        padding-top: 20px; /* Additional padding for visual separation */
    }
    
    /* First page after stamp space (if stamp option selected) */
    .first-content-page {
        margin-top: 40px;
    }
    
    /* Regular content pages */
    .content-page {
        margin-top: 60px;
    }
    
    /* Page header for subsequent pages */
    .page-header {
        text-align: center;
        margin-bottom: 30px;
        font-size: 16px;
        font-weight: bold;
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
    }
    
    /* Ensure proper spacing for paragraphs at page starts */
    .page-content p:first-child {
        margin-top: 20px;
    }
    
    /* Table spacing improvements */
    .page-content table {
        margin: 20px 0;
    }
    
    /* List spacing improvements */
    .page-content ul, .page-content ol {
        margin: 15px 0;
        padding-left: 30px;
    }
    
    /* Heading spacing improvements */
    .page-content h5, .page-content h6 {
        margin-top: 25px;
        margin-bottom: 15px;
    }

    @media print {
        .action-buttons {
            display: none;
        }
        .no-print {
            display: none;
        }
        .new-page {
            page-break-before: always;
            margin-top: 100px !important; /* Even more space for print */
            padding-top: 30px;
        }
        .page-content {
            page-break-inside: avoid;
        }
        /* Prevent orphans and widows */
        p, h1, h2, h3, h4, h5, h6 {
            orphans: 3;
            widows: 3;
        }
        /* Better table handling in print */
        table {
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>
</head>
<body>
    <div class="action-buttons no-print">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="ri-printer-line"></i> Print
            </button>
            <button type="button" class="btn btn-success btn-sm" onclick="downloadPDF()">
                <i class="ri-download-line"></i> Download PDF
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.close()">
                <i class="ri-close-line"></i> Close
            </button>
        </div>
        
        <div class="mt-2">
            <label class="form-label small">Print Option:</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="printOption" id="eStampOption" value="e-stamp" checked>
                <label class="form-check-label small" for="eStampOption">E-Stamp (Full Page)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="printOption" id="stampOption" value="stamp">
                <label class="form-check-label small" for="stampOption">With Stamp Paper (Space for Stamp)</label>
            </div>
        </div>
    </div>

    <div class="agreement-container">
        <!-- Agreement Header -->
        <div class="agreement-header">
            <h2>DISTRIBUTORSHIP AGREEMENT</h2>
        </div>

        <!-- Stamp Space (Visible only when stamp option is selected) -->
        <div id="stampSpace" class="stamp-space text-center d-none">
            <p class="text-muted mt-5"></p>
        </div>

        <!-- Page 1 Content -->
        <div class="agreement-content page-content first-content-page">
            <p><strong>This Distributorship Agreement ("the Agreement")</strong> is made and entered into as of [_______________Effective Date], by and between:</p>
            
            <p><strong>VNR Seeds Private Limited</strong> a company incorporated under the provisions of the Companies Act, 1956, having its registered office at Corporate Centre, Canal Road Crossing, Ring Road No. 1, Raipur, Chhattisgarh – 492006 represented by Authorized Representative, (hereinafter referred to as the "First Party", which expression shall, unless repugnant to the context or meaning thereof, mean and include its affiliates, successors and permitted assigns) of One Part.</p>
            
            <p><strong>AND</strong></p>
            
            <p><strong>{{ $application->entityDetails->establishment_name ?? '[Distributors Firm Name]' }}</strong>, Distributor Name <strong>{{ $application->getAuthorizedOrEntityName() ?? '[Distributor Name ]' }}</strong>, having its Company place of business <strong>{{ $application->entityDetails->getFullAddress() ?? '[Distributor Address]' }}</strong>, (hereinafter referred to as the "Second Party", which expression shall, unless repugnant to the context or meaning thereof, mean and include its affiliates, successors and permitted assigns) of Other Part.</p>
        </div>

        <!-- Page 2 -->
        <div class="new-page agreement-content page-content">
            <p>For the purposes of this Agreement, the First Party shall be referred to as the "Company" and the Second Party shall be referred to as the "Authorized Distributor." The Company and the Authorized Distributor are hereinafter collectively referred to as the “Parties” and individually referred to as a “Party.”</p>

            <p><strong>WHEREAS,</strong> the Company is engaged in the manufacture, production, and supply of Seeds (the “Products”) and desires to appoint the Authorized Distributor on the non exclusive basis to sell and distribute the Products; </p>

            <p><strong>AND WHEREAS,</strong> the Authorized Distributor possesses the ability to promote the sale of the Products and desires to develop demand for and sell the Products and has expressed interest in distributing and selling the Company's products within a specified territory; and</p>

            <p><strong>AND WHEREAS,</strong> the Company hereby appoints the Authorized Distributor, and the Authorized Distributor hereby accepts such appointment, to distribute and sell the Company's product under the terms and conditions set forth in this Agreement.</p>

            <p><strong>NOW, THEREFORE, IN LIGHT OF THE MUTUAL AGREEMENTS AND COMMITMENTS CONTAINED HEREIN, THE PARTIES HEREBY AGREE AS FOLLOWS:</strong></p>

            <h5>Appointment of Authorized Distributor</h5>
            <p>The Company hereby appoints the Authorized Distributor, upon completion and acceptance of the Distributor appointment form, on a non-exclusive basis for the sale of the Company’s products. The Authorized Distributor accepts this appointment and agrees to diligently act as the Company’s distributor for the crop vertical and within the specified territory as outlined in appointment form.</p>

            <p>The Authorized Distributor shall submit all requested documents to the Company. These may include, but are not limited to, photocopies of the Partnership Deed, Profit & Loss Account, Balance Sheets for prior years, educational certificates, GST Number, PAN, IEC, Seed License Number, Aadhaar Number, and any other documents specified by the Company. These documents must be provided along with the signed Agreement.</p>

            <h5>Term of Agreement</h5>
            <p>This agreement shall be effective from ___________ and shall remain in force for the period unless terminated by either Party in writing.</p>
        </div>

        <!-- Page 3 -->
        <div class="new-page agreement-content page-content">
            <h5>Security Deposit / Bank guarantee</h5>
            <p>The Authorized Distributor shall maintain a security deposit with the Company of Rs.25,000/- (Rupees  Twenty  Five Thousand Only), or any other amount as may be specified by the Company based on the volume of business. This security deposit is to ensure the fulfillment of the obligations and terms and conditions outlined in this Agreement, including prompt payment for supplies made by the Company.</p>

            <p>At the Company's discretion, and upon specific request by the Authorized Distributor, the Company may allow the Authorized Distributor to substitute the security deposit with a Bank Guarantee from any Scheduled Bank, in a form approved by the Company. The right to enforce the Bank Guarantee shall vest in the Company as stated in the terms of the Guarantee. The Authorized Distributor agrees not to dispute the enforcement of the Guarantee in accordance with its terms.</p>

            <h5>Responsibility of Authorized Distributor</h5>

            <h6>Placement of order</h6>
            <p>The Authorized Distributor shall place the orders to the Company at its registered office, or to such other office/s as may subsequently be notified by the Company.</p>

            <h6>Pricing and Payment</h6>
            <p>The Authorized Distributor shall purchase products at the prices specified by the Company or as amended by the Company from time to time. The applicable prices will be those in effect at the time of delivery. The Authorized Distributor shall make payments for the products directly to the Company's bank account.</p>

            <h6>Product packaging and labeling</h6>
            <p>This Agreement pertains exclusively to the Company’s Product portfolio marketed by the Company. The Authorized Distributor agrees to sell the Company’s products exclusively in their original packaging with the original label affixed. The Authorized Distributor shall not tamper with, alter, or repackage any products supplied by the Company. </p>

            <h6>Sale Price of Product</h6>
            <p>The Authorized Distributor shall sell the Products at the prices fixed by the Company. The Authorized Distributor is not permitted to charge prices lower than the maximum prices fixed/intimated by the Company.</p>
        </div>

        <!-- Page 4 -->
        <div class="new-page agreement-content page-content">
            <h6>Record Keeping and Change of Status</h6>
            <p>The Authorized Distributor agrees to maintain accurate records of the stock of the Company’s products. These records shall be open for inspection by the Company’s representatives as and when required.</p>

            <p>The Authorized Distributor shall promptly notify the Company in writing of any changes to its legal status or organizational structure, including but not limited to changes in ownership, management, business constitution, or any form of legal reorganization, to facilitate the updating of official records. The continuation of this Agreement following such changes shall be subject solely to the discretion of the Company.</p>

            <h6>Promotion and Cooperation</h6>
            <p>In addition to maintaining adequate stock levels, it is understood that the Authorized Distributor shall actively engage in sales activities, including participation in local and/or regional fairs and exhibitions, to promote the Company’s products to the best of their ability. The Authorized Distributor shall endeavor to foster close cooperation with the Company’s representatives. This includes:</p>
            <ul>
                <li>Actively promoting the Company’s products through local and regional events.</li>
                <li>Contributing proactively to sales promotion efforts.</li>
                <li>Collaborating closely with Company’s representatives to enhance market presence and sales effectiveness.</li>
            </ul>

            <h6>Compliance with Laws and Licenses</h6>
            <p>The Authorized Distributor shall be responsible for obtaining and maintaining all necessary licenses and permissions required under Local, Municipal, State, and Central Government laws and regulations to possess, store, deal with, and dispose of the products. The Authorized Distributor further agrees to comply with all applicable laws and regulations in force from time to time.</p>

            <h5>Delivery of Products</h5>
            <p><strong>Delivery Address:</strong> The Company shall deliver the ordered products to the address specified by the Authorized Distributor.</p>

            <p><strong>Liability and Transport Cost:</strong> The Company’s liability for the products shall cease upon delivery to the carrier at the dispatching point. The transport cost shall be borne by the Company.</p>

            <p><strong>Self-Pickup Approval:</strong> The Authorized Distributor may request to collect the products directly from the Company’s location, subject to prior approval by the Company.</p>

            <p><strong>Responsibility Upon Pickup:</strong> If approved, the entire responsibility for the products shall transfer to the Authorized Distributor upon collection, in such case no claims</p>
        </div>

        <!-- Page 5 -->
        <div class="new-page agreement-content page-content">
            <p>and liability other than the transport or delivery costs shall be entertained by the Company.</p>

            <h5>Sales Return and Representations</h5>
            <p>The Company does not accept returns of sold and supplied products under any circumstances. The Company is not obligated to accept back any damaged stocks without prior written approval, except as agreed upon in normal business practices between the Company and the Authorized Distributor.</p>

            <p>During the term of this agreement, the Authorized Distributor shall sell the Company's products according to the specifications provided by the Company. The Authorized Distributor shall not make any representations or warranties regarding the products beyond those specified in the Company’s prevailing conditions of sale at the time of offering of sale. The Authorized Distributor agrees to indemnify the Company against losses, damages, or claims arising from unauthorized representations. The Company is not liable for acts or defaults of the Authorized Distributor, their employees, or representatives.</p>

            <h5>Credit Limit Facility</h5>
            <p>The Authorized Distributor shall be eligible to avail a credit limit facility as determined and communicated by an Authorized Representative of the Company. This credit limit will be based on the security deposit maintained with the Company, Company’s policies (subject to changes), and the performance of the Authorized Distributor. </p>

            <p>The Company may, at its sole discretion, increase the Authorized Distributor’s credit limit beyond the initially approved amount, subject to any terms and conditions it may prescribe.</p>

            <h5>Payment Terms and Methods</h5>

            <h6>Payment term</h6>
            <p>The Authorized Distributor shall make all payments to the Company by Demand Draft, RTGS, or NEFT, in advance.</p>

            <h6>Payment Modes and Consequences of Dishonored Cheques</h6>
            <p>The Authorized Distributor shall make all payments due to the Company for supplies provided under this Agreement by way of Demand Draft, RTGS, or NEFT, to the bank account designated by the Company. In exceptional circumstances where the Company agrees to accept a cheque, such cheques must be honored at all times. The dishonor of any cheque shall constitute a material breach of this Agreement. In such </p>
        </div>

        <!-- Page 6 -->
        <div class="new-page agreement-content page-content">
            <p>an event, the Company reserves the right to immediately terminate the appointment of the Authorized Distributor without prior notice. Furthermore, the Company may initiate appropriate legal proceedings and impose penalties or recovery charges at its discretion.</p>

            <h6>Credit Limit Payment Requirements</h6>
            <p>If the Authorized Distributor avails the credit limit facility, payments must be accompanied by specific references to the corresponding invoices paid. In the absence of invoice references, the Company will allocate the payment amount to the oldest outstanding invoices.</p>

            <h5>Appointment of Sub Distributors and Indemnification   </h5>
            <p>The Authorized Distributor shall not appoint Sub Distributors without the prior written consent of the Company. The Authorized Distributor shall be solely responsible for ensuring that any Sub Distributors comply with all rights and liabilities conferred upon the Authorized Distributor by the Company under this Agreement.</p>

            <p>The Authorized Distributor agrees and undertakes to indemnify the Company against any loss, damage, claim, or demand arising from:</p>
            <ul>
                <li>-Any act, deed, misfeasance, or negligence of the Sub Distributor, its servants, agents, or contracted parties.</li>
                <li>-Breach of any terms or conditions of this Agreement, including failure to comply with the Distributor's directions or instructions related to the products sold by the Company.</li>
            </ul>

            <p>The Authorized Distributor acknowledges that its appointment under this Agreement does not constitute or imply any agency, partnership, joint venture, or employment relationship between the Company and either the Authorized Distributor or any Sub-Distributor appointed by the Authorized Distributor. Any Sub-Distributor shall be engaged solely at the discretion and responsibility of the Authorized Distributor and shall have no authority to act on behalf of, bind, or make representations for the Company. The relationship between the Authorized Distributor and any Sub-Distributor shall be entirely independent, that of a supplier and purchaser, and the Company shall have no obligation or liability in connection therewith.</p>

            <h5>Cheque Issuance and Authorization</h5>
            <p>The Authorized Distributor acknowledges and agrees that, in compliance with its payment obligations under this Agreement, it has issued the following cheques in favor of the Company to settle outstanding dues arising from its transactions with the</p>
        </div>

        <!-- Page 7 -->
        <div class="new-page agreement-content page-content">
            <p>Company. The Authorized Distributor further agrees to promptly issue replacement cheques and furnish the updated banking information in the event of any changes of its bank account details, or upon request by the Company.</p>
            <table class="table table-bordered blank-table">
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
                        <td>1</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <!-- Add more rows as needed -->
                </tbody>
            </table>

            <h6>Authorization to Present Cheques</h6>
            <p>The Authorized Distributor hereby authorizes the Company to present the aforementioned cheques, or any cheques issued in the future, for payment of outstanding dues. The Authorized Distributor agrees not to contest any legal proceedings arising from the dishonor of the cheques, including but not limited to claims related to material alterations, insufficient funds, or other issues pertaining to the presentation of these cheques.</p>

            <h6>Commitments of the Authorized Distributor</h6>
            <p>The Authorized Distributor commits to the following regarding the issued cheques:</p>
            <ul>
                <li>If there are any changes to the bank details (including mergers, demergers, or other alterations), the Authorized Distributor shall promptly issue new cheques and provide an updated declaration.</li>
                <li>The Authorized Distributor shall not issue stop-payment instructions on any of the cheques issued.</li>
                <li>The Authorized Distributor shall not close the account from which the cheques are drawn or request the return or deferment of the cheques for any reason.</li>
                <li>The Authorized Distributor shall ensure that sufficient funds are maintained in the relevant account(s) to cover the total amount of the cheques at all times.</li>
            </ul>

            <h5>Declarations and Documentation</h5>
            <p>The Authorized Distributor agrees to provide the following declarations as an integral part of this Agreement and acknowledges that all the information provided in these declarations is crucial to this Agreement. Any failure to submit accurate or complete information may lead to legal consequences or the termination of this Agreement:</p>
            <ul>
                <li>Declaration of Relations – In the format prescribed by the Company.</li>
                <li>Authorization Letter – For the Authorized Distributor’s appointed representatives.</li>
                <li>Declaration of Non-Applicability of GST – If applicable.</li>
                <li>Entity Ownership/ Management Declaration – Detailing the ownership and management structure of the Authorized Distributor’s entity.</li>
        </div>

        <!-- Page 8 -->
        <div class="new-page agreement-content page-content">
            <li>Consent Letter/No Objection Certificate – If the Authorized Distributor is utilizing a relative's premises for business operations.</li>
            </ul>

            <h5>Confidentiality</h5>
            <p>All information provided by the Company to the Authorized Distributor under or in connection with this agreement shall remain confidential and is the exclusive property of the Company. The Authorized Distributor shall take all necessary measures to prevent theft, damage, loss, or unauthorized access to this confidential information. The Authorized Distributor shall not copy or disclose any confidential information without the prior written consent of the Company. This obligation of confidentiality shall survive any variation, renewal, or termination of this agreement. However, it shall not apply to information that becomes part of the public domain through no fault of either party, their employees, agents, or representatives.</p>

            <h5>Prohibition Clauses</h5>
            <p>The Authorized Distributor hereby undertakes and agrees with the Company to observe and perform the terms and conditions outlined in this Agreement throughout its duration, including:</p>
            <ul>
                <li>shall comply with all policies of the Company as announced from time to time.</li>
                <li>shall not sell goods to any person, body corporate, or entity that they know or have reason to believe intends to resell the products outside the Authorized Distributor's territory.</li>
                <li>shall not initiate any contest or promotional/prize scheme concerning the Company’s products without prior written approval from the Company.</li>
                <li>shall not use the name, trademark, or logo of the Company on letterheads or in any manner except as approved by the Company.</li>
                <li>shall not assign or attempt to assign the benefits of this Agreement without the prior written consent of the Company.</li>
            </ul>

            <h5>Termination of Agreement</h5>

            <h6>Termination Notice:</h6>
            <p>Either Party may terminate this Agreement at any time by providing one month’s notice in writing. Notice shall be sent by registered post, speed post, fax, or courier to the registered office of the Company or the Authorized Distributor, as applicable.</p>

            <h6>Company’s Right to Terminate:</h6>
            <p>Without prejudice to any other remedies available to the Company, the Company may terminate this Agreement immediately by giving written notice to the Authorized Distributor under any of the following circumstances:</p>
        </div>

        <!-- Page 9 -->
        <div class="new-page agreement-content page-content">
            <ul>
                <li>The Authorized Distributor breaches any terms or conditions of this Agreement.</li>
                <li>The Authorized Distributor is unable to perform their duties for a continuous period of three months or for a total of three months within any twelve-month period.</li>
                <li>The Authorized Distributor engages in conduct deemed prejudicial to the Company’s interests.</li>
                <li>The Authorized Distributor attempts to assign or transfer the rights or obligations of this Agreement without the written consent of the Company.</li>
            </ul>

            <h6>Consequences of Termination:</h6>
            <p>Upon termination of this Agreement for any reason the Authorized Distributor shall promptly return or dispose of, as instructed by the Company, all samples, instruction books, technical pamphlets, catalogs, advertising material, POP material, signboards, and any other materials related to the Company’s business. Any materials in possession of the Authorized Distributor shall remain the property of the Company, and the Authorized Distributor shall hold them as Bailee until their return or disposal. The Authorized Distributor is obligated to promptly settle all outstanding dues with the Company, as per the provided statement of account and notices. Non-compliance may lead to the Company utilizing the security deposit or encashing the Bank Guarantee, or pursuing other legal remedies as deemed necessary.</p>

            <h5>Revocation of Authorizations</h5>
            <p><strong>Automatic Revocation of Authorization: </strong></p>
            <p>Upon termination of this Agreement for any reason, all authorizations granted to the Authorized Distributor under this Agreement will be automatically revoked. </p>

            <p><strong>Prohibition on Use of Principal Certificate: </strong></p>
            <p>After termination, the Authorized Distributor shall not use any certificates or documents issued by the Company to carry out business as a dealer, as per applicable seed laws and regulations.</p>

            <p><strong>Legal Action: </strong></p>
            <p>The Authorized Distributor hereby acknowledges and accepts these terms, fully understanding that any violation of this clause may result in legal action by the Company. The Authorized Distributor accepts these terms with full knowledge of the potential legal consequences.</p>

            <h5>Force Majeure</h5>
            <p>The Company shall not incur any legal liability for delays in performance of this agreement resulting directly or indirectly from circumstances beyond its control. These circumstances include, but are not limited to, fire, explosion, accidents, floods, labor disputes or shortages, war, hostilities, acts of government or authorized bodies, government orders or restrictions, inability to obtain suitable materials, transportation issues, or acts of God. The Company shall be the sole judge in determining the impact of such events, and its decision will be binding on the Authorized Distributor.</p>
        </div>

        <!-- Page 10 -->
        <div class="new-page agreement-content page-content">
            <h5>Governing Law and Dispute Resolution</h5>
            <p>The laws of India shall govern this Agreement. Both parties irrevocably submit to the exclusive jurisdiction of the Courts in Raipur, for any action or proceeding regarding this Agreement.</p>

            <p>Any dispute, claim, or controversy arising out of or relating to this Agreement shall first be attempted to be resolved amicably through negotiation or mediation. If unresolved within thirty (30) days of written notice, the dispute shall be referred to arbitration under the Arbitration and Conciliation Act, 1996 (as amended). A sole arbitrator, mutually appointed by the Parties, shall conduct the arbitration. The arbitration shall be conducted in Raipur, Chhattisgarh, and the proceedings shall be conducted in English or Hindi. The arbitral award shall be final and binding on both Parties.</p>

            <h5>Entire Agreement </h5>
            <p>This Agreement, including all annexures, constitutes the entire agreement between the Parties with respect to the subject matter hereof and supersedes all prior and contemporaneous agreements, understandings, and representations.</p>

            <h5>Amendments</h5>
            <p>No amendment or modification of this Agreement shall be effective unless in writing and signed by both Parties.</p>

            <h5>Assignment </h5>
            <p>Neither Party may assign its rights or obligations under this Agreement without the prior written consent of the other Party.</p>

            <h5>Notices</h5>
            <p>All notices and confirmations required or permitted under this Agreement shall be in writing and delivered to the addresses specified in this Agreement (or to such other addresses as a party may designate by written notice). Notices shall be deemed given (a) </p>
        </div>

        <!-- Page 11 -->
        <div class="new-page agreement-content page-content">
            <p>when delivered personally, (b) three days after being sent by certified mail, return receipt requested, postage prepaid, or (c) upon confirmation of delivery when sent by email.</p>

            <h5>Severability </h5>
            <p>If any provision of this Agreement is held to be invalid or unenforceable, the remaining provisions shall continue in full force and effect.</p>

            <h5>Waiver </h5>
            <p>The failure of either Party to enforce any right or provision of this Agreement shall not constitute a waiver of such right or provision.</p>

            <p><strong>IN WITNESS WHEREOF THE PARTIES HERETO HAVE EXECUTED THIS AGREEMENT THE DAY AND YEAR FIRST ABOVE WRITTEN</strong></p>

            <p>_________________________</p>

            <p>________________________</p>
            <p><strong>First Party</strong><br>
            VNR Seeds Private Limited<br>
            Name : ________________<br>
            Designation : ________________</p>

            <p><strong>Second Party</strong><br>
            Name:<br>
            Designation: ________________</p>

            <p><strong>Witness:</strong></p>

            <p><strong>Witness:</strong><br>
            Signature_________________<br>
            Name- _________________<br>
            Address- ________________</p>

            <p>Signature_________________<br>
            Name- __________________<br>
            Address- ________________</p>
        </div>

        <!-- Page 12 -->
        <div class="new-page agreement-content page-content">
            <h5>A. Declaration of Relations</h5>
            <p><strong>Please update applicable information</strong></p>
            <p><strong>In case no relative is working with the Company</strong></p>

            <p>To,<br>
            VNR Seeds Private Limited<br>
            Corporate Center, Canal Road Crossing,<br>
            Ring Road No.1, Raipur-492006, Chhattisgarh </p>

            <p><strong>Subject :</strong> Declaration of Relations</p>

            <p>Dear Sir/Madam,</p>

            <p>I/ We hereby declare that none of our relatives are working with VNR Seeds Private Limited  or any of its group companies directly or indirectly.</p>

            <p>______________________________<br>
            Seal & Signature of Authorized Distributor <br>
            I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents.</p>

            <p><strong>In case any relative is working with the Company</strong></p>
            <p>To,<br>
            VNR Seeds Private Limited<br>
            Corporate Center, Canal Road Crossing,<br>
            Ring Road No.1, Raipur- __________, Chhattisgarh </p>

            <p><strong>Subject :</strong> Declaration of Relations</p>
            <p>Dear Sir/Madam,</p>
            <p>1/ We hereby declare that following persons is  my / our relative working with the Company : </p>
            <table class="table table-bordered blank-table">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Name of Persons </th>
                        <th>Location</th>
                        <th>Relation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <!-- Add more rows as needed -->
                </tbody>
            </table>

            <p>______________________________<br>
            Seal & Signature of Authorized Distributor <br>
            I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents.</p>
        </div>

        <!-- Page 13 -->
        <div class="new-page agreement-content page-content">
            <h5>B. Authorisation Letter</h5>

            <p>To,<br>
            VNR Seeds Private Limited<br>
            Corporate Center, Canal Road Crossing,<br>
            Ring Road No.1, Raipur- __________, Chhattisgarh</p>

            <p><strong>Subject:</strong> Authorisation letter <br>
            Dear Sir/Ma’am,</p>

            <p>I/We hereby grant authority to the individual named below to execute documents on our behalf. We affirm our absolute liability for the contents of all documents signed by the authorized individual.</p>

            <p>Authorisations include but not limited to:<br>
            Balance Confirmations <br>
            Stock & Invoices Confirmations<br>
            Any other official documents</p>

            <p><strong>Details of Authorized Person:</strong></p>
            <table class="table table-bordered blank-table">
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
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <p>______________________________<br>
            Seal & Signature of Proprietor/ Partners/Directors<br>
            I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents</p>
        </div>

        <!-- Page 14 -->
        <div class="new-page agreement-content page-content">
            <h5>C. Declaration of Non-Applicability of GST </h5>
            <p><strong>Date :</strong> ________________</p>
            <p>To,<br>
            VNR Seeds Private Limited<br>
            Corporate Center, Canal Road Crossing,<br>
            Ring Road No.1, Raipur- 492006, Chhattisgarh</p>

            <p><strong>Subject:</strong> Declaration of Non-Applicability of GST</p>
            <p>Dear Sir/Madam,</p>

            <p>I/We, the undersigned, hereby confirm that our business is not subject to the provisions of the Goods and Services Tax (GST) Act, 2017, and accordingly, we are not required to obtain GST registration or comply with its associated procedures and formalities. This declaration is made for the following reason(s) (please select as applicable):<br>
            ☐ Our business involves goods or services that are exempt under the Goods and Services Tax Act, 2017<br>
            ☐  Our turnover is below the threshold limit as specified under the Goods and Services Tax Act, 2017</p>

            <p>I/We acknowledge that information furnished above is true to the best of our knowledge and request you to treat this communication as a declaration regarding non-requirement to be registered under the Goods and Service Tax Act, 2017.</p>

            <p>Yours sincerely,</p>

            <p>_________________________<br>
            Seal & Signature of Authorized Distributor <br>
            I/We hereby declare that the above signature is the same as reflected on the cheque and bank documents</p>
        </div>

        <!-- Page 15 -->
        <div class="new-page agreement-content page-content">
            <h5>D. Entity Ownership/Management  Declaration</h5>
            <p>To,<br>
            VNR Seeds Private Limited<br>
            Corporate Center, Canal Road Crossing,<br>
            Ring Road No.1, Raipur- __________, Chhattisgarh</p>

            <p><strong>Subject:</strong> Entity Ownership/Management Declaration</p>

            <p>Dear Sir/Ma’am,</p>

            <p>This is to certify that ______________________ [Applicant Name], a _______________ [Type of Business Entity, e.g., Sole Proprietor, Partnership Firm, Private Limited Company, etc.], having its place of business at __________ ________________ __________________ ____________ _________________ [Address of Business Place]. The details of ownership/,management of the entity are as follows:</p>

            <p><strong>Name of Proprietor/Partners/Directors/Trustees:</strong></p>
            <table class="table table-bordered blank-table">
                <thead>
                    <tr>
                        <th>Name </th>
                        <th>Designation</th>
                        <th>Signature</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                    </tr>
					  <tr>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                    </tr>
					  <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
					  <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <!-- Add more rows as needed -->
                </tbody>
            </table>

            <p>[Company Name] is not a subsidiary or affiliate of any other company or entity.<br>
            [Additional details about ownership structure, if applicable]</p>

            <p>______________________________<br>
            Seal & Signature of Authorized Distributor<br>
            I/We hereby declare that the above signature is the same as reflected on the cheque and bank </p>
        </div>

        <!-- Page 16 -->
        <div class="new-page agreement-content page-content">
            <h5>E. Consent Letter / No Objection Certificate</h5>

            <p><strong>Applicable only if using a relative's premises</strong></p>

            <p>To,<br>
            VNR Seeds Private Limited<br>
            Corporate Center, Canal Road Crossing,<br>
            Ring Road No.1, Raipur- __________, Chhattisgarh</p>

            <p>To Whom It May Concern,</p>

            <p>This is to certify that I, _____________ [Owner Name], residing at __________________ _______________________[Owner’s Address], hereby consent to the use of my premises located at ___________________________  for [Business Purpose, e.g., office space, warehouse, retail store, etc.] by [Name of Business Entity/ Applicant].</p>

            <p>I hereby confirm that I have no objection to the use of the said premises for the aforementioned purpose.</p>

            <p>[Add any specific conditions or limitations, if applicable]</p>

            <p>__________________________<br>
            Signature of Owner</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        // Handle print option changes
        document.querySelectorAll('input[name="printOption"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const stampSpace = document.getElementById('stampSpace');
                if (this.value === 'stamp') {
                    stampSpace.classList.remove('d-none');
                } else {
                    stampSpace.classList.add('d-none');
                }
            });
        });

        // PDF Download function
        function downloadPDF() {
            const element = document.querySelector('.agreement-container');
            const opt = {
                margin: 10,
                filename: 'distributorship-agreement-{{ $application->id }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }

        // Auto-adjust layout based on print option
        document.addEventListener('DOMContentLoaded', function() {
            const stampOption = document.getElementById('stampOption');
            if (stampOption.checked) {
                document.getElementById('stampSpace').classList.remove('d-none');
            }
        });
    </script>
</body>
</html>