/**
 * Member Management System - Pure JavaScript Implementation
 * Handles member registration, data storage, and retrieval
 */

class MemberSystem {
    constructor() {
        this.storageKey = 'charity_members';
        this.pendingKey = 'charity_pending_members';
        this.counterKey = 'charity_member_counter';
        this.lastExportKey = 'charity_last_export';
        this.autoExportInterval = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
    }

    /**
     * Initialize the system
     */
    init() {
        if (!localStorage.getItem(this.storageKey)) {
            localStorage.setItem(this.storageKey, JSON.stringify([]));
        }
        if (!localStorage.getItem(this.pendingKey)) {
            localStorage.setItem(this.pendingKey, JSON.stringify([]));
        }
        if (!localStorage.getItem(this.counterKey)) {
            localStorage.setItem(this.counterKey, '1000');
        }
        
        // Check and perform auto-export if needed
        this.checkAutoExport();
    }

    /**
     * Generate unique User ID
     */
    generateUserId() {
        let counter = parseInt(localStorage.getItem(this.counterKey));
        counter++;
        localStorage.setItem(this.counterKey, counter.toString());
        return `SNJ${counter}`;
    }

    /**
     * Register a new member (goes to pending list)
     */
    registerMember(memberData) {
        try {
            const userId = this.generateUserId();
            const member = {
                ...memberData,
                userId: userId,
                registrationDate: new Date().toISOString(),
                status: 'pending',
                approved: false,
                paymentStatus: memberData.paymentStatus || 'pending',
                paymentVerified: false
            };

            // Add to pending members
            const pending = this.getPendingMembers();
            pending.push(member);
            localStorage.setItem(this.pendingKey, JSON.stringify(pending));

            return { success: true, userId: userId, member: member };
        } catch (error) {
            console.error('Error registering member:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get all pending members (awaiting approval)
     */
    getPendingMembers() {
        try {
            return JSON.parse(localStorage.getItem(this.pendingKey)) || [];
        } catch (error) {
            console.error('Error getting pending members:', error);
            return [];
        }
    }

    /**
     * Get all approved members
     */
    getApprovedMembers() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey)) || [];
        } catch (error) {
            console.error('Error getting approved members:', error);
            return [];
        }
    }

    /**
     * Approve a member (move from pending to approved)
     */
    approveMember(userId) {
        try {
            const pending = this.getPendingMembers();
            const memberIndex = pending.findIndex(m => m.userId === userId);
            
            if (memberIndex === -1) {
                return { success: false, error: 'Member not found in pending list' };
            }

            const member = pending[memberIndex];
            member.status = 'approved';
            member.approved = true;
            member.approvalDate = new Date().toISOString();

            // Remove from pending
            pending.splice(memberIndex, 1);
            localStorage.setItem(this.pendingKey, JSON.stringify(pending));

            // Add to approved
            const approved = this.getApprovedMembers();
            approved.push(member);
            localStorage.setItem(this.storageKey, JSON.stringify(approved));

            return { success: true, member: member };
        } catch (error) {
            console.error('Error approving member:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Reject a member (remove from pending)
     */
    rejectMember(userId, reason = '') {
        try {
            const pending = this.getPendingMembers();
            const memberIndex = pending.findIndex(m => m.userId === userId);
            
            if (memberIndex === -1) {
                return { success: false, error: 'Member not found in pending list' };
            }

            const member = pending[memberIndex];
            member.status = 'rejected';
            member.rejectionReason = reason;
            member.rejectionDate = new Date().toISOString();

            // Remove from pending
            pending.splice(memberIndex, 1);
            localStorage.setItem(this.pendingKey, JSON.stringify(pending));

            return { success: true, member: member };
        } catch (error) {
            console.error('Error rejecting member:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get member by User ID (check both approved and pending)
     */
    getMemberByUserId(userId) {
        const approved = this.getApprovedMembers();
        const member = approved.find(m => m.userId === userId);
        
        if (member) {
            return { success: true, member: member, status: 'approved' };
        }

        const pending = this.getPendingMembers();
        const pendingMember = pending.find(m => m.userId === userId);
        
        if (pendingMember) {
            return { success: true, member: pendingMember, status: 'pending' };
        }

        return { success: false, error: 'Member not found' };
    }

    /**
     * Verify member credentials for ID card download
     */
    verifyMember(userId, dob) {
        const result = this.getMemberByUserId(userId);
        
        if (!result.success) {
            return { success: false, error: 'Invalid User ID' };
        }

        if (result.member.dob !== dob) {
            return { success: false, error: 'Date of Birth does not match' };
        }

        if (result.status !== 'approved') {
            return { success: false, error: 'Your application is pending approval' };
        }

        return { success: true, member: result.member };
    }

    /**
     * Export data as JSON (for backup)
     */
    exportData() {
        return {
            approved: this.getApprovedMembers(),
            pending: this.getPendingMembers(),
            counter: localStorage.getItem(this.counterKey),
            exportDate: new Date().toISOString()
        };
    }

    /**
     * Import data from JSON (for restore)
     */
    importData(data) {
        try {
            if (data.approved) {
                localStorage.setItem(this.storageKey, JSON.stringify(data.approved));
            }
            if (data.pending) {
                localStorage.setItem(this.pendingKey, JSON.stringify(data.pending));
            }
            if (data.counter) {
                localStorage.setItem(this.counterKey, data.counter);
            }
            return { success: true };
        } catch (error) {
            console.error('Error importing data:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Clear all data (use with caution!)
     */
    clearAllData() {
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem(this.pendingKey);
        localStorage.removeItem(this.counterKey);
        this.init();
    }

    /**
     * Verify payment and update status
     */
    verifyPayment(userId, verified = true) {
        try {
            // Check in pending
            let pending = this.getPendingMembers();
            let memberIndex = pending.findIndex(m => m.userId === userId);
            
            if (memberIndex !== -1) {
                pending[memberIndex].paymentVerified = verified;
                pending[memberIndex].paymentStatus = verified ? 'verified' : 'rejected';
                localStorage.setItem(this.pendingKey, JSON.stringify(pending));
                return { success: true, member: pending[memberIndex] };
            }

            // Check in approved
            let approved = this.getApprovedMembers();
            memberIndex = approved.findIndex(m => m.userId === userId);
            
            if (memberIndex !== -1) {
                approved[memberIndex].paymentVerified = verified;
                approved[memberIndex].paymentStatus = verified ? 'verified' : 'rejected';
                localStorage.setItem(this.storageKey, JSON.stringify(approved));
                return { success: true, member: approved[memberIndex] };
            }

            return { success: false, error: 'Member not found' };
        } catch (error) {
            console.error('Error verifying payment:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Member login (User ID + DOB)
     */
    memberLogin(userId, dob) {
        const result = this.getMemberByUserId(userId);
        
        if (!result.success) {
            return { success: false, error: 'Invalid User ID' };
        }

        if (result.member.dob !== dob) {
            return { success: false, error: 'Date of Birth does not match' };
        }

        return { success: true, member: result.member, status: result.status };
    }

    /**
     * Check if auto-export is needed and perform it
     */
    checkAutoExport() {
        try {
            const lastExport = localStorage.getItem(this.lastExportKey);
            const now = new Date().getTime();
            
            if (!lastExport || (now - parseInt(lastExport)) > this.autoExportInterval) {
                this.performAutoExport();
                localStorage.setItem(this.lastExportKey, now.toString());
            }
        } catch (error) {
            console.error('Auto-export check failed:', error);
        }
    }

    /**
     * Perform automatic export to browser download
     */
    performAutoExport() {
        try {
            const data = this.exportData();
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-').split('T')[0];
            const filename = `charity-backup-${timestamp}.json`;
            
            // Create blob and download
            const dataStr = JSON.stringify(data, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            console.log(`✅ Auto-backup created: ${filename}`);
            return true;
        } catch (error) {
            console.error('Auto-export failed:', error);
            return false;
        }
    }

    /**
     * Manual trigger for auto-export (for testing)
     */
    triggerAutoExport() {
        this.performAutoExport();
        localStorage.setItem(this.lastExportKey, new Date().getTime().toString());
    }

    /**
     * Get last export timestamp
     */
    getLastExportInfo() {
        const lastExport = localStorage.getItem(this.lastExportKey);
        if (!lastExport) {
            return { success: false, message: 'No exports yet' };
        }
        
        const lastExportDate = new Date(parseInt(lastExport));
        const nextExportDate = new Date(parseInt(lastExport) + this.autoExportInterval);
        
        return {
            success: true,
            lastExport: lastExportDate.toLocaleString(),
            nextExport: nextExportDate.toLocaleString(),
            daysUntilNext: Math.ceil((nextExportDate - new Date()) / (1000 * 60 * 60 * 24))
        };
    }
}

// Initialize global instance
const memberSystem = new MemberSystem();
memberSystem.init();
