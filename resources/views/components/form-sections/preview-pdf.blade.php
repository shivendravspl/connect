<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distributors Appointment Form ({{ $application->application_code ?? 'N/A' }})</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 15mm 10mm;
            line-height: 1.4;
            background-color: #fff;
        }
        h1 {
            font-size: 14pt;
            text-align: center;
            margin: 10px 0;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            font-weight: bold;
        }
        h3 {
            font-size: 12pt;
            background-color: #f0f0f0;
            padding: 6px 10px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
            font-weight: bold;
        }
        h4, h5 {
            font-size: 10pt;
            margin: 8px 0;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            border: 1px solid #000;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 9pt;
            vertical-align: top;
        }
        .table th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .table td.label {
            width: 30%;
            font-weight: bold;
        }
        .table td.value {
            width: 70%;
            font-weight: normal;
        }
        .table td.q_label {
            width: 70%;
            font-weight: bold;
        }
        .table td.q_value {
            width: 30%;
            font-weight: normal;
        }
        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 10px 0;
        }
        .header-info {
            font-size: 9pt;
            text-align: right;
            margin-bottom: 10px;
        }
        .header-container {
            font-size: 10pt;
            margin-bottom: 10px;
        }
        .header-container div {
            display: inline-block;
            width: 33%;
            font-weight: bold;
        }
        .footer-container {
            font-size: 9pt;
            position: running(footer);
        }
        .footer-container div {
            display: inline-block;
            width: 33%;
        }
        .declaration-text {
            font-size: 9pt;
            padding: 8px;
            border: 1px dashed #666;
            margin: 10px 0;
            background-color: #f9f9f9;
        }
        p {
            margin: 5px 0;
            font-size: 10pt;
        }
        @page {
            header: html_header;
            footer: html_footer;
        }
    </style>
</head>
<body>
    <htmlpageheader name="header">
        <div class="header-container" style="width:100%; display:flex; justify-content: space-between; font-size: 10pt; font-weight:bold;">
            <div>Version 1.0</div>
            <div>Distributor Appointment Form</div>
            <div>VNR Seeds Pvt. Ltd.</div>
        </div>
        <hr>
    </htmlpageheader>

    <htmlpagefooter name="footer">
        <div class="footer-container">
            {{--<div style="text-align: left;">Date of Release: 09 July 2025</div>--}}
            <div style="text-align: center;">Released by VNR Seeds</div>
        </div>
    </htmlpagefooter>

    <h1>Distributors Appointment Form</h1>
    <div class="header-info">
        <p><strong>Application Code:</strong> {{ $application->application_code ?? 'N/A' }}</p>
        <p><strong>Generated At:</strong> {{ now()->format('d-m-Y H:i') }}</p>
    </div>

    @include('components.form-sections.review-content', [
        'application' => $application,
        'years' => $years,
        'states' => $states,
        'districts' => $districts,
        'countries' => $countries
    ])
</body>
</html>