Connect — Project Documentation
Last Updated: October 25, 2025
Contents

Overview
Setup Guide
2.1 Minimum Requirements
2.2 Installation Steps
Architecture Overview
3.1 Application/Project Structure
3.2 Frameworks/Libraries Used
3.3 Component Interaction
Application Workflow
4.1 Dual Approval Workflow System
4.1.1 MIS-Initiated (Fast-Track)
4.1.2 Sales-Initiated (Hierarchical Approval)
4.1.3 Flowcharts
Users & Roles
Tech Stack & Conventions
Authentication Flow
Routing Structure
8.1 Complete Distributor Routes (distributor_route.php)
8.2 Admin/Super Admin Routes
Onboarding (8-step) Workflow
Approval & Status Flow
Detailed Workflow Explanation
11.1 MIS-Initiated Fast-Track Workflow
11.2 Sales-Initiated Hierarchical Workflow
MIS Document Verification
Core API Integration
Master Data Management
Admin/Super Admin Dashboard
Database Schema
Permission Structure
Frontend Notes & Snippets
Controllers
Development & Deployment
Troubleshooting
How to Extend
Where to Look
Next Actions


1 Overview
Connect is a distributor onboarding and approval application primarily used by sales personnel and MIS users. The system supports two distinct approval workflows: MIS-initiated (fast-track) and Sales-initiated (hierarchical). The immediate goal is to provide a smooth distributor onboarding workflow (8-step form) and a multi-level approval pipeline. Later phases plan to add vendor creation and PO generation.
Key Capabilities:

Dual Approval Workflows: MIS-initiated (fast-track) vs Sales-initiated (hierarchical)
Sales users create distributor onboarding requests (multi-step form)
Hierarchical approvals (ABM → RBM → ZBM → GM → MIS)
MIS document verification (PAN, GST, Seed License, Bank docs, Authorization letters, Aadhar)
Additional document requests and resubmission handling
Physical dispatch tracking for onboarding paperwork
Admin/Super Admin: Master data management, user access control, data synchronization with core APIs


2 Setup Guide
2.1 Minimum Requirements
The Connect application requires the following environment and tools for development:

Programming Language: PHP 8.1
Framework: Laravel 10.x
Database: MySQL 5.7
Web Server: Apache or Nginx
PHP Modules: PDO, MBString, BCMath, Ctype, JSON, XML, cURL
Development Tools: Composer, Git, Node.js/NPM, Code Editor (e.g., VS Code)

2.2 Installation Steps

Install Git to clone the code repository.
Install Composer, the PHP package manager, to install dependencies:
textcurl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

Install Node.js and NPM to compile frontend assets:
textcurl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

Clone the repository into the web server directory:
textgit clone https://github.com/your-repo/connect.git /var/www/html/connect
cd /var/www/html/connect

Install PHP dependencies via Composer:
textcomposer install --optimize-autoloader --no-dev

Install NPM dependencies:
textnpm install && npm run build

Configure environment variables in .env file (copy .env.example and update with your settings, e.g., database credentials, AWS S3, API tokens).
Generate application key:
textphp artisan key:generate

Run database migrations:
textphp artisan migrate --seed

Start the development server:
textphp artisan serve


The application can now be accessed at http://127.0.0.1:8000 for development.

3 Architecture Overview
3.1 Application/Project Structure
The Connect application follows a standard Laravel project structure:

app: Contains application code and core logic

Http/Controllers: Web controllers (e.g., OnboardingController, ApprovalController)
Models: Eloquent models (e.g., Application, EntityDetails)
Exports: Excel export classes for reports
Helpers: Helper functions
Imports: Master data import files
Spotlight: Search functionality related files
Mails: Email-related files
Providers: Service providers


config: Application configuration files
database: Database migration and seed files
public: Publicly accessible assets
resources: Frontend assets, views, and language files
routes: Application routes/endpoints
vendor: Composer dependencies (third-party libraries)

3.2 Frameworks/Libraries Used

Laravel: Main application framework
Blade: Frontend templating
MySQL: Database
Bootstrap 5: CSS framework
jQuery: JavaScript library for dynamic interactions
Canvas.js: Data visualization
Spatie Roles & Permission: Access management
Laravel Excel: Excel import/export
Spotlight: Search functionality
Yajra Datatable: Datatable functionality

3.3 Component Interaction
Laravel Blade templates render the frontend UI and pass data to it, calling controllers for processing. Controllers validate request data and interact with models, which represent business objects like Application and User. Business logic may be delegated to service classes or repositories. Database queries use Eloquent ORM. Frontend state management relies on Laravel Session and Flash messaging. jQuery handles dynamic UI interactions in Blade templates. Authentication and authorization are managed via Spatie Roles & Permission, with role-based Gate policies securing endpoints.

4 Application Workflow
4.1 Dual Approval Workflow System
The system implements two workflows to accommodate different initiation scenarios, ensuring flexibility while maintaining control.
4.1.1 MIS-Initiated (Fast-Track)

Description: MIS users create applications that bypass the sales hierarchy for faster processing.
Path: MIS User Creates Application → Direct MIS Processing → Document Verification → Distributorship Created
Status Flow: draft → initiated → mis_processing → document_verified → distributorship_created
Use Case: Internal MIS team creating distributorships directly without sales team involvement.

4.1.2 Sales-Initiated (Hierarchical Approval)

Description: Sales personnel applications require sequential approvals from managers before MIS verification.
Path: Sales Person Creates → ABM Approval → RBM Approval → ZBM Approval → GM Approval → MIS Processing → Document Verification → Distributorship Created
Status Flow: draft → initiated → under_review (various levels) → mis_processing → document_verified → distributorship_created
Use Case: Standard sales team distributor onboarding process.

