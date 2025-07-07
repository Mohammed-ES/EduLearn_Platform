<?php
/**
 * UIRenderer Class
 * Handles the rendering of user interface components
 */
class UIRenderer {
    private $user_data;
    private $user_initials;
    private $csrf_token;
    
    public function __construct($user_data, $user_initials, $csrf_token) {
        $this->user_data = $user_data;
        $this->user_initials = $user_initials;
        $this->csrf_token = $csrf_token;
    }
    
    /**
     * Render page header
     */
    public function renderHeader() {
        return '
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <a href="student_dashboard.php" class="logo">
                        <i class="fas fa-graduation-cap"></i>
                        EduLearn
                    </a>
                    
                    <a href="student_dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </header>';
    }
    
    /**
     * Render settings header
     */
    public function renderSettingsHeader() {
        return '
        <div class="settings-header">
            <div class="profile-avatar">
                ' . htmlspecialchars($this->user_initials) . '
            </div>
            <h1 class="settings-title">Account Settings</h1>
            <p class="settings-subtitle">Manage your profile information and security settings</p>
        </div>';
    }
    
    /**
     * Render notification message
     */
    public function renderNotification($message, $type) {
        if (empty($message)) return '';
        
        $icon = $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        return '
        <div class="notification ' . htmlspecialchars($type) . '">
            <i class="fas ' . $icon . '"></i>
            ' . htmlspecialchars($message) . '
        </div>';
    }
    
