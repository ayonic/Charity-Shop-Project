<?php
/**
 * Header Template
 * 
 * This file contains the HTML header for all pages.
 */

// Get current page title
$page_title = '';
$current_page = current_page();

switch ($current_page) {
    case 'dashboard.php':
        $page_title = 'Dashboard';
        break;
    case 'inventory.php':
        $page_title = 'Inventory Management';
        break;
    case 'donations.php':
        $page_title = 'Donations Management';
        break;
    case 'sales.php':
        $page_title = 'Sales & POS';
        break;
    case 'volunteers.php':
        $page_title = 'Volunteer Management';
        break;
    case 'reports.php':
        $page_title = 'Reports & Analytics';
        break;
    case 'settings.php':
        $page_title = 'Settings';
        break;
    default:
        $page_title = 'Charity Shop Management System';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Charity Shop Management System</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#10B981',
                        accent: '#F59E0B',
                        danger: '#EF4444'
                    },
                    fontFamily: {
                        'display': ['Inter', 'sans-serif'],
                        'body': ['Inter', 'sans-serif']
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'scale-in': 'scaleIn 0.2s ease-out'
                    },
                    backdropBlur: {
                        xs: '2px'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-dot.in-stock { background-color: #10B981; }
        .status-dot.low-stock { background-color: #F59E0B; }
        .status-dot.out-of-stock { background-color: #EF4444; }
        
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .category-badge.clothing { background-color: #EFF6FF; color: #3B82F6; }
        .category-badge.books { background-color: #F0FDF4; color: #10B981; }
        .category-badge.furniture { background-color: #FEF3C7; color: #D97706; }
        .category-badge.electronics { background-color: #EDE9FE; color: #8B5CF6; }
        .category-badge.toys { background-color: #FCE7F3; color: #DB2777; }
        .category-badge.kitchenware { background-color: #E0F2FE; color: #0EA5E9; }
        .category-badge.accessories { background-color: #FEF2F2; color: #EF4444; }
        .category-badge.other { background-color: #F3F4F6; color: #6B7280; }
        
        .flash-message {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .flash-message.success {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
            border-color: #10B981;
        }
        .flash-message.error {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            color: #991B1B;
            border-color: #EF4444;
        }
        .flash-message.warning {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            color: #92400E;
            border-color: #F59E0B;
        }
        .flash-message.info {
            background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
            color: #1E40AF;
            border-color: #3B82F6;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338CA 0%, #6D28D9 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }
        
        .sidebar-item {
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .sidebar-item:hover {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
        }
        
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            border-color: #4F46E5;
        }
        
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%);
            transform: scale(1.01);
        }
    </style>
</head>
<body class="font-body bg-gray-50 min-h-screen">
    <div class="flex h-screen bg-gray-50">