4.1.3 Flowcharts
Below are Mermaid flowcharts representing the workflows. You can copy-paste these into a Mermaid renderer (e.g., mermaid.live) for visualization.
MIS-Initiated (Fast-Track) Workflow:
#mermaid-diagram-mermaid-bu13iv0{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;fill:#000000;}@keyframes edge-animation-frame{from{stroke-dashoffset:0;}}@keyframes dash{to{stroke-dashoffset:0;}}#mermaid-diagram-mermaid-bu13iv0 .edge-animation-slow{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 50s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-bu13iv0 .edge-animation-fast{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 20s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-bu13iv0 .error-icon{fill:#552222;}#mermaid-diagram-mermaid-bu13iv0 .error-text{fill:#552222;stroke:#552222;}#mermaid-diagram-mermaid-bu13iv0 .edge-thickness-normal{stroke-width:1px;}#mermaid-diagram-mermaid-bu13iv0 .edge-thickness-thick{stroke-width:3.5px;}#mermaid-diagram-mermaid-bu13iv0 .edge-pattern-solid{stroke-dasharray:0;}#mermaid-diagram-mermaid-bu13iv0 .edge-thickness-invisible{stroke-width:0;fill:none;}#mermaid-diagram-mermaid-bu13iv0 .edge-pattern-dashed{stroke-dasharray:3;}#mermaid-diagram-mermaid-bu13iv0 .edge-pattern-dotted{stroke-dasharray:2;}#mermaid-diagram-mermaid-bu13iv0 .marker{fill:#666;stroke:#666;}#mermaid-diagram-mermaid-bu13iv0 .marker.cross{stroke:#666;}#mermaid-diagram-mermaid-bu13iv0 svg{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;}#mermaid-diagram-mermaid-bu13iv0 p{margin:0;}#mermaid-diagram-mermaid-bu13iv0 .label{font-family:"trebuchet ms",verdana,arial,sans-serif;color:#000000;}#mermaid-diagram-mermaid-bu13iv0 .cluster-label text{fill:#333;}#mermaid-diagram-mermaid-bu13iv0 .cluster-label span{color:#333;}#mermaid-diagram-mermaid-bu13iv0 .cluster-label span p{background-color:transparent;}#mermaid-diagram-mermaid-bu13iv0 .label text,#mermaid-diagram-mermaid-bu13iv0 span{fill:#000000;color:#000000;}#mermaid-diagram-mermaid-bu13iv0 .node rect,#mermaid-diagram-mermaid-bu13iv0 .node circle,#mermaid-diagram-mermaid-bu13iv0 .node ellipse,#mermaid-diagram-mermaid-bu13iv0 .node polygon,#mermaid-diagram-mermaid-bu13iv0 .node path{fill:#eee;stroke:#999;stroke-width:1px;}#mermaid-diagram-mermaid-bu13iv0 .rough-node .label text,#mermaid-diagram-mermaid-bu13iv0 .node .label text,#mermaid-diagram-mermaid-bu13iv0 .image-shape .label,#mermaid-diagram-mermaid-bu13iv0 .icon-shape .label{text-anchor:middle;}#mermaid-diagram-mermaid-bu13iv0 .node .katex path{fill:#000;stroke:#000;stroke-width:1px;}#mermaid-diagram-mermaid-bu13iv0 .rough-node .label,#mermaid-diagram-mermaid-bu13iv0 .node .label,#mermaid-diagram-mermaid-bu13iv0 .image-shape .label,#mermaid-diagram-mermaid-bu13iv0 .icon-shape .label{text-align:center;}#mermaid-diagram-mermaid-bu13iv0 .node.clickable{cursor:pointer;}#mermaid-diagram-mermaid-bu13iv0 .root .anchor path{fill:#666!important;stroke-width:0;stroke:#666;}#mermaid-diagram-mermaid-bu13iv0 .arrowheadPath{fill:#333333;}#mermaid-diagram-mermaid-bu13iv0 .edgePath .path{stroke:#666;stroke-width:2.0px;}#mermaid-diagram-mermaid-bu13iv0 .flowchart-link{stroke:#666;fill:none;}#mermaid-diagram-mermaid-bu13iv0 .edgeLabel{background-color:white;text-align:center;}#mermaid-diagram-mermaid-bu13iv0 .edgeLabel p{background-color:white;}#mermaid-diagram-mermaid-bu13iv0 .edgeLabel rect{opacity:0.5;background-color:white;fill:white;}#mermaid-diagram-mermaid-bu13iv0 .labelBkg{background-color:rgba(255, 255, 255, 0.5);}#mermaid-diagram-mermaid-bu13iv0 .cluster rect{fill:hsl(0, 0%, 98.9215686275%);stroke:#707070;stroke-width:1px;}#mermaid-diagram-mermaid-bu13iv0 .cluster text{fill:#333;}#mermaid-diagram-mermaid-bu13iv0 .cluster span{color:#333;}#mermaid-diagram-mermaid-bu13iv0 div.mermaidTooltip{position:absolute;text-align:center;max-width:200px;padding:2px;font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:12px;background:hsl(-160, 0%, 93.3333333333%);border:1px solid #707070;border-radius:2px;pointer-events:none;z-index:100;}#mermaid-diagram-mermaid-bu13iv0 .flowchartTitleText{text-anchor:middle;font-size:18px;fill:#000000;}#mermaid-diagram-mermaid-bu13iv0 rect.text{fill:none;stroke-width:0;}#mermaid-diagram-mermaid-bu13iv0 .icon-shape,#mermaid-diagram-mermaid-bu13iv0 .image-shape{background-color:white;text-align:center;}#mermaid-diagram-mermaid-bu13iv0 .icon-shape p,#mermaid-diagram-mermaid-bu13iv0 .image-shape p{background-color:white;padding:2px;}#mermaid-diagram-mermaid-bu13iv0 .icon-shape rect,#mermaid-diagram-mermaid-bu13iv0 .image-shape rect{opacity:0.5;background-color:white;fill:white;}#mermaid-diagram-mermaid-bu13iv0 :root{--mermaid-font-family:"trebuchet ms",verdana,arial,sans-serif;}VerifiedIssues FoundDraftInitiated by MISMIS ProcessingDocument VerificationDistributorship CreatedRequest Additional DocsResubmit DocsEnd
Sales-Initiated (Hierarchical) Workflow:
#mermaid-diagram-mermaid-dvpi9s6{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;fill:#000000;}@keyframes edge-animation-frame{from{stroke-dashoffset:0;}}@keyframes dash{to{stroke-dashoffset:0;}}#mermaid-diagram-mermaid-dvpi9s6 .edge-animation-slow{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 50s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-dvpi9s6 .edge-animation-fast{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 20s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-dvpi9s6 .error-icon{fill:#552222;}#mermaid-diagram-mermaid-dvpi9s6 .error-text{fill:#552222;stroke:#552222;}#mermaid-diagram-mermaid-dvpi9s6 .edge-thickness-normal{stroke-width:1px;}#mermaid-diagram-mermaid-dvpi9s6 .edge-thickness-thick{stroke-width:3.5px;}#mermaid-diagram-mermaid-dvpi9s6 .edge-pattern-solid{stroke-dasharray:0;}#mermaid-diagram-mermaid-dvpi9s6 .edge-thickness-invisible{stroke-width:0;fill:none;}#mermaid-diagram-mermaid-dvpi9s6 .edge-pattern-dashed{stroke-dasharray:3;}#mermaid-diagram-mermaid-dvpi9s6 .edge-pattern-dotted{stroke-dasharray:2;}#mermaid-diagram-mermaid-dvpi9s6 .marker{fill:#666;stroke:#666;}#mermaid-diagram-mermaid-dvpi9s6 .marker.cross{stroke:#666;}#mermaid-diagram-mermaid-dvpi9s6 svg{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;}#mermaid-diagram-mermaid-dvpi9s6 p{margin:0;}#mermaid-diagram-mermaid-dvpi9s6 .label{font-family:"trebuchet ms",verdana,arial,sans-serif;color:#000000;}#mermaid-diagram-mermaid-dvpi9s6 .cluster-label text{fill:#333;}#mermaid-diagram-mermaid-dvpi9s6 .cluster-label span{color:#333;}#mermaid-diagram-mermaid-dvpi9s6 .cluster-label span p{background-color:transparent;}#mermaid-diagram-mermaid-dvpi9s6 .label text,#mermaid-diagram-mermaid-dvpi9s6 span{fill:#000000;color:#000000;}#mermaid-diagram-mermaid-dvpi9s6 .node rect,#mermaid-diagram-mermaid-dvpi9s6 .node circle,#mermaid-diagram-mermaid-dvpi9s6 .node ellipse,#mermaid-diagram-mermaid-dvpi9s6 .node polygon,#mermaid-diagram-mermaid-dvpi9s6 .node path{fill:#eee;stroke:#999;stroke-width:1px;}#mermaid-diagram-mermaid-dvpi9s6 .rough-node .label text,#mermaid-diagram-mermaid-dvpi9s6 .node .label text,#mermaid-diagram-mermaid-dvpi9s6 .image-shape .label,#mermaid-diagram-mermaid-dvpi9s6 .icon-shape .label{text-anchor:middle;}#mermaid-diagram-mermaid-dvpi9s6 .node .katex path{fill:#000;stroke:#000;stroke-width:1px;}#mermaid-diagram-mermaid-dvpi9s6 .rough-node .label,#mermaid-diagram-mermaid-dvpi9s6 .node .label,#mermaid-diagram-mermaid-dvpi9s6 .image-shape .label,#mermaid-diagram-mermaid-dvpi9s6 .icon-shape .label{text-align:center;}#mermaid-diagram-mermaid-dvpi9s6 .node.clickable{cursor:pointer;}#mermaid-diagram-mermaid-dvpi9s6 .root .anchor path{fill:#666!important;stroke-width:0;stroke:#666;}#mermaid-diagram-mermaid-dvpi9s6 .arrowheadPath{fill:#333333;}#mermaid-diagram-mermaid-dvpi9s6 .edgePath .path{stroke:#666;stroke-width:2.0px;}#mermaid-diagram-mermaid-dvpi9s6 .flowchart-link{stroke:#666;fill:none;}#mermaid-diagram-mermaid-dvpi9s6 .edgeLabel{background-color:white;text-align:center;}#mermaid-diagram-mermaid-dvpi9s6 .edgeLabel p{background-color:white;}#mermaid-diagram-mermaid-dvpi9s6 .edgeLabel rect{opacity:0.5;background-color:white;fill:white;}#mermaid-diagram-mermaid-dvpi9s6 .labelBkg{background-color:rgba(255, 255, 255, 0.5);}#mermaid-diagram-mermaid-dvpi9s6 .cluster rect{fill:hsl(0, 0%, 98.9215686275%);stroke:#707070;stroke-width:1px;}#mermaid-diagram-mermaid-dvpi9s6 .cluster text{fill:#333;}#mermaid-diagram-mermaid-dvpi9s6 .cluster span{color:#333;}#mermaid-diagram-mermaid-dvpi9s6 div.mermaidTooltip{position:absolute;text-align:center;max-width:200px;padding:2px;font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:12px;background:hsl(-160, 0%, 93.3333333333%);border:1px solid #707070;border-radius:2px;pointer-events:none;z-index:100;}#mermaid-diagram-mermaid-dvpi9s6 .flowchartTitleText{text-anchor:middle;font-size:18px;fill:#000000;}#mermaid-diagram-mermaid-dvpi9s6 rect.text{fill:none;stroke-width:0;}#mermaid-diagram-mermaid-dvpi9s6 .icon-shape,#mermaid-diagram-mermaid-dvpi9s6 .image-shape{background-color:white;text-align:center;}#mermaid-diagram-mermaid-dvpi9s6 .icon-shape p,#mermaid-diagram-mermaid-dvpi9s6 .image-shape p{background-color:white;padding:2px;}#mermaid-diagram-mermaid-dvpi9s6 .icon-shape rect,#mermaid-diagram-mermaid-dvpi9s6 .image-shape rect{opacity:0.5;background-color:white;fill:white;}#mermaid-diagram-mermaid-dvpi9s6 :root{--mermaid-font-family:"trebuchet ms",verdana,arial,sans-serif;}ApproveApproveApproveApproveVerifiedIssuesRejectDraftInitiated by SalesUnder Review - ABMUnder Review - RBMUnder Review - ZBMUnder Review - GMMIS ProcessingDocument VerificationDistributorship CreatedRequest Additional DocsResubmit by CreatorRevisions NeededEnd
Rejection/Revision Paths (Common to Both):
#mermaid-diagram-mermaid-od3ji2p{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;fill:#000000;}@keyframes edge-animation-frame{from{stroke-dashoffset:0;}}@keyframes dash{to{stroke-dashoffset:0;}}#mermaid-diagram-mermaid-od3ji2p .edge-animation-slow{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 50s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-od3ji2p .edge-animation-fast{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 20s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-od3ji2p .error-icon{fill:#552222;}#mermaid-diagram-mermaid-od3ji2p .error-text{fill:#552222;stroke:#552222;}#mermaid-diagram-mermaid-od3ji2p .edge-thickness-normal{stroke-width:1px;}#mermaid-diagram-mermaid-od3ji2p .edge-thickness-thick{stroke-width:3.5px;}#mermaid-diagram-mermaid-od3ji2p .edge-pattern-solid{stroke-dasharray:0;}#mermaid-diagram-mermaid-od3ji2p .edge-thickness-invisible{stroke-width:0;fill:none;}#mermaid-diagram-mermaid-od3ji2p .edge-pattern-dashed{stroke-dasharray:3;}#mermaid-diagram-mermaid-od3ji2p .edge-pattern-dotted{stroke-dasharray:2;}#mermaid-diagram-mermaid-od3ji2p .marker{fill:#666;stroke:#666;}#mermaid-diagram-mermaid-od3ji2p .marker.cross{stroke:#666;}#mermaid-diagram-mermaid-od3ji2p svg{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;}#mermaid-diagram-mermaid-od3ji2p p{margin:0;}#mermaid-diagram-mermaid-od3ji2p .label{font-family:"trebuchet ms",verdana,arial,sans-serif;color:#000000;}#mermaid-diagram-mermaid-od3ji2p .cluster-label text{fill:#333;}#mermaid-diagram-mermaid-od3ji2p .cluster-label span{color:#333;}#mermaid-diagram-mermaid-od3ji2p .cluster-label span p{background-color:transparent;}#mermaid-diagram-mermaid-od3ji2p .label text,#mermaid-diagram-mermaid-od3ji2p span{fill:#000000;color:#000000;}#mermaid-diagram-mermaid-od3ji2p .node rect,#mermaid-diagram-mermaid-od3ji2p .node circle,#mermaid-diagram-mermaid-od3ji2p .node ellipse,#mermaid-diagram-mermaid-od3ji2p .node polygon,#mermaid-diagram-mermaid-od3ji2p .node path{fill:#eee;stroke:#999;stroke-width:1px;}#mermaid-diagram-mermaid-od3ji2p .rough-node .label text,#mermaid-diagram-mermaid-od3ji2p .node .label text,#mermaid-diagram-mermaid-od3ji2p .image-shape .label,#mermaid-diagram-mermaid-od3ji2p .icon-shape .label{text-anchor:middle;}#mermaid-diagram-mermaid-od3ji2p .node .katex path{fill:#000;stroke:#000;stroke-width:1px;}#mermaid-diagram-mermaid-od3ji2p .rough-node .label,#mermaid-diagram-mermaid-od3ji2p .node .label,#mermaid-diagram-mermaid-od3ji2p .image-shape .label,#mermaid-diagram-mermaid-od3ji2p .icon-shape .label{text-align:center;}#mermaid-diagram-mermaid-od3ji2p .node.clickable{cursor:pointer;}#mermaid-diagram-mermaid-od3ji2p .root .anchor path{fill:#666!important;stroke-width:0;stroke:#666;}#mermaid-diagram-mermaid-od3ji2p .arrowheadPath{fill:#333333;}#mermaid-diagram-mermaid-od3ji2p .edgePath .path{stroke:#666;stroke-width:2.0px;}#mermaid-diagram-mermaid-od3ji2p .flowchart-link{stroke:#666;fill:none;}#mermaid-diagram-mermaid-od3ji2p .edgeLabel{background-color:white;text-align:center;}#mermaid-diagram-mermaid-od3ji2p .edgeLabel p{background-color:white;}#mermaid-diagram-mermaid-od3ji2p .edgeLabel rect{opacity:0.5;background-color:white;fill:white;}#mermaid-diagram-mermaid-od3ji2p .labelBkg{background-color:rgba(255, 255, 255, 0.5);}#mermaid-diagram-mermaid-od3ji2p .cluster rect{fill:hsl(0, 0%, 98.9215686275%);stroke:#707070;stroke-width:1px;}#mermaid-diagram-mermaid-od3ji2p .cluster text{fill:#333;}#mermaid-diagram-mermaid-od3ji2p .cluster span{color:#333;}#mermaid-diagram-mermaid-od3ji2p div.mermaidTooltip{position:absolute;text-align:center;max-width:200px;padding:2px;font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:12px;background:hsl(-160, 0%, 93.3333333333%);border:1px solid #707070;border-radius:2px;pointer-events:none;z-index:100;}#mermaid-diagram-mermaid-od3ji2p .flowchartTitleText{text-anchor:middle;font-size:18px;fill:#000000;}#mermaid-diagram-mermaid-od3ji2p rect.text{fill:none;stroke-width:0;}#mermaid-diagram-mermaid-od3ji2p .icon-shape,#mermaid-diagram-mermaid-od3ji2p .image-shape{background-color:white;text-align:center;}#mermaid-diagram-mermaid-od3ji2p .icon-shape p,#mermaid-diagram-mermaid-od3ji2p .image-shape p{background-color:white;padding:2px;}#mermaid-diagram-mermaid-od3ji2p .icon-shape rect,#mermaid-diagram-mermaid-od3ji2p .image-shape rect{opacity:0.5;background-color:white;fill:white;}#mermaid-diagram-mermaid-od3ji2p :root{--mermaid-font-family:"trebuchet ms",verdana,arial,sans-serif;}RejectHoldApproveApproval/Rejection PointFeedback to CreatorRevise & ResubmitOn HoldResumeNext Level