    /**
     * Render profile form section
     */
    public function renderProfileForm() {
        return '
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Profile Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="fullname">
                        <i class="fas fa-id-card"></i>
                        Full Name
                    </label>
                    <input type="text" id="fullname" name="fullname" class="form-input" 
                           value="' . htmlspecialchars($this->user_data['fullname']) . '" 
                           placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-at"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" class="form-input" 
                           value="' . htmlspecialchars($this->user_data['username']) . '" 
                           placeholder="Enter username" required>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="' . htmlspecialchars($this->user_data['email']) . '" 
                           disabled>
                    <small style="color: var(--text-light); font-size: 12px; margin-top: 4px; display: block;">
                        Email cannot be changed. Contact administrator if needed.
                    </small>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render security form section
     */
    public function renderSecurityForm() {
        return '
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-shield-alt"></i>
                Security Settings
            </h3>

            <div class="security-info">
                <h4><i class="fas fa-info-circle"></i> Password Requirements</h4>
                <p>• Minimum 6 characters<br>
                • Must contain uppercase, lowercase, and numbers<br>
                • Leave fields empty to keep current password<br>
                • You must enter your current password to change it</p>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="current_password">
                        <i class="fas fa-key"></i>
                        Current Password
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" id="current_password" name="current_password" class="form-input" 
                               placeholder="Enter current password to change">
                        <button type="button" class="password-toggle" onclick="togglePassword(\'current_password\')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">
                        <i class="fas fa-lock"></i>
                        New Password
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-input" 
                               placeholder="Enter new password">
                        <button type="button" class="password-toggle" onclick="togglePassword(\'new_password\')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirm Password
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               placeholder="Confirm new password">
                        <button type="button" class="password-toggle" onclick="togglePassword(\'confirm_password\')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render submit button with CSRF token
     */
    public function renderSubmitButton() {
        return '
        <input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->csrf_token) . '">
        <button type="submit" class="btn-primary" id="submitBtn">
            <i class="fas fa-save"></i>
            Update Settings
        </button>';
    }
    
    /**
     * Render animated background
     */
    public function renderAnimatedBackground() {
        return '
        <div class="animated-background">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="floating-shape shape-4"></div>
        </div>';
    }
      /**
     * Render sidebar with navigation
     */
    public function renderSidebar() {
        return '
        <div class="settings-sidebar">
            <div class="user-profile">
                <div class="profile-avatar-sidebar">
                    ' . htmlspecialchars($this->user_initials) . '
                </div>
                <div class="user-name">' . htmlspecialchars($this->user_data['fullname']) . '</div>
                <div class="user-email">' . htmlspecialchars($this->user_data['email']) . '</div>
            </div>
            
            <nav>
                <ul class="sidebar-nav">
                    <li class="nav-item">
                        <a href="#profile" class="nav-link active" id="profileTab">
                            <i class="fas fa-user"></i>
                            Profile Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#security" class="nav-link" id="securityTab">
                            <i class="fas fa-shield-alt"></i>
                            Security
                        </a>
                    </li>
                </ul>
            </nav>
        </div>';
    }
    
    /**
     * Render main settings header
     */
    public function renderSettingsMainHeader() {
        return '
        <div class="settings-header-main">
            <h1 class="settings-title-main">
                <i class="fas fa-users"></i>
                Profile Settings
            </h1>
            <p class="settings-subtitle-main">Manage your personal information and account settings</p>
        </div>';
    }
    
    /**
     * Render profile form section (updated for new layout)
     */
    public function renderProfileFormNew() {
        return '
        <div id="profileSection" class="settings-section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Personal Information
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="fullname">Full Name</label>
                    <div class="form-input-wrapper">
                        <input type="text" id="fullname" name="fullname" class="form-input" 
                               value="' . htmlspecialchars($this->user_data['fullname']) . '" 
                               placeholder="Admin User" required>
                        <i class="fas fa-user form-input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="form-input-wrapper">
                        <input type="text" id="username" name="username" class="form-input" 
                               value="' . htmlspecialchars($this->user_data['username']) . '" 
                               placeholder="admin" required>
                        <i class="fas fa-at form-input-icon"></i>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="form-input-wrapper">
                        <input type="email" id="email" name="email" class="form-input" 
                               value="' . htmlspecialchars($this->user_data['email']) . '" 
                               disabled>
                        <i class="fas fa-envelope form-input-icon"></i>
                    </div>
                    <small style="color: var(--text-light); font-size: 12px; margin-top: 4px; display: block;">
                        Email cannot be changed. Contact administrator if needed.
                    </small>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="phone">Phone Number</label>
                    <div class="form-input-wrapper">
                        <input type="tel" id="phone" name="phone" class="form-input" 
                               placeholder="Enter phone number">
                        <i class="fas fa-phone form-input-icon"></i>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render security form section (updated for new layout)
     */
    public function renderSecurityFormNew() {
        return '
        <div id="securitySection" class="settings-section" style="display: none;">
            <h3 class="section-title">
                <i class="fas fa-shield-alt"></i>
                Security Settings
            </h3>

            <div class="security-info">
                <h4><i class="fas fa-info-circle"></i> Password Requirements</h4>
                <p>• Minimum 6 characters<br>
                • Must contain uppercase, lowercase, and numbers<br>
                • Leave fields empty to keep current password<br>
                • You must enter your current password to change it</p>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="current_password">Change Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="current_password" name="current_password" class="form-input" 
                               placeholder="Leave empty to keep current password">
                        <button type="button" class="password-toggle" onclick="togglePassword(\'current_password\')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small style="color: var(--text-light); font-size: 12px; margin-top: 4px; display: block;">
                        Leave empty to keep current password
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="new_password" name="new_password" class="form-input" 
                               placeholder="Enter new password">
                        <button type="button" class="password-toggle" onclick="togglePassword(\'new_password\')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                               placeholder="Confirm new password">
                        <button type="button" class="password-toggle" onclick="togglePassword(\'confirm_password\')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render action buttons
     */
    public function renderActionButtons() {
        return '
        <div class="action-buttons">
            <input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->csrf_token) . '">
            <button type="button" class="btn-secondary" onclick="resetForm()">
                <i class="fas fa-undo"></i>
                Reset Changes
            </button>
            <button type="submit" class="btn-primary" id="submitBtn">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
        </div>';
    }
    
    /**
     * Render complete new layout
     */
    public function renderNewLayout($message = '', $messageType = '') {
        return '
        <div class="settings-layout">
            ' . $this->renderSidebar() . '
            
            <div class="settings-container">
                ' . $this->renderSettingsMainHeader() . '
                
                <form method="POST" action="" id="settingsForm">
                    <div class="settings-body">
                        ' . $this->renderNotification($message, $messageType) . '
                        ' . $this->renderProfileFormNew() . '
                        ' . $this->renderSecurityFormNew() . '
                    </div>
                    ' . $this->renderActionButtons() . '
                </form>
            </div>
        </div>';
    }
}
?>
