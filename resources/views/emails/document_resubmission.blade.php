@component('mail::message')
# 📋 Document Resubmission
New documents uploaded for review

@component('mail::alert', ['type' => 'success'])
**Resubmission Received!** The applicant has uploaded updated documents for your review.
@endcomponent

---

## 📄 Application Details

| Field | Value |
|-------|--------|
| **Application ID** | {{ $application_code }} |
| **Submitted By** | {{ $user_name }} |
| **Business Name** | **{{ $establishment_name }}** |
| **Resubmitted** | {{ $resubmitted_at }} |

---

@if($feedback)
## 📝 Previous MIS Feedback

@if($checkpoints)
### Verification Checkpoints
@foreach($checkpoints as $name => $data)
- **{{ $name }}**  
  @if($data['status'] === 'verified')
  ✔ **Verified**
  @else
  ❌ **Rejected**
  @endif

  @if($data['reason'])
  _Reason_: {{ $data['reason'] }}
  @endif

@endforeach
@endif

@if($additional_documents && count($additional_documents) > 0)
### Additional Requirements:
@foreach($additional_documents as $doc)
- **{{ $doc['name'] }}**  
  @if($doc['remark'])
  _{{ $doc['remark'] }}_
  @endif
@endforeach
@endif

@endif

---

## 🎯 Action Required
Please review the updated documents and complete verification within **3 business days**.

@component('mail::button', ['url' => config('app.frontend_url') . '/applications/' . $application_id . '/verify', 'color' => 'success'])
Review Documents
@endcomponent

@component('mail::button', ['url' => config('app.frontend_url') . '/applications/' . $application_id, 'color' => 'primary'])
View Application
@endcomponent

---

### 🔄 Current Status
**Documents Resubmitted**  
Priority: **High** - Requires immediate attention

---

Thanks & Regards,  
**{{ config('app.name') }} - Application Management System**

> _This is an automated message. Please do not reply._
@endcomponent