5 Users & Roles

Sales Person (Creator): Creates distributor forms and re-uploads requested documents when MIS rejects. Limited to hierarchical workflow.
Sales Managers (ABM / RBM / ZBM / GM): Perform staged approvals in the sales approval funnel for hierarchical workflows.
MIS Team: Verifies document checks and entity details; can initiate fast-track workflows or verify standard ones.
Admin / Super Admin: Full system access including user management, role permissions, master data management, core API data synchronization, and system configuration.
System: Config, role/permission management.

Role & permission management uses Laravel's Spatie Roles & Permission package. MIS can use both workflows; Sales is limited to standard.

6 Tech Stack & Conventions

Backend: Laravel (PHP 8.1)
Frontend: Blade + Bootstrap 5, jQuery
Storage: AWS S3 (via Storage::disk('s3'))
DB: MySQL 5.7
Auth: Laravel Auth (login with email or phone)
Logging: Laravel Log for debugging and audit captures

Coding Conventions:

Controllers grouped by responsibility: OnboardingController, ApprovalController, DocumentController, DispatchController, MISProcessingController.
Routes for distributor functionality in distributor_route.php.
Admin/master data routes in web.php, user.php, core_route.php.


7 Authentication Flow
Users log in with email or phone. Credential resolution is overridden in LoginController:
php// Example snippet from LoginController
public function credentials(Request $request)
{
    $login = $request->{config('fortify.username')};

    return strpos($login, '@') 
        ? ['email' => $login, 'password' => $request->password]
        : ['phone' => $login, 'password' => $request->password];
}
After login, users are routed to the Dashboard managed by HomeController.

