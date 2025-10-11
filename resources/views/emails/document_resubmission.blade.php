<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Resubmission</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #a8dadc 0%, #f1faee 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 300; }
        .content { padding: 30px; }
        .alert { padding: 12px 15px; margin-bottom: 20px; border-radius: 6px; border-left: 4px solid; }
        .alert-warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .alert-danger { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .checkpoint-item { display: flex; align-items: flex-start; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid; }
        .checkpoint-icon { margin-right: 10px; font-size: 18px; margin-top: 2px; }
        .checkpoint-verified .checkpoint-icon { color: #28a745; }
        .checkpoint-rejected .checkpoint-icon { color: #dc3545; }
        .checkpoint-details { flex: 1; }
        .checkpoint-title { font-weight: 600; margin-bottom: 4px; color: #333; }
        .checkpoint-reason { font-size: 14px; color: #666; font-style: italic; }
        .additional-doc { background: #fff3cd; padding: 10px; margin-bottom: 10px; border-radius: 4px; border-left: 4px solid #ffc107; }
        .additional-doc h6 { margin: 0 0 5px 0; color: #856404; }
        .additional-doc p { margin: 0; color: #666; font-size: 14px; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 14px; color: #6c757d; }
        .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; }
        .btn-outline { background-color: transparent; color: #007bff; border: 1px solid #007bff; }
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-verified { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        @media (max-width: 600px) { .email-container { margin: 10px; } .content { padding: 20px; } }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Document Resubmission</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">New documents uploaded for review</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="alert alert-success">
                <strong>Resubmission Received!</strong> The applicant has uploaded updated documents for your review.
            </div>

            <!-- Application Details -->
            <div class="mb-4 p-3" style="background: #f8f9fa; border-radius: 6px;">
                <h5 style="margin: 0 0 15px 0; color: #495057;">📄 Application Details</h5>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
                    <div>
                        <strong>Application ID:</strong><br>
                        <span style="color: #6c757d;">{{ $application_code }}</span>
                    </div>
                    <div>
                        <strong>Submitted By:</strong><br>
                        <span style="color: #6c757d;">{{ $user_name }}</span>
                    </div>
                    <div>
                        <strong>Business Name:</strong><br>
                        <span style="color: #495057; font-weight: 500;">{{ $establishment_name }}</span>
                    </div>
                    <div>
                        <strong>Resubmitted:</strong><br>
                        <span style="color: #6c757d;">{{ $resubmitted_at }}</span>
                    </div>
                </div>
            </div>

            <!-- Previous Feedback Summary -->
            @if($feedback)
            <div class="mb-4">
                <h5 style="margin: 0 0 15px 0; color: #495057;">📝 Previous MIS Feedback</h5>
                
                @if($checkpoints)
                <div style="margin-bottom: 20px;">
                    <h6 style="margin: 0 0 10px 0; color: #856404;">Verification Checkpoints</h6>
                    @foreach($checkpoints as $name => $data)
                        <div class="checkpoint-item {{ $data['status'] === 'verified' ? 'checkpoint-verified' : 'checkpoint-rejected' }}">
                            <i class="checkpoint-icon ri-{{ $data['status'] === 'verified' ? 'check' : 'close' }}-circle-fill"></i>
                            <div class="checkpoint-details">
                                <div class="checkpoint-title">{{ $name }}</div>
                                @if($data['reason'])
                                    <div class="checkpoint-reason">{{ $data['reason'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                @if($additional_documents && count($additional_documents) > 0)
                <div>
                    <h6 style="margin: 0 0 10px 0; color: #856404;">Additional Requirements</h6>
                    @foreach($additional_documents as $doc)
                        <div class="additional-doc">
                            <h6>{{ $doc['name'] }}</h6>
                            @if($doc['remark'])
                                <p>{{ $doc['remark'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Action Items -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin-top: 20px;">
                <h5 style="margin: 0 0 15px 0; color: #495057;">🎯 Action Required</h5>
                <p style="margin: 0 0 15px 0; color: #6c757d;">
                    Please review the updated documents and complete verification within <strong>3 business days</strong>.
                </p>
                
                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ config('app.frontend_url') }}/applications/{{ $application_id }}/verify" class="btn" style="background: #28a745;">
                        <i class="ri-eye-line me-1"></i>Review Documents
                    </a>
                    <a href="{{ config('app.frontend_url') }}/applications/{{ $application_id }}" class="btn btn-outline">
                        <i class="ri-file-list-line me-1"></i>View Application
                    </a>
                </div>
            </div>

            <!-- Status Summary -->
            <div class="mt-4 p-3" style="background: #e9ecef; border-radius: 6px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><strong>Current Status:</strong></span>
                    <span class="status-badge status-pending">Documents Resubmitted</span>
                </div>
                <div style="margin-top: 10px; font-size: 12px; color: #6c757d;">
                    <i class="ri-time-line me-1"></i>
                    Priority: <strong>High</strong> - Resubmission requires immediate attention
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 5px 0;">
                <strong>{{ config('app.name') }}</strong> - Application Management System
            </p>
            <p style="margin: 0; font-size: 12px;">
                This is an automated message. Please do not reply to this email.<br>
            </p>
        </div>
    </div>
</body>
</html>