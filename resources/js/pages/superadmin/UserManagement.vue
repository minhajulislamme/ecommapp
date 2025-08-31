<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users, UserCheck, UserX, Shield, Database, Search, Filter, Plus, Edit, Trash2, Eye, ChevronLeft, ChevronRight, Download, FileText, FileSpreadsheet } from 'lucide-vue-next';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ref, computed, watch } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
    email_verified_at: string | null;
}

interface Props {
    stats: {
        total_users: number;
        total_admins: number;
        total_superadmins: number;
        total_regular_users: number;
        active_users: number;
        inactive_users: number;
    };
    users: User[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Super Admin Dashboard',
        href: '/superadmin/dashboard',
    },
];

// Reactive filters
const searchQuery = ref('');
const selectedRole = ref('all');
const selectedStatus = ref('all');

// Pagination
const currentPage = ref(1);
const itemsPerPage = 15;

// Loading states for exports
const isExportingPDF = ref(false);
const isExportingExcel = ref(false);

// Selection state
const selectedUsers = ref<number[]>([]);
const selectAll = ref(false);

// Client-side filtering
const filteredUsers = computed(() => {
    let filtered = [...props.users];

    // Search filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(user => 
            user.name.toLowerCase().includes(query) || 
            user.email.toLowerCase().includes(query)
        );
    }

    // Role filter
    if (selectedRole.value !== 'all') {
        filtered = filtered.filter(user => user.role === selectedRole.value);
    }

    // Status filter
    if (selectedStatus.value !== 'all') {
        const isActive = selectedStatus.value === 'active';
        filtered = filtered.filter(user => user.is_active === isActive);
    }

    return filtered;
});

// Paginated users
const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredUsers.value.slice(start, end);
});

// Pagination info
const paginationInfo = computed(() => {
    const total = filteredUsers.value.length;
    const totalPages = Math.ceil(total / itemsPerPage);
    const from = total > 0 ? (currentPage.value - 1) * itemsPerPage + 1 : 0;
    const to = Math.min(currentPage.value * itemsPerPage, total);

    return {
        total,
        totalPages,
        from,
        to,
        currentPage: currentPage.value
    };
});

// Reset to first page when filters change
const resetToFirstPage = () => {
    currentPage.value = 1;
};

// Clear all filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedRole.value = 'all';
    selectedStatus.value = 'all';
    resetToFirstPage();
};

// Selection management
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedUsers.value = filteredUsers.value.map(user => user.id);
    } else {
        selectedUsers.value = [];
    }
};

const toggleUserSelection = (userId: number) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
    
    // Update select all state
    selectAll.value = selectedUsers.value.length === filteredUsers.value.length;
};

const isUserSelected = (userId: number) => {
    return selectedUsers.value.includes(userId);
};

// Get users to export (selected or all filtered)
const getUsersToExport = () => {
    if (selectedUsers.value.length > 0) {
        return props.users.filter(user => selectedUsers.value.includes(user.id));
    }
    return filteredUsers.value;
};

// Watch for filter changes and update selection state
watch(filteredUsers, () => {
    // Remove selected users that are no longer in filtered results
    selectedUsers.value = selectedUsers.value.filter(userId => 
        filteredUsers.value.some(user => user.id === userId)
    );
    
    // Update select all state
    selectAll.value = filteredUsers.value.length > 0 && 
        selectedUsers.value.length === filteredUsers.value.length;
});