8 Routing Structure
Main Route Files:

web.php: Core authentication, password reset, distributor listing.
distributor_route.php: Distributor onboarding and approval workflows.
home_route.php: Dashboard and notification routes.
user.php: User management and role/permission routes.
core_route.php: Master data management and core API synchronization.

8.1 Complete Distributor Routes (distributor_route.php)
phpRoute::middleware('auth')->group(function () {
    Route::get('/applications/pending-documents', [OnboardingController::class, 'pendingDocuments'])->name('applications.pending-documents');
    Route::post('/applications/pending-documents/{application}/upload', [OnboardingController::class, 'uploadPendingDocuments'])->name('applications.upload-pending-documents');

    Route::resource('applications', OnboardingController::class);
    Route::post('applications/datatable', [OnboardingController::class, 'datatable'])->name('applications.datatable');
    Route::post('/applications/save-step/{stepNumber}', [OnboardingController::class, 'saveStep'])->name('applications.save-step');
    Route::get('/get-districts/{state_id}', [OnboardingController::class, 'getDistricts']);
    Route::get('/application/{id}/preview', [OnboardingController::class, 'preview'])->name('application.preview');
    Route::get('/application/{id}/download', [OnboardingController::class, 'downloadApplicationPdf'])->name('application.download');

    Route::prefix('approvals')->group(function () {
        Route::get('/dashboard', [ApprovalController::class, 'dashboard'])->name('approvals.dashboard');
        Route::get('/{application}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/{application}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/{application}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/{application}/revert', [ApprovalController::class, 'revert'])->name('approvals.revert');
        Route::post('/{application}/hold', [ApprovalController::class, 'hold'])->name('approvals.hold');
    });

    Route::post('/process-pan-card', [DocumentController::class, 'processPANCard'])->name('process-pan-card');
    Route::post('/process-bank-document', [DocumentController::class, 'processBankDocument'])->name('process.bank.document');
    Route::post('/process-seed-license', [DocumentController::class, 'processSeedLicense'])->name('process-seed-license');
    Route::post('/process-gst-document', [DocumentController::class, 'processGSTDocument'])->name('process-gst-document');
    Route::post('/process-letter-document', [DocumentController::class, 'processLetterDocument'])->name('process-letter-document');
    Route::post('/process-aadhar-document', [DocumentController::class, 'processAadharDocument'])->name('process-aadhar-document');
    Route::post('/fetch-bank-details', [DocumentController::class, 'fetchDetails']);

    Route::get('/mis/verification', [ApprovalController::class, 'misVerificationList'])->name('mis.verification-list');
    Route::get('/approvals/{application}/verify-documents', [ApprovalController::class, 'verifyDocuments'])->name('approvals.verify-documents');
    Route::post('/approvals/{application}/update-documents', [ApprovalController::class, 'updateDocuments'])->name('approvals.update-documents');
    Route::get('/approvals/{application}/view-checklist', [ApprovalController::class, 'viewChecklist'])->name('approvals.view-checklist');
});
8.2 Admin/Super Admin Routes
User & Permission Management (user.php):
phpRoute::middleware(['auth', 'role:admin|super_admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
});
Master Data Management (core_route.php):
phpRoute::middleware(['auth', 'role:admin|super_admin'])->prefix('core')->group(function () {
    Route::get('/zones', [CoreZoneController::class, 'index'])->name('core.zones');
    Route::get('/regions', [CoreRegionController::class, 'index'])->name('core.regions');
    // ... other master data routes
    Route::post('/sync-all', [CoreAPIController::class, 'syncAll'])->name('core.sync-all');
});
Core API Synchronization (core_route.php):
phpRoute::post('/core/sync/distributors', [CoreAPIController::class, 'syncDistributors'])->name('core.sync.distributors');
Dashboard Routes (home_route.php):
phpRoute::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/notifications', [HomeController::class, 'notifications'])->name('notifications');
});

