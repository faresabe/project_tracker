<div style="padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2 style="margin-bottom: 20px;">Account Settings</h2>
        
        <form method="POST" class="settings-form ajax-form" data-ajax="true">
            <div class="settings-section">
                <h3>Profile Information</h3>
                
                <div class="form-group">
                    <label for="userName">Full Name *</label>
                    <input type="text" id="userName" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($user['name']); ?>">
                    <div class="field-error-container"></div>
                </div>
                
                <div class="form-group">
                    <label for="userEmail">Email Address *</label>
                    <input type="email" id="userEmail" name="email" class="form-control" required 
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                    <div class="field-error-container"></div>
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Change Password</h3>
                <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    Leave password fields empty if you don't want to change your password.
                </p>
                
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password" class="form-control">
                    <div class="field-error-container"></div>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="new_password" class="form-control" minlength="6">
                    <small style="color: #666;">Minimum 6 characters</small>
                    <div class="field-error-container"></div>
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Preferences</h3>
                
                <div class="form-group">
                    <label for="theme">Theme</label>
                    <select id="theme" name="theme" class="form-control">
                        <option value="light" <?php echo $user['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $user['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="email_notifications" <?php echo $user['email_notifications'] ? 'checked' : ''; ?>>
                        <span class="checkmark"></span>
                        Receive email notifications
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <span class="btn-text">Save Changes</span>
                    <span class="btn-loading" style="display: none;">Saving...</span>
                </button>
                <a href="?page=home" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .settings-form {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        margin-bottom: 30px;
    }

    .settings-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .settings-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .settings-section h3 {
        margin-bottom: 15px;
        color: #2c3e50;
        font-size: 18px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #2c3e50;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-weight: normal !important;
    }

    .checkbox-label input[type="checkbox"] {
        margin-right: 8px;
        transform: scale(1.2);
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }

    .form-control.error {
        border-color: #dc3545;
        box-shadow: 0 0 0 2px rgba(220,53,69,0.25);
    }

    .field-error-container {
        min-height: 20px;
    }

    .field-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.3s;
        position: relative;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        background: #0056b3;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #545b62;
    }

    .btn-loading {
        display: none;
    }

    .btn.loading .btn-text {
        display: none;
    }

    .btn.loading .btn-loading {
        display: inline;
    }

    @media (max-width: 768px) {
        .settings-form {
            padding: 20px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>
