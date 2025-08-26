<?php
/**
 * Content Start Template
 * 
 * This file contains the opening HTML for the main content area.
 */
?>
<!-- Main content -->
<div class="flex flex-col flex-1 overflow-hidden">
    <?php include_once INCLUDES_PATH . '/navbar.php'; ?>
    
    <!-- Main content area -->
    <main class="flex-1 relative overflow-y-auto focus:outline-none custom-scrollbar">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <?php display_flash_message(); ?>