9 Onboarding (8-step) Workflow
The distributor onboarding form is implemented as an 8-step wizard. Each step persists partial data to allow saving progress.
Typical Steps:

Basic entity details
Business address & contacts
Bank details
PAN / GST details
Seed license (if applicable)
Authorized persons
Documents upload
Final review & submit

The /applications/save-step/{stepNumber} endpoint saves the related subset. After completion, the application enters the appropriate workflow based on initiator role.

10 Approval & Status Flow
Statuses are string enums stored in the onboardings table.
Key Status Transitions:

Sales-Initiated: draft → initiated → under_review (ABM) → under_review (RBM) → under_review (ZBM) → under_review (GM) → mis_processing → document_verified → distributorship_created
MIS-Initiated: draft → initiated → mis_processing → document_verified → distributorship_created

If MIS finds issues: application may move to documents_pending or documents_resubmitted. The creator uploads/resubmits documents, and the application returns to MIS for re-verification. Note: status is the canonical workflow field; other columns (e.g., doc_verification_status) are for convenience.

11 Detailed Workflow Explanation
11.1 MIS-Initiated Fast-Track Workflow
Controller Logic (OnboardingController::store or saveStep):
php// Example snippet
public function store(Request $request)
{
    $application = Onboarding::create([
        'workflow_type' => 'fast_track',
        'status' => 'initiated',
        // ... other fields
    ]);
    
    // Direct to MIS processing
    $this->advanceToMisProcessing($application);
}
11.2 Sales-Initiated Hierarchical Workflow
Approval Chain Logic (ApprovalController::approve):
phppublic function approve(Request $request, Onboarding $application)
{
    $application->update(['status' => 'under_review', 'current_approver_id' => $nextApprover->id]);
    
    // Determine next level based on current approval_level
    $nextLevel = $this->getNextApprovalLevel($application->approval_level);
    // Notify next approver via email/queue
}
Hierarchical Levels:

ABM (Area Business Manager)
RBM (Regional Business Manager)
ZBM (Zonal Business Manager)
GM (General Manager)
MIS


12 MIS Document Verification
MIS verifies:

Main documents: PAN, GST (if applicable), Seed License (if any), Bank document
Authorized persons docs: Authorization letter, Aadhar
Additional documents: Inserted into application_additional_documents

MIS Actions:

Update ApplicationCheckpoint entries
Save additional documents into ApplicationAdditionalDocument
Resubmits update original document row, linked to ApplicationAdditionalUpload

Additional Document Requests (ApprovalController::updateDocuments):
phppublic function updateDocuments(Request $request, Onboarding $application)
{
    // Validate and store additional docs
    $additionalDoc = ApplicationAdditionalDocument::create([
        'application_id' => $application->id,
        'document_type' => $request->type,
        'path' => $request->file('document')->store('docs'),
        'requested_by' => auth()->id(),
    ]);
    
    // Update status to documents_pending
    $application->update(['status' => 'documents_pending']);
    
    // Notify creator
}

13 Core API Integration
Available Core APIs:





















































































































































#EndpointController MethodDescription1distributorsdistributors()Returns all distributor list2functionsfunctions()Returns function list3verticalsverticals()Returns vertical list4zoneszones()Returns all zone list5regionsregions()Returns all region list6zone_region_mappingzoneRegionMapping()Returns Zone Region Mapping List7companycompany()Returns all company list8territoryterritory()Returns all territory list9region_territoryregionTerritory()Returns Region Territory Mapping list10cropscrops()Returns All Crop List11product_categoryproductCategory()Returns category list12business_unitbusinessUnit()Returns business unit list13varietiesvarieties()Returns crop variety list14vertical_function_mappingverticalFunctionMapping()Returns Function Vertical Mapping15vertical_department_mappingverticalDepartmentMapping()Returns Vertical and Department Mapping16bu_zone_mappingbuZoneMapping()Returns BU and Zone mapping17business_typebusinessType()Returns Business Types list18countriescountries()Returns country list19statesstates()Returns state list20districtsdistricts()Returns district list21employee_toolsemployeeTools()Returns users based on tools name22active_employeeactiveEmployee()Returns Active Employee List23departmentsdepartments()Returns department list
Data Synchronization Flow:

Connect to core server APIs
Retrieve updated data
Import into local database
Export to Excel/CSV

Example API Request/Response Sample (Distributors Sync):
Request:
textGET /api/core/distributors?api_token=your_token
Response:
json{
  "data": [
    {
      "id": 1,
      "name": "ABC Distributors",
      "code": "DIST001",
      "status": "active"
    }
  ],
  "message": "Success"
}

14 Master Data Management
Location Masters:

Zones
Regions
Territories
Cascade APIs: get_zone_by_bu, get_region_by_zone, get_territory_by_region

Product Masters:

Categories
Crops
Varieties

Business Masters:

Business Units
Companies
Organization Functions
Business Types
Verticals


15 Admin/Super Admin Dashboard

User Management: Create/edit users, role assignments, permissions, password reset, activity tracking
Role-Based Access Control: Permission-based routing, hierarchical access
Export Controls: Excel/CSV, filtered/bulk download


16 Database Schema
Below is an improved representation of key tables using Markdown tables. For a full ERD, use a tool like Lucidchart or draw.io with the following relationships:

onboardings 1:1 entity_details
onboardings 1:M application_workflow_logs
onboardings 1:M application_additional_documents

onboardings Table:































































































































































































ColumnTypeNullDefaultidbigint(20) UNSIGNEDNoAUTO_INCREMENTapplication_codevarchar(255)NoNoneterritorybigint(20) UNSIGNEDYesNULLcrop_verticalbigint(20) UNSIGNEDNoNoneregionbigint(20) UNSIGNEDYesNULLzonebigint(20) UNSIGNEDYesNULLbusiness_unitbigint(20) UNSIGNEDYesNULLdistrictbigint(20) UNSIGNEDYesNULLstatebigint(20) UNSIGNEDYesNULLstatusvarchar(255)Nodraftdoc_verification_statusvarchar(50)YesNULLagreement_statusvarchar(50)YesNULLphysical_docs_statusvarchar(50)YesNULLfinal_statusvarchar(50)YesNULLis_hierarchy_approvedtinyint(1)No0mis_feedbacklongtextYesNULLmis_rejected_attimestampYesNULLresubmitted_attimestampYesNULLmis_verified_attimestampYesNULLcurrent_progress_steptinyint(3) UNSIGNEDYes1current_approver_idbigint(20) UNSIGNEDYesNULLfinal_approver_idbigint(20) UNSIGNEDYesNULLapproval_levelvarchar(255)YesNULLcreated_bybigint(20) UNSIGNEDNoNonecreated_attimestampYesNULLupdated_attimestampYesNULLdeleted_attimestampYesNULLworkflow_typeENUM('standard', 'fast_track')No'standard'hierarchy_bypassedBOOLEANNoFALSEinitiated_by_roleVARCHAR(50)YesNULL
entity_details Table:



































































































































































































































