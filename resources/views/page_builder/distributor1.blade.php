<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributor Management System - Wireframe</title>
    <style>
        /* General Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f7fa;
            color: #2d3748;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 0 0 12px 12px; /* Rounded bottom corners */
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .notification-bell {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background 0.2s;
        }

        .notification-bell:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Layout Container */
        .container {
            display: flex;
            min-height: calc(100vh - 80px); /* Adjust based on header height */
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e2e8f0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            border-radius: 0 12px 12px 0; /* Rounded right corners */
        }

        .nav-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .nav-item {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .nav-item:hover, .nav-item.active {
            background: #f7fafc;
            border-left-color: #667eea;
            color: #667eea;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        h1 {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        /* Dashboard Card Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card-header {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2d3748;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f7fafc;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-value {
            font-weight: 600;
            color: #667eea;
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
        }

        .form-progress {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 8px;
            overflow-x: auto; /* Allow horizontal scroll on small screens */
            -webkit-overflow-scrolling: touch; /* For smoother scrolling on iOS */
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 2rem;
            flex-shrink: 0; /* Prevent steps from shrinking */
        }

        .progress-step:last-child {
            margin-right: 0;
        }

        .progress-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            background: #e2e8f0;
            color: #718096;
        }

        .progress-circle.active {
            background: #667eea;
            color: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }

        .progress-circle.completed {
            background: #48bb78;
            color: white;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2d3748;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .form-input, .form-select, .form-textarea {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 1rem; /* Ensure consistent font size */
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #edf2f7;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #48bb78;
            color: white;
            box-shadow: 0 2px 8px rgba(72, 187, 120, 0.2);
        }

        .btn-success:hover {
            background: #38a169;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.4);
        }

        .btn-danger {
            background: #f56565;
            color: white;
            box-shadow: 0 2px 8px rgba(245, 101, 101, 0.2);
        }

        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4);
        }

        .btn-warning {
            background: #ed8936;
            color: white;
            box-shadow: 0 2px 8px rgba(237, 137, 54, 0.2);
        }

        .btn-warning:hover {
            background: #dd6b20;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(237, 137, 54, 0.4);
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden; /* Ensures rounded corners for table */
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            margin-top: 1.5rem;
        }

        .table th {
            background: #f7fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f7fafc;
            color: #4a5568;
        }

        .table tr:last-child td {
            border-bottom: none; /* No border for the last row */
        }

        .table tbody tr:hover {
            background: #edf2f7;
        }

        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f7fafc;
            flex-wrap: wrap; /* Allow buttons to wrap on small screens */
        }

        /* Screen Navigation (Demo purposes) */
        .screen-nav {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .screen-btn {
            padding: 0.5rem 1rem;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }

        .screen-btn:hover {
            background: #edf2f7;
        }

        .screen-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* Hide screens by default */
        .screen {
            display: none;
        }

        .screen.active {
            display: block;
        }

        /* Status badges */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex; /* Ensure it wraps correctly */
            align-items: center;
            justify-content: center;
        }

        .status-pending {
            background: #fed7d7;
            color: #c53030;
        }

        .status-approved {
            background: #c6f6d5;
            color: #2f855a;
        }

        .status-reverted {
            background: #feebc8;
            color: #c05621;
        }
        
        .status-onhold {
            background: #bee3f8;
            color: #2b6cb0;
        }

        /* MIS Dashboard Tabs */
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.5rem;
            flex-wrap: wrap; /* Allow tabs to wrap */
            gap: 0.5rem; /* Space between tabs on wrap */
        }

        .tab-btn {
            padding: 0.75rem 1.25rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: #718096;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: #4a5568;
        }

        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: 600;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
                border-radius: 0 0 8px 8px;
            }

            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
                border-radius: 0;
            }

            .nav-menu {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                padding: 0.5rem;
            }

            .nav-item {
                padding: 0.5rem 1rem;
                border-left: none;
                border-bottom: 3px solid transparent; /* Change border for mobile nav */
            }

            .nav-item:hover, .nav-item.active {
                border-left-color: transparent;
                border-bottom-color: #667eea;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .screen-nav {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn {
                width: 100%; /* Full width buttons on small screens */
            }

            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .tab-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .tab-btn {
                border-bottom: none;
                border-left: 3px solid transparent;
            }

            .tab-btn.active {
                border-bottom-color: transparent;
                border-left-color: #667eea;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">🌾 Distributor Management System</div>
        <div class="user-info">
            <span>John Doe | Territory Manager</span>
            <button class="notification-bell">🔔</button>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <ul class="nav-menu">
                <li class="nav-item active" onclick="showScreen('dashboard', this)">📊 Dashboard</li>
                <li class="nav-item" onclick="showScreen('applications', this)">📝 My Applications</li>
                <li class="nav-item" onclick="showScreen('new-form', this)">➕ New Application</li>
                <li class="nav-item" onclick="showScreen('approvals', this)">✅ Pending Approvals</li>
                <li class="nav-item" onclick="showScreen('mis-dashboard', this)">🏢 MIS Dashboard</li>
                <li class="nav-item" onclick="showScreen('reports', this)">📈 Reports</li>
                <li class="nav-item" onclick="showScreen('users', this)">👥 User Management</li>
            </ul>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Screen Navigation for Demo (Quick access between screens) -->
            <div class="screen-nav">
                <button class="screen-btn active" onclick="showScreen('dashboard', this)">Dashboard</button>
                <button class="screen-btn" onclick="showScreen('new-form', this)">New Form</button>
                <button class="screen-btn" onclick="showScreen('applications', this)">Applications</button>
                <button class="screen-btn" onclick="showScreen('review', this)">Review</button>
                <button class="screen-btn" onclick="showScreen('mis-dashboard', this)">MIS Dashboard</button>
            </div>

            <!-- Dashboard Screen Content -->
            <div id="dashboard" class="screen active">
                <h1>Dashboard Overview</h1>
                <div class="dashboard-grid">
                    <div class="card">
                        <div class="card-header">My Applications</div>
                        <div class="stat-item">
                            <span>Submitted</span>
                            <span class="stat-value">5</span>
                        </div>
                        <div class="stat-item">
                            <span>Approved</span>
                            <span class="stat-value">10</span>
                        </div>
                        <div class="stat-item">
                            <span>Reverted</span>
                            <span class="stat-value">2</span>
                        </div>
                        <div class="stat-item">
                            <span>Rejected</span>
                            <span class="stat-value">1</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Pending Your Approval</div>
                        <div class="stat-item">
                            <span>RBM Level</span>
                            <span class="stat-value">3</span>
                        </div>
                        <div class="stat-item">
                            <span>ZBM Level</span>
                            <span class="stat-value">2</span>
                        </div>
                        <div class="stat-item">
                            <span>GM-Sales Level</span>
                            <span class="stat-value">1</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">MIS Workload</div>
                        <div class="stat-item">
                            <span>Pending Doc Verify</span>
                            <span class="stat-value">4</span>
                        </div>
                        <div class="stat-item">
                            <span>Pending Agreement</span>
                            <span class="stat-value">2</span>
                        </div>
                        <div class="stat-item">
                            <span>Pending Physical</span>
                            <span class="stat-value">1</span>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="showScreen('new-form', document.querySelector('.screen-nav button[onclick*=\'new-form\']'))">Submit New Application</button>
                    <button class="btn btn-secondary" onclick="showScreen('approvals', document.querySelector('.screen-nav button[onclick*=\'approvals\']'))">View All Pending Approvals</button>
                </div>
            </div>

            <!-- New Application Form Screen -->
            <div id="new-form" class="screen">
                <h1>Distributor Appointment Form</h1>
                
                <div class="form-container">
                    <!-- Progress Indicator -->
                    <div class="form-progress">
                        <div class="progress-step">
                            <div class="progress-circle active">1</div>
                            <span>General & Entity</span>
                        </div>
                        <div class="progress-step">
                            <div class="progress-circle pending">2</div>
                            <span>Ownership & Bank</span>
                        </div>
                        <div class="progress-step">
                            <div class="progress-circle pending">3</div>
                            <span>Distribution & Plan</span>
                        </div>
                        <div class="progress-step">
                            <div class="progress-circle pending">4</div>
                            <span>Documents</span>
                        </div>
                    </div>

                    <form>
                        <div class="form-section">
                            <h2 class="section-title">General & Entity Information</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Territory *</label>
                                    <select class="form-select">
                                        <option>Select Territory</option>
                                        <option>North Region</option>
                                        <option>South Region</option>
                                        <option>East Region</option>
                                        <option>West Region</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Crop Vertical *</label>
                                    <select class="form-select">
                                        <option>Select Crop Vertical</option>
                                        <option>Field Crop</option>
                                        <option>Vegetable Crop</option>
                                        <option>Hybrid Seeds</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Zone *</label>
                                    <input type="text" class="form-input" placeholder="Enter zone">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">District *</label>
                                    <input type="text" class="form-input" placeholder="Enter district">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">State *</label>
                                    <select class="form-select">
                                        <option>Select State</option>
                                        <option>Maharashtra</option>
                                        <option>Karnataka</option>
                                        <option>Andhra Pradesh</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Establishment Name *</label>
                                    <input type="text" class="form-input" placeholder="Enter establishment name">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Establishment Type *</label>
                                    <select class="form-select">
                                        <option>Select Type</option>
                                        <option>Sole Proprietorship</option>
                                        <option>Partnership</option>
                                        <option>Company</option>
                                        <option>LLP</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">PAN Number *</label>
                                    <input type="text" class="form-input" placeholder="Enter PAN number">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mobile Number *</label>
                                    <input type="tel" class="form-input" placeholder="Enter mobile number">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-input" placeholder="Enter email address">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="section-title">Business Address</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Shop/House Number *</label>
                                    <input type="text" class="form-input" placeholder="Enter shop/house number">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Landmark</label>
                                    <input type="text" class="form-input" placeholder="Enter landmark">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">City *</label>
                                    <input type="text" class="form-input" placeholder="Enter city">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">District *</label>
                                    <input type="text" class="form-input" placeholder="Enter district">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pincode *</label>
                                    <input type="text" class="form-input" placeholder="Enter pincode">
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="button" class="btn btn-secondary">Previous</button>
                            <button type="button" class="btn btn-primary">Next</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Applications List Screen -->
            <div id="applications" class="screen">
                <h1>My Applications</h1>
                
                <div class="card">
                    <!-- Search and Filter -->
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;">
                        <select class="form-select" style="width: 200px;">
                            <option>All Status</option>
                            <option>Pending</option>
                            <option>Approved</option>
                            <option>Rejected</option>
                            <option>Reverted</option>
                            <option>On Hold</option>
                        </select>
                        <input type="text" class="form-input" placeholder="Search applications..." style="flex: 1; min-width: 200px;">
                        <input type="date" class="form-input" style="width: 200px;">
                    </div>

                    <!-- Applications Table -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>App ID</th>
                                <th>Distributor Name</th>
                                <th>Initiated By</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>APP001</td>
                                <td>ABC Distributors</td>
                                <td>John Doe</td>
                                <td><span class="status-badge status-pending">Pending RBM</span></td>
                                <td>2024-01-15</td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>APP002</td>
                                <td>XYZ Agro Co.</td>
                                <td>Jane Smith</td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                                <td>2024-01-10</td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td>APP003</td>
                                <td>Green Fields</td>
                                <td>John Doe</td>
                                <td><span class="status-badge status-reverted">Reverted</span></td>
                                <td>2024-01-08</td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button>
                                    <button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('new-form', document.querySelector('.screen-nav button[onclick*=\'new-form\']'))">Edit</button>
                                </td>
                            </tr>
                            <tr>
                                <td>APP004</td>
                                <td>Harvest Solutions</td>
                                <td>John Doe</td>
                                <td><span class="status-badge status-onhold">On Hold</span></td>
                                <td>2024-01-20</td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button>
                                </td>
                            </tr>
                             <tr>
                                <td>APP005</td>
                                <td>FarmTech Innovations</td>
                                <td>Jane Smith</td>
                                <td><span class="status-badge status-pending">Pending MIS Doc Verify</span></td>
                                <td>2024-01-22</td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div style="display: flex; justify-content: center; margin-top: 1rem; gap: 0.5rem;">
                        <button class="btn btn-secondary">« Previous</button>
                        <button class="btn btn-primary">1</button>
                        <button class="btn btn-secondary">2</button>
                        <button class="btn btn-secondary">3</button>
                        <button class="btn btn-secondary">Next »</button>
                    </div>
                </div>
            </div>

            <!-- Application Review Screen -->
            <div id="review" class="screen">
                <h1>Application Details: Green Fields (ID: APP003)</h1>
                
                <div class="form-container">
                    <div class="form-section">
                        <h2 class="section-title">General & Entity Information</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Territory</label>
                                <div style="padding: 0.75rem; background: #f7fafc; border-radius: 8px;">North Region</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Crop Vertical</label>
                                <div style="padding: 0.75rem; background: #f7fafc; border-radius: 8px;">Field Crop</div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Establishment Name</label>
                                <div style="padding: 0.75rem; background: #f7fafc; border-radius: 8px;">Green Fields Agriculture</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">PAN Number</label>
                                <div style="padding: 0.75rem; background: #f7fafc; border-radius: 8px;">ABCDE1234F</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">Mandatory Documents</h2>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f7fafc; border-radius: 8px; flex-wrap: wrap; gap: 0.5rem;">
                                <span>Business Entity Proofs: Document1.pdf</span>
                                <div>
                                    <button class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</button>
                                    <button class="btn btn-secondary" style="padding: 0.5rem 1rem;">Download</button>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f7fafc; border-radius: 8px; flex-wrap: wrap; gap: 0.5rem;">
                                <span>Ownership Confirmation: Document2.jpg</span>
                                <div>
                                    <button class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-right: 0.5rem;">View</button>
                                    <button class="btn btn-secondary" style="padding: 0.5rem 1rem;">Download</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">Workflow Actions</h2>
                        <p style="margin-bottom: 1rem;"><strong>Current Status:</strong> <span class="status-badge status-pending">pending_rbm_approval</span></p>
                        
                        <div style="margin-top: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Comments</label>
                                <textarea class="form-textarea" placeholder="Add your comments here..."></textarea>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-success">✅ Approve</button>
                            <button class="btn btn-danger">❌ Reject</button>
                            <button class="btn btn-warning">🔄 Revert</button>
                            <button class="btn btn-secondary">⏸ Hold</button>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">Application History</h2>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Previous Status</th>
                                    <th>New Status</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2024-01-08</td>
                                    <td>John Doe</td>
                                    <td>submitted</td>
                                    <td>-</td>
                                    <td>pending_rbm</td>
                                    <td>Initial submission</td>
                                </tr>
                                <tr>
                                    <td>2024-01-10</td>
                                    <td>Jane Smith</td>
                                    <td>reverted</td>
                                    <td>pending_rbm</td>
                                    <td>reverted_to_initiator</td>
                                    <td>Missing PAN document</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MIS Dashboard Screen -->
            <div id="mis-dashboard" class="screen">
                <h1>MIS Dashboard</h1>
                
                <div class="card">
                    <!-- Tabs for MIS Dashboard -->
                    <div class="tab-buttons">
                        <button class="tab-btn active" onclick="showTab('mis-tab-pending', this)">Pending Applications</button>
                        <button class="tab-btn" onclick="showTab('mis-tab-approved', this)">Approved Applications</button>
                        <button class="tab-btn" onclick="showTab('mis-tab-reverted', this)">Reverted Applications</button>
                    </div>

                    <!-- Pending Applications Tab Content -->
                    <div id="mis-tab-pending" class="tab-content active">
                        <h3>Pending Document Verification</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Distributor Name</th>
                                    <th>Current Status</th>
                                    <th>Initiated By</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>APP005</td>
                                    <td>FarmTech Innovations</td>
                                    <td><span class="status-badge status-pending">Pending Doc Verify</span></td>
                                    <td>Jane Smith</td>
                                    <td>2024-01-22</td>
                                    <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">Review</button></td>
                                </tr>
                                <tr>
                                    <td>APP006</td>
                                    <td>AgriSupply Co.</td>
                                    <td><span class="status-badge status-pending">Pending Agreement</span></td>
                                    <td>John Doe</td>
                                    <td>2024-01-25</td>
                                    <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">Review</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="display: flex; justify-content: center; margin-top: 1rem; gap: 0.5rem;">
                            <button class="btn btn-secondary">« Previous</button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-secondary">2</button>
                            <button class="btn btn-secondary">Next »</button>
                        </div>
                    </div>

                    <!-- Approved Applications Tab Content -->
                    <div id="mis-tab-approved" class="tab-content">
                        <h3>All Approved Applications</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Distributor Name</th>
                                    <th>Approved By</th>
                                    <th>Approval Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>APP002</td>
                                    <td>XYZ Agro Co.</td>
                                    <td>GM-Sales</td>
                                    <td>2024-01-18</td>
                                    <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button></td>
                                </tr>
                                <tr>
                                    <td>APP007</td>
                                    <td>Rural Roots</td>
                                    <td>ZBM</td>
                                    <td>2024-01-20</td>
                                    <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                         <div style="display: flex; justify-content: center; margin-top: 1rem; gap: 0.5rem;">
                            <button class="btn btn-secondary">« Previous</button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-secondary">2</button>
                            <button class="btn btn-secondary">Next »</button>
                        </div>
                    </div>

                    <!-- Reverted Applications Tab Content -->
                    <div id="mis-tab-reverted" class="tab-content">
                        <h3>All Reverted Applications</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>App ID</th>
                                    <th>Distributor Name</th>
                                    <th>Reverted By</th>
                                    <th>Revert Date</th>
                                    <th>Comments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>APP003</td>
                                    <td>Green Fields</td>
                                    <td>Jane Smith</td>
                                    <td>2024-01-10</td>
                                    <td>Missing PAN document</td>
                                    <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">View</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="display: flex; justify-content: center; margin-top: 1rem; gap: 0.5rem;">
                            <button class="btn btn-secondary">« Previous</button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-secondary">Next »</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Screen (Placeholder) -->
            <div id="approvals" class="screen">
                <h1>Pending Approvals</h1>
                <div class="card">
                    <p>This section would list applications pending your approval based on your role.</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>App ID</th>
                                <th>Distributor Name</th>
                                <th>Initiated By</th>
                                <th>Current Level</th>
                                <th>Date Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>APP001</td>
                                <td>ABC Distributors</td>
                                <td>John Doe</td>
                                <td>RBM Approval</td>
                                <td>2024-01-15</td>
                                <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">Review</button></td>
                            </tr>
                            <tr>
                                <td>APP008</td>
                                <td>Future Farms</td>
                                <td>Mary Green</td>
                                <td>ZBM Approval</td>
                                <td>2024-01-28</td>
                                <td><button class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;" onclick="showScreen('review', document.querySelector('.screen-nav button[onclick*=\'review\']'))">Review</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reports Screen (Placeholder) -->
            <div id="reports" class="screen">
                <h1>Reports</h1>
                <div class="card">
                    <p>This section would contain various reports, e.g., application status, regional performance.</p>
                    <button class="btn btn-secondary">Generate Application Report</button>
                    <button class="btn btn-secondary">Generate Performance Report</button>
                </div>
            </div>

            <!-- User Management Screen (Placeholder) -->
            <div id="users" class="screen">
                <h1>User Management</h1>
                <div class="card">
                    <p>This section would allow for managing user accounts and roles.</p>
                    <button class="btn btn-primary">Add New User</button>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>USR001</td>
                                <td>John Doe</td>
                                <td>Territory Manager</td>
                                <td>Active</td>
                                <td><button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Edit</button></td>
                            </tr>
                            <tr>
                                <td>USR002</td>
                                <td>Jane Smith</td>
                                <td>Regional Business Manager</td>
                                <td>Active</td>
                                <td><button class="btn btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Edit</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
        /**
         * Shows the specified screen and updates active states for navigation buttons.
         * @param {string} screenId The ID of the screen to show.
         * @param {HTMLElement} [clickedButton] The button element that was clicked to trigger the screen change.
         */
        function showScreen(screenId, clickedButton) {
            // Hide all screens
            document.querySelectorAll('.screen').forEach(screen => {
                screen.classList.remove('active');
            });

            // Show the target screen
            const targetScreen = document.getElementById(screenId);
            if (targetScreen) {
                targetScreen.classList.add('active');
            }

            // Update active state for sidebar navigation
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            const sidebarNavItem = document.querySelector(`.nav-item[onclick*='${screenId}']`);
            if (sidebarNavItem) {
                sidebarNavItem.classList.add('active');
            }

            // Update active state for demo screen navigation buttons
            document.querySelectorAll('.screen-nav .screen-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            if (clickedButton) {
                clickedButton.classList.add('active');
            } else {
                // If triggered by sidebar, try to activate the corresponding demo button
                const demoButton = document.querySelector(`.screen-nav button[onclick*='${screenId}']`);
                if (demoButton) {
                    demoButton.classList.add('active');
                }
            }

            // If navigating to MIS Dashboard, ensure the default tab is active
            if (screenId === 'mis-dashboard') {
                showTab('mis-tab-pending', document.querySelector('#mis-dashboard .tab-buttons .tab-btn.active') || document.querySelector('#mis-dashboard .tab-buttons .tab-btn'));
            }
        }

        /**
         * Shows the specified tab content within the MIS Dashboard and updates active tab button.
         * @param {string} tabId The ID of the tab content to show (e.g., 'mis-tab-pending').
         * @param {HTMLElement} clickedButton The button element that was clicked to trigger the tab change.
         */
        function showTab(tabId, clickedButton) {
            // Hide all tab contents within the MIS dashboard
            document.querySelectorAll('#mis-dashboard .tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Show the target tab content
            const targetTabContent = document.getElementById(tabId);
            if (targetTabContent) {
                targetTabContent.classList.add('active');
            }

            // Update active state for tab buttons
            document.querySelectorAll('#mis-dashboard .tab-buttons .tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            if (clickedButton) {
                clickedButton.classList.add('active');
            }
        }

        // Initialize the first screen on page load
        document.addEventListener('DOMContentLoaded', () => {
            showScreen('dashboard', document.querySelector('.screen-nav button.active'));
        });
    </script>
</body>
</html>