// Watch for filter changes and reset page
const goToPage = (page: number) => {
    currentPage.value = page;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getRoleIcon = (role: string) => {
    switch (role) {
        case 'superadmin':
            return Shield;
        case 'admin':
            return Database;
        default:
            return Users;
    }
};

const getRoleColor = (role: string) => {
    switch (role) {
        case 'superadmin':
            return 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20';
        case 'admin':
            return 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20';
        default:
            return 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20';
    }
};

// Export functions
const exportToPDF = async () => {
    if (isExportingPDF.value) return;
    
    isExportingPDF.value = true;
    try {
        // Import jsPDF and autoTable
        const jsPDF = (await import('jspdf')).default;
        
        // Create new document
        const doc = new jsPDF();
        
        // Get users to export
        const usersToExport = getUsersToExport();
        
        // Add title with better styling
        doc.setFontSize(20);
        doc.setTextColor(40, 40, 40);
        doc.setFont('helvetica', 'bold');
        doc.text('User Management Report', 20, 25);
        
        // Add a line under the title
        doc.setDrawColor(79, 70, 229);
        doc.setLineWidth(0.5);
        doc.line(20, 30, 190, 30);
        
        // Add metadata
        doc.setFontSize(11);
        doc.setTextColor(100, 100, 100);
        doc.setFont('helvetica', 'normal');
        doc.text(`Generated: ${new Date().toLocaleString()}`, 20, 40);
        doc.text(`Total Records: ${usersToExport.length}`, 20, 48);
        
        // Add filter information (only show actual filters, not selection info)
        let yPosition = 55;
        if (searchQuery.value || selectedRole.value !== 'all' || selectedStatus.value !== 'all') {
            doc.setFontSize(10);
            doc.setTextColor(220, 38, 127); // Pink color for filters
            doc.setFont('helvetica', 'bold');
            doc.text('Applied Filters:', 20, yPosition);
            yPosition += 8;
            
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(100, 100, 100);
            
            if (searchQuery.value) {
                doc.text(`• Search: "${searchQuery.value}"`, 25, yPosition);
                yPosition += 7;
            }
            if (selectedRole.value !== 'all') {
                doc.text(`• Role: ${selectedRole.value.charAt(0).toUpperCase() + selectedRole.value.slice(1)}`, 25, yPosition);
                yPosition += 7;
            }
            if (selectedStatus.value !== 'all') {
                doc.text(`• Status: ${selectedStatus.value.charAt(0).toUpperCase() + selectedStatus.value.slice(1)}`, 25, yPosition);
                yPosition += 7;
            }
            yPosition += 5;
        } else {
            yPosition += 5; // Just add some spacing instead of the "no filters" text
        }
        
        // Prepare data
        const headers = ['Name', 'Email', 'Role', 'Status', 'Verified', 'Joined'];
        const data = usersToExport.map(user => [
            user.name,
            user.email,
            user.role.charAt(0).toUpperCase() + user.role.slice(1),
            user.is_active ? 'Active' : 'Inactive',
            user.email_verified_at ? 'Yes' : 'No',
            new Date(user.created_at).toLocaleDateString()
        ]);
        
        // Try to use autoTable for better formatting
        try {
            const autoTable = (await import('jspdf-autotable')).default;
            
            autoTable(doc, {
                head: [headers],
                body: data,
                startY: yPosition + 10,
                theme: 'striped',
                styles: {
                    fontSize: 9,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    halign: 'center', // Center align all content
                    valign: 'middle',
                },
                headStyles: {
                    fillColor: [79, 70, 229],
                    textColor: 255,
                    fontStyle: 'bold',
                    fontSize: 9,
                    halign: 'center', // Center align headers
                    valign: 'middle',
                },
                columnStyles: {
                    0: { cellWidth: 35, halign: 'left' }, // Name - left align for readability
                    1: { cellWidth: 45, halign: 'left' }, // Email - left align for readability
                    2: { cellWidth: 20, halign: 'center' }, // Role - center
                    3: { cellWidth: 18, halign: 'center' }, // Status - center
                    4: { cellWidth: 18, halign: 'center' }, // Verified - center
                    5: { cellWidth: 25, halign: 'center' }, // Joined - center
                },
                alternateRowStyles: {
                    fillColor: [248, 250, 252],
                },
                margin: { top: 20, right: 14, bottom: 20, left: 14 },
            });
        } catch {
            console.log('AutoTable not available, using manual table');
            
            // Manual table creation with better formatting
            let y = yPosition + 10;
            doc.setFontSize(9);
            doc.setTextColor(0);
            
            // Define column positions and widths with center alignment
            const columns = [
                { header: 'Name', x: 20, width: 35, align: 'left' },
                { header: 'Email', x: 55, width: 45, align: 'left' },
                { header: 'Role', x: 100, width: 20, align: 'center' },
                { header: 'Status', x: 120, width: 18, align: 'center' },
                { header: 'Verified', x: 138, width: 18, align: 'center' },
                { header: 'Joined', x: 156, width: 25, align: 'center' }
            ];
            
            // Draw header background
            doc.setFillColor(79, 70, 229);
            doc.rect(20, y - 3, 165, 8, 'F');
            
            // Draw headers (centered)
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(255, 255, 255);
            columns.forEach(col => {
                const headerX = col.align === 'center' ? col.x + (col.width / 2) : col.x;
                doc.text(col.header, headerX, y + 2, { align: col.align === 'center' ? 'center' : 'left' });
            });
            
            y += 10;
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(0);
            
            // Draw data rows
            data.forEach((row, rowIndex) => {
                if (y > 270) {
                    doc.addPage();
                    y = 20;
                    
                    // Redraw header on new page
                    doc.setFillColor(79, 70, 229);
                    doc.rect(20, y - 3, 165, 8, 'F');
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(255, 255, 255);
                    columns.forEach(col => {
                        const headerX = col.align === 'center' ? col.x + (col.width / 2) : col.x;
                        doc.text(col.header, headerX, y + 2, { align: col.align === 'center' ? 'center' : 'left' });
                    });
                    y += 10;
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(0);
                }
                
                // Alternate row background
                if (rowIndex % 2 === 1) {
                    doc.setFillColor(248, 250, 252);
                    doc.rect(20, y - 2, 165, 7, 'F');
                }
                
                // Draw cell data with proper alignment
                columns.forEach((col, colIndex) => {
                    let cellText = String(row[colIndex] || '');
                    
                    // Truncate text based on column width
                    const maxChars = Math.floor(col.width / 2.5);
                    if (cellText.length > maxChars) {
                        cellText = cellText.substring(0, maxChars - 3) + '...';
                    }
                    
                    const textX = col.align === 'center' ? col.x + (col.width / 2) : col.x;
                    doc.text(cellText, textX, y + 2, { align: col.align === 'center' ? 'center' : 'left' });
                });
                
                y += 7;
            });
        }
        
        // Save
        let fileName = `users-${new Date().toISOString().split('T')[0]}`;
        
        // Add filter info to filename
        if (searchQuery.value || selectedRole.value !== 'all' || selectedStatus.value !== 'all') {
            const filters = [];
            if (searchQuery.value) filters.push('search');
            if (selectedRole.value !== 'all') filters.push(selectedRole.value);
            if (selectedStatus.value !== 'all') filters.push(selectedStatus.value);
            fileName += `-filtered-${filters.join('-')}`;
        } else if (selectedUsers.value.length > 0) {
            fileName += '-selected';
        } else {
            fileName += '-all';
        }
        fileName += '.pdf';
        
        doc.save(fileName);
        console.log('PDF saved successfully');
        
    } catch (error) {
        console.error('PDF export error:', error);
        
        // Fallback: Create a simple text-based PDF
        try {
            const jsPDF = (await import('jspdf')).default;
            const doc = new jsPDF();
            
            doc.setFontSize(16);
            doc.text('User Management Export', 20, 30);
            doc.setFontSize(12);
            const fallbackUsersCount = getUsersToExport();
            doc.text(`Total Users: ${fallbackUsersCount.length}`, 20, 50);
            doc.text(`Exported: ${new Date().toLocaleString()}`, 20, 60);
            
            // Add filter info if any
            let yPos = 70;
            if (searchQuery.value || selectedRole.value !== 'all' || selectedStatus.value !== 'all') {
                doc.setFontSize(10);
                doc.text('Applied Filters:', 20, yPos);
                yPos += 10;
                if (searchQuery.value) {
                    doc.text(`• Search: ${searchQuery.value}`, 25, yPos);
                    yPos += 8;
                }
                if (selectedRole.value !== 'all') {
                    doc.text(`• Role: ${selectedRole.value}`, 25, yPos);
                    yPos += 8;
                }
                if (selectedStatus.value !== 'all') {
                    doc.text(`• Status: ${selectedStatus.value}`, 25, yPos);
                    yPos += 8;
                }
                yPos += 10;
            }
            
            // List users in a simple format
            doc.setFontSize(10);
            const fallbackUsers = getUsersToExport();
            fallbackUsers.forEach((user: User, index: number) => {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                }
                const userLine = `${index + 1}. ${user.name} | ${user.email} | ${user.role} | ${user.is_active ? 'Active' : 'Inactive'}`;
                doc.text(userLine, 20, yPos);
                yPos += 8;
            });
            
            const fallbackFileName = selectedUsers.value.length > 0 
                ? `users-selected-${new Date().toISOString().split('T')[0]}.pdf`
                : `users-simple-${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fallbackFileName);
        } catch (fallbackError) {
            alert('Unable to generate PDF. Please try again or contact support.');
            console.error('PDF fallback error:', fallbackError);
        }
    } finally {
        isExportingPDF.value = false;
    }
};

const exportToExcel = async () => {
    if (isExportingExcel.value) return;
    
    isExportingExcel.value = true;
    try {
        const ExcelJS = await import('exceljs');
        
        // Get users to export
        const usersToExport = getUsersToExport();
        
        // Prepare data for Excel
        const excelData = usersToExport.map(user => ({
            'ID': user.id,
            'Name': user.name,
            'Email': user.email,
            'Role': user.role.charAt(0).toUpperCase() + user.role.slice(1),
            'Status': user.is_active ? 'Active' : 'Inactive',
            'Email Status': user.email_verified_at ? 'Verified' : 'Unverified',
            'Email Verified At': user.email_verified_at ? new Date(user.email_verified_at).toLocaleDateString() : 'Not Verified',
            'Joined Date': new Date(user.created_at).toLocaleDateString(),
            'Created At (Full)': user.created_at
        }));
        
        // Create workbook and worksheet
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Users');
        
        // Define columns
        worksheet.columns = Object.keys(excelData[0] || {}).map(key => ({
            header: key,
            key: key,
            width: Math.min(Math.max(key.length + 2, 10), 50) // Min width 10, max 50
        }));
        
        // Add data rows
        excelData.forEach(row => {
            worksheet.addRow(row);
        });
        
        // Auto-resize columns based on content
        worksheet.columns.forEach(column => {
            let maxLength = 0;
            worksheet.eachRow({ includeEmpty: false }, (row) => {
                const columnLetter = column.letter;
                if (columnLetter) {
                    const cellValue = row.getCell(columnLetter).value;
                    if (cellValue !== null && cellValue !== undefined) {
                        maxLength = Math.max(maxLength, cellValue.toString().length);
                    }
                }
            });
            column.width = Math.min(Math.max(maxLength + 2, 10), 50);
        });
        
        // Add summary sheet with stats and filter info
        const summaryData = [
            ['User Export Summary', ''],
            ['', ''],
            ['Export Information', ''],
            ['Generated On', new Date().toLocaleDateString()],
            ['Generated At', new Date().toLocaleTimeString()],
            ['Total Users in System', props.stats.total_users],
            ['Exported Records', usersToExport.length],
            ['', ''],
            ['Applied Filters', ''],
        ];

        // Add filter information (only show actual filters, not selection info)
        if (searchQuery.value || selectedRole.value !== 'all' || selectedStatus.value !== 'all') {
            if (searchQuery.value) {
                summaryData.push(['Search Filter', searchQuery.value]);
            }
            if (selectedRole.value !== 'all') {
                summaryData.push(['Role Filter', selectedRole.value.charAt(0).toUpperCase() + selectedRole.value.slice(1)]);
            }
            if (selectedStatus.value !== 'all') {
                summaryData.push(['Status Filter', selectedStatus.value.charAt(0).toUpperCase() + selectedStatus.value.slice(1)]);
            }
        } else {
            summaryData.push(['Filters Applied', 'None']);
        }

        // Add system statistics
        summaryData.push(
            ['', ''],
            ['System Statistics', ''],
            ['Total Users', props.stats.total_users],
            ['Super Admins', props.stats.total_superadmins],
            ['Admins', props.stats.total_admins],
            ['Regular Users', props.stats.total_regular_users],
            ['Active Users', props.stats.active_users],
            ['Inactive Users', props.stats.inactive_users]
        );
        
        const summaryWorksheet = workbook.addWorksheet('Summary');
        
        // Add summary data as rows
        summaryData.forEach(row => {
            summaryWorksheet.addRow(row);
        });
        
        // Format the summary sheet
        summaryWorksheet.columns = [
            { width: 25 },
            { width: 30 }
        ];
        
        // Generate filename
        let fileName = `users-export-${new Date().toISOString().split('T')[0]}`;
        
        // Add filter info to filename
        if (selectedUsers.value.length > 0) {
            fileName += '-selected';
        } else if (searchQuery.value || selectedRole.value !== 'all' || selectedStatus.value !== 'all') {
            const filters = [];
            if (searchQuery.value) filters.push('search');
            if (selectedRole.value !== 'all') filters.push(selectedRole.value);
            if (selectedStatus.value !== 'all') filters.push(selectedStatus.value);
            fileName += `-filtered-${filters.join('-')}`;
        } else {
            fileName += '-all';
        }
        fileName += '.xlsx';
        
        // Write file and trigger download
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        link.click();
        window.URL.revokeObjectURL(url);
        
        console.log('Excel file downloaded successfully:', fileName);
    } catch (error) {
        console.error('Error generating Excel file:', error);
        alert('Error generating Excel file. Please try again.');
    } finally {
        isExportingExcel.value = false;
    }
};
</script>

<template>
    <Head title="User Management - Super Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto rounded-xl p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Manage all users in your application</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm" class="flex items-center gap-2 w-full sm:w-auto">
                                <Download class="h-4 w-4" />
                                <span class="hidden sm:inline">Export Data</span>
                                <span class="sm:hidden">Export</span>
                                <span v-if="selectedUsers.length > 0" 
                                      class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900 dark:text-green-300">
                                    {{ selectedUsers.length }}
                                </span>
                                <span v-else-if="searchQuery || selectedRole !== 'all' || selectedStatus !== 'all'" 
                                      class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                    Filtered
                                </span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="exportToPDF" class="flex flex-col items-start gap-1" :disabled="isExportingPDF">
                                <div class="flex items-center gap-2">
                                    <FileText class="h-4 w-4" />
                                    {{ isExportingPDF ? 'Generating PDF...' : 'Export as PDF' }}
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ selectedUsers.length > 0 ? selectedUsers.length : filteredUsers.length }} record{{ (selectedUsers.length > 0 ? selectedUsers.length : filteredUsers.length) !== 1 ? 's' : '' }}
                                    {{ selectedUsers.length > 0 ? '(selected)' : '' }}
                                </span>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="exportToExcel" class="flex flex-col items-start gap-1" :disabled="isExportingExcel">
                                <div class="flex items-center gap-2">
                                    <FileSpreadsheet class="h-4 w-4" />
                                    {{ isExportingExcel ? 'Generating Excel...' : 'Export as Excel' }}
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ selectedUsers.length > 0 ? selectedUsers.length : filteredUsers.length }} record{{ (selectedUsers.length > 0 ? selectedUsers.length : filteredUsers.length) !== 1 ? 's' : '' }} + summary
                                    {{ selectedUsers.length > 0 ? '(selected)' : '' }}
                                </span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Add New User
                    </Button>
                    <div class="flex items-center gap-2">
                        <Shield class="h-8 w-8 text-red-500" />
                        <span class="text-sm font-semibold text-red-500">SUPER ADMIN</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <StatCard
                    title="Total Users"
                    :value="props.stats.total_users"
                    :icon="Users"
                    color="blue"
                />
                <StatCard
                    title="Super Admins"
                    :value="props.stats.total_superadmins"
                    :icon="Shield"
                    color="red"
                />
                <StatCard
                    title="Admins"
                    :value="props.stats.total_admins"
                    :icon="Database"
                    color="purple"
                />
                <StatCard
                    title="Regular Users"
                    :value="props.stats.total_regular_users"
                    :icon="Users"
                    color="green"
                />
                <StatCard
                    title="Active Users"
                    :value="props.stats.active_users"
                    :icon="UserCheck"
                    color="emerald"
                />
                <StatCard
                    title="Inactive Users"
                    :value="props.stats.inactive_users"
                    :icon="UserX"
                    color="orange"
                />
            </div>

            <!-- Filters and Search -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">All Users</h2>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            {{ filteredUsers.length }} of {{ props.users.length }} users
                            <span v-if="searchQuery || selectedRole !== 'all' || selectedStatus !== 'all'">
                                (filtered)
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search users..."
                                class="pl-10 w-full sm:w-64"
                                @input="resetToFirstPage"
                            />
                        </div>
                        
                        <div class="flex gap-2">
                            <!-- Role Filter -->
                            <select
                                v-model="selectedRole"
                                class="flex-1 sm:flex-none rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                @change="resetToFirstPage"
                            >
                                <option value="all">All Roles</option>
                                <option value="user">Regular Users</option>
                                <option value="admin">Admins</option>
                                <option value="superadmin">Super Admins</option>
                            </select>
                            
                            <!-- Status Filter -->
                            <select
                                v-model="selectedStatus"
                                class="flex-1 sm:flex-none rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                @change="resetToFirstPage"
                            >
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Clear Filters Button -->
                        <Button 
                            v-if="searchQuery || selectedRole !== 'all' || selectedStatus !== 'all'"
                            variant="ghost" 
                            size="sm" 
                            @click="clearFilters"
                            class="flex items-center gap-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 w-full sm:w-auto justify-center"
                        >
                            <Filter class="h-4 w-4" />
                            Clear
                        </Button>
                    </div>
                </div>
                
                <!-- Selection Controls -->
                <div v-if="filteredUsers.length > 0" class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="selectAll"
                                @change="toggleSelectAll"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="hidden sm:inline">Select All ({{ filteredUsers.length }})</span>
                                <span class="sm:hidden">All ({{ filteredUsers.length }})</span>
                            </span>
                        </label>
                        
                        <span v-if="selectedUsers.length > 0" class="text-sm text-blue-600 dark:text-blue-400">
                            {{ selectedUsers.length }} selected
                        </span>
                    </div>
                    
                    <div v-if="selectedUsers.length > 0" class="flex items-center gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            @click="selectedUsers = []"
                            class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 w-full sm:w-auto justify-center"
                        >
                            Clear Selection
                        </Button>
                    </div>
                </div>

                <!-- Users Table/Cards -->
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400 w-12">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">User</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Role</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Email Status</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Joined</th>
                                <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="space-y-3">
                            <tr v-if="paginatedUsers.length === 0">
                                <td colspan="7" class="py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Users class="h-12 w-12 text-gray-400" />
                                        <p class="text-gray-500 dark:text-gray-400">No users found</p>
                                        <p class="text-sm text-gray-400 dark:text-gray-500">
                                            Try adjusting your search or filter criteria
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="user in paginatedUsers" :key="user.id" class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <!-- Selection Checkbox -->
                                <td class="py-4">
                                    <input
                                        type="checkbox"
                                        :checked="isUserSelected(user.id)"
                                        @change="toggleUserSelection(user.id)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </td>
                                
                                <!-- User Info -->
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Role -->
                                <td class="py-4">
                                    <div class="flex items-center gap-2">
                                        <component :is="getRoleIcon(user.role)" class="h-4 w-4" />
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize"
                                            :class="getRoleColor(user.role)"
                                        >
                                            {{ user.role }}
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Status -->
                                <td class="py-4">
                                    <span
                                        :class="user.is_active 
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' 
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    >
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                
                                <!-- Email Status -->
                                <td class="py-4">
                                    <span
                                        :class="user.email_verified_at 
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' 
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400'"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    >
                                        {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                    </span>
                                </td>
                                
                                <!-- Joined Date -->
                                <td class="py-4">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(user.created_at) }}
                                    </span>
                                </td>
                                
                                <!-- Actions -->
                                <td class="py-4">
                                    <div class="flex items-center gap-2">
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-red-600 hover:text-red-700">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-4">
                    <div v-if="paginatedUsers.length === 0" class="py-8 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <Users class="h-12 w-12 text-gray-400" />
                            <p class="text-gray-500 dark:text-gray-400">No users found</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                Try adjusting your search or filter criteria
                            </p>
                        </div>
                    </div>
                    
                    <div v-for="user in paginatedUsers" :key="user.id" 
                         class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                        <!-- Header with checkbox and avatar -->
                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                :checked="isUserSelected(user.id)"
                                @change="toggleUserSelection(user.id)"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</p>
                            </div>
                        </div>
                        
                        <!-- User details -->
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <!-- Role -->
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Role</dt>
                                <dd class="mt-1">
                                    <div class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium" :class="getRoleColor(user.role)">
                                        <component :is="getRoleIcon(user.role)" class="h-3 w-3" />
                                        {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                    </div>
                                </dd>
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium"
                                          :class="user.is_active 
                                              ? 'text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/20' 
                                              : 'text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-900/20'">
                                        <div class="h-1.5 w-1.5 rounded-full" 
                                             :class="user.is_active ? 'bg-green-400' : 'bg-red-400'"></div>
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            
                            <!-- Email Status -->
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Email</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium"
                                          :class="user.email_verified_at 
                                              ? 'text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/20' 
                                              : 'text-yellow-700 bg-yellow-50 dark:text-yellow-400 dark:bg-yellow-900/20'">
                                        {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                    </span>
                                </dd>
                            </div>
                            
                            <!-- Joined Date -->
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Joined</dt>
                                <dd class="mt-1 text-gray-900 dark:text-white">
                                    {{ formatDate(user.created_at) }}
                                </dd>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <Button variant="ghost" size="sm" class="flex-1 justify-center">
                                <Eye class="h-4 w-4 mr-2" />
                                View
                            </Button>
                            <Button variant="ghost" size="sm" class="flex-1 justify-center">
                                <Edit class="h-4 w-4 mr-2" />
                                Edit
                            </Button>
                            <Button variant="ghost" size="sm" class="flex-1 justify-center text-red-600 hover:text-red-700">
                                <Trash2 class="h-4 w-4 mr-2" />
                                Delete
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400 text-center sm:text-left">
                        Showing {{ paginationInfo.from }} to {{ paginationInfo.to }} of {{ paginationInfo.total }} users
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="paginationInfo.currentPage === 1"
                            @click="goToPage(paginationInfo.currentPage - 1)"
                            class="px-2 sm:px-3"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            <span class="hidden sm:inline ml-1">Previous</span>
                        </Button>
                        
                        <div class="flex items-center gap-1">
                            <!-- Show simplified pagination on mobile -->
                            <div class="sm:hidden text-sm text-gray-600 dark:text-gray-400 px-2">
                                {{ paginationInfo.currentPage }} / {{ paginationInfo.totalPages }}
                            </div>
                            
                            <!-- Show full pagination on desktop -->
                            <div class="hidden sm:flex items-center gap-1">
                                <template v-for="page in Math.min(paginationInfo.totalPages, 5)" :key="page">
                                    <Button
                                        v-if="page === paginationInfo.currentPage"
                                        size="sm"
                                        class="w-8"
                                    >
                                        {{ page }}
                                    </Button>
                                    <Button
                                        v-else
                                        variant="ghost"
                                        size="sm"
                                        class="w-8"
                                        @click="goToPage(page)"
                                    >
                                        {{ page }}
                                    </Button>
                                </template>
                            </div>
                        </div>
                        
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="paginationInfo.currentPage === paginationInfo.totalPages"
                            @click="goToPage(paginationInfo.currentPage + 1)"
                            class="px-2 sm:px-3"
                        >
                            <span class="hidden sm:inline mr-1">Next</span>
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