ColumnTypeNullDefaultidbigint(20) UNSIGNEDNoAUTO_INCREMENTapplication_idbigint(20) UNSIGNEDNoNoneestablishment_namevarchar(255)NoNoneentity_typevarchar(255)NoNonebusiness_addresstextYesNULLhouse_novarchar(255)YesNULLlandmarkvarchar(255)YesNULLcityvarchar(255)YesNULLdistrict_idbigint(20) UNSIGNEDYesNULLstate_idbigint(20) UNSIGNEDYesNULLcountry_idbigint(20) UNSIGNEDNo1pincodevarchar(255)NoNonemobilevarchar(255)NoNoneemailvarchar(255)YesNULLpan_numbervarchar(255)NoNonepan_pathvarchar(255)YesNULLpan_verifiedtinyint(1)No0gst_applicableENUM('yes', 'no')YesNULLgst_numbervarchar(255)YesNULLgst_pathvarchar(255)YesNULLgst_validitydateYesNULLgst_verifiedtinyint(1)No0seed_licensevarchar(255)YesNULLseed_license_pathvarchar(255)YesNULLseed_license_validitydateYesNULLseed_license_verifiedtinyint(1)No0bank_namevarchar(255)YesNULLaccount_holder_namevarchar(255)YesNULLaccount_numbervarchar(255)YesNULLifsc_codevarchar(11)YesNULLbank_document_pathvarchar(255)YesNULLbank_document_verifiedtinyint(1)No0tan_numbervarchar(10)YesNULLhas_authorized_personsENUM('yes', 'no')No'no'created_attimestampYesNULLupdated_attimestampYesNULL
application_workflow_logs Table:

































































ColumnTypeNullDefaultidbigint UNSIGNEDNoAUTO_INCREMENTapplication_idbigint UNSIGNEDNoNoneactionvarchar(255)NoNoneperformed_bybigint UNSIGNEDNoNonestatus_beforevarchar(255)YesNULLstatus_aftervarchar(255)YesNULLcommentstextYesNULLcreated_attimestampYesNULLupdated_attimestampYesNULL
Simplified ERD (Text Representation):
text[Users] --(has_role)--> [Roles] --(has_permission)--> [Permissions]
[Onboardings] --(1:1)--> [EntityDetails]
[Onboardings] --(1:M)--> [ApplicationWorkflowLogs]
[Onboardings] --(1:M)--> [ApplicationAdditionalDocuments]
[Onboardings] --(belongs_to)--> [Users] (created_by, current_approver_id)
[EntityDetails] --(belongs_to)--> [Districts/States/Countries] (location masters)
For a visual ERD, import the above schema into dbdiagram.io or similar.

17 Permission Structure
Key Permissions:

list-distributor
list-user
list-role
list-core-api
list-zone, list-region, list-territory
list-crop, list-variety, list-category
list-business-unit, list-company, list-org-function

Role-Specific Permissions:

MIS: create-fast-track-application, verify-documents, skip-approval-hierarchy
Sales: create-application, view-approval-queue, submit-documents
Managers: approve-application, revert-application, view-team-applications


18 Frontend Notes & Snippets
Additional Documents UI:
html<!-- Blade snippet -->
<div class="additional-docs">
    @foreach($application->additionalDocuments as $doc)
        <div class="doc-item">
            <span>{{ $doc->document_type }}</span>
            <a href="{{ Storage::url($doc->path) }}">Download</a>
        </div>
    @endforeach
    <form method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="document">
        <button type="submit">Upload Additional</button>
    </form>
</div>
Master Data Listing:
html<!-- Datatable example -->
<table id="master-data-table" class="table">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
    </thead>
    <tbody>
        @foreach($zones as $zone)
            <tr>
                <td>{{ $zone->id }}</td>
                <td>{{ $zone->name }}</td>
                <td>
                    <a href="{{ route('core.zones.edit', $zone) }}" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<script>
    $('#master-data-table').DataTable();
</script>
Dual Workflow UI:
html<!-- Role-based workflow selector -->
@if(auth()->user()->hasRole('mis'))
    <button onclick="startFastTrack()">Start Fast-Track</button>
@else
    <button onclick="startHierarchical()">Start Hierarchical</button>
@endif

19 Controllers

OnboardingController: Workflow detection, 8-step form, pending documents, preview/PDF
ApprovalController: Approval routing, workflow determination, MIS verification, status transitions
DocumentController: OCR processing, data extraction, validation
DispatchController: Document tracking, courier management
CoreAPIController: API synchronization
UserController: User management, permissions, exports
Core*Controllers: Master data listing
HomeController: Dashboard, notifications


20 Development & Deployment
Setup Commands:
bashcomposer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run prod
Environment Configuration (.env):
textAPP_NAME=Connect
APP_ENV=production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=connect_db
DB_USERNAME=root
DB_PASSWORD=secret
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
CORE_API_TOKEN=your_token
Testing Scenarios:

MIS Fast-Track: Verify direct mis_processing status
Sales Standard: Verify hierarchical path
Mixed: MIS sees both workflows; Sales only standard
Admin: Test user/roles, permissions, API sync, exports


21 Troubleshooting

additional_documents validation: Check input naming
Duplicate rows: Use updateOrCreate with id
Undefined has_authorized_persons: Submit hidden default
S3 issues: Verify Storage::disk('s3')->url()
Permission errors: Check Spatie setup
Export failures: Verify Laravel Excel dependencies
API sync: Check connectivity/tokens


22 How to Extend

Vendor & PO modules
Dynamic approval chains
JSON API for mobile apps
Admin: Audit logs, bulk operations, reporting, API monitoring
Technical: Rate limiting, caching, queue for sync


23 Where to Look

Routes: routes/{distributor_route,web,user,core_route,home_route}.php
Controllers: app/Http/Controllers/
Views: resources/views/{approvals,users,core_api,master_data}
Models: app/Models/
Exports: app/Exports/
API Integration: app/{Services/CoreAPIService,DataMappers/CoreDataMapper}.php


24 Next Actions

