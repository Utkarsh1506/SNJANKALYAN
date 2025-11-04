/**
 * Authentication System - Admin and Member Login
 */

class AuthSystem {
    constructor() {
        this.adminKey = 'charity_admin';
        this.sessionKey = 'charity_session';
        this.init();
    }

    /**
     * Initialize admin credentials
     */
    init() {
        // Check if admin exists, if not create default
        const admin = localStorage.getItem(this.adminKey);
        if (!admin) {
            const defaultAdmin = {
                username: 'admin',
                password: 'admin123', // Default password
                email: 's.nautiyalpabo@gmail.com',
                role: 'admin'
            };
            localStorage.setItem(this.adminKey, JSON.stringify(defaultAdmin));
        }
    }

    /**
     * Admin login (using email)
     */
    adminLogin(email, password) {
        try {
            const adminData = JSON.parse(localStorage.getItem(this.adminKey));
            
            if (!adminData) {
                return { success: false, error: 'Admin account not found' };
            }

            if (adminData.email === email && adminData.password === password) {
                // Create session
                const session = {
                    userId: 'admin',
                    email: email,
                    username: adminData.username,
                    role: 'admin',
                    loginTime: new Date().toISOString()
                };
                localStorage.setItem(this.sessionKey, JSON.stringify(session));
                return { success: true, user: session };
            } else {
                return { success: false, error: 'Invalid email or password' };
            }
        } catch (error) {
            console.error('Admin login error:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Check if admin is logged in
     */
    isAdminLoggedIn() {
        try {
            const session = localStorage.getItem(this.sessionKey);
            if (!session) return false;
            
            const sessionData = JSON.parse(session);
            return sessionData.role === 'admin';
        } catch (error) {
            return false;
        }
    }

    /**
     * Get current session
     */
    getCurrentSession() {
        try {
            const session = localStorage.getItem(this.sessionKey);
            if (!session) return null;
            return JSON.parse(session);
        } catch (error) {
            return null;
        }
    }

    /**
     * Logout
     */
    logout() {
        localStorage.removeItem(this.sessionKey);
        return { success: true };
    }

    /**
     * Change admin password
     */
    changeAdminPassword(oldPassword, newPassword) {
        try {
            const adminData = JSON.parse(localStorage.getItem(this.adminKey));
            
            if (!adminData) {
                return { success: false, error: 'Admin account not found' };
            }

            if (adminData.password !== oldPassword) {
                return { success: false, error: 'Old password is incorrect' };
            }

            adminData.password = newPassword;
            localStorage.setItem(this.adminKey, JSON.stringify(adminData));
            return { success: true, message: 'Password changed successfully' };
        } catch (error) {
            console.error('Error changing password:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Update admin email
     */
    updateAdminEmail(email) {
        try {
            const adminData = JSON.parse(localStorage.getItem(this.adminKey));
            
            if (!adminData) {
                return { success: false, error: 'Admin account not found' };
            }

            adminData.email = email;
            localStorage.setItem(this.adminKey, JSON.stringify(adminData));
            return { success: true, message: 'Email updated successfully' };
        } catch (error) {
            console.error('Error updating email:', error);
            return { success: false, error: error.message };
        }
    }
}

// Initialize global instance
const authSystem = new AuthSystem();