Add screen captures for Dashboard, MIS verification, Onboarding wizard, Admin listings
Add full visual ERD (using draw.io export)
Include more API request/response samples
Add sequence diagrams for workflows (e.g., using PlantUML or Mermaid)

Sequence Diagram Example (Approval Flow - Mermaid):
MISApprovalControllerOnboardingControllerSales UserMISApprovalControllerOnboardingControllerSales User#mermaid-diagram-mermaid-wzo0oc7{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;fill:#000000;}@keyframes edge-animation-frame{from{stroke-dashoffset:0;}}@keyframes dash{to{stroke-dashoffset:0;}}#mermaid-diagram-mermaid-wzo0oc7 .edge-animation-slow{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 50s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-wzo0oc7 .edge-animation-fast{stroke-dasharray:9,5!important;stroke-dashoffset:900;animation:dash 20s linear infinite;stroke-linecap:round;}#mermaid-diagram-mermaid-wzo0oc7 .error-icon{fill:#552222;}#mermaid-diagram-mermaid-wzo0oc7 .error-text{fill:#552222;stroke:#552222;}#mermaid-diagram-mermaid-wzo0oc7 .edge-thickness-normal{stroke-width:1px;}#mermaid-diagram-mermaid-wzo0oc7 .edge-thickness-thick{stroke-width:3.5px;}#mermaid-diagram-mermaid-wzo0oc7 .edge-pattern-solid{stroke-dasharray:0;}#mermaid-diagram-mermaid-wzo0oc7 .edge-thickness-invisible{stroke-width:0;fill:none;}#mermaid-diagram-mermaid-wzo0oc7 .edge-pattern-dashed{stroke-dasharray:3;}#mermaid-diagram-mermaid-wzo0oc7 .edge-pattern-dotted{stroke-dasharray:2;}#mermaid-diagram-mermaid-wzo0oc7 .marker{fill:#666;stroke:#666;}#mermaid-diagram-mermaid-wzo0oc7 .marker.cross{stroke:#666;}#mermaid-diagram-mermaid-wzo0oc7 svg{font-family:"trebuchet ms",verdana,arial,sans-serif;font-size:16px;}#mermaid-diagram-mermaid-wzo0oc7 p{margin:0;}#mermaid-diagram-mermaid-wzo0oc7 .actor{stroke:hsl(0, 0%, 83%);fill:#eee;}#mermaid-diagram-mermaid-wzo0oc7 text.actor>tspan{fill:#333;stroke:none;}#mermaid-diagram-mermaid-wzo0oc7 .actor-line{stroke:hsl(0, 0%, 83%);}#mermaid-diagram-mermaid-wzo0oc7 .messageLine0{stroke-width:1.5;stroke-dasharray:none;stroke:#333;}#mermaid-diagram-mermaid-wzo0oc7 .messageLine1{stroke-width:1.5;stroke-dasharray:2,2;stroke:#333;}#mermaid-diagram-mermaid-wzo0oc7 #arrowhead path{fill:#333;stroke:#333;}#mermaid-diagram-mermaid-wzo0oc7 .sequenceNumber{fill:white;}#mermaid-diagram-mermaid-wzo0oc7 #sequencenumber{fill:#333;}#mermaid-diagram-mermaid-wzo0oc7 #crosshead path{fill:#333;stroke:#333;}#mermaid-diagram-mermaid-wzo0oc7 .messageText{fill:#333;stroke:none;}#mermaid-diagram-mermaid-wzo0oc7 .labelBox{stroke:hsl(0, 0%, 83%);fill:#eee;}#mermaid-diagram-mermaid-wzo0oc7 .labelText,#mermaid-diagram-mermaid-wzo0oc7 .labelText>tspan{fill:#333;stroke:none;}#mermaid-diagram-mermaid-wzo0oc7 .loopText,#mermaid-diagram-mermaid-wzo0oc7 .loopText>tspan{fill:#333;stroke:none;}#mermaid-diagram-mermaid-wzo0oc7 .loopLine{stroke-width:2px;stroke-dasharray:2,2;stroke:hsl(0, 0%, 83%);fill:hsl(0, 0%, 83%);}#mermaid-diagram-mermaid-wzo0oc7 .note{stroke:#999;fill:#666;}#mermaid-diagram-mermaid-wzo0oc7 .noteText,#mermaid-diagram-mermaid-wzo0oc7 .noteText>tspan{fill:#fff;stroke:none;}#mermaid-diagram-mermaid-wzo0oc7 .activation0{fill:#f4f4f4;stroke:#666;}#mermaid-diagram-mermaid-wzo0oc7 .activation1{fill:#f4f4f4;stroke:#666;}#mermaid-diagram-mermaid-wzo0oc7 .activation2{fill:#f4f4f4;stroke:#666;}#mermaid-diagram-mermaid-wzo0oc7 .actorPopupMenu{position:absolute;}#mermaid-diagram-mermaid-wzo0oc7 .actorPopupMenuPanel{position:absolute;fill:#eee;box-shadow:0px 8px 16px 0px rgba(0,0,0,0.2);filter:drop-shadow(3px 5px 2px rgb(0 0 0 / 0.4));}#mermaid-diagram-mermaid-wzo0oc7 .actor-man line{stroke:hsl(0, 0%, 83%);fill:#eee;}#mermaid-diagram-mermaid-wzo0oc7 .actor-man circle,#mermaid-diagram-mermaid-wzo0oc7 line{stroke:hsl(0, 0%, 83%);fill:#eee;stroke-width:2px;}#mermaid-diagram-mermaid-wzo0oc7 :root{--mermaid-font-family:"trebuchet ms",verdana,arial,sans-serif;}Hierarchical ApprovalsCreate ApplicationInitiate ReviewNotify ABMForward to MISVerify DocumentsNotify Creator (if resubmit)

To download this document:
Copy the entire content above and paste it into a new file named connect_documentation.md. You can view it in any Markdown editor (e.g., VS Code, Typora) or convert to PDF using tools like Pandoc (pandoc connect_documentation.md -o connect_documentation.pdf) or online converters like md-to-pdf. For flowcharts, render the Mermaid code at mermaid.live. If you need a pre-generated PDF or further customizations, let me know!